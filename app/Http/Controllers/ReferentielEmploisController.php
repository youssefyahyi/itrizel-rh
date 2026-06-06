<?php
namespace App\Http\Controllers;

use App\Models\CategorieEmploye;
use App\Models\Fonction;
use App\Models\Poste;
use App\Models\FichePoste;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ReferentielEmploisController extends Controller
{
    // ── Données communes pour toutes les vues ──────────────────────
    private function baseData(Request $request): array
    {
        $categories = CategorieEmploye::orderBy('ordre')->orderBy('nom')->get();
        $fonctions  = Fonction::orderBy('ordre')->orderBy('nom')->get();
        $postes     = Poste::orderBy('nom')->get();
        $fiches     = FichePoste::with(['categorie', 'fonction', 'poste'])->get();
        $panel      = $request->get('panel'); // 'categories' | 'fonctions' | 'postes' | null

        // Construction de l'arbre : catégorie → fonction → fiches
        $tree = [];
        foreach ($categories as $cat) {
            $tree[$cat->id] = ['label' => $cat, 'fonctions' => []];
        }
        foreach ($fiches->sortBy(fn($f) => str_pad($f->fonction->ordre ?? 0, 3, '0', STR_PAD_LEFT) . $f->fonction->nom) as $fiche) {
            $catId = $fiche->categorie_id;
            $fonId = $fiche->fonction_id;
            if (!array_key_exists($catId, $tree)) continue;
            if (!array_key_exists($fonId, $tree[$catId]['fonctions'])) {
                $tree[$catId]['fonctions'][$fonId] = ['label' => $fiche->fonction, 'fiches' => []];
            }
            $tree[$catId]['fonctions'][$fonId]['fiches'][] = $fiche;
        }

        return compact('categories', 'fonctions', 'postes', 'fiches', 'tree', 'panel');
    }

    // ── Index ──────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $data     = $this->baseData($request);
        $selected = null;
        $mode     = $request->get('action') === 'create' ? 'create' : null;
        return view('parametrage.emplois.index', array_merge($data, compact('selected', 'mode')));
    }

    // ── Affichage d'une fiche ──────────────────────────────────────
    public function show(Request $request, FichePoste $fiche)
    {
        $fiche->load(['categorie', 'fonction', 'poste']);
        $data     = $this->baseData($request);
        $selected = $fiche;
        $mode     = 'edit';
        return view('parametrage.emplois.index', array_merge($data, compact('selected', 'mode')));
    }

    // ── Création d'une fiche ───────────────────────────────────────
    public function store(Request $request)
    {
        $data = $request->validate([
            'categorie_id' => 'required|exists:categories_employe,id',
            'fonction_id'  => 'required|exists:fonctions,id',
            'poste_id'     => 'required|exists:postes,id',
        ], [
            'categorie_id.required' => 'Veuillez sélectionner une catégorie.',
            'fonction_id.required'  => 'Veuillez sélectionner une fonction.',
            'poste_id.required'     => 'Veuillez sélectionner un poste.',
        ]);

        // Unicité de la combinaison
        if (FichePoste::where($data)->exists()) {
            return back()
                ->withErrors(['combo' => 'Cette combinaison Catégorie / Fonction / Poste existe déjà.'])
                ->withInput();
        }

        $data['actif'] = true;
        $fiche = FichePoste::create($data);
        $fiche->load(['categorie', 'fonction', 'poste']);

        AuditLog::log('Emplois', 'creation', "Fiche «{$fiche->label}» créée", $fiche);

        return redirect()
            ->route('parametrage.emplois.show', $fiche)
            ->with('success', "Fiche d'emploi créée avec succès.");
    }

    // ── Mise à jour d'une fiche ────────────────────────────────────
    public function update(Request $request, FichePoste $fiche)
    {
        $data = $request->validate([
            'categorie_id' => 'required|exists:categories_employe,id',
            'fonction_id'  => 'required|exists:fonctions,id',
            'poste_id'     => 'required|exists:postes,id',
        ]);

        // Unicité (sauf soi-même)
        if (FichePoste::where($data)->where('id', '!=', $fiche->id)->exists()) {
            return back()
                ->withErrors(['combo' => 'Cette combinaison existe déjà.'])
                ->withInput();
        }

        $data['actif'] = $request->boolean('actif', true);
        $fiche->update($data);
        $fiche->load(['categorie', 'fonction', 'poste']);

        AuditLog::log('Emplois', 'modification', "Fiche «{$fiche->label}» modifiée", $fiche);

        return redirect()
            ->route('parametrage.emplois.show', $fiche)
            ->with('success', 'Fiche mise à jour.');
    }

    // ── Suppression ────────────────────────────────────────────────
    public function destroy(FichePoste $fiche)
    {
        $fiche->load(['categorie', 'fonction', 'poste']);
        $label = $fiche->label;
        $fiche->delete();

        AuditLog::log('Emplois', 'suppression', "Fiche «{$label}» supprimée", null);

        return redirect()
            ->route('parametrage.emplois.index')
            ->with('success', "Fiche «{$label}» supprimée.");
    }

    // ── Toggle actif ───────────────────────────────────────────────
    public function toggle(FichePoste $fiche)
    {
        $fiche->update(['actif' => !$fiche->actif]);
        $etat = $fiche->fresh()->actif ? 'activée' : 'désactivée';
        return back()->with('success', "Fiche {$etat}.");
    }
}
