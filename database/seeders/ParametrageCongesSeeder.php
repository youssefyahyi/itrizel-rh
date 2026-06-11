<?php

namespace Database\Seeders;

use App\Models\ParametrageRh;
use Illuminate\Database\Seeder;

/**
 * Paramètres congés & temps de travail — Maroc (Code du Travail).
 * Usage : php artisan db:seed --class=ParametrageCongesSeeder
 */
class ParametrageCongesSeeder extends Seeder
{
    public function run(): void
    {
        $params = [
            ['groupe' => 'rh', 'cle' => 'rh.conges_jours_par_mois',
             'libelle'     => 'Congés annuels — jours acquis par mois',
             'valeur'      => '1.5',
             'description' => 'Droit légal marocain : 1,5 jour ouvrable par mois de travail effectif (18 j/an). Art. 231 C.T.'],

            ['groupe' => 'rh', 'cle' => 'rh.heures_semaine_base',
             'libelle'     => 'Durée légale hebdomadaire (heures)',
             'valeur'      => '44',
             'description' => 'Durée légale de travail au Maroc : 44 h/semaine (2 288 h/an). Art. 184 C.T.'],

            ['groupe' => 'rh', 'cle' => 'rh.quotites_disponibles',
             'libelle'     => 'Quotités de travail disponibles (%)',
             'valeur'      => json_encode([100, 80, 75, 50, 25]),
             'description' => 'Liste des quotités proposées dans le formulaire employé. JSON array d\'entiers.'],
        ];

        foreach ($params as $p) {
            ParametrageRh::updateOrCreate(
                ['cle' => $p['cle']],
                ['groupe' => $p['groupe'], 'libelle' => $p['libelle'],
                 'valeur' => $p['valeur'], 'description' => $p['description']]
            );
        }

        $this->command->info('Paramètres congés seedés (' . count($params) . ' entrées).');
    }
}
