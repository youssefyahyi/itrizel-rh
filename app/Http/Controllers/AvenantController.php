<?php

namespace App\Http\Controllers;

use App\Models\Avenant;
use App\Models\Contrat;
use App\Models\ClauseContrat;
use App\Models\AuditLog;
use App\Services\ContratPdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvenantController extends Controller
{
    public function create(Contrat $contrat)
    {
        $contrat->load(['employe', 'fichePoste.poste']);
        $clauses = ClauseContrat::actives()->pourType('avenant')->get();
        return view('avenants.form', compact('contrat', 'clauses'));
    }

    public function store(Request $request, Contrat $contrat)
    {
        $data = $request->validate([
            'date_effet'      => ['required', 'date'],
            'nature'          => ['required', 'in:' . implode(',', array_keys(Avenant::NATURES))],
            'ancienne_valeur' => ['nullable', 'string', 'max:255'],
            'nouvelle_valeur' => ['nullable', 'string', 'max:255'],
            'motif'           => ['nullable', 'string'],
        ], [
            'date_effet.required' => 'La date d\'effet est obligatoire.',
            'nature.required'     => 'La nature de l\'avenant est obligatoire.',
        ]);

        $data['contrat_id']  = $contrat->id;
        $data['reference']   = Avenant::generateReference($contrat);
        $data['created_by']  = auth()->id();

        $avenant = Avenant::create($data);

        // Appliquer la modification sur le contrat si pertinent
        $this->appliquerModification($contrat, $avenant);

        AuditLog::log('Contrats', 'avenant', "Avenant {$avenant->reference} créé sur {$contrat->reference}", $avenant);

        return redirect()->route('contrats.show', $contrat)->with('success', "Avenant {$avenant->reference} créé.");
    }

    public function show(Avenant $avenant)
    {
        $avenant->load(['contrat.employe', 'contrat.fichePoste.poste']);
        $clauses = ClauseContrat::actives()->pourType('avenant')->get();
        return view('avenants.show', compact('avenant', 'clauses'));
    }

    public function genererPdf(Avenant $avenant, ContratPdfGenerator $generator)
    {
        $avenant->load(['contrat.employe', 'contrat.fichePoste.poste', 'contrat.fichePoste.categorie']);
        $clausesSelectionnees = request()->input('clauses', []);

        $path = $generator->genererAvenant($avenant, $clausesSelectionnees);

        return Storage::download($path, $avenant->reference . '.pdf');
    }

    private function appliquerModification(Contrat $contrat, Avenant $avenant): void
    {
        match ($avenant->nature) {
            'salaire'   => $contrat->update(['salaire_base' => $avenant->nouvelle_valeur]),
            'date_fin'  => $contrat->update(['date_fin' => $avenant->nouvelle_valeur]),
            default     => null,
        };
    }
}
