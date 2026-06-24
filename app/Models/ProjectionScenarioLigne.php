<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\UniteOrganisationnelle;

class ProjectionScenarioLigne extends Model
{
    protected $fillable = [
        'scenario_id', 'type', 'mois_effet',
        'pourcentage', 'categorie_id', 'salaire_estime', 'employe_id', 'unite_id',
    ];

    protected $casts = [
        'mois_effet'     => 'date',
        'pourcentage'    => 'float',
        'salaire_estime' => 'float',
    ];

    const TYPES = [
        'revalorisation' => 'Revalorisation',
        'embauche'       => 'Embauche planifiée',
        'depart'         => 'Départ planifié',
    ];

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(ProjectionScenario::class);
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(CategorieEmploye::class, 'categorie_id');
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class);
    }

    public function unite(): BelongsTo
    {
        return $this->belongsTo(UniteOrganisationnelle::class, 'unite_id');
    }

    public function getLibelleAttribute(): string
    {
        return match ($this->type) {
            'revalorisation' => ($this->pourcentage > 0 ? '+' : '') . $this->pourcentage . '% — '
                . ($this->employe
                    ? $this->employe->nom_complet
                    : ($this->categorie ? $this->categorie->nom : 'global'))
                . ' dès ' . $this->mois_effet->format('m/Y'),
            'embauche' => 'Embauche '
                . ($this->categorie ? $this->categorie->nom : '—')
                . ' ' . number_format($this->salaire_estime, 0, ',', ' ') . ' DH'
                . ($this->unite ? ' / ' . $this->unite->nom : '')
                . ' dès ' . $this->mois_effet->format('m/Y'),
            'depart' => 'Départ '
                . ($this->employe ? $this->employe->nom_complet : '—')
                . ' dès ' . $this->mois_effet->format('m/Y'),
            default => $this->type,
        };
    }
}
