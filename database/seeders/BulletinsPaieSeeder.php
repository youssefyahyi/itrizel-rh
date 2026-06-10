<?php

namespace Database\Seeders;

use App\Models\BulletinPaie;
use App\Models\Employe;
use App\Services\PaieCalculateur;
use Illuminate\Database\Seeder;

/**
 * Seeder bulletins de paie — ALF Sebou
 *
 * Génère 5 mois de bulletins (Janvier → Mai 2026) pour tous les
 * employés actifs. Utilise PaieCalculateur pour tous les calculs
 * (CNSS/AMO/CIMR/IR + prime d'ancienneté + cotisations patronales).
 *
 * - Jan-Avr 2026 : statut "paye"  (date_paiement = dernier jour du mois)
 * - Mai 2026     : statut "valide"
 *
 * Idempotent : updateOrCreate sur (employe_id, periode_mois, periode_annee).
 *
 * PRÉ-REQUIS : ParametragePaieSeeder + AlfSebouEmployesSeeder exécutés.
 * Usage      : php artisan db:seed --class=BulletinsPaieSeeder
 */
class BulletinsPaieSeeder extends Seeder
{
    // Périodes à générer : [mois, annee, statut, date_paiement]
    private array $periodes = [
        [1, 2026, 'paye',   '2026-01-31'],
        [2, 2026, 'paye',   '2026-02-28'],
        [3, 2026, 'paye',   '2026-03-31'],
        [4, 2026, 'paye',   '2026-04-30'],
        [5, 2026, 'valide', null],
    ];

    // Primes ponctuelles par CIN : [CIN => [mois => montant]]
    private array $primesParCin = [
        'BE100001' => [1 => 2000, 4 => 1500],
        'BK100002' => [1 => 1500],
        'BB100003' => [1 => 1000],
        'BE100015' => [1 => 2000, 4 => 2000],
        'BL100016' => [4 => 1000],
        'BG100017' => [4 => 1000],
        'BA100043' => [1 =>  500],
        'BK100044' => [1 =>  500],
    ];

    public function run(): void
    {
        $this->command->info('⏳ Initialisation du calculateur paie…');
        $calc = new PaieCalculateur();

        $employes = Employe::where('statut', 'actif')
            ->with('contratActif')
            ->orderBy('id')
            ->get();

        if ($employes->isEmpty()) {
            $this->command->error('Aucun employé actif trouvé. Lancez AlfSebouEmployesSeeder d\'abord.');
            return;
        }

        $this->command->info("  → {$employes->count()} employés actifs");
        $this->command->info("  → " . count($this->periodes) . " périodes");

        $created = 0;
        $updated = 0;

        foreach ($this->periodes as [$mois, $annee, $statut, $datePaiement]) {
            foreach ($employes as $emp) {
                $salaireBase = $this->getSalaireBase($emp);
                $primes      = (float)($this->primesParCin[$emp->cin][$mois] ?? 0);

                // Calcul complet via le service centralisé
                $c = $calc->calculer($salaireBase, $primes, $emp, $mois, $annee);

                $exists = BulletinPaie::where('employe_id',   $emp->id)
                    ->where('periode_mois',  $mois)
                    ->where('periode_annee', $annee)
                    ->exists();

                BulletinPaie::updateOrCreate(
                    ['employe_id' => $emp->id, 'periode_mois' => $mois, 'periode_annee' => $annee],
                    [
                        'salaire_base'     => $c['salaire_base'],
                        'prime_anciennete' => $c['prime_anciennete'],
                        'total_primes'     => $c['total_primes'],
                        'cnss_salarie'     => $c['cnss_salarie'],
                        'amo_salarie'      => $c['amo_salarie'],
                        'cimr_salarie'     => $c['cimr_salarie'],
                        'ir_mensuel'       => $c['ir_mensuel'],
                        'avances_deduites' => $c['avances_deduites'],
                        'cnss_patronal'    => $c['cnss_patronal'],
                        'amo_patronal'     => $c['amo_patronal'],
                        'cimr_patronal'    => $c['cimr_patronal'],
                        'total_patronal'   => $c['total_patronal'],
                        'net_imposable'    => $c['net_imposable'],
                        'total_retenues'   => $c['total_retenues'],
                        'net_a_payer'      => $c['net_a_payer'],
                        'jours_travailles' => 26,
                        'heures_travailles'=> 191,
                        'statut'           => $statut,
                        'date_paiement'    => $datePaiement,
                        'created_by'       => 1,
                    ]
                );

                $exists ? $updated++ : $created++;
            }
        }

        $total = $created + $updated;
        $this->command->info("✅ Terminé — {$total} bulletins ({$created} créés, {$updated} mis à jour).");

        $moisNoms = ['','Janvier','Février','Mars','Avril','Mai','Juin',
                     'Juillet','Août','Septembre','Octobre','Novembre','Décembre'];

        $this->command->table(
            ['Période', 'Bulletins', 'Masse brute (DH)', 'Masse nette (DH)', 'Coût patronal (DH)', 'Statut'],
            collect($this->periodes)->map(function ($p) use ($moisNoms) {
                [$mois, $annee, $statut] = $p;
                $bulletins = BulletinPaie::where('periode_mois', $mois)
                    ->where('periode_annee', $annee)->get();
                $brut = $bulletins->sum(fn($b) => $b->salaire_base + $b->prime_anciennete + $b->total_primes);
                return [
                    $moisNoms[$mois] . ' ' . $annee,
                    $bulletins->count(),
                    number_format($brut, 0, ',', ' '),
                    number_format($bulletins->sum('net_a_payer'), 0, ',', ' '),
                    number_format($bulletins->sum('total_patronal'), 0, ',', ' '),
                    ucfirst($statut),
                ];
            })->toArray()
        );
    }

    private function getSalaireBase(Employe $emp): float
    {
        if ($emp->contratActif && $emp->contratActif->salaire_base > 0) {
            return (float)$emp->contratActif->salaire_base;
        }
        return 3000.0; // SMIG marocain 2026
    }
}
