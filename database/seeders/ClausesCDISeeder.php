<?php

namespace Database\Seeders;

use App\Models\ClauseContrat;
use Illuminate\Database\Seeder;

class ClausesCDISeeder extends Seeder
{
    public function run(): void
    {
        ClauseContrat::where('type_doc', 'CDI')->delete();

        $clauses = [
            [
                'titre'       => 'Article 1 — Engagement',
                'contenu'     => "La société {{societe.nom}}, {{societe.forme_juridique}}, dont le siège social est sis à {{societe.adresse}}, {{societe.ville}}, ci-après dénommée « l'Employeur », engage Monsieur/Madame {{employe.nom_complet}}, demeurant à {{employe.adresse}}, titulaire de la CIN n° {{employe.cin}}, ci-après dénommé(e) « le Salarié ».\n\nLe présent contrat de travail à durée indéterminée prend effet à compter du {{contrat.date_debut}}, sous réserve des résultats satisfaisants de la visite médicale d'embauche.",
                'obligatoire' => true,
                'ordre'       => 1,
            ],
            [
                'titre'       => 'Article 2 — Fonction et poste',
                'contenu'     => "Le Salarié est engagé en qualité de {{contrat.poste}}, relevant de la catégorie {{contrat.categorie}}.\n\nIl est affecté au sein de l'établissement situé à {{societe.ville}}. En fonction des circonstances et de l'évolution des structures, le Salarié pourra être amené à exercer toutes autres fonctions compatibles avec sa qualification, sans que cela constitue une modification substantielle de son contrat de travail.\n\nLe Salarié exercera ses fonctions sous l'autorité et la direction de la hiérarchie de l'Employeur.",
                'obligatoire' => true,
                'ordre'       => 2,
            ],
            [
                'titre'       => 'Article 3 — Période d\'essai',
                'contenu'     => "Le présent contrat est conclu sous réserve d'une période d'essai de trois (3) mois pour les cadres et agents de maîtrise, et d'un (1) mois pour les employés et ouvriers, conformément aux dispositions de l'article 14 du Code du travail marocain.\n\nPendant cette période, chacune des deux parties pourra mettre fin à l'engagement sans indemnité ni préavis autre que celui prévu par la loi. Le silence de l'Employeur à l'issue de la période d'essai vaudra confirmation du présent contrat.",
                'obligatoire' => true,
                'ordre'       => 3,
            ],
            [
                'titre'       => 'Article 4 — Rémunération',
                'contenu'     => "En contrepartie de ses services, le Salarié percevra un salaire mensuel brut de {{contrat.salaire_base}}, versé à terme échu.\n\nCe salaire est soumis aux retenues légales en vigueur, notamment les cotisations CNSS, AMO et l'impôt sur le revenu (IR) conformément à la législation fiscale marocaine en vigueur.\n\nLe Salarié est affilié à la CNSS sous le numéro employeur {{societe.cnss}}. Toute révision de la rémunération fera l'objet d'un avenant au présent contrat.",
                'obligatoire' => true,
                'ordre'       => 4,
            ],
            [
                'titre'       => 'Article 5 — Durée et horaires de travail',
                'contenu'     => "Le Salarié est soumis à la durée légale de travail fixée à 2 288 heures par an, soit 44 heures par semaine, conformément aux dispositions du Code du travail marocain.\n\nLes horaires de travail sont fixés par le règlement intérieur de la société et peuvent être modifiés par l'Employeur en fonction des nécessités du service, dans le respect des dispositions légales en vigueur.",
                'obligatoire' => true,
                'ordre'       => 5,
            ],
            [
                'titre'       => 'Article 6 — Congés annuels',
                'contenu'     => "Le Salarié bénéficie d'un congé annuel payé conformément aux dispositions des articles 231 et suivants du Code du travail marocain, soit :\n\n- 1,5 jour ouvrable par mois de service effectif pour les salariés ayant moins de cinq ans d'ancienneté ;\n- 1 jour et demi supplémentaire par tranche de cinq ans d'ancienneté, sans que la durée totale puisse excéder 30 jours ouvrables.\n\nLes dates de congés sont fixées d'un commun accord entre l'Employeur et le Salarié, en tenant compte des nécessités du service.",
                'obligatoire' => true,
                'ordre'       => 6,
            ],
            [
                'titre'       => 'Article 7 — Absences et congés maladie',
                'contenu'     => "En cas d'absence pour maladie ou accident, le Salarié doit en informer l'Employeur dans les quarante-huit (48) heures suivant le premier jour d'absence et faire parvenir un certificat médical justificatif dans les mêmes délais.\n\nLes absences injustifiées sont déduites de la rémunération et peuvent, en cas de répétition, entraîner l'application de mesures disciplinaires conformément au règlement intérieur.",
                'obligatoire' => false,
                'ordre'       => 7,
            ],
            [
                'titre'       => 'Article 8 — Obligations du salarié',
                'contenu'     => "Le Salarié s'engage à :\n\n- Consacrer l'intégralité de son activité professionnelle à l'Employeur et à s'abstenir, pendant la durée du présent contrat, d'exercer toute activité concurrente, directe ou indirecte ;\n- Respecter les règles de confidentialité concernant les informations, données et savoir-faire de l'Employeur auxquels il aurait accès dans l'exercice de ses fonctions, y compris après la rupture du présent contrat ;\n- Se conformer aux dispositions du règlement intérieur, aux instructions de la direction et aux procédures internes en vigueur.",
                'obligatoire' => true,
                'ordre'       => 8,
            ],
            [
                'titre'       => 'Article 9 — Déplacements professionnels',
                'contenu'     => "Le Salarié pourra être amené à effectuer des déplacements professionnels, tant sur le territoire national qu'à l'étranger, selon les besoins du service.\n\nLes frais de déplacement engagés dans l'exercice des fonctions seront remboursés par l'Employeur sur présentation des justificatifs correspondants, conformément à la politique de remboursement des frais en vigueur au sein de la société.",
                'obligatoire' => false,
                'ordre'       => 9,
            ],
            [
                'titre'       => 'Article 10 — Rupture du contrat et préavis',
                'contenu'     => "En cas de rupture du présent contrat à l'initiative de l'une ou l'autre des parties, un préavis doit être respecté conformément aux dispositions des articles 43 et suivants du Code du travail marocain :\n\n- Cadres et assimilés : trois (3) mois ;\n- Employés et ouvriers : un (1) mois pour moins d'un an d'ancienneté, deux (2) mois au-delà.\n\nEn cas de faute grave, la rupture peut intervenir sans préavis ni indemnité, conformément aux dispositions légales en vigueur.",
                'obligatoire' => true,
                'ordre'       => 10,
            ],
            [
                'titre'       => 'Article 11 — Indemnité de licenciement',
                'contenu'     => "En cas de licenciement sans faute grave, le Salarié bénéficiera d'une indemnité de licenciement calculée conformément aux dispositions de l'article 52 du Code du travail marocain, sur la base du salaire mensuel brut perçu au moment de la rupture et de l'ancienneté acquise.",
                'obligatoire' => false,
                'ordre'       => 11,
            ],
            [
                'titre'       => 'Article 12 — Règlement des litiges',
                'contenu'     => "Tout différend relatif à l'interprétation ou à l'exécution du présent contrat sera, à défaut de règlement amiable, soumis aux juridictions compétentes du ressort du siège social de la société, conformément aux dispositions du Code du travail marocain et aux textes réglementaires en vigueur.",
                'obligatoire' => true,
                'ordre'       => 12,
            ],
            [
                'titre'       => 'Article 13 — Dispositions générales',
                'contenu'     => "Le présent contrat est régi par les dispositions du Code du travail marocain (Loi n° 65-99) et les textes réglementaires pris pour son application, ainsi que par la convention collective applicable à la branche d'activité de l'Employeur.\n\nLe Salarié reconnaît avoir pris connaissance du règlement intérieur de la société et s'engage à le respecter.\n\nFait à {{societe.ville}}, le {{date.complete}}, en deux exemplaires originaux, dont un remis à chaque partie.",
                'obligatoire' => true,
                'ordre'       => 13,
            ],
        ];

        foreach ($clauses as $clause) {
            ClauseContrat::create([
                'type_doc'    => 'CDI',
                'titre'       => $clause['titre'],
                'contenu'     => $clause['contenu'],
                'obligatoire' => $clause['obligatoire'],
                'ordre'       => $clause['ordre'],
                'actif'       => true,
            ]);
        }
    }
}
