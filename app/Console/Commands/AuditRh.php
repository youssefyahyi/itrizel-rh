<?php

namespace App\Console\Commands;

use App\Models\ParametrageRh;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditRh extends Command
{
    protected $signature   = 'rh:audit {--json : Sortie JSON brute}';
    protected $description = 'Audit fonctionnel du module RH — contrôle des règles métier';

    private array $findings = [];

    public function handle(): int
    {
        $this->line('');
        $this->line('═══════════════════════════════════════════════════');
        $this->line('  AUDIT FONCTIONNEL ITRIZEL-RH — ' . now()->format('d/m/Y H:i'));
        $this->line('═══════════════════════════════════════════════════');

        $this->auditContrats();
        $this->auditEmployes();
        $this->auditBulletins();
        $this->auditConges();
        $this->auditAbsences();
        $this->auditParametrage();

        $this->afficherResultats();

        return self::SUCCESS;
    }

    // ── CONTRATS ──────────────────────────────────────────────────────

    private function auditContrats(): void
    {
        $this->line('');
        $this->line('── Contrats ──────────────────────────────────────');

        // CDD/CDDI/intérim sans date_fin
        $rows = DB::table('contrats')
            ->join('employes', 'contrats.employe_id', '=', 'employes.id')
            ->whereIn('contrats.type', ['CDD', 'CDDI', 'interim'])
            ->whereNull('contrats.date_fin')
            ->whereNull('employes.deleted_at')
            ->select('contrats.reference', 'contrats.type', 'employes.nom', 'employes.prenom')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Contrats', "Contrat {$r->type} sans date_fin : {$r->reference} ({$r->nom} {$r->prenom})");
        }

        // Contrats en_cours dont date_fin est dépassée
        $rows = DB::table('contrats')
            ->join('employes', 'contrats.employe_id', '=', 'employes.id')
            ->where('contrats.statut', 'en_cours')
            ->whereNotNull('contrats.date_fin')
            ->where('contrats.date_fin', '<', now()->format('Y-m-d'))
            ->whereNull('employes.deleted_at')
            ->select('contrats.reference', 'contrats.date_fin', 'employes.nom', 'employes.prenom')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Contrats', "Contrat en_cours avec date_fin dépassée ({$r->date_fin}) : {$r->reference} ({$r->nom} {$r->prenom})");
        }

        // Plusieurs contrats en_cours pour le même employé
        $rows = DB::table('contrats')
            ->join('employes', 'contrats.employe_id', '=', 'employes.id')
            ->where('contrats.statut', 'en_cours')
            ->whereNull('employes.deleted_at')
            ->groupBy('contrats.employe_id', 'employes.nom', 'employes.prenom')
            ->havingRaw('COUNT(*) > 1')
            ->selectRaw('employes.nom, employes.prenom, COUNT(*) as nb')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Contrats', "Employé avec {$r->nb} contrats en_cours simultanés : {$r->nom} {$r->prenom}");
        }

        // date_debut > date_fin
        $rows = DB::table('contrats')
            ->join('employes', 'contrats.employe_id', '=', 'employes.id')
            ->whereNotNull('contrats.date_fin')
            ->whereColumn('contrats.date_debut', '>', 'contrats.date_fin')
            ->whereNull('employes.deleted_at')
            ->select('contrats.reference', 'contrats.date_debut', 'contrats.date_fin', 'employes.nom', 'employes.prenom')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Contrats', "Contrat avec date_debut ({$r->date_debut}) > date_fin ({$r->date_fin}) : {$r->reference} ({$r->nom} {$r->prenom})");
        }

        // Salaire de base nul ou négatif sur contrat actif
        $rows = DB::table('contrats')
            ->join('employes', 'contrats.employe_id', '=', 'employes.id')
            ->where('contrats.statut', 'en_cours')
            ->where(fn($q) => $q->where('contrats.salaire_base', '<=', 0)->orWhereNull('contrats.salaire_base'))
            ->whereNull('employes.deleted_at')
            ->select('contrats.reference', 'contrats.salaire_base', 'employes.nom', 'employes.prenom')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Contrats', "Contrat en_cours avec salaire_base invalide ({$r->salaire_base} DH) : {$r->reference} ({$r->nom} {$r->prenom})");
        }

        // Contrats en_cours sans fiche de poste
        $rows = DB::table('contrats')
            ->join('employes', 'contrats.employe_id', '=', 'employes.id')
            ->where('contrats.statut', 'en_cours')
            ->whereNull('contrats.fiche_poste_id')
            ->whereNull('employes.deleted_at')
            ->select('contrats.reference', 'employes.nom', 'employes.prenom')
            ->get();

        foreach ($rows as $r) {
            $this->add('ATTENTION', 'Contrats', "Contrat en_cours sans fiche de poste : {$r->reference} ({$r->nom} {$r->prenom})");
        }

        $this->line("  Contrôles contrats : " . $this->countSection('Contrats') . " anomalie(s)");
    }

    // ── EMPLOYÉS ──────────────────────────────────────────────────────

    private function auditEmployes(): void
    {
        $this->line('');
        $this->line('── Employés ──────────────────────────────────────');

        // Employé actif sans aucun contrat en_cours
        $rows = DB::table('employes')
            ->leftJoin('contrats', function ($j) {
                $j->on('contrats.employe_id', '=', 'employes.id')
                  ->where('contrats.statut', 'en_cours');
            })
            ->where('employes.statut', 'actif')
            ->whereNull('employes.deleted_at')
            ->whereNull('contrats.id')
            ->select('employes.matricule', 'employes.nom', 'employes.prenom')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Employés', "Employé actif sans contrat en_cours : {$r->matricule} {$r->nom} {$r->prenom}");
        }

        // Employé inactif/suspendu avec contrat en_cours
        $rows = DB::table('employes')
            ->join('contrats', function ($j) {
                $j->on('contrats.employe_id', '=', 'employes.id')
                  ->where('contrats.statut', 'en_cours');
            })
            ->whereIn('employes.statut', ['inactif', 'suspendu'])
            ->whereNull('employes.deleted_at')
            ->select('employes.matricule', 'employes.nom', 'employes.prenom', 'employes.statut', 'contrats.reference')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Employés', "Employé {$r->statut} avec contrat en_cours ({$r->reference}) : {$r->matricule} {$r->nom} {$r->prenom}");
        }

        // Employé actif sans fiche de poste
        $rows = DB::table('employes')
            ->where('statut', 'actif')
            ->whereNull('fiche_poste_id')
            ->whereNull('deleted_at')
            ->select('matricule', 'nom', 'prenom')
            ->get();

        foreach ($rows as $r) {
            $this->add('ATTENTION', 'Employés', "Employé actif sans fiche de poste : {$r->matricule} {$r->nom} {$r->prenom}");
        }

        // Employé actif sans date_embauche
        $rows = DB::table('employes')
            ->where('statut', 'actif')
            ->whereNull('date_embauche')
            ->whereNull('deleted_at')
            ->select('matricule', 'nom', 'prenom')
            ->get();

        foreach ($rows as $r) {
            $this->add('ATTENTION', 'Employés', "Employé actif sans date d'embauche : {$r->matricule} {$r->nom} {$r->prenom}");
        }

        // Employé actif sans unité organisationnelle
        $rows = DB::table('employes')
            ->where('statut', 'actif')
            ->whereNull('unite_id')
            ->whereNull('deleted_at')
            ->select('matricule', 'nom', 'prenom')
            ->get();

        foreach ($rows as $r) {
            $this->add('INFO', 'Employés', "Employé actif sans unité organisationnelle : {$r->matricule} {$r->nom} {$r->prenom}");
        }

        // date_embauche postérieure à date_debut du contrat actif
        $rows = DB::table('employes')
            ->join('contrats', function ($j) {
                $j->on('contrats.employe_id', '=', 'employes.id')
                  ->where('contrats.statut', 'en_cours');
            })
            ->whereNotNull('employes.date_embauche')
            ->whereColumn('employes.date_embauche', '>', 'contrats.date_debut')
            ->whereNull('employes.deleted_at')
            ->select('employes.matricule', 'employes.nom', 'employes.prenom', 'employes.date_embauche', 'contrats.date_debut', 'contrats.reference')
            ->get();

        foreach ($rows as $r) {
            $this->add('ATTENTION', 'Employés', "Date embauche ({$r->date_embauche}) postérieure à date début contrat ({$r->date_debut}) : {$r->matricule} {$r->nom} {$r->prenom} / {$r->reference}");
        }

        $this->line("  Contrôles employés : " . $this->countSection('Employés') . " anomalie(s)");
    }

    // ── BULLETINS DE PAIE ────────────────────────────────────────────

    private function auditBulletins(): void
    {
        $this->line('');
        $this->line('── Bulletins de paie ────────────────────────────');

        // Net à payer négatif
        $rows = DB::table('bulletins_paie')
            ->join('employes', 'bulletins_paie.employe_id', '=', 'employes.id')
            ->where('bulletins_paie.net_a_payer', '<', 0)
            ->select('bulletins_paie.periode_mois', 'bulletins_paie.periode_annee', 'bulletins_paie.net_a_payer', 'employes.nom', 'employes.prenom', 'employes.matricule')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Bulletins', "Net à payer négatif ({$r->net_a_payer} DH) : {$r->matricule} {$r->nom} {$r->prenom} — {$r->periode_mois}/{$r->periode_annee}");
        }

        // Double bulletin même employé/même période
        $rows = DB::table('bulletins_paie')
            ->join('employes', 'bulletins_paie.employe_id', '=', 'employes.id')
            ->groupBy('bulletins_paie.employe_id', 'bulletins_paie.periode_mois', 'bulletins_paie.periode_annee', 'employes.nom', 'employes.prenom', 'employes.matricule')
            ->havingRaw('COUNT(*) > 1')
            ->selectRaw('employes.matricule, employes.nom, employes.prenom, bulletins_paie.periode_mois, bulletins_paie.periode_annee, COUNT(*) as nb')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Bulletins', "Double bulletin ({$r->nb}x) : {$r->matricule} {$r->nom} {$r->prenom} — {$r->periode_mois}/{$r->periode_annee}");
        }

        // Bulletin avec salaire_base nul
        $rows = DB::table('bulletins_paie')
            ->join('employes', 'bulletins_paie.employe_id', '=', 'employes.id')
            ->where(fn($q) => $q->where('bulletins_paie.salaire_base', '<=', 0)->orWhereNull('bulletins_paie.salaire_base'))
            ->select('bulletins_paie.periode_mois', 'bulletins_paie.periode_annee', 'employes.nom', 'employes.prenom', 'employes.matricule')
            ->get();

        foreach ($rows as $r) {
            $this->add('ATTENTION', 'Bulletins', "Bulletin avec salaire_base nul : {$r->matricule} {$r->nom} {$r->prenom} — {$r->periode_mois}/{$r->periode_annee}");
        }

        // Bulletin sans contrat actif sur la période
        $rows = DB::table('bulletins_paie as b')
            ->join('employes as e', 'b.employe_id', '=', 'e.id')
            ->whereNotExists(function ($q) {
                $q->from('contrats as c')
                  ->whereColumn('c.employe_id', 'b.employe_id')
                  ->whereRaw("c.date_debut <= LAST_DAY(STR_TO_DATE(CONCAT(b.periode_annee,'-',LPAD(b.periode_mois,2,'0'),'-01'), '%Y-%m-%d'))")
                  ->whereRaw("(c.date_fin IS NULL OR c.date_fin >= STR_TO_DATE(CONCAT(b.periode_annee,'-',LPAD(b.periode_mois,2,'0'),'-01'), '%Y-%m-%d'))");
            })
            ->whereNull('e.deleted_at')
            ->select('b.periode_mois', 'b.periode_annee', 'e.nom', 'e.prenom', 'e.matricule')
            ->orderBy('b.periode_annee')->orderBy('b.periode_mois')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Bulletins', "Bulletin sans contrat actif sur la période : {$r->matricule} {$r->nom} {$r->prenom} — {$r->periode_mois}/{$r->periode_annee}");
        }

        $this->line("  Contrôles bulletins : " . $this->countSection('Bulletins') . " anomalie(s)");
    }

    // ── CONGÉS ───────────────────────────────────────────────────────

    private function auditConges(): void
    {
        $this->line('');
        $this->line('── Congés ────────────────────────────────────────');

        // date_debut > date_fin
        $rows = DB::table('conges')
            ->join('employes', 'conges.employe_id', '=', 'employes.id')
            ->whereColumn('conges.date_debut', '>', 'conges.date_fin')
            ->whereNull('employes.deleted_at')
            ->select('conges.id', 'conges.date_debut', 'conges.date_fin', 'conges.statut', 'employes.nom', 'employes.prenom', 'employes.matricule')
            ->get();

        foreach ($rows as $r) {
            $this->add('ERREUR', 'Congés', "Congé avec date_debut ({$r->date_debut}) > date_fin ({$r->date_fin}) : {$r->matricule} {$r->nom} {$r->prenom} [statut: {$r->statut}]");
        }

        // nb_jours nul ou négatif
        $rows = DB::table('conges')
            ->join('employes', 'conges.employe_id', '=', 'employes.id')
            ->where(fn($q) => $q->where('conges.nb_jours', '<=', 0)->orWhereNull('conges.nb_jours'))
            ->whereNull('employes.deleted_at')
            ->select('conges.id', 'conges.nb_jours', 'conges.statut', 'employes.nom', 'employes.prenom', 'employes.matricule')
            ->get();

        foreach ($rows as $r) {
            $this->add('ATTENTION', 'Congés', "Congé avec nb_jours invalide ({$r->nb_jours}) : {$r->matricule} {$r->nom} {$r->prenom} [statut: {$r->statut}]");
        }

        // Congés approuvés qui se chevauchent pour le même employé
        $rows = DB::table('conges as c1')
            ->join('conges as c2', function ($j) {
                $j->on('c1.employe_id', '=', 'c2.employe_id')
                  ->whereColumn('c1.id', '<', 'c2.id')
                  ->whereColumn('c1.date_debut', '<=', 'c2.date_fin')
                  ->whereColumn('c1.date_fin', '>=', 'c2.date_debut');
            })
            ->join('employes', 'c1.employe_id', '=', 'employes.id')
            ->where('c1.statut', 'approuve')
            ->where('c2.statut', 'approuve')
            ->whereNull('employes.deleted_at')
            ->select('employes.matricule', 'employes.nom', 'employes.prenom', 'c1.date_debut as d1', 'c1.date_fin as f1', 'c2.date_debut as d2', 'c2.date_fin as f2')
            ->get();

        foreach ($rows as $r) {
            $this->add('ATTENTION', 'Congés', "Congés approuvés se chevauchant : {$r->matricule} {$r->nom} {$r->prenom} ({$r->d1}→{$r->f1}) et ({$r->d2}→{$r->f2})");
        }

        $this->line("  Contrôles congés : " . $this->countSection('Congés') . " anomalie(s)");
    }

    // ── ABSENCES (pointage journalier) ───────────────────────────────

    private function auditAbsences(): void
    {
        $this->line('');
        $this->line('── Absences / Pointage ───────────────────────────');

        // Pointage absent sans motif_absence renseigné
        $rows = DB::table('absences')
            ->join('employes', 'absences.employe_id', '=', 'employes.id')
            ->where('absences.statut', 'absent')
            ->whereNull('absences.motif_absence')
            ->whereNull('employes.deleted_at')
            ->select('absences.date', 'employes.nom', 'employes.prenom', 'employes.matricule')
            ->orderBy('absences.date', 'desc')
            ->limit(50)
            ->get();

        foreach ($rows as $r) {
            $this->add('ATTENTION', 'Absences', "Pointage absent sans motif : {$r->matricule} {$r->nom} {$r->prenom} — {$r->date}");
        }

        // Pointage avec heures_realisees > heures_prevues (incohérence)
        $rows = DB::table('absences')
            ->join('employes', 'absences.employe_id', '=', 'employes.id')
            ->whereNotNull('absences.heures_realisees')
            ->whereColumn('absences.heures_realisees', '>', 'absences.heures_prevues')
            ->whereNull('employes.deleted_at')
            ->select('absences.date', 'absences.heures_prevues', 'absences.heures_realisees', 'employes.nom', 'employes.prenom', 'employes.matricule')
            ->limit(50)
            ->get();

        foreach ($rows as $r) {
            $this->add('INFO', 'Absences', "Heures réalisées ({$r->heures_realisees}h) > heures prévues ({$r->heures_prevues}h) : {$r->matricule} {$r->nom} {$r->prenom} — {$r->date}");
        }

        // Pointage marqué "conge" sans congé approuvé ce jour
        $rows = DB::table('absences as a')
            ->join('employes as e', 'a.employe_id', '=', 'e.id')
            ->whereNull('e.deleted_at')
            ->where('a.statut', 'conge')
            ->whereNotExists(function ($q) {
                $q->from('conges as c')
                  ->whereColumn('c.employe_id', 'a.employe_id')
                  ->where('c.statut', 'approuve')
                  ->whereColumn('c.date_debut', '<=', 'a.date')
                  ->whereColumn('c.date_fin', '>=', 'a.date');
            })
            ->select('a.date', 'e.nom', 'e.prenom', 'e.matricule')
            ->limit(50)
            ->get();

        foreach ($rows as $r) {
            $this->add('ATTENTION', 'Absences', "Pointage 'conge' sans congé approuvé ce jour : {$r->matricule} {$r->nom} {$r->prenom} — {$r->date}");
        }

        $this->line("  Contrôles absences : " . $this->countSection('Absences') . " anomalie(s)");
    }

    // ── PARAMÉTRAGE ──────────────────────────────────────────────────

    private function auditParametrage(): void
    {
        $this->line('');
        $this->line('── Paramétrage paie ──────────────────────────────');

        $checks = [
            ['cle' => 'paie.cnss_taux_salarie',  'label' => 'CNSS taux salarié',    'min' => 4.29,  'max' => 4.48],
            ['cle' => 'paie.cnss_taux_patron',    'label' => 'CNSS taux patronal',   'min' => 10.0,  'max' => 11.0],
            ['cle' => 'paie.cnss_plafond',        'label' => 'Plafond CNSS',         'min' => 5500,  'max' => 6500],
            ['cle' => 'paie.amo_taux_salarie',    'label' => 'AMO taux salarié',     'min' => 2.0,   'max' => 2.5],
            ['cle' => 'paie.amo_taux_patron',     'label' => 'AMO taux patronal',    'min' => 2.0,   'max' => 2.5],
            ['cle' => 'paie.ir_abattement_taux',  'label' => 'IR abattement taux',   'min' => 17,    'max' => 25],
            ['cle' => 'paie.ir_abattement_min',   'label' => 'IR abattement min/an', 'min' => 2000,  'max' => 3000],
            ['cle' => 'paie.ir_abattement_max',   'label' => 'IR abattement max/an', 'min' => 28000, 'max' => 32000],
        ];

        foreach ($checks as $c) {
            $val = (float) ParametrageRh::get($c['cle'], null);
            if ($val === 0.0) {
                $this->add('ATTENTION', 'Paramétrage', "{$c['label']} non configuré (clé : {$c['cle']})");
            } elseif ($val < $c['min'] || $val > $c['max']) {
                $this->add('ATTENTION', 'Paramétrage', "{$c['label']} hors plage attendue [{$c['min']} — {$c['max']}] : valeur actuelle = {$val}");
            }
        }

        $this->line("  Contrôles paramétrage : " . $this->countSection('Paramétrage') . " anomalie(s)");
    }

    // ── Helpers ───────────────────────────────────────────────────────

    private function add(string $niveau, string $section, string $message): void
    {
        $this->findings[] = compact('niveau', 'section', 'message');
    }

    private function countSection(string $section): int
    {
        return count(array_filter($this->findings, fn($f) => $f['section'] === $section));
    }

    private function afficherResultats(): void
    {
        $erreurs    = array_filter($this->findings, fn($f) => $f['niveau'] === 'ERREUR');
        $attentions = array_filter($this->findings, fn($f) => $f['niveau'] === 'ATTENTION');
        $infos      = array_filter($this->findings, fn($f) => $f['niveau'] === 'INFO');

        $this->line('');
        $this->line('═══════════════════════════════════════════════════');
        $this->line("  RÉSUMÉ : " . count($erreurs) . " erreur(s) · " . count($attentions) . " attention(s) · " . count($infos) . " info(s)");
        $this->line('═══════════════════════════════════════════════════');

        if (empty($this->findings)) {
            $this->info('  ✓ Aucune anomalie détectée.');
            return;
        }

        foreach (['ERREUR' => '🔴', 'ATTENTION' => '🟡', 'INFO' => '🔵'] as $niveau => $icone) {
            $groupe = array_filter($this->findings, fn($f) => $f['niveau'] === $niveau);
            if (empty($groupe)) continue;

            $this->line('');
            $this->line("  {$icone} {$niveau}S (" . count($groupe) . ")");
            foreach ($groupe as $f) {
                $this->line("     [{$f['section']}] {$f['message']}");
            }
        }

        $this->line('');

        if ($this->option('json')) {
            $this->line(json_encode($this->findings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
    }
}
