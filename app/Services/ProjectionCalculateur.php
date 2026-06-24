<?php

namespace App\Services;

use App\Models\Employe;
use App\Models\ProjectionScenario;
use App\Models\ProjectionScenarioLigne;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProjectionCalculateur
{
    private PaieCalculateur $paie;

    public function __construct()
    {
        $this->paie = new PaieCalculateur();
    }

    // ── API publique ──────────────────────────────────────────────────

    public function situationActuelle(int $horizon): array
    {
        return $this->compute($this->employes(), $horizon, collect());
    }

    public function scenario(ProjectionScenario $scenario, ?int $horizon = null): array
    {
        return $this->compute($this->employes(), $horizon ?? $scenario->horizon, $scenario->lignes);
    }

    // ── Calcul mois par mois ──────────────────────────────────────────

    private function compute(Collection $employes, int $horizon, Collection $lignes): array
    {
        $debut = now()->addMonth()->startOfMonth();
        $result = [];

        for ($i = 0; $i < $horizon; $i++) {
            $mois = $debut->copy()->addMonths($i);
            $result[] = $this->calculerMois($employes, $mois, $lignes);
        }

        return $result;
    }

    private function calculerMois(Collection $employes, Carbon $mois, Collection $lignes): array
    {
        $dateRef = $mois->format('Y-m-01');

        // Lignes dont l'effet a démarré ce mois ou avant
        $actives = $lignes->filter(fn($l) => $l->mois_effet->format('Y-m-01') <= $dateRef);

        $global   = $this->zeroes();
        $parUnite = [];

        // ── Employés réels ────────────────────────────────────────────
        foreach ($employes as $emp) {
            $contrat = $emp->contratActif;
            if (!$contrat) continue;

            // Départ planifié → on exclut cet employé
            if ($actives->where('type', 'depart')->where('employe_id', $emp->id)->isNotEmpty()) {
                continue;
            }

            $salaire = $this->appliquerRevalorisation(
                (float) $contrat->salaire_base,
                $emp->fichePoste?->categorie_id,
                $emp->id,
                $actives
            );

            $calc = $this->paie->calculer($salaire, 0, $emp, $mois->month, $mois->year);

            $this->accumulate($global, $calc);
            $this->accumulateUnite($parUnite, $emp, $calc);
        }

        // ── Embauches planifiées ──────────────────────────────────────
        foreach ($actives->where('type', 'embauche') as $ligne) {
            $emp     = $this->fakeEmploye($ligne);
            $salaire = $this->appliquerRevalorisation(
                (float) $ligne->salaire_estime,
                $ligne->categorie_id,
                0, // employé virtuel — seules revalorisation globale et catégorie s'appliquent
                $actives
            );
            $calc = $this->paie->calculer($salaire, 0, $emp, $mois->month, $mois->year);
            $this->accumulate($global, $calc);
            if ($ligne->unite_id) {
                $this->accumulateUnite($parUnite, $emp, $calc);
            }
        }

        return [
            'mois'   => $mois->format('Y-m'),
            'label'  => $this->labelMois($mois),
            'global' => $global,
            'unites' => array_values($parUnite),
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function employes(): Collection
    {
        return Employe::with(['contratActif', 'fichePoste.categorie', 'unite'])
            ->actifs()
            ->get();
    }

    private function zeroes(): array
    {
        return [
            'effectif'       => 0,
            'brut'           => 0.0,
            'charges_sal'    => 0.0,
            'ir'             => 0.0,
            'net'            => 0.0,
            'charges_pat'    => 0.0,
            'cout_employeur' => 0.0,
        ];
    }

    private function accumulate(array &$acc, array $calc): void
    {
        $acc['effectif']++;
        $acc['brut']           += $calc['_brut'];
        $acc['charges_sal']    += $calc['cnss_salarie'] + $calc['amo_salarie'] + $calc['cimr_salarie'];
        $acc['ir']             += $calc['ir_mensuel'];
        $acc['net']            += $calc['net_a_payer'];
        $acc['charges_pat']    += $calc['total_patronal'];
        $acc['cout_employeur'] += $calc['_cout_employeur'];
    }

    private function accumulateUnite(array &$parUnite, Employe $emp, array $calc): void
    {
        $uid  = $emp->unite_id ?? 0;
        $nom  = $emp->unite?->nom ?? 'Non affectés';
        $type = $emp->unite?->type ?? '';

        if (!isset($parUnite[$uid])) {
            $parUnite[$uid] = array_merge(['id' => $uid, 'nom' => $nom, 'type' => $type], $this->zeroes());
        }

        $this->accumulate($parUnite[$uid], $calc);
    }

    private function appliquerRevalorisation(float $salaire, ?int $categorieId, int $employeId, Collection $lignes): float
    {
        foreach ($lignes->where('type', 'revalorisation') as $ligne) {
            if ($ligne->employe_id !== null) {
                // Ciblage employé spécifique
                if ($ligne->employe_id === $employeId) {
                    $salaire = round($salaire * (1 + $ligne->pourcentage / 100), 2);
                }
            } elseif ($ligne->categorie_id !== null) {
                // Ciblage catégorie
                if ($ligne->categorie_id === $categorieId) {
                    $salaire = round($salaire * (1 + $ligne->pourcentage / 100), 2);
                }
            } else {
                // Global
                $salaire = round($salaire * (1 + $ligne->pourcentage / 100), 2);
            }
        }
        return $salaire;
    }

    private function fakeEmploye(ProjectionScenarioLigne $ligne): Employe
    {
        $emp = new Employe();
        $emp->situation_familiale = 'celibataire';
        $emp->nombre_enfants      = 0;
        $emp->date_embauche       = $ligne->mois_effet;
        if ($ligne->unite_id) {
            $emp->unite_id = $ligne->unite_id;
            $emp->setRelation('unite', $ligne->unite);
        }
        return $emp;
    }

    private function labelMois(Carbon $mois): string
    {
        $moisFr = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        return $moisFr[$mois->month - 1] . ' ' . $mois->year;
    }
}
