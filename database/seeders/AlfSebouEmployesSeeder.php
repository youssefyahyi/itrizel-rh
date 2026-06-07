<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\UniteOrganisationnelle;
use App\Models\Employe;
use App\Models\Contrat;

/**
 * Seeder ALF Sebou — Organisation & Employés
 *
 * Réinitialise la structure organisationnelle (unites_organisationnelles)
 * et crée 71 employés avec leurs contrats, câblés sur le référentiel
 * des emplois (fiches_poste IDs 1-38) existant en base.
 *
 * PRÉ-REQUIS : AlfSebouSeeder doit avoir été exécuté (référentiel emplois).
 * Usage     : php artisan db:seed --class=AlfSebouEmployesSeeder
 */
class AlfSebouEmployesSeeder extends Seeder
{
    /** @var array<string, int>  clé mnémonique → ID unité */
    private array $u = [];

    /** @var array<int, int>  index 1-based dans la liste → ID employé créé */
    private array $empIds = [];

    /** Compteurs de séquence matricule par année */
    private array $matSeq = [];

    /** Compteurs de séquence référence contrat par année */
    private array $ctrSeq = [];

    // ──────────────────────────────────────────────────────────────────────
    public function run(): void
    {
        $this->command->info('⏳ ALF Sebou — Chargement organisation + employés…');

        // 1. Nettoyer l'ancienne organisation
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        UniteOrganisationnelle::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->command->line('  → Ancienne organisation supprimée');

        // 2. Créer la structure ALF Sebou
        $this->buildOrganisation();
        $this->command->line('  → ' . count($this->u) . ' unités organisationnelles créées');

        // 3. Créer les 71 employés + leur contrat
        $this->buildEmployes();

        // 4. Assigner les responsables d'unités (après que les IDs employés sont connus)
        $this->assignResponsables();

        $this->command->info('✅ Terminé — récapitulatif :');
        $this->command->table(
            ['Ressource', 'Nombre'],
            [
                ['Unités organisationnelles', count($this->u)],
                ['Employés actifs', Employe::count()],
                ['Contrats',        Contrat::count()],
            ]
        );
    }

    // ──────────────────────────────────────────────────────────────────────
    // ORGANISATION
    // ──────────────────────────────────────────────────────────────────────

    private function buildOrganisation(): void
    {
        // Raccourci : crée une unité, stocke son ID, retourne l'ID
        $mk = function (string $key, string $code, string $nom, string $type, ?string $parent, int $ordre): void {
            $this->u[$key] = UniteOrganisationnelle::create([
                'code'      => $code,
                'nom'       => $nom,
                'type'      => $type,
                'parent_id' => $parent ? $this->u[$parent] : null,
                'ordre'     => $ordre,
                'actif'     => true,
            ])->id;
        };

        // Racine
        $mk('dg',    'DG',    'Direction Générale',                      'direction', null,   1);
        // Directions filles
        $mk('arh',   'ARH',   'Direction Administrative & RH',            'direction', 'dg',  1);
        $mk('dfc',   'DFC',   'Direction Finance & Comptabilité',          'direction', 'dg',  2);
        $mk('dcom',  'DCOM',  'Direction Commerciale',                     'direction', 'dg',  3);
        $mk('dach',  'DACH',  'Direction Achats & Approvisionnement',      'direction', 'dg',  4);
        $mk('dlog',  'DLOG',  'Direction Logistique & Transport',          'direction', 'dg',  5);
        $mk('dmkt',  'DMKT',  'Direction Marketing & Communication',       'direction', 'dg',  6);
        $mk('dsi',   'DSI',   "Direction Systèmes d'Information",          'direction', 'dg',  7);
        // Services ARH
        $mk('srh',   'SRH',   'Service RH & Paie',                        'service',   'arh', 1);
        $mk('sadm',  'SADM',  'Service Administratif',                    'service',   'arh', 2);
        // Services Finance
        $mk('scpta', 'SCPTA', 'Service Comptabilité',                     'service',   'dfc', 1);
        $mk('srec',  'SREC',  'Service Recouvrement',                     'service',   'dfc', 2);
        // Équipes Commercial
        $mk('enord', 'ENORD', 'Équipe Ventes Terrain Nord',               'equipe',    'dcom',1);
        $mk('esud',  'ESUD',  'Équipe Ventes Terrain Sud',                'equipe',    'dcom',2);
        $mk('stele', 'STELE', 'Service Télémarketing & Clientèle',        'service',   'dcom',3);
        // Services Logistique
        $mk('depot', 'DEPOT', 'Dépôt Central Kénitra',                   'service',   'dlog',1);
        $mk('flot',  'FLOT',  'Flotte & Livraison',                      'service',   'dlog',2);
    }

    // ──────────────────────────────────────────────────────────────────────
    // EMPLOYÉS
    // ──────────────────────────────────────────────────────────────────────

    private function buildEmployes(): void
    {
        $defs = $this->definitions();

        foreach ($defs as $idx => $d) {
            $num = $idx + 1;
            $emp = Employe::create($this->empData($d, $num));
            $this->empIds[$num] = $emp->id;
            Contrat::create($this->contratData($emp, $d));
        }

        // Câbler manager_id maintenant que tous les IDs sont disponibles
        foreach ($defs as $idx => $d) {
            if ($d['manager'] !== null) {
                Employe::where('id', $this->empIds[$idx + 1])
                    ->update(['manager_id' => $this->empIds[$d['manager']]]);
            }
        }

        $this->command->line('  → ' . count($defs) . ' employés et contrats créés');
    }

    private function empData(array $d, int $seq): array
    {
        $annee = substr($d['embauche'], 0, 4);
        $this->matSeq[$annee] = ($this->matSeq[$annee] ?? 0) + 1;

        return [
            'matricule'           => 'EMP-' . $annee . '-' . str_pad($this->matSeq[$annee], 4, '0', STR_PAD_LEFT),
            'nom'                 => $d['nom'],
            'prenom'              => $d['prenom'],
            'cin'                 => $d['cin'],
            'sexe'                => $d['sexe'],
            'date_naissance'      => $d['naissance'],
            'lieu_naissance'      => $d['lieu'],
            'nationalite'         => 'Marocaine',
            'situation_familiale' => $d['famille'],
            'nombre_enfants'      => $d['enfants'],
            'email'               => $d['email'],
            'telephone'           => $d['tel'],
            'ville'               => $d['ville'],
            'date_embauche'       => $d['embauche'],
            'fiche_poste_id'      => $d['fiche'],
            'unite_id'            => $this->u[$d['unite']],
            'statut'              => 'actif',
            'created_by'          => 1,
        ];
    }

    private function contratData(Employe $emp, array $d): array
    {
        $annee  = substr($d['embauche'], 0, 4);
        $this->ctrSeq[$annee] = ($this->ctrSeq[$annee] ?? 0) + 1;
        $ref    = 'CTR-' . $annee . '-' . str_pad($this->ctrSeq[$annee], 4, '0', STR_PAD_LEFT);

        $debut  = Carbon::parse($d['embauche']);
        $type   = $d['contrat'];

        if ($type === 'CDD') {
            $finCalc = $debut->copy()->addYear();
            // Si la date de fin calculée est passée, on simule un renouvellement
            if ($finCalc->isPast()) {
                $fin    = Carbon::now()->addMonths(6)->toDateString();
                $statut = 'en_cours';
            } else {
                $fin    = $finCalc->toDateString();
                $statut = 'en_cours';
            }
        } else {
            $fin    = null;
            $statut = 'en_cours';
        }

        return [
            'employe_id'          => $emp->id,
            'reference'           => $ref,
            'type'                => $type,
            'fiche_poste_id'      => $d['fiche'],
            'salaire_base'        => $d['salaire'],
            'date_debut'          => $d['embauche'],
            'date_fin'            => $fin,
            'duree_mois'          => $type === 'CDD' ? 12 : null,
            'renouvellement_auto' => $type === 'CDD',
            'statut'              => $statut,
            'created_by'          => 1,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // RESPONSABLES D'UNITÉS
    // ──────────────────────────────────────────────────────────────────────

    private function assignResponsables(): void
    {
        // clé unité => numéro (1-based) de l'employé responsable
        $map = [
            'dg'    =>  1,  // BENNANI Mohamed — DG
            'arh'   =>  3,  // CHERKAOUI Fatima — DRH
            'srh'   =>  4,  // HAJJI Nadia — Resp RH
            'sadm'  =>  6,  // BENHADDOU Said — Agent admin
            'dfc'   =>  8,  // KETTANI Omar — DAF
            'scpta' =>  9,  // REGRAGUI Saad — Chef comptable
            'srec'  => 12,  // BAKKALI Yasmine — Resp recouvrement
            'dcom'  => 15,  // FASSI Amine — Dir commercial
            'enord' => 16,  // OUALI Youssef — Resp comm Nord
            'esud'  => 17,  // SAIDI Mehdi — Resp comm Sud
            'stele' => 28,  // AMRANI Sara — Chargée clientèle (cheffe de service télémarketing)
            'dach'  => 35,  // MEKKI Noureddine — Dir achats
            'dlog'  => 41,  // IDRISSI Fouad — Dir logistique
            'depot' => 43,  // AOUAD Rachid — Chef dépôt
            'flot'  => 44,  // BENKIRANE Khalid — Chef flotte
            'dmkt'  => 66,  // DOUIBI Rim — Resp marketing
            'dsi'   => 69,  // BENSAID Youssef — RSI
        ];

        foreach ($map as $key => $empNum) {
            UniteOrganisationnelle::where('id', $this->u[$key])
                ->update(['responsable_id' => $this->empIds[$empNum]]);
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // DONNÉES DES 71 EMPLOYÉS
    // ──────────────────────────────────────────────────────────────────────
    // Colonnes : nom | prenom | sexe | cin | naissance | lieu | famille | enfants
    //            email | tel | ville | embauche | fiche (ID fiches_poste)
    //            unite (clé) | contrat | salaire | manager (index 1-based ou null)
    //
    // Fiches de poste (référentiel AlfSebouSeeder) :
    //   37 DG · 38 DGA · 32 DRH · 17 RespRH · 18 ChgRH-Paie · 8 AgentAdm · 9 SecrDir
    //   33 DAF · 19 ChefCompta · 21 Comptable · 20 RespRecouv · 6 AgentRecouv
    //   34 DirCom · 22 RespCommReg · 23 Commercial · 16 SupervCom · 24 ChgClientèle · 10 Télév
    //   35 DirAch · 25 RespAchat · 26 Acheteur · 27 AgentAppro
    //   36 DirLog · 28 RespLog · 15 ChefDépôt · 11 GestStock
    //   2 PrépaCmd · 1 Manutentn · 3 Cariste · 4 Chauffeur-livreur · 5 Chauffeur PL
    //   29 RespMkt · 30 ChgDigital · 14 Designer · 31 RSI · 12 TechInfo · 13 Dév
    // ──────────────────────────────────────────────────────────────────────
    private function definitions(): array
    {
        return [
            // ── 1-2 : Direction Générale ───────────────────────────────────────
            /* 01 */ ['nom'=>'BENNANI',   'prenom'=>'Mohamed',      'sexe'=>'M','cin'=>'BE100001','naissance'=>'1970-03-15','lieu'=>'Rabat',       'famille'=>'marie',      'enfants'=>3,'email'=>'m.bennani@alfsebou.ma',    'tel'=>'0661000001','ville'=>'Kénitra','embauche'=>'2018-01-15','fiche'=>37,'unite'=>'dg',   'contrat'=>'CDI','salaire'=>38000,'manager'=>null],
            /* 02 */ ['nom'=>'ALAOUI',    'prenom'=>'Rachid',       'sexe'=>'M','cin'=>'BK100002','naissance'=>'1975-07-22','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>2,'email'=>'r.alaoui@alfsebou.ma',     'tel'=>'0661000002','ville'=>'Kénitra','embauche'=>'2019-03-01','fiche'=>38,'unite'=>'dg',   'contrat'=>'CDI','salaire'=>32000,'manager'=>1],

            // ── 3-7 : Direction Administrative & RH ───────────────────────────
            /* 03 */ ['nom'=>'CHERKAOUI', 'prenom'=>'Fatima',       'sexe'=>'F','cin'=>'BB100003','naissance'=>'1978-11-05','lieu'=>'Rabat',       'famille'=>'marie',      'enfants'=>2,'email'=>'f.cherkaoui@alfsebou.ma',  'tel'=>'0661000003','ville'=>'Kénitra','embauche'=>'2019-06-01','fiche'=>32,'unite'=>'arh', 'contrat'=>'CDI','salaire'=>22000,'manager'=>2],
            /* 04 */ ['nom'=>'HAJJI',     'prenom'=>'Nadia',        'sexe'=>'F','cin'=>'BJ100004','naissance'=>'1985-04-18','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>1,'email'=>'n.hajji@alfsebou.ma',      'tel'=>'0661000004','ville'=>'Kénitra','embauche'=>'2020-02-01','fiche'=>17,'unite'=>'srh', 'contrat'=>'CDI','salaire'=>12000,'manager'=>3],
            /* 05 */ ['nom'=>'TAZI',      'prenom'=>'Houda',        'sexe'=>'F','cin'=>'BG100005','naissance'=>'1990-08-30','lieu'=>'Salé',        'famille'=>'celibataire','enfants'=>0,'email'=>'h.tazi@alfsebou.ma',       'tel'=>'0661000005','ville'=>'Kénitra','embauche'=>'2021-03-15','fiche'=>18,'unite'=>'srh', 'contrat'=>'CDI','salaire'=>9000, 'manager'=>4],
            /* 06 */ ['nom'=>'BENHADDOU', 'prenom'=>'Said',         'sexe'=>'M','cin'=>'BL100006','naissance'=>'1992-02-14','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'s.benhaddou@alfsebou.ma',  'tel'=>'0661000006','ville'=>'Kénitra','embauche'=>'2021-09-01','fiche'=>8, 'unite'=>'sadm','contrat'=>'CDI','salaire'=>5000, 'manager'=>3],
            /* 07 */ ['nom'=>'ZIANI',     'prenom'=>'Samira',       'sexe'=>'F','cin'=>'BC100007','naissance'=>'1988-06-20','lieu'=>'Meknès',      'famille'=>'marie',      'enfants'=>1,'email'=>'s.ziani@alfsebou.ma',      'tel'=>'0661000007','ville'=>'Kénitra','embauche'=>'2020-05-01','fiche'=>9, 'unite'=>'sadm','contrat'=>'CDI','salaire'=>5500, 'manager'=>2],

            // ── 8-14 : Direction Finance & Comptabilité ────────────────────────
            /* 08 */ ['nom'=>'KETTANI',   'prenom'=>'Omar',         'sexe'=>'M','cin'=>'BE100008','naissance'=>'1976-09-03','lieu'=>'Fès',         'famille'=>'marie',      'enfants'=>3,'email'=>'o.kettani@alfsebou.ma',    'tel'=>'0661000008','ville'=>'Kénitra','embauche'=>'2019-09-01','fiche'=>33,'unite'=>'dfc', 'contrat'=>'CDI','salaire'=>24000,'manager'=>2],
            /* 09 */ ['nom'=>'REGRAGUI',  'prenom'=>'Saad',         'sexe'=>'M','cin'=>'BH100009','naissance'=>'1982-12-11','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>2,'email'=>'s.regragui@alfsebou.ma',   'tel'=>'0661000009','ville'=>'Kénitra','embauche'=>'2020-04-01','fiche'=>19,'unite'=>'scpta','contrat'=>'CDI','salaire'=>14000,'manager'=>8],
            /* 10 */ ['nom'=>'LAHLOU',    'prenom'=>'Asmae',        'sexe'=>'F','cin'=>'BA100010','naissance'=>'1991-03-25','lieu'=>'Rabat',       'famille'=>'celibataire','enfants'=>0,'email'=>'a.lahlou@alfsebou.ma',     'tel'=>'0661000010','ville'=>'Kénitra','embauche'=>'2021-06-01','fiche'=>21,'unite'=>'scpta','contrat'=>'CDI','salaire'=>9500, 'manager'=>9],
            /* 11 */ ['nom'=>'BERRADA',   'prenom'=>'Hassan',       'sexe'=>'M','cin'=>'BK100011','naissance'=>'1993-07-08','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'h.berrada@alfsebou.ma',    'tel'=>'0661000011','ville'=>'Kénitra','embauche'=>'2022-01-01','fiche'=>21,'unite'=>'scpta','contrat'=>'CDI','salaire'=>9000, 'manager'=>9],
            /* 12 */ ['nom'=>'BAKKALI',   'prenom'=>'Yasmine',      'sexe'=>'F','cin'=>'BJ100012','naissance'=>'1986-10-17','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>1,'email'=>'y.bakkali@alfsebou.ma',    'tel'=>'0661000012','ville'=>'Kénitra','embauche'=>'2021-03-01','fiche'=>20,'unite'=>'srec', 'contrat'=>'CDI','salaire'=>11000,'manager'=>8],
            /* 13 */ ['nom'=>'MANSOURI',  'prenom'=>'Ilyas',        'sexe'=>'M','cin'=>'BB100013','naissance'=>'1995-01-29','lieu'=>'Salé',        'famille'=>'celibataire','enfants'=>0,'email'=>'i.mansouri@alfsebou.ma',   'tel'=>'0661000013','ville'=>'Kénitra','embauche'=>'2022-06-01','fiche'=>6, 'unite'=>'srec', 'contrat'=>'CDI','salaire'=>5000, 'manager'=>12],
            /* 14 */ ['nom'=>'FILALI',    'prenom'=>'Fatima-Zahra', 'sexe'=>'F','cin'=>'BC100014','naissance'=>'1998-05-12','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'fz.filali@alfsebou.ma',    'tel'=>'0661000014','ville'=>'Kénitra','embauche'=>'2025-01-15','fiche'=>6, 'unite'=>'srec', 'contrat'=>'CDD','salaire'=>5000, 'manager'=>12],

            // ── 15-34 : Direction Commerciale ─────────────────────────────────
            /* 15 */ ['nom'=>'FASSI',     'prenom'=>'Amine',        'sexe'=>'M','cin'=>'BE100015','naissance'=>'1979-08-04','lieu'=>'Casablanca',  'famille'=>'marie',      'enfants'=>2,'email'=>'a.fassi@alfsebou.ma',      'tel'=>'0661000015','ville'=>'Kénitra','embauche'=>'2019-04-01','fiche'=>34,'unite'=>'dcom', 'contrat'=>'CDI','salaire'=>26000,'manager'=>1],
            /* 16 */ ['nom'=>'OUALI',     'prenom'=>'Youssef',      'sexe'=>'M','cin'=>'BL100016','naissance'=>'1983-02-28','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>2,'email'=>'y.ouali@alfsebou.ma',     'tel'=>'0661000016','ville'=>'Kénitra','embauche'=>'2020-01-15','fiche'=>22,'unite'=>'enord','contrat'=>'CDI','salaire'=>15000,'manager'=>15],
            /* 17 */ ['nom'=>'SAIDI',     'prenom'=>'Mehdi',        'sexe'=>'M','cin'=>'BG100017','naissance'=>'1984-06-14','lieu'=>'Rabat',       'famille'=>'marie',      'enfants'=>1,'email'=>'m.saidi@alfsebou.ma',     'tel'=>'0661000017','ville'=>'Kénitra','embauche'=>'2021-02-01','fiche'=>22,'unite'=>'esud', 'contrat'=>'CDI','salaire'=>15000,'manager'=>15],
            /* 18 */ ['nom'=>'MOUSSAOUI', 'prenom'=>'Karim',        'sexe'=>'M','cin'=>'BH100018','naissance'=>'1989-11-22','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'k.moussaoui@alfsebou.ma', 'tel'=>'0661000018','ville'=>'Kénitra','embauche'=>'2021-05-01','fiche'=>23,'unite'=>'enord','contrat'=>'CDI','salaire'=>10000,'manager'=>16],
            /* 19 */ ['nom'=>'TAHIRI',    'prenom'=>'Soufiane',     'sexe'=>'M','cin'=>'BA100019','naissance'=>'1991-04-09','lieu'=>'Salé',        'famille'=>'celibataire','enfants'=>0,'email'=>'s.tahiri@alfsebou.ma',    'tel'=>'0661000019','ville'=>'Kénitra','embauche'=>'2022-03-01','fiche'=>23,'unite'=>'enord','contrat'=>'CDI','salaire'=>10500,'manager'=>16],
            /* 20 */ ['nom'=>'BELKADI',   'prenom'=>'Imane',        'sexe'=>'F','cin'=>'BK100020','naissance'=>'1993-09-17','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'i.belkadi@alfsebou.ma',   'tel'=>'0661000020','ville'=>'Kénitra','embauche'=>'2022-09-01','fiche'=>23,'unite'=>'enord','contrat'=>'CDI','salaire'=>10000,'manager'=>16],
            /* 21 */ ['nom'=>'CHRAIBI',   'prenom'=>'Othmane',      'sexe'=>'M','cin'=>'BC100021','naissance'=>'1988-12-01','lieu'=>'Fès',         'famille'=>'marie',      'enfants'=>1,'email'=>'o.chraibi@alfsebou.ma',   'tel'=>'0661000021','ville'=>'Kénitra','embauche'=>'2021-07-01','fiche'=>23,'unite'=>'esud', 'contrat'=>'CDI','salaire'=>10500,'manager'=>17],
            /* 22 */ ['nom'=>'BOUAZZAOUI','prenom'=>'Zineb',        'sexe'=>'F','cin'=>'BJ100022','naissance'=>'1990-03-08','lieu'=>'Meknès',      'famille'=>'celibataire','enfants'=>0,'email'=>'z.bouazzaoui@alfsebou.ma','tel'=>'0661000022','ville'=>'Kénitra','embauche'=>'2022-04-01','fiche'=>23,'unite'=>'esud', 'contrat'=>'CDI','salaire'=>10000,'manager'=>17],
            /* 23 */ ['nom'=>'DRISSI',    'prenom'=>'Hamza',        'sexe'=>'M','cin'=>'BB100023','naissance'=>'1992-07-19','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'h.drissi@alfsebou.ma',    'tel'=>'0661000023','ville'=>'Kénitra','embauche'=>'2023-01-01','fiche'=>23,'unite'=>'esud', 'contrat'=>'CDI','salaire'=>10000,'manager'=>17],
            /* 24 */ ['nom'=>'RZINI',     'prenom'=>'Loubna',       'sexe'=>'F','cin'=>'BE100024','naissance'=>'1996-10-26','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'l.rzini@alfsebou.ma',     'tel'=>'0661000024','ville'=>'Kénitra','embauche'=>'2025-03-01','fiche'=>23,'unite'=>'enord','contrat'=>'CDD','salaire'=>9500, 'manager'=>16],
            /* 25 */ ['nom'=>'BENALI',    'prenom'=>'Khalid',       'sexe'=>'M','cin'=>'BL100025','naissance'=>'1994-02-13','lieu'=>'Salé',        'famille'=>'celibataire','enfants'=>0,'email'=>'kh.benali@alfsebou.ma',   'tel'=>'0661000025','ville'=>'Kénitra','embauche'=>'2025-06-01','fiche'=>23,'unite'=>'esud', 'contrat'=>'CDD','salaire'=>9500, 'manager'=>17],
            /* 26 */ ['nom'=>'NASRI',     'prenom'=>'Ayoub',        'sexe'=>'M','cin'=>'BG100026','naissance'=>'1987-05-31','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>1,'email'=>'a.nasri@alfsebou.ma',     'tel'=>'0661000026','ville'=>'Kénitra','embauche'=>'2021-10-01','fiche'=>16,'unite'=>'enord','contrat'=>'CDI','salaire'=>8500, 'manager'=>16],
            /* 27 */ ['nom'=>'QADIRI',    'prenom'=>'Hanane',       'sexe'=>'F','cin'=>'BA100027','naissance'=>'1989-08-23','lieu'=>'Rabat',       'famille'=>'celibataire','enfants'=>0,'email'=>'h.qadiri@alfsebou.ma',    'tel'=>'0661000027','ville'=>'Kénitra','embauche'=>'2022-08-01','fiche'=>16,'unite'=>'esud', 'contrat'=>'CDI','salaire'=>8000, 'manager'=>17],
            /* 28 */ ['nom'=>'AMRANI',    'prenom'=>'Sara',         'sexe'=>'F','cin'=>'BK100028','naissance'=>'1986-01-07','lieu'=>'Casablanca',  'famille'=>'marie',      'enfants'=>1,'email'=>'s.amrani@alfsebou.ma',    'tel'=>'0661000028','ville'=>'Kénitra','embauche'=>'2021-11-01','fiche'=>24,'unite'=>'stele','contrat'=>'CDI','salaire'=>11000,'manager'=>15],
            /* 29 */ ['nom'=>'BENSOUDA',  'prenom'=>'Yassine',      'sexe'=>'M','cin'=>'BC100029','naissance'=>'1988-04-15','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>2,'email'=>'y.bensouda@alfsebou.ma',  'tel'=>'0661000029','ville'=>'Kénitra','embauche'=>'2022-02-01','fiche'=>24,'unite'=>'stele','contrat'=>'CDI','salaire'=>11000,'manager'=>15],
            /* 30 */ ['nom'=>'ESSABRI',   'prenom'=>'Myriam',       'sexe'=>'F','cin'=>'BB100030','naissance'=>'1991-09-02','lieu'=>'Salé',        'famille'=>'celibataire','enfants'=>0,'email'=>'m.essabri@alfsebou.ma',   'tel'=>'0661000030','ville'=>'Kénitra','embauche'=>'2023-05-01','fiche'=>24,'unite'=>'stele','contrat'=>'CDI','salaire'=>10500,'manager'=>15],
            /* 31 */ ['nom'=>'RAHMANI',   'prenom'=>'Tariq',        'sexe'=>'M','cin'=>'BJ100031','naissance'=>'1990-12-18','lieu'=>'Meknès',      'famille'=>'marie',      'enfants'=>1,'email'=>'t.rahmani@alfsebou.ma',   'tel'=>'0661000031','ville'=>'Kénitra','embauche'=>'2024-01-01','fiche'=>24,'unite'=>'stele','contrat'=>'CDI','salaire'=>10500,'manager'=>15],
            /* 32 */ ['nom'=>'TOUZANI',   'prenom'=>'Ines',         'sexe'=>'F','cin'=>'BE100032','naissance'=>'1995-06-24','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'i.touzani@alfsebou.ma',   'tel'=>'0661000032','ville'=>'Kénitra','embauche'=>'2023-09-01','fiche'=>10,'unite'=>'stele','contrat'=>'CDI','salaire'=>5500, 'manager'=>28],
            /* 33 */ ['nom'=>'BENALI',    'prenom'=>'Zakaria',      'sexe'=>'M','cin'=>'BL100033','naissance'=>'1997-11-10','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'z.benali@alfsebou.ma',    'tel'=>'0661000033','ville'=>'Kénitra','embauche'=>'2024-06-01','fiche'=>10,'unite'=>'stele','contrat'=>'CDI','salaire'=>5500, 'manager'=>28],
            /* 34 */ ['nom'=>'CHAHBI',    'prenom'=>'Lamiae',       'sexe'=>'F','cin'=>'BG100034','naissance'=>'1999-03-05','lieu'=>'Rabat',       'famille'=>'celibataire','enfants'=>0,'email'=>'l.chahbi@alfsebou.ma',    'tel'=>'0661000034','ville'=>'Kénitra','embauche'=>'2025-04-01','fiche'=>10,'unite'=>'stele','contrat'=>'CDD','salaire'=>5500, 'manager'=>28],

            // ── 35-40 : Direction Achats & Approvisionnement ───────────────────
            /* 35 */ ['nom'=>'MEKKI',     'prenom'=>'Noureddine',   'sexe'=>'M','cin'=>'BA100035','naissance'=>'1977-06-16','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>3,'email'=>'n.mekki@alfsebou.ma',     'tel'=>'0661000035','ville'=>'Kénitra','embauche'=>'2019-07-01','fiche'=>35,'unite'=>'dach', 'contrat'=>'CDI','salaire'=>22000,'manager'=>2],
            /* 36 */ ['nom'=>'LAMRANI',   'prenom'=>'Hind',         'sexe'=>'F','cin'=>'BK100036','naissance'=>'1984-01-28','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>1,'email'=>'h.lamrani@alfsebou.ma',   'tel'=>'0661000036','ville'=>'Kénitra','embauche'=>'2021-01-15','fiche'=>25,'unite'=>'dach', 'contrat'=>'CDI','salaire'=>13000,'manager'=>35],
            /* 37 */ ['nom'=>'BARGACH',   'prenom'=>'Anas',         'sexe'=>'M','cin'=>'BC100037','naissance'=>'1989-08-07','lieu'=>'Salé',        'famille'=>'celibataire','enfants'=>0,'email'=>'a.bargach@alfsebou.ma',   'tel'=>'0661000037','ville'=>'Kénitra','embauche'=>'2022-05-01','fiche'=>26,'unite'=>'dach', 'contrat'=>'CDI','salaire'=>11000,'manager'=>36],
            /* 38 */ ['nom'=>'DAOUDI',    'prenom'=>'Siham',        'sexe'=>'F','cin'=>'BB100038','naissance'=>'1992-11-14','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'s.daoudi@alfsebou.ma',    'tel'=>'0661000038','ville'=>'Kénitra','embauche'=>'2023-02-01','fiche'=>26,'unite'=>'dach', 'contrat'=>'CDI','salaire'=>11000,'manager'=>36],
            /* 39 */ ['nom'=>'BENOMAR',   'prenom'=>'Ayoub',        'sexe'=>'M','cin'=>'BJ100039','naissance'=>'1994-04-21','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'a.benomar@alfsebou.ma',   'tel'=>'0661000039','ville'=>'Kénitra','embauche'=>'2023-08-01','fiche'=>27,'unite'=>'dach', 'contrat'=>'CDI','salaire'=>8000, 'manager'=>36],
            /* 40 */ ['nom'=>'SLAOUI',    'prenom'=>'Kenza',        'sexe'=>'F','cin'=>'BE100040','naissance'=>'1997-09-03','lieu'=>'Rabat',       'famille'=>'celibataire','enfants'=>0,'email'=>'k.slaoui@alfsebou.ma',    'tel'=>'0661000040','ville'=>'Kénitra','embauche'=>'2025-02-01','fiche'=>27,'unite'=>'dach', 'contrat'=>'CDD','salaire'=>7500, 'manager'=>36],

            // ── 41-65 : Direction Logistique & Transport ───────────────────────
            /* 41 */ ['nom'=>'IDRISSI',   'prenom'=>'Fouad',        'sexe'=>'M','cin'=>'BL100041','naissance'=>'1973-12-09','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>4,'email'=>'f.idrissi@alfsebou.ma',   'tel'=>'0661000041','ville'=>'Kénitra','embauche'=>'2018-06-01','fiche'=>36,'unite'=>'dlog', 'contrat'=>'CDI','salaire'=>23000,'manager'=>2],
            /* 42 */ ['nom'=>'HAJJAM',    'prenom'=>'Youssef',      'sexe'=>'M','cin'=>'BG100042','naissance'=>'1981-05-26','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>2,'email'=>'y.hajjam@alfsebou.ma',    'tel'=>'0661000042','ville'=>'Kénitra','embauche'=>'2020-08-01','fiche'=>28,'unite'=>'dlog', 'contrat'=>'CDI','salaire'=>14000,'manager'=>41],
            /* 43 */ ['nom'=>'AOUAD',     'prenom'=>'Rachid',       'sexe'=>'M','cin'=>'BA100043','naissance'=>'1983-10-14','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>2,'email'=>'r.aouad@alfsebou.ma',     'tel'=>'0661000043','ville'=>'Kénitra','embauche'=>'2020-11-01','fiche'=>15,'unite'=>'depot','contrat'=>'CDI','salaire'=>9000, 'manager'=>42],
            /* 44 */ ['nom'=>'BENKIRANE', 'prenom'=>'Khalid',       'sexe'=>'M','cin'=>'BK100044','naissance'=>'1985-07-31','lieu'=>'Salé',        'famille'=>'marie',      'enfants'=>1,'email'=>'kh.benkirane@alfsebou.ma','tel'=>'0661000044','ville'=>'Kénitra','embauche'=>'2021-06-01','fiche'=>15,'unite'=>'flot', 'contrat'=>'CDI','salaire'=>9000, 'manager'=>42],
            /* 45 */ ['nom'=>'SAHLI',     'prenom'=>'Mohamed',      'sexe'=>'M','cin'=>'BC100045','naissance'=>'1988-02-18','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'m.sahli@alfsebou.ma',     'tel'=>'0661000045','ville'=>'Kénitra','embauche'=>'2021-09-01','fiche'=>11,'unite'=>'depot','contrat'=>'CDI','salaire'=>7000, 'manager'=>43],
            /* 46 */ ['nom'=>'BOUAYAD',   'prenom'=>'Zineb',        'sexe'=>'F','cin'=>'BB100046','naissance'=>'1990-06-05','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'z.bouayad@alfsebou.ma',   'tel'=>'0661000046','ville'=>'Kénitra','embauche'=>'2022-04-01','fiche'=>11,'unite'=>'depot','contrat'=>'CDI','salaire'=>7000, 'manager'=>43],
            /* 47 */ ['nom'=>'LAHLOU',    'prenom'=>'Imad',         'sexe'=>'M','cin'=>'BJ100047','naissance'=>'1993-12-22','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'im.lahlou@alfsebou.ma',   'tel'=>'0661000047','ville'=>'Kénitra','embauche'=>'2022-10-01','fiche'=>11,'unite'=>'flot', 'contrat'=>'CDI','salaire'=>7000, 'manager'=>44],
            /* 48 */ ['nom'=>'BENALI',    'prenom'=>'Omar',         'sexe'=>'M','cin'=>'BE100048','naissance'=>'1991-08-10','lieu'=>'Salé',        'famille'=>'celibataire','enfants'=>0,'email'=>'om.benali@alfsebou.ma',   'tel'=>'0661000048','ville'=>'Kénitra','embauche'=>'2022-01-01','fiche'=>2, 'unite'=>'depot','contrat'=>'CDI','salaire'=>4500, 'manager'=>43],
            /* 49 */ ['nom'=>'CHAKRI',    'prenom'=>'Samir',        'sexe'=>'M','cin'=>'BL100049','naissance'=>'1992-04-27','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'s.chakri@alfsebou.ma',    'tel'=>'0661000049','ville'=>'Kénitra','embauche'=>'2022-07-01','fiche'=>2, 'unite'=>'depot','contrat'=>'CDI','salaire'=>4500, 'manager'=>43],
            /* 50 */ ['nom'=>'FILALI',    'prenom'=>'Youssef',      'sexe'=>'M','cin'=>'BG100050','naissance'=>'1993-11-15','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'y.filali@alfsebou.ma',    'tel'=>'0661000050','ville'=>'Kénitra','embauche'=>'2023-03-01','fiche'=>2, 'unite'=>'depot','contrat'=>'CDI','salaire'=>4500, 'manager'=>43],
            /* 51 */ ['nom'=>'OUAZZANI',  'prenom'=>'Brahim',       'sexe'=>'M','cin'=>'BA100051','naissance'=>'1990-01-30','lieu'=>'Meknès',      'famille'=>'marie',      'enfants'=>1,'email'=>'b.ouazzani@alfsebou.ma',  'tel'=>'0661000051','ville'=>'Kénitra','embauche'=>'2024-01-01','fiche'=>2, 'unite'=>'depot','contrat'=>'CDI','salaire'=>4500, 'manager'=>43],
            /* 52 */ ['nom'=>'TAOUFIK',   'prenom'=>'Anas',         'sexe'=>'M','cin'=>'BK100052','naissance'=>'1998-07-17','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'an.taoufik@alfsebou.ma',  'tel'=>'0661000052','ville'=>'Kénitra','embauche'=>'2025-05-01','fiche'=>2, 'unite'=>'depot','contrat'=>'CDD','salaire'=>4200, 'manager'=>43],
            /* 53 */ ['nom'=>'MOULAY',    'prenom'=>'Hassan',       'sexe'=>'M','cin'=>'BC100053','naissance'=>'1985-09-23','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>2,'email'=>'h.moulay@alfsebou.ma',    'tel'=>'0661000053','ville'=>'Kénitra','embauche'=>'2021-03-01','fiche'=>1, 'unite'=>'depot','contrat'=>'CDI','salaire'=>3500, 'manager'=>43],
            /* 54 */ ['nom'=>'SEDDIK',    'prenom'=>'Khalid',       'sexe'=>'M','cin'=>'BB100054','naissance'=>'1987-06-11','lieu'=>'Salé',        'famille'=>'marie',      'enfants'=>1,'email'=>'kh.seddik@alfsebou.ma',   'tel'=>'0661000054','ville'=>'Kénitra','embauche'=>'2022-02-01','fiche'=>1, 'unite'=>'depot','contrat'=>'CDI','salaire'=>3500, 'manager'=>43],
            /* 55 */ ['nom'=>'RAMI',      'prenom'=>'Fouad',        'sexe'=>'M','cin'=>'BJ100055','naissance'=>'1993-03-04','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'f.rami@alfsebou.ma',      'tel'=>'0661000055','ville'=>'Kénitra','embauche'=>'2023-06-01','fiche'=>1, 'unite'=>'depot','contrat'=>'CDI','salaire'=>3500, 'manager'=>43],
            /* 56 */ ['nom'=>'BENSAID',   'prenom'=>'Abdelhak',     'sexe'=>'M','cin'=>'BE100056','naissance'=>'1996-12-20','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'ab.bensaid@alfsebou.ma',  'tel'=>'0661000056','ville'=>'Kénitra','embauche'=>'2025-01-01','fiche'=>1, 'unite'=>'depot','contrat'=>'CDD','salaire'=>3300, 'manager'=>43],
            /* 57 */ ['nom'=>'JABRI',     'prenom'=>'Karim',        'sexe'=>'M','cin'=>'BL100057','naissance'=>'1989-05-08','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'k.jabri@alfsebou.ma',     'tel'=>'0661000057','ville'=>'Kénitra','embauche'=>'2022-05-01','fiche'=>3, 'unite'=>'depot','contrat'=>'CDI','salaire'=>4200, 'manager'=>43],
            /* 58 */ ['nom'=>'ALAMI',     'prenom'=>'Hassan',       'sexe'=>'M','cin'=>'BG100058','naissance'=>'1991-10-16','lieu'=>'Salé',        'famille'=>'celibataire','enfants'=>0,'email'=>'h.alami@alfsebou.ma',     'tel'=>'0661000058','ville'=>'Kénitra','embauche'=>'2023-09-01','fiche'=>3, 'unite'=>'depot','contrat'=>'CDI','salaire'=>4200, 'manager'=>43],
            /* 59 */ ['nom'=>'BENALI',    'prenom'=>'Mohamed',      'sexe'=>'M','cin'=>'BA100059','naissance'=>'1983-07-02','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>2,'email'=>'mo.benali@alfsebou.ma',   'tel'=>'0661000059','ville'=>'Kénitra','embauche'=>'2020-09-01','fiche'=>4, 'unite'=>'flot', 'contrat'=>'CDI','salaire'=>5000, 'manager'=>44],
            /* 60 */ ['nom'=>'OUAZZANI',  'prenom'=>'Khalid',       'sexe'=>'M','cin'=>'BK100060','naissance'=>'1985-11-19','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>1,'email'=>'kh.ouazzani@alfsebou.ma', 'tel'=>'0661000060','ville'=>'Kénitra','embauche'=>'2021-04-01','fiche'=>4, 'unite'=>'flot', 'contrat'=>'CDI','salaire'=>5000, 'manager'=>44],
            /* 61 */ ['nom'=>'TAHIR',     'prenom'=>'Youssef',      'sexe'=>'M','cin'=>'BC100061','naissance'=>'1988-03-27','lieu'=>'Salé',        'famille'=>'marie',      'enfants'=>1,'email'=>'y.tahir@alfsebou.ma',     'tel'=>'0661000061','ville'=>'Kénitra','embauche'=>'2022-06-01','fiche'=>4, 'unite'=>'flot', 'contrat'=>'CDI','salaire'=>5000, 'manager'=>44],
            /* 62 */ ['nom'=>'SABIR',     'prenom'=>'Hamid',        'sexe'=>'M','cin'=>'BB100062','naissance'=>'1990-08-13','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'h.sabir@alfsebou.ma',     'tel'=>'0661000062','ville'=>'Kénitra','embauche'=>'2023-08-01','fiche'=>4, 'unite'=>'flot', 'contrat'=>'CDI','salaire'=>5000, 'manager'=>44],
            /* 63 */ ['nom'=>'CHAMI',     'prenom'=>'Abdelhak',     'sexe'=>'M','cin'=>'BJ100063','naissance'=>'1992-02-01','lieu'=>'Meknès',      'famille'=>'celibataire','enfants'=>0,'email'=>'a.chami@alfsebou.ma',     'tel'=>'0661000063','ville'=>'Kénitra','embauche'=>'2024-09-01','fiche'=>4, 'unite'=>'flot', 'contrat'=>'CDI','salaire'=>5000, 'manager'=>44],
            /* 64 */ ['nom'=>'LAKHDAR',   'prenom'=>'Brahim',       'sexe'=>'M','cin'=>'BE100064','naissance'=>'1980-04-24','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>3,'email'=>'b.lakhdar@alfsebou.ma',   'tel'=>'0661000064','ville'=>'Kénitra','embauche'=>'2021-07-01','fiche'=>5, 'unite'=>'flot', 'contrat'=>'CDI','salaire'=>6500, 'manager'=>44],
            /* 65 */ ['nom'=>'SENHAJI',   'prenom'=>'Ahmed',        'sexe'=>'M','cin'=>'BL100065','naissance'=>'1982-09-06','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>2,'email'=>'a.senhaji@alfsebou.ma',   'tel'=>'0661000065','ville'=>'Kénitra','embauche'=>'2022-11-01','fiche'=>5, 'unite'=>'flot', 'contrat'=>'CDI','salaire'=>6500, 'manager'=>44],

            // ── 66-68 : Direction Marketing & Communication ────────────────────
            /* 66 */ ['nom'=>'DOUIBI',    'prenom'=>'Rim',          'sexe'=>'F','cin'=>'BG100066','naissance'=>'1985-01-13','lieu'=>'Casablanca',  'famille'=>'marie',      'enfants'=>1,'email'=>'r.douibi@alfsebou.ma',    'tel'=>'0661000066','ville'=>'Kénitra','embauche'=>'2020-10-01','fiche'=>29,'unite'=>'dmkt', 'contrat'=>'CDI','salaire'=>13000,'manager'=>1],
            /* 67 */ ['nom'=>'CHRAIBI',   'prenom'=>'Leila',        'sexe'=>'F','cin'=>'BA100067','naissance'=>'1991-07-21','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'l.chraibi@alfsebou.ma',   'tel'=>'0661000067','ville'=>'Kénitra','embauche'=>'2022-03-01','fiche'=>30,'unite'=>'dmkt', 'contrat'=>'CDI','salaire'=>10000,'manager'=>66],
            /* 68 */ ['nom'=>'TOUZANI',   'prenom'=>'Hassan',       'sexe'=>'M','cin'=>'BK100068','naissance'=>'1993-05-09','lieu'=>'Salé',        'famille'=>'celibataire','enfants'=>0,'email'=>'ha.touzani@alfsebou.ma',  'tel'=>'0661000068','ville'=>'Kénitra','embauche'=>'2023-07-01','fiche'=>14,'unite'=>'dmkt', 'contrat'=>'CDI','salaire'=>7500, 'manager'=>66],

            // ── 69-71 : Direction Systèmes d'Information ──────────────────────
            /* 69 */ ['nom'=>'BENSAID',   'prenom'=>'Youssef',      'sexe'=>'M','cin'=>'BC100069','naissance'=>'1982-03-17','lieu'=>'Kénitra',     'famille'=>'marie',      'enfants'=>2,'email'=>'y.bensaid@alfsebou.ma',   'tel'=>'0661000069','ville'=>'Kénitra','embauche'=>'2020-01-15','fiche'=>31,'unite'=>'dsi',  'contrat'=>'CDI','salaire'=>14000,'manager'=>2],
            /* 70 */ ['nom'=>'MANSOURI',  'prenom'=>'Khalid',       'sexe'=>'M','cin'=>'BB100070','naissance'=>'1989-10-28','lieu'=>'Kénitra',     'famille'=>'celibataire','enfants'=>0,'email'=>'kh.mansouri@alfsebou.ma', 'tel'=>'0661000070','ville'=>'Kénitra','embauche'=>'2022-08-01','fiche'=>12,'unite'=>'dsi',  'contrat'=>'CDI','salaire'=>7000, 'manager'=>69],
            /* 71 */ ['nom'=>'ZERRAD',    'prenom'=>'Amine',        'sexe'=>'M','cin'=>'BJ100071','naissance'=>'1994-06-15','lieu'=>'Rabat',       'famille'=>'celibataire','enfants'=>0,'email'=>'a.zerrad@alfsebou.ma',    'tel'=>'0661000071','ville'=>'Kénitra','embauche'=>'2023-11-01','fiche'=>13,'unite'=>'dsi',  'contrat'=>'CDI','salaire'=>9000, 'manager'=>69],
        ];
    }
}
