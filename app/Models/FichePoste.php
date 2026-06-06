<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichePoste extends Model
{
    protected $table    = 'fiches_poste';
    protected $fillable = ['categorie_id', 'fonction_id', 'poste_id', 'actif'];
    protected $casts    = ['actif' => 'boolean'];

    // ── Relations ──────────────────────────────────────────────────
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieEmploye::class, 'categorie_id');
    }

    public function fonction(): BelongsTo
    {
        return $this->belongsTo(Fonction::class, 'fonction_id');
    }

    public function poste(): BelongsTo
    {
        return $this->belongsTo(Poste::class, 'poste_id');
    }

    // ── Accessor ───────────────────────────────────────────────────
    public function getLabelAttribute(): string
    {
        return ($this->categorie?->nom ?? '—')
             . ' › ' . ($this->fonction?->nom  ?? '—')
             . ' › ' . ($this->poste?->nom     ?? '—');
    }
}
