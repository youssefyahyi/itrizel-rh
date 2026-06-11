<?php

namespace Database\Seeders;

use App\Models\JourFerie;
use Illuminate\Database\Seeder;

/**
 * Jours fériés marocains 2025 et 2026.
 * Fêtes variables : dates sous réserve d'observation de la lune.
 */
class JoursFeriesSeeder extends Seeder
{
    public function run(): void
    {
        $feries = [];

        // ── Fêtes fixes — même date chaque année ──────────────────────
        foreach ([2025, 2026] as $annee) {
            $fixes = [
                ['01-01', "Nouvel An"],
                ['01-11', "Manifeste de l'Indépendance"],
                ['05-01', "Fête du Travail"],
                ['07-30', "Fête du Trône"],
                ['08-14', "Allégeance (Oued Ed-Dahab)"],
                ['08-20', "Révolution du Roi et du Peuple"],
                ['08-21', "Fête de la Jeunesse"],
                ['11-06', "Marche Verte"],
                ['11-18', "Fête de l'Indépendance"],
            ];
            foreach ($fixes as [$mmjj, $libelle]) {
                $feries[] = ['date' => "{$annee}-{$mmjj}", 'libelle' => $libelle, 'annee' => $annee, 'type' => 'fixe'];
            }
        }

        // ── Fêtes variables 2025 ──────────────────────────────────────
        $variables2025 = [
            ['2025-03-30', "Aïd Al Fitr (1er jour)"],
            ['2025-03-31', "Aïd Al Fitr (2ème jour)"],
            ['2025-06-07', "Aïd Al Adha (1er jour)"],
            ['2025-06-08', "Aïd Al Adha (2ème jour)"],
            ['2025-06-27', "1er Moharram 1447"],
            ['2025-09-05', "Mawlid Annabawi 1447"],
        ];
        foreach ($variables2025 as [$date, $libelle]) {
            $feries[] = ['date' => $date, 'libelle' => $libelle, 'annee' => 2025, 'type' => 'variable'];
        }

        // ── Fêtes variables 2026 ──────────────────────────────────────
        $variables2026 = [
            ['2026-03-19', "Aïd Al Fitr (1er jour)"],
            ['2026-03-20', "Aïd Al Fitr (2ème jour)"],
            ['2026-05-27', "Aïd Al Adha (1er jour)"],
            ['2026-05-28', "Aïd Al Adha (2ème jour)"],
            ['2026-06-17', "1er Moharram 1448"],
            ['2026-08-25', "Mawlid Annabawi 1448"],
        ];
        foreach ($variables2026 as [$date, $libelle]) {
            $feries[] = ['date' => $date, 'libelle' => $libelle, 'annee' => 2026, 'type' => 'variable'];
        }

        foreach ($feries as $f) {
            JourFerie::updateOrCreate(['date' => $f['date']], [
                'libelle' => $f['libelle'],
                'annee'   => $f['annee'],
                'type'    => $f['type'],
            ]);
        }

        $this->command->info('Jours fériés seedés : ' . count($feries) . ' entrées (2025-2026).');
    }
}
