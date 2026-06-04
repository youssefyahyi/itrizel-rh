<?php
namespace App\Http\Controllers;
use App\Models\Formation;
use App\Models\Employe;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    public function index(Request $request)
    {
        $query = Formation::with('employe')->latest();
        if ($request->filled('q'))         $query->whereHas('employe', fn($e) => $e->where('nom','like',"%{$request->q}%")->orWhere('prenom','like',"%{$request->q}%"))->orWhere('intitule','like',"%{$request->q}%");
        if ($request->filled('type'))      $query->where('type', $request->type);
        if ($request->filled('statut'))    $query->where('statut', $request->statut);
        if ($request->filled('employe_id'))$query->where('employe_id', $request->employe_id);
        $formations = $query->paginate(20)->withQueryString();
        $stats = [
            'planifiees'  => Formation::where('statut','planifie')->count(),
            'en_cours'    => Formation::where('statut','en_cours')->count(),
            'terminees'   => Formation::where('statut','termine')->count(),
            'total_heures'=> (float) Formation::whereYear('date_debut',now()->year)->sum('nb_heures'),
        ];
        return view('formations.index', compact('formations','stats'));
    }
    public function create()
    {
        $employes = Employe::where('statut','actif')->orderBy('nom')->get();
        return view('formations.form',['formation'=>new Formation,'mode'=>'create','employes'=>$employes]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'employe_id' => 'required|exists:employes,id',
            'intitule'   => 'required|string|max:255',
            'organisme'  => 'nullable|string|max:255',
            'type'       => 'required|in:interne,externe',
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'nb_heures'  => 'required|numeric|min:0',
            'cout'       => 'nullable|numeric|min:0',
            'observations'=> 'nullable|string',
        ]);
        $data['statut'] = 'planifie';
        $data['cout'] = $data['cout'] ?? 0;
        $data['created_by'] = auth()->id();
        $formation = Formation::create($data);
        AuditLog::log('Formations','creation',"Formation {$formation->intitule} creee pour {$formation->employe->nom_complet}",$formation);
        return redirect()->route('formations.show',$formation)->with('success','Formation creee.');
    }
    public function show(Formation $formation) { $formation->load('employe'); return view('formations.show',compact('formation')); }
    public function edit(Formation $formation)
    {
        $employes = Employe::where('statut','actif')->orderBy('nom')->get();
        return view('formations.form',['formation'=>$formation,'mode'=>'edit','employes'=>$employes]);
    }
    public function update(Request $request, Formation $formation)
    {
        $data = $request->validate([
            'intitule'    => 'required|string|max:255',
            'organisme'   => 'nullable|string|max:255',
            'type'        => 'required|in:interne,externe',
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'nb_heures'   => 'required|numeric|min:0',
            'cout'        => 'nullable|numeric|min:0',
            'statut'      => 'required|in:planifie,en_cours,termine,annule',
            'observations'=> 'nullable|string',
        ]);
        $formation->update($data);
        return redirect()->route('formations.show',$formation)->with('success','Formation mise a jour.');
    }
    public function destroy(Formation $formation)
    {
        $formation->delete();
        return redirect()->route('formations.index')->with('success','Formation supprimee.');
    }
}