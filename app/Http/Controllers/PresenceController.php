<?php
namespace App\Http\Controllers;
use App\Models\Presence;
use App\Models\Employe;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PresenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Presence::with('employe')->latest('date');
        if ($request->filled('q'))      $query->whereHas('employe', fn($e) => $e->where('nom','like',"%{$request->q}%")->orWhere('prenom','like',"%{$request->q}%"));
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('mois'))   $query->whereMonth('date', $request->mois);
        if ($request->filled('annee'))  $query->whereYear('date', $request->annee);
        $presences = $query->paginate(20)->withQueryString();
        $today = now()->toDateString();
        $stats = [
            'presents_today' => Presence::whereDate('date', $today)->where('statut','present')->count(),
            'absents_today'  => Presence::whereDate('date', $today)->where('statut','absent')->count(),
            'cette_semaine'  => Presence::whereBetween('date',[now()->startOfWeek(),now()->endOfWeek()])->count(),
            'ce_mois'        => Presence::whereYear('date',now()->year)->whereMonth('date',now()->month)->count(),
        ];
        return view('presences.index', compact('presences','stats'));
    }
    public function create()
    {
        $employes = Employe::where('statut','actif')->orderBy('nom')->get();
        return view('presences.form', ['presence' => new Presence, 'mode' => 'create', 'employes' => $employes]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'employe_id'       => 'required|exists:employes,id',
            'date'             => 'required|date',
            'statut'           => 'required|in:present,absent,conge,ferie,mission',
            'heure_arrivee'    => 'nullable|date_format:H:i',
            'heure_depart'     => 'nullable|date_format:H:i',
            'heures_prevues'   => 'nullable|numeric|min:0|max:24',
            'heures_realisees' => 'nullable|numeric|min:0|max:24',
            'motif_absence'    => 'nullable|string|max:255',
            'remarque'         => 'nullable|string',
        ]);
        $data['created_by'] = auth()->id();
        $presence = Presence::create($data);
        AuditLog::log('Presences','creation',"Presence du {$presence->date->format('d/m/Y')} - {$presence->employe->nom_complet}",$presence);
        return redirect()->route('presences.show',$presence)->with('success','Presence enregistree.');
    }
    public function show(Presence $presence) { $presence->load('employe'); return view('presences.show',compact('presence')); }
    public function edit(Presence $presence)
    {
        $employes = Employe::where('statut','actif')->orderBy('nom')->get();
        return view('presences.form',['presence'=>$presence,'mode'=>'edit','employes'=>$employes]);
    }
    public function update(Request $request, Presence $presence)
    {
        $data = $request->validate([
            'statut'           => 'required|in:present,absent,conge,ferie,mission',
            'heure_arrivee'    => 'nullable|date_format:H:i',
            'heure_depart'     => 'nullable|date_format:H:i',
            'heures_prevues'   => 'nullable|numeric|min:0|max:24',
            'heures_realisees' => 'nullable|numeric|min:0|max:24',
            'motif_absence'    => 'nullable|string|max:255',
            'remarque'         => 'nullable|string',
        ]);
        $presence->update($data);
        AuditLog::log('Presences','modification',"Presence du {$presence->date->format('d/m/Y')} modifiee",$presence);
        return redirect()->route('presences.show',$presence)->with('success','Presence mise a jour.');
    }
    public function destroy(Presence $presence)
    {
        $presence->delete();
        return redirect()->route('presences.index')->with('success','Presence supprimee.');
    }
}