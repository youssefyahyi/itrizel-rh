<?php
namespace App\Http\Controllers;
use App\Models\Absence;
use App\Models\Employe;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Absence::with('employe')->latest('date');
        if ($request->filled('q'))      $query->whereHas('employe', fn($e) => $e->where('nom','like',"%{$request->q}%")->orWhere('prenom','like',"%{$request->q}%"));
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('mois'))   $query->whereMonth('date', $request->mois);
        if ($request->filled('annee'))  $query->whereYear('date', $request->annee);
        $absences = $query->paginate(20)->withQueryString();
        $today = now()->toDateString();
        $stats = [
            'presents_today' => Absence::whereDate('date', $today)->where('statut','present')->count(),
            'absents_today'  => Absence::whereDate('date', $today)->where('statut','absent')->count(),
            'cette_semaine'  => Absence::whereBetween('date',[now()->startOfWeek(),now()->endOfWeek()])->count(),
            'ce_mois'        => Absence::whereYear('date',now()->year)->whereMonth('date',now()->month)->count(),
        ];
        return view('absences.index', compact('absences','stats'));
    }
    public function create()
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        return view('absences.form', ['absence' => new Absence, 'mode' => 'create', 'employes' => $employes]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'employe_id'       => 'required|exists:employes,id',
            'date'             => 'required|date|before_or_equal:today',
            'statut'           => 'required|in:present,absent,conge,ferie,mission',
            'heure_arrivee'    => 'nullable|date_format:H:i',
            'heure_depart'     => 'nullable|date_format:H:i',
            'heures_prevues'   => 'nullable|numeric|min:0|max:24',
            'heures_realisees' => 'nullable|numeric|min:0|max:24',
            'motif_absence'    => 'nullable|string|max:255',
            'remarque'         => 'nullable|string',
        ]);
        $data['created_by'] = auth()->id();
        $absence = Absence::create($data);
        AuditLog::log('Absences','creation',"Absence du {$absence->date->format('d/m/Y')} - {$absence->employe->nom_complet}",$absence);
        return redirect()->route('absences.show',$absence)->with('success','Absence enregistrée.');
    }
    public function show(Absence $absence) { $absence->load('employe'); return view('absences.show',compact('absence')); }
    public function edit(Absence $absence)
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        return view('absences.form',['absence'=>$absence,'mode'=>'edit','employes'=>$employes]);
    }
    public function update(Request $request, Absence $absence)
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
        $absence->update($data);
        AuditLog::log('Absences','modification',"Absence du {$absence->date->format('d/m/Y')} modifiée",$absence);
        return redirect()->route('absences.show',$absence)->with('success','Absence mise à jour.');
    }
    public function destroy(Absence $absence)
    {
        $absence->delete();
        return redirect()->route('absences.index')->with('success','Absence supprimée.');
    }
}
