<?php
namespace App\Http\Controllers;
use App\Models\Evaluation;
use App\Models\Employe;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    public function index(Request $request)
    {
        $query = Evaluation::with(['employe','evaluateur'])->latest();
        if ($request->filled('q'))         $query->whereHas('employe', fn($e) => $e->where('nom','like',"%{$request->q}%")->orWhere('prenom','like',"%{$request->q}%"));
        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('statut'))    $query->where('statut', $request->statut);
        if ($request->filled('decision'))  $query->where('decision', $request->decision);
        if ($request->filled('employe_id'))$query->where('employe_id', $request->employe_id);
        $evaluations = $query->paginate(20)->withQueryString();
        $stats = [
            'brouillons'      => Evaluation::where('statut','brouillon')->count(),
            'finalisees'      => Evaluation::where('statut','finalise')->count(),
            'renouvellements' => Evaluation::where('decision','renouvellement')->count(),
            'cette_annee'     => Evaluation::whereYear('created_at',now()->year)->count(),
        ];
        return view('evaluations.index', compact('evaluations','stats'));
    }
    public function create()
    {
        $employes  = Employe::where('statut','actif')->orderBy('nom')->get();
        $evaluateurs = User::where('actif',true)->orderBy('name')->get();
        return view('evaluations.form',['evaluation'=>new Evaluation,'mode'=>'create','employes'=>$employes,'evaluateurs'=>$evaluateurs]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'employe_id'    => 'required|exists:employes,id',
            'evaluateur_id' => 'required|exists:users,id',
            'type'          => 'required|in:semestriel,annuel,periode_essai',
            'periode_debut' => 'required|date',
            'periode_fin'   => 'required|date|after_or_equal:periode_debut',
            'note_globale'  => 'nullable|numeric|min:0|max:20',
            'decision'      => 'required|in:renouvellement,non_renouvellement,amelioration,en_attente',
            'observations'  => 'nullable|string',
        ]);
        $data['statut'] = 'brouillon';
        $data['created_by'] = auth()->id();
        $eval = Evaluation::create($data);
        AuditLog::log('Evaluations','creation',"Evaluation {$eval->type_libelle} creee pour {$eval->employe->nom_complet}",$eval);
        return redirect()->route('evaluations.show',$eval)->with('success','Evaluation creee.');
    }
    public function show(Evaluation $evaluation) { $evaluation->load(['employe','evaluateur']); return view('evaluations.show',compact('evaluation')); }
    public function edit(Evaluation $evaluation)
    {
        $employes    = Employe::where('statut','actif')->orderBy('nom')->get();
        $evaluateurs = User::where('actif',true)->orderBy('name')->get();
        return view('evaluations.form',['evaluation'=>$evaluation,'mode'=>'edit','employes'=>$employes,'evaluateurs'=>$evaluateurs]);
    }
    public function update(Request $request, Evaluation $evaluation)
    {
        $data = $request->validate([
            'evaluateur_id' => 'required|exists:users,id',
            'type'          => 'required|in:semestriel,annuel,periode_essai',
            'periode_debut' => 'required|date',
            'periode_fin'   => 'required|date|after_or_equal:periode_debut',
            'note_globale'  => 'nullable|numeric|min:0|max:20',
            'statut'        => 'required|in:brouillon,finalise',
            'decision'      => 'required|in:renouvellement,non_renouvellement,amelioration,en_attente',
            'observations'  => 'nullable|string',
        ]);
        $evaluation->update($data);
        return redirect()->route('evaluations.show',$evaluation)->with('success','Evaluation mise a jour.');
    }
    public function destroy(Evaluation $evaluation)
    {
        $evaluation->delete();
        return redirect()->route('evaluations.index')->with('success','Evaluation supprimee.');
    }
}