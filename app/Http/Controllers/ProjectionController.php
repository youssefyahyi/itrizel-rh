<?php

namespace App\Http\Controllers;

use App\Models\CategorieEmploye;
use App\Models\Employe;
use App\Models\ProjectionScenario;
use App\Models\ProjectionScenarioLigne;
use App\Services\ProjectionCalculateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectionController extends Controller
{
    private ProjectionCalculateur $calc;

    public function __construct()
    {
        $this->calc = new ProjectionCalculateur();
    }

    public function index(Request $request)
    {
        $horizon   = (int) $request->get('horizon', 12);
        $horizon   = in_array($horizon, [6, 12, 24]) ? $horizon : 12;

        $situation = $this->calc->situationActuelle($horizon);
        $scenarios = ProjectionScenario::actifs()
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get();

        return view('projection.index', compact('situation', 'scenarios', 'horizon'));
    }

    public function create()
    {
        $categories = CategorieEmploye::actives();
        $employes   = Employe::with('fichePoste.categorie')->actifs()->orderBy('nom')->get();
        $scenario   = new ProjectionScenario(['horizon' => 12]);

        return view('projection.form', compact('scenario', 'categories', 'employes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'         => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'horizon'     => 'required|in:6,12,24',
            'lignes'      => 'nullable|array',
            'lignes.*.type'          => 'required|in:revalorisation,embauche,depart',
            'lignes.*.mois_effet'    => 'required|date_format:Y-m',
            'lignes.*.pourcentage'   => 'nullable|numeric|between:-100,100',
            'lignes.*.categorie_id'  => 'nullable|exists:categories_employe,id',
            'lignes.*.salaire_estime'=> 'nullable|numeric|min:0',
            'lignes.*.employe_id'    => 'nullable|exists:employes,id',
        ]);

        if (ProjectionScenario::actifs()->count() >= 10) {
            return back()->withErrors(['nom' => 'Limite de 10 scénarios actifs atteinte. Archivez-en un avant d\'en créer un nouveau.']);
        }

        $scenario = ProjectionScenario::create([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
            'horizon'     => $data['horizon'],
            'created_by'  => Auth::id(),
        ]);

        foreach ($data['lignes'] ?? [] as $ligne) {
            $scenario->lignes()->create([
                'type'           => $ligne['type'],
                'mois_effet'     => $ligne['mois_effet'] . '-01',
                'pourcentage'    => $ligne['pourcentage'] ?? null,
                'categorie_id'   => $ligne['categorie_id'] ?? null,
                'salaire_estime' => $ligne['salaire_estime'] ?? null,
                'employe_id'     => $ligne['employe_id'] ?? null,
            ]);
        }

        return redirect()->route('projection.show', $scenario)
            ->with('success', 'Scénario créé avec succès.');
    }

    public function show(ProjectionScenario $scenario)
    {
        return redirect()->route('projection.comparer', ['a' => $scenario->id, 'b' => 'base']);
    }

    public function comparer(Request $request)
    {
        $aParam  = $request->get('a', 'base');
        $bParam  = $request->get('b', 'base');
        $horizon = (int) $request->get('horizon', 12);
        $horizon = in_array($horizon, [6, 12, 24]) ? $horizon : 12;

        $scenarios = ProjectionScenario::actifs()->orderBy('nom')->get();

        $scenarioA = $aParam !== 'base' ? ProjectionScenario::find($aParam) : null;
        $scenarioB = $bParam !== 'base' ? ProjectionScenario::find($bParam) : null;

        // Si horizon non forcé, on prend le max des deux scénarios
        if (!$request->has('horizon')) {
            $hA = $scenarioA?->horizon ?? 12;
            $hB = $scenarioB?->horizon ?? 12;
            $horizon = max($hA, $hB);
        }

        $dataA = $scenarioA
            ? $this->calc->scenario($scenarioA, $horizon)
            : $this->calc->situationActuelle($horizon);

        $dataB = $scenarioB
            ? $this->calc->scenario($scenarioB, $horizon)
            : $this->calc->situationActuelle($horizon);

        $labelA = $scenarioA?->nom ?? 'Situation actuelle';
        $labelB = $scenarioB?->nom ?? 'Situation actuelle';

        if ($scenarioA) $scenarioA->load('lignes.categorie', 'lignes.employe');
        if ($scenarioB) $scenarioB->load('lignes.categorie', 'lignes.employe');

        return view('projection.comparer', compact(
            'dataA', 'dataB', 'labelA', 'labelB',
            'scenarios', 'aParam', 'bParam', 'horizon',
            'scenarioA', 'scenarioB'
        ));
    }

    public function edit(ProjectionScenario $scenario)
    {
        $categories = CategorieEmploye::actives();
        $employes   = Employe::with('fichePoste.categorie')->actifs()->orderBy('nom')->get();
        $scenario->load('lignes.categorie', 'lignes.employe');

        return view('projection.form', compact('scenario', 'categories', 'employes'));
    }

    public function update(Request $request, ProjectionScenario $scenario)
    {
        $data = $request->validate([
            'nom'         => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'horizon'     => 'required|in:6,12,24',
            'lignes'      => 'nullable|array',
            'lignes.*.type'          => 'required|in:revalorisation,embauche,depart',
            'lignes.*.mois_effet'    => 'required|date_format:Y-m',
            'lignes.*.pourcentage'   => 'nullable|numeric|between:-100,100',
            'lignes.*.categorie_id'  => 'nullable|exists:categories_employe,id',
            'lignes.*.salaire_estime'=> 'nullable|numeric|min:0',
            'lignes.*.employe_id'    => 'nullable|exists:employes,id',
        ]);

        $scenario->update([
            'nom'         => $data['nom'],
            'description' => $data['description'] ?? null,
            'horizon'     => $data['horizon'],
        ]);

        $scenario->lignes()->delete();

        foreach ($data['lignes'] ?? [] as $ligne) {
            $scenario->lignes()->create([
                'type'           => $ligne['type'],
                'mois_effet'     => $ligne['mois_effet'] . '-01',
                'pourcentage'    => $ligne['pourcentage'] ?? null,
                'categorie_id'   => $ligne['categorie_id'] ?? null,
                'salaire_estime' => $ligne['salaire_estime'] ?? null,
                'employe_id'     => $ligne['employe_id'] ?? null,
            ]);
        }

        return redirect()->route('projection.show', $scenario)
            ->with('success', 'Scénario mis à jour.');
    }

    public function destroy(ProjectionScenario $scenario)
    {
        $scenario->delete();
        return redirect()->route('projection.index')
            ->with('success', 'Scénario supprimé.');
    }

    public function archiver(ProjectionScenario $scenario)
    {
        $scenario->update(['archived_at' => now()]);
        return back()->with('success', 'Scénario archivé.');
    }
}
