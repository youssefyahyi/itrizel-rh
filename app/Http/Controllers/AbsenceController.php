<?php
namespace App\Http\Controllers;
use App\Http\Requests\StoreAbsenceRequest;
use App\Http\Requests\UpdateAbsenceRequest;
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
    public function store(StoreAbsenceRequest $request)
    {
        $data = $request->validated();
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
    public function update(UpdateAbsenceRequest $request, Absence $absence)
    {
        $absence->update($request->validated());
        AuditLog::log('Absences','modification',"Absence du {$absence->date->format('d/m/Y')} modifiée",$absence);
        return redirect()->route('absences.show',$absence)->with('success','Absence mise à jour.');
    }
    public function destroy(Absence $absence)
    {
        $absence->delete();
        return redirect()->route('absences.index')->with('success','Absence supprimée.');
    }
}
