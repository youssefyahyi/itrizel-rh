<?php
namespace App\Http\Controllers;
use App\Models\Conge;
use App\Models\Employe;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class CongeController extends Controller
{
    public function index(Request $request)
    {
        $query = Conge::with('employe')->latest();
        if ($request->filled('q'))         $query->whereHas('employe', fn($e) => $e->where('nom','like',"%{$request->q}%")->orWhere('prenom','like',"%{$request->q}%"));
        if ($request->filled('statut'))    $query->where('statut', $request->statut);
        if ($request->filled('type_conge'))$query->where('type_conge', $request->type_conge);
        if ($request->filled('employe_id'))$query->where('employe_id', $request->employe_id);
        $conges = $query->paginate(20)->withQueryString();
        $stats = [
            'en_attente' => Conge::enAttente()->count(),
            'approuves'  => Conge::approuves()->whereYear('created_at',now()->year)->whereMonth('created_at',now()->month)->count(),
            'rejetes'    => Conge::where('statut','rejete')->whereYear('created_at',now()->year)->whereMonth('created_at',now()->month)->count(),
            'jours_mois' => (int) Conge::approuves()->whereYear('date_debut',now()->year)->whereMonth('date_debut',now()->month)->sum('nb_jours'),
        ];
        return view('conges.index', compact('conges','stats'));
    }
    public function create()
    {
        $employes = Employe::where('statut','actif')->orderBy('nom')->get();
        return view('conges.form',['conge'=>new Conge,'mode'=>'create','employes'=>$employes]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'employe_id'  => 'required|exists:employes,id',
            'type_conge'  => 'required|in:annuel,maladie,maternite,paternite,sans_solde,exceptionnel,recuperation',
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'nb_jours'    => 'nullable|integer|min:1',
            'motif'       => 'nullable|string',
        ]);
        if (empty($data['nb_jours'])) {
            $data['nb_jours'] = \Carbon\Carbon::parse($data['date_debut'])->diffInDays($data['date_fin']) + 1;
        }
        $data['statut'] = 'soumis';
        $data['created_by'] = auth()->id();
        $conge = Conge::create($data);
        AuditLog::log('Conges','creation',"Conge {$conge->type_conge_libelle} soumis pour {$conge->employe->nom_complet}",$conge);
        return redirect()->route('conges.show',$conge)->with('success','Demande de conge soumise.');
    }
    public function show(Conge $conge) { $conge->load('employe'); return view('conges.show',compact('conge')); }
    public function edit(Conge $conge)
    {
        $employes = Employe::where('statut','actif')->orderBy('nom')->get();
        return view('conges.form',['conge'=>$conge,'mode'=>'edit','employes'=>$employes]);
    }
    public function update(Request $request, Conge $conge)
    {
        $data = $request->validate([
            'type_conge' => 'required|in:annuel,maladie,maternite,paternite,sans_solde,exceptionnel,recuperation',
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'nb_jours'   => 'nullable|integer|min:1',
            'statut'     => 'required|in:soumis,en_validation,approuve,rejete,annule',
            'motif'      => 'nullable|string',
        ]);
        $conge->update($data);
        return redirect()->route('conges.show',$conge)->with('success','Conge mis a jour.');
    }
    public function approuver(Conge $conge)
    {
        $conge->update(['statut'=>'approuve','etape_actuelle'=>$conge->etape_actuelle+1]);
        AuditLog::log('Conges','approbation',"Conge approuve pour {$conge->employe->nom_complet}",$conge);
        return back()->with('success','Conge approuve.');
    }
    public function rejeter(Request $request, Conge $conge)
    {
        $conge->update(['statut'=>'rejete']);
        AuditLog::log('Conges','rejet',"Conge rejete pour {$conge->employe->nom_complet}",$conge);
        return back()->with('success','Conge rejete.');
    }
    public function destroy(Conge $conge)
    {
        $conge->delete();
        return redirect()->route('conges.index')->with('success','Conge supprime.');
    }
}