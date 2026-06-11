<?php

namespace App\Services;

use App\Models\Avenant;
use App\Models\ClauseContrat;
use App\Models\Contrat;
use App\Models\ParametrageRh;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ContratPdfGenerator
{
    /**
     * Génère le PDF d'un contrat (CDI ou CDD), le stocke et retourne le chemin.
     */
    public function genererContrat(Contrat $contrat, array $clausesSelectionnees = []): string
    {
        $contrat->load(['employe', 'fichePoste.poste', 'fichePoste.categorie']);

        $clauses = ClauseContrat::actives()
            ->pourType($contrat->type)
            ->get()
            ->filter(fn($c) => $c->obligatoire || in_array($c->id, $clausesSelectionnees))
            ->map(fn($c) => array_merge($c->toArray(), [
                'contenu' => $this->resoudreVariables($c->contenu, $contrat),
            ]));

        $html  = view('pdf.contrat', compact('contrat', 'clauses'))->render();
        $path  = 'contrats/' . $contrat->reference . '.pdf';

        Storage::put($path, Pdf::loadHTML($html)->setPaper('A4')->output());

        $contrat->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Génère le PDF d'un avenant, le stocke et retourne le chemin.
     */
    public function genererAvenant(Avenant $avenant, array $clausesSelectionnees = []): string
    {
        $avenant->load(['contrat.employe', 'contrat.fichePoste.poste']);

        $clauses = ClauseContrat::actives()
            ->pourType('avenant')
            ->get()
            ->filter(fn($c) => $c->obligatoire || in_array($c->id, $clausesSelectionnees))
            ->map(fn($c) => array_merge($c->toArray(), [
                'contenu' => $this->resoudreVariables($c->contenu, $avenant->contrat, $avenant),
            ]));

        $html = view('pdf.avenant', compact('avenant', 'clauses'))->render();
        $path = 'contrats/avenants/' . $avenant->reference . '.pdf';

        Storage::put($path, Pdf::loadHTML($html)->setPaper('A4')->output());

        $avenant->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Remplace toutes les variables {{x.y}} dans un texte de clause.
     */
    public function resoudreVariables(string $texte, Contrat $contrat, ?Avenant $avenant = null): string
    {
        $employe  = $contrat->employe;
        $societe  = ParametrageRh::getGroupe('societe');
        $now      = now();

        $vars = [
            // Employé
            'employe.nom'           => $employe->nom,
            'employe.prenom'        => $employe->prenom,
            'employe.nom_complet'   => $employe->nom_complet,
            'employe.matricule'     => $employe->matricule,
            'employe.cin'           => $employe->cin ?? '',
            'employe.date_naissance'=> $employe->date_naissance?->format('d/m/Y') ?? '',
            'employe.adresse'       => $employe->adresse ?? '',

            // Contrat
            'contrat.reference'     => $contrat->reference,
            'contrat.type'          => $contrat->type,
            'contrat.salaire_base'  => number_format($contrat->salaire_base, 2, ',', ' ') . ' DH',
            'contrat.date_debut'    => $contrat->date_debut->format('d/m/Y'),
            'contrat.date_fin'      => $contrat->date_fin?->format('d/m/Y') ?? 'Sans échéance',
            'contrat.duree'         => $contrat->duree_mois ? $contrat->duree_mois . ' mois' : '',
            'contrat.poste'         => $contrat->fichePoste?->poste->nom ?? '',
            'contrat.categorie'     => $contrat->fichePoste?->categorie->nom ?? '',

            // Société
            'societe.nom'            => $societe['societe.nom'] ?? '',
            'societe.forme_juridique'=> $societe['societe.forme_juridique'] ?? '',
            'societe.adresse'        => $societe['societe.adresse'] ?? '',
            'societe.ville'          => $societe['societe.ville'] ?? '',
            'societe.ice'            => $societe['societe.ice'] ?? '',
            'societe.rc'             => $societe['societe.rc'] ?? '',
            'societe.if'             => $societe['societe.if'] ?? '',
            'societe.cnss_patron'    => $societe['societe.cnss_patron'] ?? '',
            'societe.telephone'      => $societe['societe.telephone'] ?? '',
            'societe.email'          => $societe['societe.email'] ?? '',

            // Date du jour
            'date.jour'             => $now->format('d'),
            'date.mois_nom'         => $this->moisFr($now->month),
            'date.annee'            => $now->format('Y'),
            'date.complete'         => $now->format('d/m/Y'),
        ];

        // Variables avenant
        if ($avenant) {
            $vars = array_merge($vars, [
                'avenant.reference'       => $avenant->reference,
                'avenant.nature'          => $avenant->nature_libelle,
                'avenant.date_effet'      => $avenant->date_effet->format('d/m/Y'),
                'avenant.ancienne_valeur' => $avenant->ancienne_valeur ?? '',
                'avenant.nouvelle_valeur' => $avenant->nouvelle_valeur ?? '',
                'avenant.motif'           => $avenant->motif ?? '',
            ]);
        }

        foreach ($vars as $cle => $valeur) {
            $texte = str_replace('{{' . $cle . '}}', $valeur, $texte);
        }

        return $texte;
    }

    private function moisFr(int $mois): string
    {
        return ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
                'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'][$mois];
    }

    /**
     * Retourne la liste des variables disponibles pour l'éditeur de clauses.
     */
    public static function variablesDisponibles(): array
    {
        return [
            'Employé'  => ['employe.nom', 'employe.prenom', 'employe.nom_complet', 'employe.matricule', 'employe.cin', 'employe.date_naissance', 'employe.adresse'],
            'Contrat'  => ['contrat.reference', 'contrat.type', 'contrat.salaire_base', 'contrat.date_debut', 'contrat.date_fin', 'contrat.duree', 'contrat.poste', 'contrat.categorie'],
            'Société'  => ['societe.nom', 'societe.forme_juridique', 'societe.adresse', 'societe.ville', 'societe.ice', 'societe.rc', 'societe.if', 'societe.cnss_patron', 'societe.telephone', 'societe.email'],
            'Date'     => ['date.jour', 'date.mois_nom', 'date.annee', 'date.complete'],
            'Avenant'  => ['avenant.reference', 'avenant.nature', 'avenant.date_effet', 'avenant.ancienne_valeur', 'avenant.nouvelle_valeur', 'avenant.motif'],
        ];
    }
}
