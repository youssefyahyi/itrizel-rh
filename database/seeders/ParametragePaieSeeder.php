<?php

namespace Database\Seeders;

use App\Models\ParametrageRh;
use Illuminate\Database\Seeder;

/**
 * Paramètres paie — taux et barèmes en vigueur au Maroc (2024/2025).
 * Les taux peuvent être modifiés depuis le paramétrage sans toucher au code.
 *
 * Usage : php artisan db:seed --class=ParametragePaieSeeder
 */
class ParametragePaieSeeder extends Seeder
{
    public function run(): void
    {
        $params = [

            // ── Informations société ──────────────────────────────────────
            ['groupe' => 'societe', 'cle' => 'societe.nom',         'libelle' => 'Raison sociale',            'valeur' => '',    'description' => 'Nom affiché sur le bulletin de paie'],
            ['groupe' => 'societe', 'cle' => 'societe.adresse',     'libelle' => 'Adresse',                   'valeur' => '',    'description' => 'Adresse complète'],
            ['groupe' => 'societe', 'cle' => 'societe.ville',       'libelle' => 'Ville',                     'valeur' => '',    'description' => ''],
            ['groupe' => 'societe', 'cle' => 'societe.ice',         'libelle' => 'Identifiant Commun Entreprise (ICE)', 'valeur' => '', 'description' => '15 chiffres'],
            ['groupe' => 'societe', 'cle' => 'societe.rc',          'libelle' => 'Registre du Commerce (RC)', 'valeur' => '',    'description' => ''],
            ['groupe' => 'societe', 'cle' => 'societe.cnss_patron', 'libelle' => 'N° affilié CNSS employeur', 'valeur' => '',    'description' => ''],
            ['groupe' => 'societe', 'cle' => 'societe.if',          'libelle' => 'Identifiant Fiscal (IF)',   'valeur' => '',    'description' => ''],
            ['groupe' => 'societe', 'cle' => 'societe.telephone',   'libelle' => 'Téléphone',                 'valeur' => '',    'description' => ''],
            ['groupe' => 'societe', 'cle' => 'societe.email',       'libelle' => 'Email',                     'valeur' => '',    'description' => ''],

            // ── CNSS salarié ──────────────────────────────────────────────
            ['groupe' => 'paie', 'cle' => 'paie.cnss_taux_salarie', 'libelle' => 'CNSS — Taux salarié (%)',  'valeur' => '4.48',  'description' => 'Taux CNSS part salariale (en %). Plafonné sur le salaire brut mensuel.'],
            ['groupe' => 'paie', 'cle' => 'paie.cnss_plafond',      'libelle' => 'CNSS — Plafond mensuel (DH)', 'valeur' => '6000', 'description' => 'Plafond mensuel CNSS en dirhams (brut × taux appliqué sur ce plafond max)'],
            ['groupe' => 'paie', 'cle' => 'paie.cnss_taux_patron',  'libelle' => 'CNSS — Taux patronal (%)', 'valeur' => '10.48', 'description' => 'Taux CNSS part patronale (informatif)'],

            // ── AMO salarié ───────────────────────────────────────────────
            ['groupe' => 'paie', 'cle' => 'paie.amo_taux_salarie',  'libelle' => 'AMO — Taux salarié (%)',   'valeur' => '2.26',  'description' => 'Taux AMO part salariale (en %). Appliqué sur salaire brut sans plafond.'],
            ['groupe' => 'paie', 'cle' => 'paie.amo_taux_patron',   'libelle' => 'AMO — Taux patronal (%)',  'valeur' => '2.26',  'description' => 'Taux AMO part patronale (informatif)'],

            // ── CIMR (optionnel) ──────────────────────────────────────────
            ['groupe' => 'paie', 'cle' => 'paie.cimr_actif',        'libelle' => 'CIMR — Activé',            'valeur' => '0',     'description' => '1 = oui, 0 = non. Mettre à 1 si la société adhère à la CIMR.'],
            ['groupe' => 'paie', 'cle' => 'paie.cimr_taux_salarie', 'libelle' => 'CIMR — Taux salarié (%)',  'valeur' => '3.00',  'description' => 'Taux CIMR salarié. Variable selon accord (souvent 3% à 6%).'],
            ['groupe' => 'paie', 'cle' => 'paie.cimr_taux_patron',  'libelle' => 'CIMR — Taux patronal (%)', 'valeur' => '6.00',  'description' => 'Taux CIMR patronal (informatif).'],

            // ── IR — Abattement professionnel ─────────────────────────────
            ['groupe' => 'paie', 'cle' => 'paie.ir_abattement_taux','libelle' => 'IR — Abattement pro (%)',   'valeur' => '20',    'description' => 'Taux abattement professionnel appliqué sur le salaire brut mensuel.'],
            ['groupe' => 'paie', 'cle' => 'paie.ir_abattement_min', 'libelle' => 'IR — Abattement min annuel (DH)', 'valeur' => '2500',  'description' => 'Abattement minimum annuel (2 500 DH). Mensuel : 208,33 DH.'],
            ['groupe' => 'paie', 'cle' => 'paie.ir_abattement_max', 'libelle' => 'IR — Abattement max annuel (DH)', 'valeur' => '30000', 'description' => 'Abattement maximum annuel (30 000 DH). Mensuel : 2 500 DH.'],

            // ── IR — Déductions charges familiales ────────────────────────
            ['groupe' => 'paie', 'cle' => 'paie.ir_deduction_charge_annuel', 'libelle' => 'IR — Déduction par personne à charge (DH/an)', 'valeur' => '360', 'description' => '360 DH par an par personne à charge (conjoint + enfants). Max 6 personnes.'],
            ['groupe' => 'paie', 'cle' => 'paie.ir_deduction_max_personnes', 'libelle' => 'IR — Nb max personnes à charge',               'valeur' => '6',   'description' => 'Nombre maximum de personnes à charge déductibles (conjoint inclus).'],

            // ── IR — Barème annuel (JSON) ─────────────────────────────────
            // Formule : IR annuel = Revenu net imposable annuel × taux - somme_a_deduire
            [
                'groupe'      => 'paie',
                'cle'         => 'paie.ir_bareme',
                'libelle'     => 'IR — Barème annuel (JSON)',
                'valeur'      => json_encode([
                    ['min' =>      0, 'max' =>   30000, 'taux' =>  0, 'somme_a_deduire' =>     0],
                    ['min' =>  30001, 'max' =>   50000, 'taux' => 10, 'somme_a_deduire' =>  3000],
                    ['min' =>  50001, 'max' =>   60000, 'taux' => 20, 'somme_a_deduire' =>  8000],
                    ['min' =>  60001, 'max' =>   80000, 'taux' => 30, 'somme_a_deduire' => 14000],
                    ['min' =>  80001, 'max' =>  180000, 'taux' => 34, 'somme_a_deduire' => 17200],
                    ['min' => 180001, 'max' =>    null, 'taux' => 38, 'somme_a_deduire' => 24400],
                ]),
                'description' => 'Barème IR Maroc (DH annuel). Formule: IR_annuel = net_imposable_annuel × taux - somme_a_deduire',
            ],

            // ── Mode de paiement par défaut ───────────────────────────────
            ['groupe' => 'paie', 'cle' => 'paie.mode_paiement_defaut', 'libelle' => 'Mode de paiement par défaut', 'valeur' => 'Virement bancaire', 'description' => 'Affiché sur le bulletin. Ex : Virement bancaire, Espèces, Chèque.'],
        ];

        foreach ($params as $p) {
            ParametrageRh::updateOrCreate(
                ['cle' => $p['cle']],
                [
                    'groupe'      => $p['groupe'],
                    'libelle'     => $p['libelle'],
                    'valeur'      => $p['valeur'],
                    'description' => $p['description'],
                ]
            );
        }

        $this->command->info('Paramètres paie seedés (' . count($params) . ' entrées).');
    }
}
