<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class UniteOrganisationnelle extends Model
{
    protected $table = 'unites_organisationnelles';

    protected $fillable = [
        'code', 'nom', 'type', 'parent_id', 'responsable_id',
        'description', 'ordre', 'actif',
    ];

    protected $casts = [
        'actif' => 'boolean',
    ];

    const TYPES = [
        'direction'   => ['label' => 'Direction',    'icon' => 'direction',   'color' => '#7C3AED'],
        'departement' => ['label' => 'Département',  'icon' => 'departement', 'color' => '#2563EB'],
        'service'     => ['label' => 'Service',      'icon' => 'service',     'color' => '#0891B2'],
        'equipe'      => ['label' => 'Équipe',       'icon' => 'equipe',      'color' => '#059669'],
        'autre'       => ['label' => 'Autre',        'icon' => 'autre',       'color' => '#6B7280'],
    ];

    // ── Accessors ──────────────────────────────────────────────────
    public function getTypeLibelleAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPES[$this->type]['color'] ?? '#6B7280';
    }

    public function getCheminAttribute(): string
    {
        $chemin = [];
        $current = $this;
        while ($current) {
            array_unshift($chemin, $current->nom);
            $current = $current->parent;
        }
        return implode(' › ', $chemin);
    }

    public function getNiveauAttribute(): int
    {
        $niveau = 0;
        $current = $this;
        while ($current->parent_id) {
            $niveau++;
            $current = $current->parent;
        }
        return $niveau;
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeActifs($query)  { return $query->where('actif', true); }
    public function scopeRacines($query) { return $query->whereNull('parent_id'); }

    // ── Relations ──────────────────────────────────────────────────
    public function parent(): BelongsTo
    {
        return $this->belongsTo(UniteOrganisationnelle::class, 'parent_id');
    }

    public function enfants(): HasMany
    {
        return $this->hasMany(UniteOrganisationnelle::class, 'parent_id')
                    ->orderBy('ordre')->orderBy('nom');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'responsable_id');
    }

    public function employes(): HasMany
    {
        return $this->hasMany(Employe::class, 'unite_id');
    }

    // ── Helpers ────────────────────────────────────────────────────

    // Tous les IDs enfants récursifs (pour éviter les références circulaires)
    public function tousEnfantsIds(): Collection
    {
        $ids = collect();
        $this->collectEnfantsIds($this->id, $ids);
        return $ids;
    }

    private function collectEnfantsIds(int $parentId, Collection &$ids): void
    {
        $enfants = static::where('parent_id', $parentId)->pluck('id');
        foreach ($enfants as $id) {
            $ids->push($id);
            $this->collectEnfantsIds($id, $ids);
        }
    }

    // Nombre d'employés dans cette unité ET ses sous-unités
    public function effectifTotal(): int
    {
        $ids = $this->tousEnfantsIds()->push($this->id);
        return Employe::whereIn('unite_id', $ids)->count();
    }
}
