<?php
namespace App\Http\Controllers;

use App\Models\UniteOrganisationnelle;
use App\Models\Employe;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    // Charge l'arbre complet (partagé par index + show)
    private function arbre(): \Illuminate\Database\Eloquent\Collection
    {
        return UniteOrganisationnelle::with('enfants.enfants.enfants.enfants')
            ->whereNull('parent_id')
            ->orderBy('ordre')->orderBy('nom')
            ->get();
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
        if (!empty($data['parent_id'])) {
            if ($data['parent_id'] == $unite->id || $unite->tousEnfantsIds()->contains($data['parent_id'])) {
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
