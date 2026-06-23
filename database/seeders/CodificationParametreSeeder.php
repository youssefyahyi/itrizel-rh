<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodificationParametreSeeder extends Seeder
{
    public function run(): void
    {
        $annee = (int) date('Y');

        // Compteurs initialisés depuis les données existantes
        $compteurMat = $this->extractCompteur(
            DB::table('employes')->max('matricule'), 4
        );
        $compteurCtr = $this->extractCompteur(
            DB::table('contrats')->max('reference'), 4
        );
        $compteurAvn = $this->extractCompteur(
            DB::table('avenants')->max('reference'), 2
        );

        $configs = [
            [
                'entite'          => 'matricule',
                'libelle'         => 'Matricule employé',
                'prefixe'         => 'EMP',
                'separateur'      => '-',
                'inclure_annee'   => true,
                'format_annee'    => 'YYYY',
                'nb_chiffres'     => 4,
                'reset_annuel'    => true,
                'compteur_courant'=> $compteurMat,
                'annee_courante'  => $annee,
            ],
            [
                'entite'          => 'contrat',
                'libelle'         => 'Référence contrat',
                'prefixe'         => 'CTR',
                'separateur'      => '-',
                'inclure_annee'   => true,
                'format_annee'    => 'YYYY',
                'nb_chiffres'     => 4,
                'reset_annuel'    => true,
                'compteur_courant'=> $compteurCtr,
                'annee_courante'  => $annee,
            ],
            [
                'entite'          => 'avenant',
                'libelle'         => 'Numéro avenant',
                'prefixe'         => 'AV',
                'separateur'      => '-',
                'inclure_annee'   => false,
                'format_annee'    => 'YYYY',
                'nb_chiffres'     => 2,
                'reset_annuel'    => false,
                'compteur_courant'=> $compteurAvn,
                'annee_courante'  => null,
            ],
        ];

        foreach ($configs as $config) {
            DB::table('codification_parametres')->updateOrInsert(
                ['entite' => $config['entite']],
                array_merge($config, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }

    private function extractCompteur(?string $dernierCode, int $digits): int
    {
        if (!$dernierCode) return 0;
        $seq = (int) substr($dernierCode, -$digits);
        return $seq > 0 ? $seq : 0;
    }
}
