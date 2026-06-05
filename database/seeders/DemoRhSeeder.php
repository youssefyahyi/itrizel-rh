<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoRhSeeder extends Seeder
{
    private int $adminId = 1; // ID de l'user admin (créé par DemoUsersSeeder)

    public function run(): void
    {
        $this->command->info('🏗  Création du jeu de données ATLAS DISTRIBUTION MAROC...');

        $this->adminId = DB::table('users')->where('role','admin')->value('id') ?? 1;

        // Nettoyage propre avant réinjection (respecte les FK)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('bulletins_paie')->truncate();
        DB::table('formations')->truncate();
        DB::table('evaluations')->truncate();
        DB::table('conges')->truncate();
        DB::table('absences')->truncate();
        DB::table('contrats')->truncate();
        DB::table('employes')->truncate();
        DB::table('unites_organisationnelles')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $unites   = $this->seedOrganisation();
        $employes = $this->seedEmployes($unites);
        $this->updateResponsables($unites, $employes);
        $this->seedContrats($employes);
        $this->seedAbsences($employes);
        $this->seedConges($employes);
        $this->seedEvaluations($employes);
        $this->seedFormations($employes);
        $this->seedBulletinsPaie($employes);

        $this->command->info('✅ Jeu de données complet créé :');
        $this->command->info('   - 10 unités organisationnelles');
        $this->command->info('   - ' . count($employes) . ' employés');
        $this->command->info('   - Contrats, absences, congés, évaluations, formations, bulletins');
    }

    // ══════════════════════════════════════════════════════════════════
    //  1. ORGANISATION
    // ══════════════════════════════════════════════════════════════════
    private function seedOrganisation(): array
    {
        $now = now();

        // Niveau 1 — Racine
        $dg = DB::table('unites_organisationnelles')->insertGetId([
            'code' => 'DG', 'nom' => 'Direction Générale',
            'type' => 'direction', 'parent_id' => null, 'ordre' => 1,
            'description' => 'Direction générale et pilotage stratégique de la société.',
            'actif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        // Niveau 2
        $daf = DB::table('unites_organisationnelles')->insertGetId([
            'code' => 'DAF', 'nom' => 'Direction Administrative & Financière',
            'type' => 'direction', 'parent_id' => $dg, 'ordre' => 1,
            'description' => 'Gestion administrative, financière et des ressources humaines.',
            'actif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $dc = DB::table('unites_organisationnelles')->insertGetId([
            'code' => 'DC', 'nom' => 'Direction Commerciale',
            'type' => 'direction', 'parent_id' => $dg, 'ordre' => 2,
            'description' => 'Développement commercial, prospection et gestion portefeuille clients.',
            'actif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $dl = DB::table('unites_organisationnelles')->insertGetId([
            'code' => 'DL', 'nom' => 'Direction Logistique & Opérations',
            'type' => 'direction', 'parent_id' => $dg, 'ordre' => 3,
            'description' => 'Transport, entrepôt, gestion des flux et opérations terrain.',
            'actif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        // Niveau 3
        $sc = DB::table('unites_organisationnelles')->insertGetId([
            'code' => 'SC', 'nom' => 'Service Comptabilité & Finance',
            'type' => 'service', 'parent_id' => $daf, 'ordre' => 1,
            'actif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $srh = DB::table('unites_organisationnelles')->insertGetId([
            'code' => 'SRH', 'nom' => 'Service Ressources Humaines',
            'type' => 'service', 'parent_id' => $daf, 'ordre' => 2,
            'actif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $ecn = DB::table('unites_organisationnelles')->insertGetId([
            'code' => 'ECN', 'nom' => 'Équipe Commerciale Nord',
            'type' => 'equipe', 'parent_id' => $dc, 'ordre' => 1,
            'description' => 'Zone Nord : Casablanca, Rabat, Tanger, Fès.',
            'actif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $ecs = DB::table('unites_organisationnelles')->insertGetId([
            'code' => 'ECS', 'nom' => 'Équipe Commerciale Sud',
            'type' => 'equipe', 'parent_id' => $dc, 'ordre' => 2,
            'description' => 'Zone Sud : Marrakech, Agadir, Ouarzazate.',
            'actif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $st = DB::table('unites_organisationnelles')->insertGetId([
            'code' => 'ST', 'nom' => 'Service Transport',
            'type' => 'equipe', 'parent_id' => $dl, 'ordre' => 1,
            'actif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);
        $se = DB::table('unites_organisationnelles')->insertGetId([
            'code' => 'SE', 'nom' => 'Service Entrepôt & Stockage',
            'type' => 'equipe', 'parent_id' => $dl, 'ordre' => 2,
            'actif' => true, 'created_at' => $now, 'updated_at' => $now,
        ]);

        return compact('dg','daf','dc','dl','sc','srh','ecn','ecs','st','se');
    }

    // ══════════════════════════════════════════════════════════════════
    //  2. EMPLOYÉS (20 personnes)
    // ══════════════════════════════════════════════════════════════════
    private function seedEmployes(array $u): array
    {
        $now = now();
        $data = [
            // ── Direction Générale
            ['matricule'=>'ADM-001','nom'=>'BENALI','prenom'=>'Mohamed','cin'=>'BE123456',
             'sexe'=>'M','email'=>'m.benali@atlas-dist.ma','telephone'=>'0661001001',
             'poste'=>'Directeur Général','categorie'=>'cadre','salaire'=>25000,
             'embauche'=>'2018-03-01','unite'=>$u['dg'],'manager'=>null,
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>3],

            // ── DAF
            ['matricule'=>'ADM-002','nom'=>'CHRAIBI','prenom'=>'Nadia','cin'=>'BE234567',
             'sexe'=>'F','email'=>'n.chraibi@atlas-dist.ma','telephone'=>'0661002002',
             'poste'=>'Directrice Administrative & Financière','categorie'=>'cadre','salaire'=>18000,
             'embauche'=>'2019-06-15','unite'=>$u['daf'],'manager'=>0, // manager = DG (index 0)
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>2],

            ['matricule'=>'CPT-001','nom'=>'OUALI','prenom'=>'Rachid','cin'=>'BE345678',
             'sexe'=>'M','email'=>'r.ouali@atlas-dist.ma','telephone'=>'0661003003',
             'poste'=>'Responsable Comptable','categorie'=>'administratif','salaire'=>12000,
             'embauche'=>'2019-09-01','unite'=>$u['sc'],'manager'=>1, // manager = DAF
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>1],

            ['matricule'=>'CPT-002','nom'=>'ZIANI','prenom'=>'Samira','cin'=>'BE456789',
             'sexe'=>'F','email'=>'s.ziani@atlas-dist.ma','telephone'=>'0661004004',
             'poste'=>'Comptable','categorie'=>'administratif','salaire'=>8500,
             'embauche'=>'2021-01-10','unite'=>$u['sc'],'manager'=>2, // manager = Responsable comptable
             'ville'=>'Mohammedia','situation'=>'celibataire','enfants'=>0],

            ['matricule'=>'RH-001','nom'=>'TAZI','prenom'=>'Aicha','cin'=>'BE567890',
             'sexe'=>'F','email'=>'a.tazi@atlas-dist.ma','telephone'=>'0661005005',
             'poste'=>'Directrice des Ressources Humaines','categorie'=>'cadre','salaire'=>15000,
             'embauche'=>'2019-01-15','unite'=>$u['srh'],'manager'=>1, // manager = DAF
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>2],

            ['matricule'=>'RH-002','nom'=>'BRAHIM','prenom'=>'Yassine','cin'=>'BE678901',
             'sexe'=>'M','email'=>'y.brahim@atlas-dist.ma','telephone'=>'0661006006',
             'poste'=>'Chargé RH & Paie','categorie'=>'administratif','salaire'=>7500,
             'embauche'=>'2022-03-01','unite'=>$u['srh'],'manager'=>4, // manager = DRH
             'ville'=>'Casablanca','situation'=>'celibataire','enfants'=>0],

            // ── Direction Commerciale
            ['matricule'=>'COM-001','nom'=>'MOUSSAID','prenom'=>'Omar','cin'=>'BE789012',
             'sexe'=>'M','email'=>'o.moussaid@atlas-dist.ma','telephone'=>'0661007007',
             'poste'=>'Directeur Commercial','categorie'=>'cadre','salaire'=>20000,
             'embauche'=>'2018-09-01','unite'=>$u['dc'],'manager'=>0, // manager = DG
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>3],

            ['matricule'=>'COM-002','nom'=>'ELFASSI','prenom'=>'Karima','cin'=>'BE890123',
             'sexe'=>'F','email'=>'k.elfassi@atlas-dist.ma','telephone'=>'0661008008',
             'poste'=>'Responsable Commercial Zone Nord','categorie'=>'commercial','salaire'=>10000,
             'embauche'=>'2020-02-01','unite'=>$u['ecn'],'manager'=>6, // manager = Dir Commercial
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>1],

            ['matricule'=>'COM-003','nom'=>'TAHIRI','prenom'=>'Hamza','cin'=>'BE901234',
             'sexe'=>'M','email'=>'h.tahiri@atlas-dist.ma','telephone'=>'0661009009',
             'poste'=>'Commercial Nord','categorie'=>'commercial','salaire'=>7000,
             'embauche'=>'2021-04-15','unite'=>$u['ecn'],'manager'=>7, // manager = Resp Nord
             'ville'=>'Rabat','situation'=>'celibataire','enfants'=>0],

            ['matricule'=>'COM-004','nom'=>'BENSOUDA','prenom'=>'Layla','cin'=>'BE012345',
             'sexe'=>'F','email'=>'l.bensouda@atlas-dist.ma','telephone'=>'0661010010',
             'poste'=>'Commercial Nord','categorie'=>'commercial','salaire'=>7000,
             'embauche'=>'2022-07-01','unite'=>$u['ecn'],'manager'=>7,
             'ville'=>'Tanger','situation'=>'celibataire','enfants'=>0],

            ['matricule'=>'COM-005','nom'=>'FILALI','prenom'=>'Mehdi','cin'=>'BE123457',
             'sexe'=>'M','email'=>'m.filali@atlas-dist.ma','telephone'=>'0661011011',
             'poste'=>'Responsable Commercial Zone Sud','categorie'=>'commercial','salaire'=>10000,
             'embauche'=>'2020-05-01','unite'=>$u['ecs'],'manager'=>6,
             'ville'=>'Marrakech','situation'=>'marie','enfants'=>2],

            ['matricule'=>'COM-006','nom'=>'ALAOUI','prenom'=>'Zineb','cin'=>'BE234568',
             'sexe'=>'F','email'=>'z.alaoui@atlas-dist.ma','telephone'=>'0661012012',
             'poste'=>'Commercial Sud','categorie'=>'commercial','salaire'=>7000,
             'embauche'=>'2023-01-09','unite'=>$u['ecs'],'manager'=>10,
             'ville'=>'Agadir','situation'=>'celibataire','enfants'=>0],

            // ── Direction Logistique
            ['matricule'=>'LOG-001','nom'=>'HAJJI','prenom'=>'Driss','cin'=>'BE345679',
             'sexe'=>'M','email'=>'d.hajji@atlas-dist.ma','telephone'=>'0661013013',
             'poste'=>'Directeur Logistique & Opérations','categorie'=>'cadre','salaire'=>18000,
             'embauche'=>'2018-11-01','unite'=>$u['dl'],'manager'=>0,
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>4],

            ['matricule'=>'TRP-001','nom'=>'BERRADA','prenom'=>'Khalid','cin'=>'BE456780',
             'sexe'=>'M','email'=>'k.berrada@atlas-dist.ma','telephone'=>'0661014014',
             'poste'=>'Responsable Transport','categorie'=>'logisticien','salaire'=>9000,
             'embauche'=>'2019-03-15','unite'=>$u['st'],'manager'=>12,
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>2],

            ['matricule'=>'TRP-002','nom'=>'MANSOURI','prenom'=>'Aziz','cin'=>'BE567891',
             'sexe'=>'M','email'=>'a.mansouri@atlas-dist.ma','telephone'=>'0661015015',
             'poste'=>'Chauffeur Livreur','categorie'=>'chauffeur','salaire'=>5500,
             'embauche'=>'2020-06-01','unite'=>$u['st'],'manager'=>13,
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>2],

            ['matricule'=>'TRP-003','nom'=>'KABOURI','prenom'=>'Said','cin'=>'BE678902',
             'sexe'=>'M','email'=>'s.kabouri@atlas-dist.ma','telephone'=>'0661016016',
             'poste'=>'Chauffeur Livreur','categorie'=>'chauffeur','salaire'=>5500,
             'embauche'=>'2021-09-01','unite'=>$u['st'],'manager'=>13,
             'ville'=>'Bouskoura','situation'=>'marie','enfants'=>1],

            ['matricule'=>'TRP-004','nom'=>'RHAZALI','prenom'=>'Mounir','cin'=>'BE789013',
             'sexe'=>'M','email'=>'m.rhazali@atlas-dist.ma','telephone'=>'0661017017',
             'poste'=>'Chauffeur Livreur','categorie'=>'chauffeur','salaire'=>5200,
             'embauche'=>'2023-04-01','unite'=>$u['st'],'manager'=>13,
             'ville'=>'Casablanca','situation'=>'celibataire','enfants'=>0],

            ['matricule'=>'ENT-001','nom'=>'ACHOUR','prenom'=>'Fatima','cin'=>'BE890124',
             'sexe'=>'F','email'=>'f.achour@atlas-dist.ma','telephone'=>'0661018018',
             'poste'=>'Responsable Entrepôt','categorie'=>'logisticien','salaire'=>8000,
             'embauche'=>'2019-07-01','unite'=>$u['se'],'manager'=>12,
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>2],

            ['matricule'=>'ENT-002','nom'=>'BELKADI','prenom'=>'Amine','cin'=>'BE901235',
             'sexe'=>'M','email'=>'a.belkadi@atlas-dist.ma','telephone'=>'0661019019',
             'poste'=>'Magasinier','categorie'=>'magasinier','salaire'=>5000,
             'embauche'=>'2022-02-15','unite'=>$u['se'],'manager'=>17,
             'ville'=>'Casablanca','situation'=>'celibataire','enfants'=>0],

            ['matricule'=>'ENT-003','nom'=>'OUKHOUYA','prenom'=>'Hassan','cin'=>'BE012346',
             'sexe'=>'M','email'=>'h.oukhouya@atlas-dist.ma','telephone'=>'0661020020',
             'poste'=>'Logisticien','categorie'=>'logisticien','salaire'=>6500,
             'embauche'=>'2021-11-01','unite'=>$u['se'],'manager'=>17,
             'ville'=>'Casablanca','situation'=>'marie','enfants'=>1],
        ];

        $ids = [];
        foreach ($data as $i => $d) {
            $managerId = null;
            if ($d['manager'] !== null && isset($ids[$d['manager']])) {
                $managerId = $ids[$d['manager']];
            }
            $ids[] = DB::table('employes')->insertGetId([
                'matricule'        => $d['matricule'],
                'nom'              => $d['nom'],
                'prenom'           => $d['prenom'],
                'cin'              => $d['cin'],
                'sexe'             => $d['sexe'],
                'email'            => $d['email'],
                'telephone'        => $d['telephone'],
                'poste'            => $d['poste'],
                'categorie'        => $d['categorie'],
                'date_embauche'    => $d['embauche'],
                'date_naissance'   => $this->randomDob(),
                'nationalite'      => 'Marocaine',
                'lieu_naissance'   => 'Casablanca',
                'adresse'          => $this->randomAdresse($d['ville']),
                'ville'            => $d['ville'],
                'situation_familiale' => $d['situation'],
                'nombre_enfants'   => $d['enfants'],
                'statut'           => $i === 15 ? 'suspendu' : 'actif', // 1 suspendu pour tester
                'unite_id'         => $d['unite'],
                'manager_id'       => $managerId,
                'rib'              => 'MA' . rand(10,99) . str_pad(rand(0,999999999999999), 22, '0', STR_PAD_LEFT),
                'banque'           => $this->randomBanque(),
                'numero_cnss'      => 'CNSS' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'numero_amo'       => 'AMO' . str_pad($i + 1, 7, '0', STR_PAD_LEFT),
                'created_by'       => $this->adminId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        return $ids; // tableau des IDs dans l'ordre d'insertion
    }

    private function updateResponsables(array $u, array $ids): void
    {
        $map = [
            'dg'  => 0,  // BENALI
            'daf' => 1,  // CHRAIBI
            'sc'  => 2,  // OUALI
            'srh' => 4,  // TAZI
            'dc'  => 6,  // MOUSSAID
            'ecn' => 7,  // ELFASSI
            'ecs' => 10, // FILALI
            'dl'  => 12, // HAJJI
            'st'  => 13, // BERRADA
            'se'  => 17, // ACHOUR
        ];
        foreach ($map as $key => $empIndex) {
            DB::table('unites_organisationnelles')
                ->where('id', $u[$key])
                ->update(['responsable_id' => $ids[$empIndex]]);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  3. CONTRATS
    // ══════════════════════════════════════════════════════════════════
    private function seedContrats(array $ids): void
    {
        // type contrat : CDI pour cadres/perm, CDD pour certains
        $contratTypes = [
            'CDI',null,   // BENALI DG
            'CDI',null,   // CHRAIBI DAF
            'CDI',null,   // OUALI Compta
            'CDI',null,   // ZIANI Compta
            'CDI',null,   // TAZI DRH
            'CDI',null,   // BRAHIM RH
            'CDI',null,   // MOUSSAID DC
            'CDI',null,   // ELFASSI
            'CDD',12,     // TAHIRI
            'CDD',12,     // BENSOUDA
            'CDI',null,   // FILALI
            'CDD',12,     // ALAOUI
            'CDI',null,   // HAJJI
            'CDI',null,   // BERRADA
            'CDI',null,   // MANSOURI
            'CDI',null,   // KABOURI
            'CDD',6,      // RHAZALI (suspendu)
            'CDI',null,   // ACHOUR
            'CDD',12,     // BELKADI
            'CDI',null,   // OUKHOUYA
        ];

        $salaires = [25000,18000,12000,8500,15000,7500,20000,10000,7000,7000,10000,7000,18000,9000,5500,5500,5200,8000,5000,6500];

        foreach ($ids as $i => $empId) {
            $typeIdx = $i * 2;
            $type  = $contratTypes[$typeIdx]  ?? 'CDI';
            $duree = $contratTypes[$typeIdx+1] ?? null;

            $emp   = DB::table('employes')->where('id',$empId)->first();
            $debut = Carbon::now()->subYears(rand(1,5))->subMonths(rand(0,11))->startOfMonth()->toDateString();
            $fin   = $duree ? Carbon::parse($debut)->addMonths($duree)->toDateString() : null;
            $statut = ($emp->statut === 'suspendu') ? 'expire' : 'en_cours';

            DB::table('contrats')->insert([
                'employe_id'    => $empId,
                'reference'     => 'CTR-2024-' . str_pad($i+1, 3, '0', STR_PAD_LEFT),
                'type'          => $type,
                'poste'         => $emp->poste,
                'categorie'     => $emp->categorie, // utilise la catégorie de l'employé (même enum)
                'salaire_base'  => $salaires[$i] ?? 6000,
                'date_debut'    => $debut,
                'date_fin'      => $fin,
                'duree_mois'    => $duree,
                'statut'        => $statut,
                'created_by'    => $this->adminId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  4. ABSENCES (pointages) — 3 derniers mois
    // ══════════════════════════════════════════════════════════════════
    private function seedAbsences(array $ids): void
    {
        $records = [];
        $used    = []; // (employe_id, date) uniq

        $statuts = ['present','present','present','present','present','absent','conge','mission'];

        // On génère des entrées sur 30 jours ouvrés pour chaque employé
        $workDays = $this->getWorkDays(Carbon::now()->subDays(60), Carbon::now()->subDay(), 25);

        foreach ($ids as $empId) {
            // Chaque employé a des entrées sur ~15 jours ouvrés aléatoires
            $sample = array_rand(array_flip($workDays), min(15, count($workDays)));
            if (!is_array($sample)) $sample = [$sample];
            foreach ($sample as $date) {
                $key = "{$empId}_{$date}";
                if (isset($used[$key])) continue;
                $used[$key] = true;

                $statut  = $statuts[array_rand($statuts)];
                $arrive  = $statut === 'present' ? sprintf('08:%02d', rand(0,45)) : null;
                $depart  = $statut === 'present' ? sprintf('17:%02d', rand(0,45)) : null;
                $heuresR = $statut === 'present' ? rand(7,9) + 0.5 : null;

                $records[] = [
                    'employe_id'       => $empId,
                    'date'             => $date,
                    'statut'           => $statut,
                    'heure_arrivee'    => $arrive,
                    'heure_depart'     => $depart,
                    'heures_prevues'   => 8,
                    'heures_realisees' => $heuresR,
                    'motif_absence'    => $statut === 'absent' ? $this->randomMotif() : null,
                    'created_by'       => $this->adminId,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
        }

        foreach (array_chunk($records, 100) as $chunk) {
            DB::table('absences')->insert($chunk);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  5. CONGÉS — divers statuts
    // ══════════════════════════════════════════════════════════════════
    private function seedConges(array $ids): void
    {
        $conges = [
            // [emp_index, type, debut, fin, statut, motif]
            [2,  'annuel',     '2026-07-10','2026-07-25','soumis',       'Congé annuel été'],
            [5,  'annuel',     '2026-08-01','2026-08-15','soumis',       'Vacances estivales'],
            [8,  'annuel',     '2026-06-20','2026-06-30','en_validation','Congé annuel'],
            [14, 'annuel',     '2026-07-01','2026-07-14','en_validation','Repos annuel'],
            [3,  'maladie',    '2026-05-12','2026-05-16','approuve',     'Grippe - certificat médical joint'],
            [10, 'maladie',    '2026-04-22','2026-04-24','approuve',     'Indisposition'],
            [6,  'exceptionnel','2026-05-28','2026-05-29','approuve',    'Mariage de sa sœur'],
            [15, 'exceptionnel','2026-06-02','2026-06-02','approuve',    'Décès beau-père'],
            [1,  'annuel',     '2026-04-01','2026-04-10','approuve',     'Congé annuel'],
            [12, 'annuel',     '2026-03-15','2026-03-28','approuve',     'Vacances printemps'],
            [7,  'recuperation','2026-05-05','2026-05-06','approuve',    'Récupération jours fériés'],
            [18, 'maternite',  '2026-05-01','2026-07-31','approuve',     'Congé maternité'],
            [4,  'sans_solde', '2026-06-15','2026-06-30','rejete',       'Raisons personnelles'],
            [9,  'annuel',     '2026-09-01','2026-09-15','soumis',       'Congé prévu'],
            [16, 'annuel',     '2026-08-15','2026-08-25','soumis',       'Fin de saison'],
        ];

        foreach ($conges as $c) {
            [$idx, $type, $debut, $fin, $statut, $motif] = $c;
            if (!isset($ids[$idx])) continue;
            $nbJours = Carbon::parse($debut)->diffInDays(Carbon::parse($fin)) + 1;
            DB::table('conges')->insert([
                'employe_id'  => $ids[$idx],
                'type_conge'  => $type,
                'date_debut'  => $debut,
                'date_fin'    => $fin,
                'nb_jours'    => $nbJours,
                'motif'       => $motif,
                'statut'      => $statut,
                'etape_actuelle' => $statut === 'soumis' ? 1 : ($statut === 'en_validation' ? 2 : 3),
                'created_by'  => $this->adminId,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  6. ÉVALUATIONS
    // ══════════════════════════════════════════════════════════════════
    private function seedEvaluations(array $ids): void
    {
        $evals = [
            [0,  'annuel',     '2025-01-01','2025-12-31', 16.5, 'finalise',  'renouvellement',     'Excellent travail, très bonne maîtrise du poste.'],
            [1,  'annuel',     '2025-01-01','2025-12-31', 17.0, 'finalise',  'renouvellement',     'Gestion rigoureuse, bons résultats financiers.'],
            [4,  'annuel',     '2025-01-01','2025-12-31', 15.5, 'finalise',  'renouvellement',     'Très bonne gestion RH, équipe bien coordonnée.'],
            [6,  'annuel',     '2025-01-01','2025-12-31', 16.0, 'finalise',  'renouvellement',     'Dépassement des objectifs commerciaux Q3/Q4.'],
            [7,  'semestriel', '2026-01-01','2026-06-30', 14.0, 'finalise',  'amelioration',       'Bon potentiel, communication à améliorer.'],
            [8,  'semestriel', '2026-01-01','2026-06-30', 13.5, 'finalise',  'amelioration',       'Résultats en dessous des objectifs Nord.'],
            [12, 'annuel',     '2025-01-01','2025-12-31', 15.0, 'finalise',  'renouvellement',     'Bonne maîtrise opérationnelle.'],
            [14, 'periode_essai','2026-04-01','2026-06-30',12.0,'brouillon', 'en_attente',         'En cours d\'évaluation.'],
            [2,  'annuel',     '2025-01-01','2025-12-31', 14.5, 'finalise',  'renouvellement',     'Travail sérieux, rigueur comptable appréciée.'],
            [17, 'periode_essai','2026-03-01','2026-05-31',11.5,'finalise',  'amelioration',       'Quelques lacunes procédurales à corriger.'],
        ];

        foreach ($evals as $e) {
            [$idx, $type, $debut, $fin, $note, $statut, $decision, $obs] = $e;
            if (!isset($ids[$idx])) continue;
            DB::table('evaluations')->insert([
                'employe_id'    => $ids[$idx],
                'evaluateur_id' => $this->adminId,
                'type'          => $type,
                'periode_debut' => $debut,
                'periode_fin'   => $fin,
                'note_globale'  => $note,
                'statut'        => $statut,
                'decision'      => $decision,
                'observations'  => $obs,
                'created_by'    => $this->adminId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  7. FORMATIONS
    // ══════════════════════════════════════════════════════════════════
    private function seedFormations(array $ids): void
    {
        $formations = [
            [4,  'Management des Ressources Humaines', 'ISCAE Casablanca', 'externe', '2025-10-01','2025-10-03', 21, 4500, 'termine'],
            [1,  'Leadership & Conduite du changement', 'HEM Business School','externe','2025-11-10','2025-11-12', 21, 6000, 'termine'],
            [7,  'Techniques de négociation commerciale','CFCIM','externe','2026-01-15','2026-01-16', 14, 3200, 'termine'],
            [2,  'IFRS & Normes comptables marocaines', 'Interne', 'interne','2025-09-20','2025-09-21', 14, 0, 'termine'],
            [13, 'Gestion de la chaîne logistique', 'ENCG Casablanca','externe','2026-02-10','2026-02-14', 35, 5500, 'termine'],
            [14, 'Conduite sécurisée & code de la route', 'Auto-École Pro','externe','2026-03-01','2026-03-01', 7, 800, 'termine'],
            [5,  'Excel avancé & Power BI', 'Interne', 'interne','2026-05-05','2026-05-06', 14, 0, 'termine'],
            [8,  'CRM Salesforce - Module Commercial', 'SoftTech Maroc','externe','2026-06-15','2026-06-17', 21, 3800, 'planifie'],
            [17, 'Gestion des stocks & inventaire', 'Interne', 'interne','2026-07-01','2026-07-01', 7, 0, 'planifie'],
            [3,  'Fiscalité marocaine - IS & TVA', 'Cabinet Audit Maroc','externe','2025-12-05','2025-12-06', 14, 2800, 'termine'],
        ];

        foreach ($formations as $f) {
            [$idx, $intitule, $organisme, $type, $debut, $fin, $heures, $cout, $statut] = $f;
            if (!isset($ids[$idx])) continue;
            DB::table('formations')->insert([
                'employe_id' => $ids[$idx],
                'intitule'   => $intitule,
                'organisme'  => $organisme !== 'Interne' ? $organisme : null,
                'type'       => $organisme === 'Interne' ? 'interne' : 'externe',
                'date_debut' => $debut,
                'date_fin'   => $fin,
                'nb_heures'  => $heures,
                'cout'       => $cout,
                'statut'     => $statut,
                'created_by' => $this->adminId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  8. BULLETINS DE PAIE — Avril + Mai 2026
    // ══════════════════════════════════════════════════════════════════
    private function seedBulletinsPaie(array $ids): void
    {
        $salaires = [25000,18000,12000,8500,15000,7500,20000,10000,7000,7000,10000,7000,18000,9000,5500,5500,5200,8000,5000,6500];

        foreach ([4, 5] as $mois) {
            foreach ($ids as $i => $empId) {
                if ($i >= count($salaires)) break;
                $base   = $salaires[$i];
                $prime  = $base * 0.10;
                $cnss   = round($base * 0.0448, 2);
                $amo    = round($base * 0.0226, 2);
                $cimr   = round($base * 0.03, 2);
                $retenues = $cnss + $amo + $cimr;
                $netImp = $base + $prime - $retenues;
                $ir     = $this->calcIr($netImp);
                $net    = $netImp - $ir;

                DB::table('bulletins_paie')->insert([
                    'employe_id'       => $empId,
                    'periode_mois'     => $mois,
                    'periode_annee'    => 2026,
                    'salaire_base'     => $base,
                    'total_primes'     => $prime,
                    'total_retenues'   => round($retenues, 2),
                    'net_imposable'    => round($netImp, 2),
                    'ir_mensuel'       => round($ir, 2),
                    'amo_salarie'      => $amo,
                    'cnss_salarie'     => $cnss,
                    'cimr_salarie'     => $cimr,
                    'avances_deduites' => 0,
                    'net_a_payer'      => round($net, 2),
                    'statut'           => $mois === 4 ? 'paye' : 'valide',
                    'date_paiement'    => $mois === 4 ? "2026-04-28" : null,
                    'created_by'       => $this->adminId,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);
            }
        }
    }

    // ══════════════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════════════
    private function randomDob(): string
    {
        return Carbon::now()->subYears(rand(25, 55))->subDays(rand(0, 365))->toDateString();
    }

    private function randomAdresse(string $ville): string
    {
        $nums = [12, 24, 45, 67, 89, 102, 3, 15, 78];
        $rues = ['Rue Hassan II', 'Avenue Mohammed V', 'Bd Zerktouni', 'Rue Ibn Rochd', 'Avenue FAR', 'Rue Al Qods'];
        return $nums[array_rand($nums)] . ' ' . $rues[array_rand($rues)] . ', ' . $ville;
    }

    private function randomBanque(): string
    {
        return ['Attijariwafa Bank', 'CIH Bank', 'BCP', 'BMCE Bank', 'Société Générale Maroc', 'BMCI'][array_rand(['Attijariwafa Bank','CIH Bank','BCP','BMCE Bank','Société Générale Maroc','BMCI'])];
    }

    private function randomMotif(): string
    {
        return ['Maladie (certificat en cours)', 'Absence non justifiée', 'Raisons personnelles', 'Rendez-vous médical'][array_rand(['Maladie (certificat en cours)','Absence non justifiée','Raisons personnelles','Rendez-vous médical'])];
    }

    private function getWorkDays(Carbon $start, Carbon $end, int $limit): array
    {
        $days = [];
        $current = $start->copy();
        while ($current <= $end && count($days) < $limit) {
            if (!in_array($current->dayOfWeek, [0, 6])) { // Pas dimanche/samedi
                $days[] = $current->toDateString();
            }
            $current->addDay();
        }
        return $days;
    }

    // Calcul IR barème marocain mensuel (simplifié)
    private function calcIr(float $netImp): float
    {
        $annuel = $netImp * 12;
        if ($annuel <= 30000)  return 0;
        if ($annuel <= 50000)  return ($annuel - 30000) * 0.10 / 12;
        if ($annuel <= 60000)  return (($annuel - 50000) * 0.15 + 2000) / 12;
        if ($annuel <= 80000)  return (($annuel - 60000) * 0.20 + 3500) / 12;
        if ($annuel <= 180000) return (($annuel - 80000) * 0.30 + 7500) / 12;
        return (($annuel - 180000) * 0.34 + 37500) / 12;
    }
}
