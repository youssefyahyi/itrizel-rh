<?php

namespace Database\Seeders;

use App\Models\Employe;
use App\Models\UniteOrganisationnelle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Simule une PME de 500 agents avec un organigramme de 10 niveaux.
 * Permet de valider les performances du chargement de l'arbre (1 requête SQL).
 *
 * Structure :
 *   Niveau  1 : Groupe (holding)                              →  1 unité
 *   Niveau  2 : Pôles métiers (3)                             →  3 unités
 *   Niveau  3 : Directions (3 par pôle)                       →  9 unités
 *   Niveau  4 : Départements (2 par direction)                → 18 unités
 *   Niveau  5 : Services (2 par département)                  → 36 unités
 *   Niveau  6 : Bureaux (2 par service)                       → 72 unités
 *   Niveaux 7-10 : branche pilote (1 seul chemin profond)     →  4 unités
 *   ─────────────────────────────────────────────────────────────────────
 *   TOTAL : 143 unités organisationnelles — 500 employés
 */
class StressTestPmeSeeder extends Seeder
{
    // ── Pools de noms marocains ──────────────────────────────────────
    private array $noms = [
        'ALAMI','BENALI','CHRAIBI','DOUKKALI','EL FASSI','FILALI',
        'GHAZI','HASSANI','IDRISSI','JALIL','KARIMI','LAHLOU',
        'MOUSSAOUI','NACIRI','OUAZZANI','RACHIDI','SABRI','TAZI',
        'WAHBI','YOUSFI','ZIANI','BENHADDOU','BERRADA','BOUHALI',
        'CHAOUI','DRISS','ELALAMI','FARISSI','GUESSOUS','HAJJI',
        'IRAQUI','JEBARI','KETTANI','LAMSISI','MRINI','NAJI',
        'QASMI','RAJI','SEBBAHI','TAHIRI','YAZIDI','ZOUITEN',
        'BENOMAR','BENSOUDA','BOUJEMAA','CHERKAOUI','DRISSI',
        'EL AMRANI','FIKRI','HAJJAM','IMRANI','JOUAHRI',
    ];

    private array $prenoms = [
        'Mohamed','Ahmed','Youssef','Omar','Hassan','Khalid',
        'Rachid','Nabil','Samir','Amine','Karim','Abdelilah',
        'Fatima','Khadija','Aicha','Zineb','Nadia','Samira',
        'Houda','Laila','Meryem','Sara','Imane','Hind',
        'Mustapha','Hamid','Driss','Tarik','Bilal','Soufiane',
        'Othmane','Reda','Mehdi','Adam','Ilias','Badr',
        'Hajar','Rim','Ghita','Salma','Amina','Loubna',
        'Najat','Rajae','Siham','Wafa','Nour','Yasmine',
        'Abderrahim','Abdellah',
    ];

    private array $villes = [
        'Casablanca','Rabat','Marrakech','Fès','Tanger',
        'Agadir','Meknès','Oujda','Kénitra','Tétouan',
    ];

    private array $categories = [
        'commercial','chauffeur','magasinier','logisticien','administratif','cadre',
    ];

    private array $postes = [
        'commercial'   => ['Commercial terrain','Responsable commercial','Chargé clientèle','Attaché commercial'],
        'chauffeur'    => ['Chauffeur livreur','Chauffeur poids lourd','Chauffeur VL','Chauffeur direction'],
        'magasinier'   => ['Magasinier','Chef magasinier','Opérateur entrepôt','Agent de stock'],
        'logisticien'  => ['Logisticien','Responsable logistique','Agent logistique','Coordinateur transport'],
        'administratif'=> ['Assistant administratif','Secrétaire','Agent administratif','Chargé de gestion'],
        'cadre'        => ['Cadre dirigeant','Responsable département','Chef de service','Directeur adjoint'],
    ];

    private array $prenomsFeminins = [
        'Fatima','Khadija','Aicha','Zineb','Nadia','Samira',
        'Houda','Laila','Meryem','Sara','Imane','Hind',
        'Hajar','Rim','Ghita','Salma','Amina','Loubna',
        'Najat','Rajae','Siham','Wafa','Nour','Yasmine',
    ];

    // ── Noms des directions par pôle ────────────────────────────────
    private array $directions = [
        'PI' => [
            'Direction Production & Fabrication',
            'Direction Qualité & Normes',
            'Direction Technique & Maintenance',
        ],
        'PC' => [
            'Direction Ventes & Développement',
            'Direction Marketing & Communication',
            'Direction Grands Comptes & Export',
        ],
        'PS' => [
            'Direction Ressources Humaines',
            'Direction Finance & Contrôle de Gestion',
            'Direction Systèmes d\'Information',
        ],
    ];

    public function run(): void
    {
        $debut = microtime(true);

        // ── Nettoyage ────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('🧹 Nettoyage des données existantes...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('bulletins_paie_lignes')->truncate();
        DB::table('bulletins_paie')->truncate();
        DB::table('evaluation_criteres')->truncate();
        DB::table('evaluations')->truncate();
        DB::table('formations')->truncate();
        DB::table('documents_rh')->truncate();
        DB::table('conge_validations')->truncate();
        DB::table('conge_delegations')->truncate();
        DB::table('conges')->truncate();
        DB::table('absences')->truncate();
        DB::table('contrats')->truncate();
        DB::table('employes')->truncate();
        DB::table('unites_organisationnelles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // ── Arbre organisationnel ────────────────────────────────────
        $this->command->info('🏗  Construction de l\'arbre (10 niveaux)...');
        $leafUnits = [];
        $totalUnites = 0;

        // N1 — Holding
        $holding = $this->unite('GAM', 'Groupe Atlas Maroc', 'direction', null, 0);
        $totalUnites++;

        $poles = [
            ['code' => 'PI', 'nom' => 'Pôle Industrie & Production',      'ordre' => 1],
            ['code' => 'PC', 'nom' => 'Pôle Commercial & Distribution',    'ordre' => 2],
            ['code' => 'PS', 'nom' => 'Pôle Services & Support',           'ordre' => 3],
        ];

        foreach ($poles as $pi => $poleData) {
            // N2 — Pôle
            $pole = $this->unite($poleData['code'], $poleData['nom'], 'direction', $holding->id, $poleData['ordre']);
            $totalUnites++;

            foreach ($this->directions[$poleData['code']] as $di => $nomDir) {
                // N3 — Direction
                $codeDir = $poleData['code'] . 'D' . ($di + 1);
                $dir = $this->unite($codeDir, $nomDir, 'direction', $pole->id, $di + 1);
                $totalUnites++;

                for ($dp = 1; $dp <= 2; $dp++) {
                    // N4 — Département
                    $codeDept = $codeDir . 'DP' . $dp;
                    $dept = $this->unite(
                        $codeDept,
                        'Département ' . chr(64 + $dp) . ' — ' . $this->abrev($nomDir),
                        'departement', $dir->id, $dp
                    );
                    $totalUnites++;

                    for ($s = 1; $s <= 2; $s++) {
                        // N5 — Service
                        $codeSvc = $codeDept . 'S' . $s;
                        $svc = $this->unite(
                            $codeSvc,
                            'Service ' . chr(64 + $s) . ' — ' . $this->abrev($nomDir),
                            'service', $dept->id, $s
                        );
                        $totalUnites++;

                        for ($b = 1; $b <= 2; $b++) {
                            // N6 — Bureau
                            $codeBur = $codeSvc . 'B' . $b;
                            $bureau = $this->unite(
                                $codeBur,
                                'Bureau ' . $b,
                                'equipe', $svc->id, $b
                            );
                            $totalUnites++;

                            // ── Branche pilote N7→N10 ──────────────────────
                            // Uniquement sur le 1er bureau du 1er pôle/direction/dept/service
                            if ($pi === 0 && $di === 0 && $dp === 1 && $s === 1 && $b === 1) {

                                $eq = $this->unite($codeBur . 'E1', 'Équipe Principale', 'equipe', $bureau->id, 1);
                                $totalUnites++;

                                $gr = $this->unite($codeBur . 'E1G1', 'Groupe de Travail Alpha', 'equipe', $eq->id, 1);
                                $totalUnites++;

                                $cell = $this->unite($codeBur . 'E1G1C1', 'Cellule Opérationnelle', 'equipe', $gr->id, 1);
                                $totalUnites++;

                                $sc = $this->unite($codeBur . 'E1G1C1S1', 'Sous-Cellule Terrain A1', 'autre', $cell->id, 1);
                                $totalUnites++;

                                $leafUnits[] = $sc; // feuille = N10
                            } else {
                                $leafUnits[] = $bureau; // feuille = N6
                            }
                        }
                    }
                }
            }
        }

        $this->command->info("   ✅ {$totalUnites} unités créées | " . count($leafUnits) . " unités feuilles");

        // ── 500 Employés ─────────────────────────────────────────────
        $this->command->info('👥 Création de 500 employés...');
        $nbLeaf   = count($leafUnits);
        $employes = [];

        for ($e = 0; $e < 500; $e++) {
            $prenom = $this->prenoms[array_rand($this->prenoms)];
            $nom    = $this->noms[array_rand($this->noms)];
            $cat    = $this->categories[$e % count($this->categories)]; // distribution équilibrée
            $unite  = $leafUnits[$e % $nbLeaf];
            $sexe   = in_array($prenom, $this->prenomsFeminins) ? 'F' : 'M';

            $emp = Employe::create([
                'matricule'     => 'EMP' . str_pad($e + 1, 4, '0', STR_PAD_LEFT),
                'nom'           => $nom,
                'prenom'        => $prenom,
                'date_naissance' => now()->subYears(rand(22, 55))->subDays(rand(0, 364))->toDateString(),
                'cin'           => strtoupper(chr(rand(65,90))) . rand(100000, 999999),
                'email'         => strtolower($prenom[0] . '.' . strtolower(str_replace(' ', '', $nom)) . ($e + 1) . '@groupe-atlas.ma'),
                'telephone'     => '06' . rand(10000000, 99999999),
                'categorie'     => $cat,
                'poste'         => $this->postes[$cat][array_rand($this->postes[$cat])],
                'date_embauche' => now()->subDays(rand(30, 3650))->toDateString(),
                'statut'        => $e < 475 ? 'actif' : ($e < 490 ? 'inactif' : 'suspendu'),
                'nationalite'   => 'Marocaine',
                'sexe'          => $sexe,
                'ville'         => $this->villes[array_rand($this->villes)],
                'unite_id'      => $unite->id,
                'created_by'    => 1,
            ]);

            $employes[] = $emp;
        }

        // ── Managers ─────────────────────────────────────────────────
        $this->command->info('🔗 Assignation de la hiérarchie...');
        $parUnite = collect($employes)->groupBy('unite_id');
        foreach ($parUnite as $groupe) {
            $manager = $groupe->first();
            foreach ($groupe->skip(1) as $sub) {
                $sub->update(['manager_id' => $manager->id]);
            }
        }

        // ── Rapport final ─────────────────────────────────────────────
        $duree = round(microtime(true) - $debut, 2);
        $this->command->info('');
        $this->command->info('════════════════════════════════════════');
        $this->command->info('  ✅ STRESS TEST PME — Données chargées');
        $this->command->info('════════════════════════════════════════');
        $this->command->info("  Unités org.   : {$totalUnites} (10 niveaux)");
        $this->command->info("  Employés      : 500 (475 actifs / 15 inactifs / 10 suspendus)");
        $this->command->info("  Unités feuille: {$nbLeaf} (~" . round(500 / $nbLeaf, 1) . " emp/unité)");
        $this->command->info("  Durée totale  : {$duree}s");
        $this->command->info('════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('  👉 Tester l\'arbre : http://localhost:8081/parametrage/organisation');
        $this->command->info('  👉 Branche N10   : GAM > PI > PID1 > PID1DP1 > PID1DP1S1 > PID1DP1S1B1 > ...');
        $this->command->info('');
    }

    // ── Helpers ──────────────────────────────────────────────────────

    private function unite(string $code, string $nom, string $type, ?int $parentId, int $ordre): UniteOrganisationnelle
    {
        return UniteOrganisationnelle::create([
            'code'        => $code,
            'nom'         => $nom,
            'type'        => $type,
            'parent_id'   => $parentId,
            'ordre'       => $ordre,
            'actif'       => true,
            'description' => null,
        ]);
    }

    private function abrev(string $nom): string
    {
        // "Direction Production & Fabrication" → "Prod. & Fab."
        $mots = explode(' ', $nom);
        $result = [];
        foreach ($mots as $mot) {
            if (strlen($mot) > 4 && !in_array($mot, ['Direction','Département','Service'])) {
                $result[] = mb_substr($mot, 0, 4) . '.';
            }
        }
        return implode(' ', $result) ?: $nom;
    }
}
