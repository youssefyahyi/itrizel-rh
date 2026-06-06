<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poste extends Model
{
    protected $fillable = ['numero', 'nom', 'competences', 'description', 'actif'];

    protected $casts = [
        'competences' => 'array',
        'actif'       => 'boolean',
    ];

    // ── Relations ──────────────────────────────────────────────────
    public function employes(): HasMany
    {
        return $this->hasMany(Employe::class);
    }

    public function contrats(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }

    // ── Helpers ────────────────────────────────────────────────────

    /**
     * Suggère le prochain numéro de poste (P001, P002…).
     */
    public static function nextNumero(): string
    {
        $last = static::orderByDesc('id')->value('numero');
        if (!$last) return 'P001';
        preg_match('/(\d+)$/', $last, $m);
        $seq = isset($m[1]) ? (int) $m[1] + 1 : 1;
        return 'P' . str_pad($seq, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Convertit un bloc textarea (une compétence par ligne) en tableau.
     */
    public static function parseCompetences(?string $text): array
    {
        if (!$text) return [];
        return array_values(array_filter(
            array_map('trim', explode("\n", str_replace("\r\n", "\n", $text))),
            fn($line) => $line !== ''
        ));
    }

    /**
     * Retourne les compétences sous forme de texte (une par ligne) pour la textarea.
     */
    public function getCompetencesTexteAttribute(): string
    {
        return implode("\n", $this->competences ?? []);
    }
}
