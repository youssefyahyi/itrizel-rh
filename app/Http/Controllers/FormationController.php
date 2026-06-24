<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasSalarieScope;
use App\Http\Requests\StoreFormationRequest;
use App\Http\Requests\UpdateFormationRequest;
use App\Models\AuditLog;
use App\Models\Employe;
use App\Models\Formation;
use Illuminate\Http\Request;

class FormationController extends Controller
{
    use HasSalarieScope;

    public function index(Request $request)
    {
        $query = Formation::with('employe')->latest();

        if ($sal = $this->salarie()) {
            $query->where('employe_id', $sal->employe_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('intitule', 'like', "%{$q}%")
                  ->orWhereHas('employe', fn($e) =>
                      $e->where('nom', 'like', "%{$q}%")->orWhere('prenom', 'like', "%{$q}%")
                  );
            });
        }

        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('statut'))     $query->where('statut', $request->statut);
        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);

        $formations = $query->paginate(20)->withQueryString();

        $stats = [
            'planifiees'  => Formation::where('statut', 'planifie')->count(),
            'en_cours'    => Formation::where('statut', 'en_cours')->count(),
            'terminees'   => Formation::where('statut', 'termine')->count(),
            'total_heures'=> (float) Formation::whereYear('date_debut', now()->year)->sum('nb_heures'),
        ];

        return view('formations.index', compact('formations', 'stats'));
    }

    public function create()
    {
        if ($this->salarie()) abort(403);
        $employes = Employe::actifs()->orderBy('nom')->get();
        return view('formations.form', ['formation' => new Formation, 'mode' => 'create', 'employes' => $employes]);
    }

    public function store(StoreFormationRequest $request)
    {
        if ($this->salarie()) abort(403);
        $data = $request->validated();
        $data['statut']     = 'planifie';
        $data['cout']       = $data['cout'] ?? 0;
        $data['created_by'] = auth()->id();

        $formation = Formation::create($data);

        AuditLog::log('Formations', 'creation', "Formation {$formation->intitule} créée pour {$formation->employe->nom_complet}", $formation);

        return redirect()->route('formations.show', $formation)->with('success', 'Formation créée.');
    }

    public function show(Formation $formation)
    {
        $this->checkOwnership($formation);
        $formation->load('employe');
        return view('formations.show', compact('formation'));
    }

    public function edit(Formation $formation)
    {
        if ($this->salarie()) abort(403);
        $employes = Employe::actifs()->orderBy('nom')->get();
        return view('formations.form', ['formation' => $formation, 'mode' => 'edit', 'employes' => $employes]);
    }

    public function update(UpdateFormationRequest $request, Formation $formation)
    {
        if ($this->salarie()) abort(403);
        $formation->update($request->validated());
        return redirect()->route('formations.show', $formation)->with('success', 'Formation mise à jour.');
    }

    public function destroy(Formation $formation)
    {
        if ($this->salarie()) abort(403);
        $formation->delete();
        return redirect()->route('formations.index')->with('success', 'Formation supprimée.');
    }
}
