<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CategorieEmploye;
use App\Models\Fonction;
use App\Models\Poste;
use App\Models\FichePoste;

/**
 * Seeder client : ALF Sebou — Commerce & Distribution
 *
 * Alimente le référentiel des emplois avec les fonctions, postes
 * et fiches d'emploi adaptés à une entreprise de commerce/distribution
 * au Maroc.
 *
 * Usage :
 *   php artisan db:seed --class=AlfSebouSeeder
 */
class AlfSebouSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('⏳ Seeder ALF Sebou — Référentiel des emplois…');

        // ── 1. FONCTIONS ──────────────────────────────────────────────
        $this->command->line('  → Fonctions…');

        $fonctionsDef = [
            ['nom' => 'Direction Générale',         'ordre' => 1],
            ['nom' => 'Administration & RH',         'ordre' => 2],
            ['nom' => 'Finance & Comptabilité',      'ordre' => 3],
            ['nom' => 'Commercial & Ventes',         'ordre' => 4],
            ['nom' => 'Achats & Approvisionnement',  'ordre' => 5],
            ['nom' => 'Logistique & Transport',      'ordre' => 6],
            ['nom' => 'Marketing & Communication',   'ordre' => 7],
            ['nom' => 'Informatique & Systèmes',     'ordre' => 8],
        ];

        $fonctions = [];
        foreach ($fonctionsDef as $def) {
            $fn = Fonction::firstOrCreate(
                ['nom' => $def['nom']],
                ['ordre' => $def['ordre'], 'actif' => true]
            );
            $fonctions[$def['nom']] = $fn;
        }

        // ── 2. POSTES ─────────────────────────────────────────────────
        $this->command->line('  → Postes…');

        $postesDef = [
            // Direction
            'Directeur Général',
            'Directeur Général Adjoint',
            // Administration & RH
            'Directeur des Ressources Humaines',
            'Responsable RH',
            'Chargé(e) RH & Paie',
            'Agent administratif',
            'Secrétaire de direction',
            'Agent de saisie',
            // Finance & Comptabilité
            'Directeur Administratif et Financier',
            'Chef comptable',
            'Comptable',
            'Responsable recouvrement',
            'Agent de recouvrement',
            // Commercial & Ventes
            'Directeur commercial',
            'Responsable commercial régional',
            'Commercial terrain',
            'Superviseur commercial',
            'Chargé(e) de clientèle',
            'Téléprospecteur / Télévendeur',
            // Achats & Approvisionnement
            'Directeur des achats',
            'Responsable achats',
            'Acheteur',
            'Agent d\'approvisionnement',
            // Logistique & Transport
            'Directeur logistique',
            'Responsable logistique',
            'Chef de dépôt',
            'Gestionnaire de stock',
            'Préparateur de commandes',
            'Manutentionnaire',
            'Cariste',
            'Chauffeur-livreur',
            'Chauffeur poids lourd',
            // Marketing & Communication
            'Responsable marketing',
            'Chargé(e) de marketing digital',
            'Designer graphique / Maquettiste',
            // Informatique & Systèmes
            'Responsable Systèmes d\'Information',
            'Technicien informatique',
            'Développeur applicatif',
        ];

        $postes = [];
        foreach ($postesDef as $nom) {
            $p = Poste::firstOrCreate(['nom' => $nom], ['actif' => true]);
            $postes[$nom] = $p;
        }

        // ── 3. FICHES D'EMPLOI ────────────────────────────────────────
        $this->command->line('  → Fiches d\'emploi…');

        // Récupération des catégories (seedées en migration)
        $cats = CategorieEmploye::pluck('id', 'nom');

        /**
         * Structure : [catégorie, fonction, poste]
         * Une fiche = une combinaison valide dans l'entreprise.
         */
        $fichesDef = [

            // ── Ouvrier ──
            ['Ouvrier', 'Logistique & Transport',     'Manutentionnaire'],
            ['Ouvrier', 'Logistique & Transport',     'Préparateur de commandes'],
            ['Ouvrier', 'Logistique & Transport',     'Cariste'],

            // ── Employé ──
            ['Employé', 'Logistique & Transport',     'Chauffeur-livreur'],
            ['Employé', 'Logistique & Transport',     'Chauffeur poids lourd'],
            ['Employé', 'Finance & Comptabilité',     'Agent de recouvrement'],
            ['Employé', 'Finance & Comptabilité',     'Agent de saisie'],
            ['Employé', 'Administration & RH',        'Agent administratif'],
            ['Employé', 'Administration & RH',        'Secrétaire de direction'],
            ['Employé', 'Commercial & Ventes',        'Téléprospecteur / Télévendeur'],

            // ── Technicien ──
            ['Technicien', 'Logistique & Transport',   'Gestionnaire de stock'],
            ['Technicien', 'Informatique & Systèmes',  'Technicien informatique'],
            ['Technicien', 'Informatique & Systèmes',  'Développeur applicatif'],
            ['Technicien', 'Marketing & Communication','Designer graphique / Maquettiste'],

            // ── Agent de maîtrise ──
            ['Agent de maîtrise', 'Logistique & Transport',  'Chef de dépôt'],
            ['Agent de maîtrise', 'Commercial & Ventes',     'Superviseur commercial'],

            // ── Cadre ──
            ['Cadre', 'Administration & RH',        'Responsable RH'],
            ['Cadre', 'Administration & RH',        'Chargé(e) RH & Paie'],
            ['Cadre', 'Finance & Comptabilité',     'Chef comptable'],
            ['Cadre', 'Finance & Comptabilité',     'Responsable recouvrement'],
            ['Cadre', 'Finance & Comptabilité',     'Comptable'],
            ['Cadre', 'Commercial & Ventes',        'Responsable commercial régional'],
            ['Cadre', 'Commercial & Ventes',        'Commercial terrain'],
            ['Cadre', 'Commercial & Ventes',        'Chargé(e) de clientèle'],
            ['Cadre', 'Achats & Approvisionnement', 'Responsable achats'],
            ['Cadre', 'Achats & Approvisionnement', 'Acheteur'],
            ['Cadre', 'Achats & Approvisionnement', "Agent d'approvisionnement"],
            ['Cadre', 'Logistique & Transport',     'Responsable logistique'],
            ['Cadre', 'Marketing & Communication',  'Responsable marketing'],
            ['Cadre', 'Marketing & Communication',  'Chargé(e) de marketing digital'],
            ['Cadre', 'Informatique & Systèmes',    'Responsable Systèmes d\'Information'],

            // ── Cadre supérieur ──
            ['Cadre supérieur', 'Administration & RH',        'Directeur des Ressources Humaines'],
            ['Cadre supérieur', 'Finance & Comptabilité',     'Directeur Administratif et Financier'],
            ['Cadre supérieur', 'Commercial & Ventes',        'Directeur commercial'],
            ['Cadre supérieur', 'Achats & Approvisionnement', 'Directeur des achats'],
            ['Cadre supérieur', 'Logistique & Transport',     'Directeur logistique'],

            // ── Dirigeant ──
            ['Dirigeant', 'Direction Générale', 'Directeur Général'],
            ['Dirigeant', 'Direction Générale', 'Directeur Général Adjoint'],
        ];

        $created = 0;
        $skipped = 0;

        foreach ($fichesDef as [$catNom, $fonNom, $posteNom]) {
            $catId    = $cats[$catNom]        ?? null;
            $fonId    = $fonctions[$fonNom]?->id ?? null;
            $posteId  = $postes[$posteNom]?->id  ?? null;

            if (!$catId || !$fonId || !$posteId) {
                $this->command->warn("    ⚠ Introuvable : {$catNom} › {$fonNom} › {$posteNom}");
                $skipped++;
                continue;
            }

            $exists = FichePoste::where([
                'categorie_id' => $catId,
                'fonction_id'  => $fonId,
                'poste_id'     => $posteId,
            ])->exists();

            if (!$exists) {
                FichePoste::create([
                    'categorie_id' => $catId,
                    'fonction_id'  => $fonId,
                    'poste_id'     => $posteId,
                    'actif'        => true,
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        $this->command->info("✅ ALF Sebou seedé :");
        $this->command->table(
            ['Ressource', 'Total'],
            [
                ['Fonctions',       count($fonctions)],
                ['Postes',          count($postes)],
                ['Fiches créées',   $created],
                ['Fiches existantes (ignorées)', $skipped],
            ]
        );
    }
}
