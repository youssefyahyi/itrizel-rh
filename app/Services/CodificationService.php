<?php

namespace App\Services;

use App\Models\CodificationParametre;
use Illuminate\Support\Facades\DB;

class CodificationService
{
    /**
     * Génère le prochain code pour l'entité donnée.
     * Pour 'avenant', passer ['parent_ref' => $contrat->reference] dans $contexte.
     */
    public function generer(string $entite, array $contexte = []): string
    {
        return DB::transaction(function () use ($entite, $contexte) {
            $config = CodificationParametre::where('entite', $entite)
                ->lockForUpdate()
                ->firstOrFail();

            $annee = (int) now()->format('Y');

            // Remise à zéro annuelle si l'année a changé
            if ($config->reset_annuel && $config->annee_courante !== $annee) {
                $config->compteur_courant = 0;
                $config->annee_courante   = $annee;
            }

            $config->compteur_courant++;
            $config->save();

            return $this->formater($config, $contexte);
        });
    }

    /**
     * Aperçu du prochain code sans incrémenter le compteur.
     */
    public function apercu(string $entite, array $contexte = []): string
    {
        $config = CodificationParametre::where('entite', $entite)->firstOrFail();
        return $this->formater($config, $contexte, previsualisation: true);
    }

    private function formater(CodificationParametre $config, array $contexte, bool $previsualisation = false): string
    {
        $parties = [];

        if ($config->prefixe !== '') {
            $parties[] = $config->prefixe;
        }

        if ($config->inclure_annee) {
            $parties[] = $config->format_annee === 'YY'
                ? now()->format('y')
                : now()->format('Y');
        }

        // Pour les avenants : insérer la référence du contrat parent
        if ($entite = $config->entite and $entite === 'avenant' && isset($contexte['parent_ref'])) {
            $parties[] = $contexte['parent_ref'];
        }

        $seq = $previsualisation
            ? $config->compteur_courant + 1
            : $config->compteur_courant;

        $parties[] = str_pad($seq, $config->nb_chiffres, '0', STR_PAD_LEFT);

        return implode($config->separateur, $parties);
    }
}
