<?php
namespace App\Http\Controllers;

use App\Models\UniteOrganisationnelle;
use App\Models\Employe;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    /**
     * Charge TOUTES les unités en 1 seule requête SQL,
     * puis construit l'arbre en mémoire PHP.
     * Fonctionne pour n'importe quelle profondeur, 10 ou 1000 unités.
     */
    private function arbre(): \Illuminate\Support\Collection
    {
        // 1 requête : tout charger d'un coup avec le responsable
        $toutes = UniteOrganisationnelle::with('responsable')
            ->orderBy('ordre')
            ->orderBy('nom')
            ->get()
            ->keyBy('id');

        // Initialiser la collection enfants sur chaque unité
        foreach ($toutes as $unite) {
            $unite->setRelation('enfants', collect());
        }

        // Rattacher chaque unité à son parent en mémoire
        $racines = collect();
        foreach ($toutes as $unite) {
            if ($unite->parent_id && $toutes->has($unite->parent_id)) {
                $toutes[$unite->parent_id]->enfants->push($unite);
            } else {
                $racines->push($unite);
            }
        }

        return $racines;
    }

    /**
     * Retourne tous les IDs descendants d'une unité
     * à partir d'une collection déjà chargée (0 requête supplémentaire).
     */
    private function tousDescendantsIds(int $uniteId, \Illuminate\Support\Collection $toutes): array
    {
        $ids = [];
        $queue = [$uniteId];
        while (!empty($queue)) {
            $currentId = array_shift($queue);
            $enfants = $toutes->where('parent_id', $currentId);
            foreach ($enfants as $enfant) {
                $ids[] = $enfant->id;
                $queue[] = $enfant->id;
            }
        }
        return $ids;
    }

    public function index(Request $request)
    {
        $racines  = $this->arbre();
        $toutes   = UniteOrganisationnelle::orderBy('nom')->get();
        $employes = Employe::actifs()->orderBy('nom')->get();
        $selected = null;
        $mode     = null;
        $parent   = $request->filled('parent_id')
            ? UniteOrganisationnelle::find($request->parent_id)
            : null;

        if ($request->filled('action') && $request->action === 'create') {
            $mode = 'create';
        }

        return view('parametrage.organisation.index',
            compact('racines', 'toutes', 'employes', 'selected', 'mode', 'parent'));
    }

    public function show(UniteOrganisationnelle $unite)
    {
        $racines  = $this->arbre();
        $toutes   = UniteOrganisationnelle::where('id', '!=', $unite->id)->orderBy('nom')->get();
        $employes = Employe::actifs()->orderBy('nom')->get();
        $unite->load(['parent', 'enfants', 'responsable', 'employes.userCompte']);
        $selected = $unite;
        $mode     = 'edit';
        $parent   = null;

        return view('parametrage.organisation.index',
            compact('racines', 'toutes', 'employes', 'selected', 'mode', 'parent'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:20|unique:unites_organisationnelles,code',
            'nom'            => 'required|string|max:100',
            'type'           => 'required|in:direction,departement,service,equipe,autre',
            'parent_id'      => 'nullable|exists:unites_organisationnelles,id',
            'responsable_id' => 'nullable|exists:employes,id',
            'description'    => 'nullable|string',
            'ordre'          => 'nullable|integer|min:0',
        ]);

        $unite = UniteOrganisationnelle::create($data);
        AuditLog::log('Organisation', 'creation', "Entité « {$unite->nom} » créée", $unite);

        return redirect()->route('parametrage.organisation.show', $unite)
            ->with('success', "Entité « {$unite->nom} » créée avec succès.");
    }

    public function update(Request $request, UniteOrganisationnelle $unite)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:20|unique:unites_organisationnelles,code,'.$unite->id,
            'nom'            => 'required|string|max:100',
            'type'           => 'required|in:direction,departement,service,equipe,autre',
            'parent_id'      => 'nullable|exists:unites_organisationnelles,id',
            'responsable_id' => 'nullable|exists:employes,id',
            'description'    => 'nullable|string',
            'ordre'          => 'nullable|integer|min:0',
            'actif'          => 'sometimes|boolean',
        ]);

        // Anti-boucle : empêcher de rattacher à un de ses propres enfants
        // Utilise tousDescendantsIds() qui ne fait 0 requête SQL (travail en mémoire)
        if (!empty($data['parent_id'])) {
            $toutes = UniteOrganisationnelle::select('id', 'parent_id')->get();
            $descendants = $this->tousDescendantsIds($unite->id, $toutes);
            if ($data['parent_id'] == $unite->id || in_array($data['parent_id'], $descendants)) {
                return back()->withErrors(['parent_id' => 'Impossible de rattacher une entité à l\'un de ses descendants.'])->withInput();
            }
        }

        $data['actif'] = $request->boolean('actif', true);
        $unite->update($data);
        AuditLog::log('Organisation', 'modification', "Entité « {$unite->nom} » modifiée", $unite);

        return redirect()->route('parametrage.organisation.show', $unite)
            ->with('success', "Entité « {$unite->nom} » mise à jour.");
    }

    public function destroy(UniteOrganisationnelle $unite)
    {
        if ($unite->enfants()->count() > 0) {
            return back()->with('error', "Impossible de supprimer « {$unite->nom} » : elle contient des sous-entités.");
        }
        if ($unite->employes()->count() > 0) {
            return back()->with('error', "Impossible de supprimer « {$unite->nom} » : des employés y sont rattachés.");
        }

        $nom = $unite->nom;
        $parentId = $unite->parent_id;
        $unite->delete();
        AuditLog::log('Organisation', 'suppression', "Entité « $nom » supprimée", null);

        if ($parentId) {
            return redirect()->route('parametrage.organisation.show', $parentId)
                ->with('success', "Entité « $nom » supprimée.");
        }
        return redirect()->route('parametrage.organisation.index')
            ->with('success', "Entité « $nom » supprimée.");
    }
}
