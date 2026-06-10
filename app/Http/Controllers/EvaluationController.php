<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEvaluationRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Models\AuditLog;
use App\Models\Employe;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $query = Evaluation::with(['employe', 'evaluateur'])->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('employe', fn($e) =>
                $e->where('nom', 'like', "%{$q}%")->orWhere('prenom', 'like', "%{$q}%")
            );
        }

        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('statut'))     $query->where('statut', $request->statut);
        if ($request->filled('decision'))   $query->where('decision', $request->decision);
        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);

        $evaluations = $query->paginate(20)->withQueryString();

        $stats = [
            'brouillons'      => Evaluation::where('statut', 'brouillon')->count(),
            'finalisees'      => Evaluation::where('statut', 'finalise')->count(),
            'renouvellements' => Evaluation::where('decision', 'renouvellement')->count(),
            'cette_annee'     => Evaluation::whereYear('created_at', now()->year)->count(),
        ];

        return view('evaluations.index', compact('evaluations', 'stats'));
    }

    public function create()
    {
        $employes    = Employe::actifs()->orderBy('nom')->get();
        $evaluateurs = User::where('actif', true)->orderBy('name')->get();
        return view('evaluations.form', [
            'evaluation'  => new Evaluation,
            'mode'        => 'create',
            'employes'    => $employes,
            'evaluateurs' => $evaluateurs,
        ]);
    }

    public function store(StoreEvaluationRequest $request)
    {
        $data = $request->validated();
        $data['statut']     = 'brouillon';
        $data['created_by'] = auth()->id();

        $eval = Evaluation::create($data);

        AuditLog::log('Évaluations', 'creation', "Évaluation {$eval->type_libelle} créée pour {$eval->employe->nom_complet}", $eval);

        return redirect()->route('evaluations.show', $eval)->with('success', 'Évaluation créée.');
    }

    public function show(Evaluation $evaluation)
    {
        $evaluation->load(['employe', 'evaluateur']);
        return view('evaluations.show', compact('evaluation'));
    }

    public function edit(Evaluation $evaluation)
    {
        $employes    = Employe::actifs()->orderBy('nom')->get();
        $evaluateurs = User::where('actif', true)->orderBy('name')->get();
        return view('evaluations.form', [
            'evaluation'  => $evaluation,
            'mode'        => 'edit',
            'employes'    => $employes,
            'evaluateurs' => $evaluateurs,
        ]);
    }

    public function update(UpdateEvaluationRequest $request, Evaluation $evaluation)
    {
        $evaluation->update($request->validated());
        return redirect()->route('evaluations.show', $evaluation)->with('success', 'Évaluation mise à jour.');
    }

    public function destroy(Evaluation $evaluation)
    {
        $evaluation->delete();
        return redirect()->route('evaluations.index')->with('success', 'Évaluation supprimée.');
    }
}
