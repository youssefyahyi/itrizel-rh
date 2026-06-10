<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCongeRequest;
use App\Http\Requests\UpdateCongeRequest;
use App\Models\AuditLog;
use App\Models\Conge;
use App\Models\Employe;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CongeController extends Controller
{
    public function index(Request $request)
    {
        $query = Conge::with('employe')->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('employe', fn($e) =>
                $e->where('nom', 'like', "%{$q}%")->orWhere('prenom', 'like', "%{$q}%")
            );
        }

        if ($request->filled('statut'))     $query->where('statut', $request->statut);
        if ($request->filled('type_conge')) $query->where('type_conge', $request->type_conge);
        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);

        $conges = $query->paginate(20)->withQueryString();

        $stats = [
            'en_attente' => Conge::enAttente()->count(),
            'approuves'  => Conge::approuves()->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
            'rejetes'    => Conge::where('statut', 'rejete')->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
            'jours_mois' => (int) Conge::approuves()->whereYear('date_debut', now()->year)->whereMonth('date_debut', now()->month)->sum('nb_jours'),
        ];

        return view('conges.index', compact('conges', 'stats'));
    }

    public function create()
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        return view('conges.form', ['conge' => new Conge, 'mode' => 'create', 'employes' => $employes]);
    }

    public function store(StoreCongeRequest $request)
    {
        $data = $request->validated();

        if (empty($data['nb_jours'])) {
            $data['nb_jours'] = Carbon::parse($data['date_debut'])->diffInDays($data['date_fin']) + 1;
        }

        $data['statut']     = 'soumis';
        $data['created_by'] = auth()->id();

        $conge = Conge::create($data);

        AuditLog::log('Congés', 'creation', "Congé {$conge->type_conge_libelle} soumis pour {$conge->employe->nom_complet}", $conge);

        return redirect()->route('conges.show', $conge)->with('success', 'Demande de congé soumise.');
    }

    public function show(Conge $conge)
    {
        $conge->load('employe');
        return view('conges.show', compact('conge'));
    }

    public function edit(Conge $conge)
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        return view('conges.form', ['conge' => $conge, 'mode' => 'edit', 'employes' => $employes]);
    }

    public function update(UpdateCongeRequest $request, Conge $conge)
    {
        $conge->update($request->validated());
        return redirect()->route('conges.show', $conge)->with('success', 'Congé mis à jour.');
    }

    public function approuver(Conge $conge)
    {
        $conge->update(['statut' => 'approuve', 'etape_actuelle' => $conge->etape_actuelle + 1]);
        AuditLog::log('Congés', 'approbation', "Congé approuvé pour {$conge->employe->nom_complet}", $conge);
        return back()->with('success', 'Congé approuvé.');
    }

    public function rejeter(Request $request, Conge $conge)
    {
        $conge->update(['statut' => 'rejete']);
        AuditLog::log('Congés', 'rejet', "Congé rejeté pour {$conge->employe->nom_complet}", $conge);
        return back()->with('success', 'Congé rejeté.');
    }

    public function destroy(Conge $conge)
    {
        $conge->delete();
        return redirect()->route('conges.index')->with('success', 'Congé supprimé.');
    }
}
