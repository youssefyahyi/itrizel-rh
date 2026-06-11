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
    private function salarie(): ?\App\Models\User
    {
        $user = auth()->user();
        return $user->isSalarie() ? $user : null;
    }

    public function index(Request $request)
    {
        $query = Conge::with('employe')->latest();

        if ($sal = $this->salarie()) {
            $query->where('employe_id', $sal->employe_id);
        }

        if ($request->filled('q') && !$this->salarie()) {
            $q = $request->q;
            $query->whereHas('employe', fn($e) =>
                $e->where('nom', 'like', "%{$q}%")->orWhere('prenom', 'like', "%{$q}%")
            );
        }

        if ($request->filled('statut'))                       $query->where('statut', $request->statut);
        if ($request->filled('type_conge'))                   $query->where('type_conge', $request->type_conge);
        if ($request->filled('employe_id') && !$this->salarie()) $query->where('employe_id', $request->employe_id);

        $conges = $query->paginate(20)->withQueryString();

        $soldeSalarie = null;
        if ($sal = $this->salarie()) {
            $emp = \App\Models\Employe::find($sal->employe_id);
            if ($emp) {
                $soldeSalarie = [
                    'acquis'  => $emp->solde_conges_acquis,
                    'pris'    => $emp->solde_conges_pris,
                    'restant' => $emp->solde_conges_restant,
                ];
            }
        }

        if ($sal = $this->salarie()) {
            $empId = $sal->employe_id;
            $stats = [
                'en_attente' => Conge::enAttente()->where('employe_id', $empId)->count(),
                'approuves'  => Conge::approuves()->where('employe_id', $empId)->whereYear('created_at', now()->year)->count(),
                'rejetes'    => Conge::where('statut', 'rejete')->where('employe_id', $empId)->whereYear('created_at', now()->year)->count(),
                'jours_mois' => (int) Conge::approuves()->where('employe_id', $empId)->whereYear('date_debut', now()->year)->whereMonth('date_debut', now()->month)->sum('nb_jours'),
            ];
        } else {
            $stats = [
                'en_attente' => Conge::enAttente()->count(),
                'approuves'  => Conge::approuves()->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
                'rejetes'    => Conge::where('statut', 'rejete')->whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
                'jours_mois' => (int) Conge::approuves()->whereYear('date_debut', now()->year)->whereMonth('date_debut', now()->month)->sum('nb_jours'),
            ];
        }

        $isSalarie = auth()->user()->isSalarie();
        return view('conges.index', compact('conges', 'stats', 'isSalarie', 'soldeSalarie'));
    }

    public function create()
    {
        if ($sal = $this->salarie()) {
            if (!$sal->employe_id) abort(403, 'Aucune fiche employé liée à votre compte.');
            $employes     = Employe::where('id', $sal->employe_id)->get();
            $preselected  = $sal->employe_id;
        } else {
            $employes    = Employe::actifs()->orderBy('nom')->get();
            $preselected = null;
        }
        return view('conges.form', ['conge' => new Conge, 'mode' => 'create', 'employes' => $employes, 'preselected' => $preselected]);
    }

    public function store(StoreCongeRequest $request)
    {
        $data = $request->validated();

        if ($sal = $this->salarie()) {
            if (!$sal->employe_id) abort(403);
            $data['employe_id'] = $sal->employe_id;
        }

        if (empty($data['nb_jours'])) {
            $data['nb_jours'] = Carbon::parse($data['date_debut'])->diffInDays($data['date_fin']) + 1;
        }

        $data['statut']     = 'soumis';
        $data['created_by'] = auth()->id();

        $conge = Conge::create($data);

        AuditLog::log('Congés', 'creation', "Congé {$conge->type_conge_libelle} soumis pour {$conge->employe->nom_complet}", $conge);

        return redirect()->route('conges.show', $conge)->with('success', 'Demande de congé soumise.');
    }

    private function checkCongeOwnership(Conge $conge): void
    {
        if ($sal = $this->salarie()) {
            if ($conge->employe_id !== $sal->employe_id) abort(403);
        }
    }

    public function show(Conge $conge)
    {
        $this->checkCongeOwnership($conge);
        $conge->load('employe');
        return view('conges.show', compact('conge'));
    }

    public function edit(Conge $conge)
    {
        $this->checkCongeOwnership($conge);
        if ($sal = $this->salarie()) {
            $employes    = Employe::where('id', $sal->employe_id)->get();
            $preselected = $sal->employe_id;
        } else {
            $employes    = Employe::actifs()->orderBy('nom')->get();
            $preselected = null;
        }
        return view('conges.form', ['conge' => $conge, 'mode' => 'edit', 'employes' => $employes, 'preselected' => $preselected]);
    }

    public function update(UpdateCongeRequest $request, Conge $conge)
    {
        $this->checkCongeOwnership($conge);
        $data = $request->validated();
        if ($sal = $this->salarie()) {
            $data['employe_id'] = $sal->employe_id;
        }
        $conge->update($data);
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
        $this->checkCongeOwnership($conge);
        $conge->delete();
        return redirect()->route('conges.index')->with('success', 'Congé supprimé.');
    }
}
