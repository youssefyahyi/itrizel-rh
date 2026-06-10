<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CategorieEmploye;
use App\Models\FichePoste;

/**
 * Trait HasFicheData
 *
 * Fournit les données du référentiel des emplois à tout controller
 * qui en a besoin (PersonnelController, ContratController…).
 *
 * Usage : use HasFicheData; puis [$fiches, $categories] = $this->ficheData();
 */
trait HasFicheData
{
    /**
     * Retourne [Collection $fiches, Collection $categories]
     * triées dans l'ordre d'affichage naturel.
     */
    protected function ficheData(): array
    {
        $fiches = FichePoste::with(['categorie', 'fonction', 'poste'])
            ->where('actif', true)
            ->get()
            ->sortBy([
                fn($f) => $f->categorie?->ordre ?? 99,
                fn($f) => $f->fonction?->nom,
                fn($f) => $f->poste?->nom,
            ]);

        $categories = CategorieEmploye::where('actif', true)
            ->orderBy('ordre')
            ->get();

        return [$fiches, $categories];
    }
}
