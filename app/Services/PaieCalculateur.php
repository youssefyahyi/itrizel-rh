<?php

namespace App\Services;

use App\Models\Employe;
use App\Models\ParametrageRh;
use Carbon\Carbon;

/**
 * Service de calcul bulletin de paie marocain.
 *
 * Prend en entrée : salaire de base, primes, employé, période.
 * Retourne un tableau complet de toutes les rubriques calculées.
 */
class PaieCalculateur
{
    // ── Taux chargés depuis parametrage_rh ────────────────────────────
    private float $cnssTauxSal;
    private float $cnssTauxPat;
    private float $cnssPlafond;
    private float $amoTauxSal;
    private float $amoTauxPat;
    private bool  $cimrActif;
    private float $cimrTauxSal;
    private float $cimrTauxPat;
    private float $abatTaux;
    private float $abatMinAn;
    private float $abatMaxAn;
    private float $deducParCharge;
    private int   $deducMaxPersonnes;
    private array $bareme;

    public function __construct()
    {
        $this->cnssTauxSal       = ParametrageRh::getFloat('paie.cnss_taux_salarie',          4.48);
        $this->cnssTauxPat       = ParametrageRh::getFloat('paie.cnss_taux_patron',           10.48);
        $this->cnssPlafond       = ParametrageRh::getFloat('paie.cnss_plafond',               6000);
        $this->amoTauxSal        = ParametrageRh::getFloat('paie.amo_taux_salarie',           2.26);
        $this->amoTauxPat        = ParametrageRh::getFloat('paie.amo_taux_patron',            2.26);
        $this->cimrActif         = (bool)(int) ParametrageRh::get('paie.cimr_actif',           '0');
        $this->cimrTauxSal       = ParametrageRh::getFloat('paie.cimr_taux_salarie',          3.00);
        $this->cimrTauxPat       = ParametrageRh::getFloat('paie.cimr_taux_patron',           6.00);
        $this->abatTaux          = ParametrageRh::getFloat('paie.ir_abattement_taux',           20);
        $this->abatMinAn         = ParametrageRh::getFloat('paie.ir_abattement_min',          2500);
        $this->abatMaxAn         = ParametrageRh::getFloat('paie.ir_abattement_max',         30000);
        $this->deducParCharge    = ParametrageRh::getFloat('paie.ir_deduction_charge_annuel',  360);
        $this->deducMaxPersonnes = (int) ParametrageRh::get('paie.ir_deduction_max_personnes', '6');
        $this->bareme            = ParametrageRh::getJson('paie.ir_bareme', [
            ['min' =>      0, 'max' =>   30000, 'taux' =>  0, 'somme_a_deduire' =>     0],
            ['min' =>  30001, 'max' =>   50000, 'taux' => 10, 'somme_a_deduire' =>  3000],
            ['min' =>  50001, 'max' =>   60000, 'taux' => 20, 'somme_a_deduire' =>  8000],
            ['min' =>  60001, 'max' =>   80000, 'taux' => 30, 'somme_a_deduire' => 14000],
            ['min' =>  80001, 'max' =>  180000, 'taux' => 34, 'somme_a_deduire' => 17200],
            ['min' => 180001, 'max' =>    null, 'taux' => 38, 'somme_a_deduire' => 24400],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    /**
     * Calcule l'intégralité d'un bulletin de paie.
     *
     * @param  float   $salaireBase   Salaire de base contractuel
     * @param  float   $primes        Primes et indemnités imposables
     * @param  Employe $emp           Instance employé (pour ancienneté + charges familiales)
     * @param  int     $periodeMois   Mois du bulletin (1-12)
     * @param  int     $periodeAnnee  Année du bulletin
     * @return array
     */
    public function calculer(
        float $salaireBase,
        float $primes,
        Employe $emp,
        int $periodeMois,
        int $periodeAnnee
    ): array {
        // ── Prime d'ancienneté ────────────────────────────────────────
        $anciennete      = $this->ancienneteAns($emp, $periodeMois, $periodeAnnee);
        $tauxAnciennete  = $this->tauxAnciennete($anciennete);
        $primeAnciennete = round($salaireBase * $tauxAnciennete / 100, 2);

        // ── Brut imposable ────────────────────────────────────────────
        // Primes & indemnités + ancienneté + base
        $brut = $salaireBase + $primeAnciennete + $primes;

        // ── CNSS salarié + patronal ───────────────────────────────────
        $baseAssietCnss  = min($salaireBase, $this->cnssPlafond); // ancienneté exclue du plafond
        $cnssSal = round($baseAssietCnss * $this->cnssTauxSal / 100, 2);
        $cnssPat = round($baseAssietCnss * $this->cnssTauxPat / 100, 2);

        // ── AMO salarié + patronal ────────────────────────────────────
        $amoSal  = round($brut * $this->amoTauxSal / 100, 2);
        $amoPat  = round($brut * $this->amoTauxPat / 100, 2);

        // ── CIMR ─────────────────────────────────────────────────────
        $cimrSal = $this->cimrActif ? round($salaireBase * $this->cimrTauxSal / 100, 2) : 0.0;
        $cimrPat = $this->cimrActif ? round($salaireBase * $this->cimrTauxPat / 100, 2) : 0.0;

        // ── IR mensuel ────────────────────────────────────────────────
        // Base imposable = brut - CNSS sal - AMO sal
        $baseImposable   = max(0, $brut - $cnssSal - $amoSal);
        $abatCalc        = $baseImposable * $this->abatTaux / 100;
        $abatMoisMin     = $this->abatMinAn / 12;
        $abatMoisMax     = $this->abatMaxAn / 12;
        $abat            = max($abatMoisMin, min($abatMoisMax, $abatCalc));
        $netImpMois      = max(0, $baseImposable - $abat);
        $netImpAn        = $netImpMois * 12;

        // Barème progressif
        $irAn = 0.0;
        foreach ($this->bareme as $t) {
            if ($netImpAn > $t['min']) {
                $irAn = $netImpAn * $t['taux'] / 100 - $t['somme_a_deduire'];
            }
        }
        // Déductions charges familiales
        $conjoint    = in_array($emp->situation_familiale, ['marie']) ? 1 : 0;
        $nbCharges   = min($conjoint + (int)$emp->nombre_enfants, $this->deducMaxPersonnes);
        $irAn        = max(0.0, $irAn - $nbCharges * $this->deducParCharge);
        $ir          = round($irAn / 12, 2);

        // ── Totaux ───────────────────────────────────────────────────
        $totalRetenues = $cnssSal + $amoSal + $cimrSal + $ir;
        $totalPatronal = $cnssPat + $amoPat + $cimrPat;
        $netAPayer     = max(0, round($brut - $totalRetenues, 2));
        $coutEmployeur = round($brut + $totalPatronal, 2);

        return [
            // Gains
            'salaire_base'      => $salaireBase,
            'prime_anciennete'  => $primeAnciennete,
            'total_primes'      => $primes,
            // Cotisations salariales
            'cnss_salarie'      => $cnssSal,
            'amo_salarie'       => $amoSal,
            'cimr_salarie'      => $cimrSal,
            'ir_mensuel'        => $ir,
            'avances_deduites'  => 0.0,
            // Cotisations patronales
            'cnss_patronal'     => $cnssPat,
            'amo_patronal'      => $amoPat,
            'cimr_patronal'     => $cimrPat,
            // IR — base de calcul (pour affichage sur bulletin)
            'net_imposable'     => round($netImpMois, 2),
            // Totaux
            'total_retenues'    => round($totalRetenues, 2),
            'total_patronal'    => round($totalPatronal, 2),
            'net_a_payer'       => $netAPayer,
            // Infos affichage
            '_brut'             => round($brut, 2),
            '_anciennete_ans'   => $anciennete,
            '_taux_anciennete'  => $tauxAnciennete,
            '_base_assiet_cnss' => $baseAssietCnss,
            '_abat_applique'    => round($abat, 2),
            '_net_imp_an'       => round($netImpAn, 2),
            '_cout_employeur'   => $coutEmployeur,
            '_nb_charges'       => $nbCharges,
            // Taux utilisés (pour colonnes bulletin)
            '_cnss_taux_sal'    => $this->cnssTauxSal,
            '_cnss_taux_pat'    => $this->cnssTauxPat,
            '_amo_taux_sal'     => $this->amoTauxSal,
            '_amo_taux_pat'     => $this->amoTauxPat,
            '_cimr_taux_sal'    => $this->cimrActif ? $this->cimrTauxSal : 0,
            '_cimr_taux_pat'    => $this->cimrActif ? $this->cimrTauxPat : 0,
            '_cimr_actif'       => $this->cimrActif,
            '_abat_taux'        => $this->abatTaux,
            '_cnss_plafond'     => $this->cnssPlafond,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────

    /** Ancienneté en années complètes à la date du bulletin. */
    public function ancienneteAns(Employe $emp, int $mois, int $annee): int
    {
        if (!$emp->date_embauche) return 0;
        $dateRef = Carbon::create($annee, $mois, 1)->endOfMonth();
        return (int) $emp->date_embauche->diffInYears($dateRef);
    }

    /**
     * Taux de prime d'ancienneté selon le barème marocain.
     * 0-2 ans: 0% | 2-5: 5% | 5-12: 10% | 12-20: 15% | 20-25: 20% | >25: 25%
     */
    public function tauxAnciennete(int $ans): float
    {
        return match (true) {
            $ans >= 25 => 25.0,
            $ans >= 20 => 20.0,
            $ans >= 12 => 15.0,
            $ans >= 5  => 10.0,
            $ans >= 2  =>  5.0,
            default    =>  0.0,
        };
    }
}
