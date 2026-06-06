<?php
namespace App\Http\Controllers;

use App\Models\CategorieEmploye;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class CategorieEmployeController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'   => 'required|string|max:100|unique:categories_employe,nom',
            'ordre' => 'required|integer|min:0|max:99',
        ], [
            'nom.unique' => 'Cette catégorie existe déjà.',
        ]);
        $data['actif'] = true;
        $cat = CategorieEmploye::create($data);
        AuditLog::log('Emplois', 'creation', "Catégorie «{$cat->nom}» ajoutée", $cat);
        return redirect()->route('parametrage.emplois.index', ['panel' => 'categories'])
            ->with('success', "Catégorie «{$cat->nom}» ajoutée.");
    }

    public function update(Request $request, CategorieEmploye $categorie)
    {
        $data = $request->validate([
            'nom'   => 'required|string|max:100|unique:categories_employe,nom,' . $categorie->id,
            'ordre' => 'required|integer|min:0|max:99',
        ], [
            'nom.unique' => 'Cette catégorie existe déjà.',
        ]);
        $categorie->update($data);
        AuditLog::log('Emplois', 'modification', "Catégorie «{$categorie->nom}» modifiée", $categorie);
        return redirect()->route('parametrage.emplois.index', ['panel' => 'categories'])
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(CategorieEmploye $categorie)
    {
        $nb = $categorie->fiches()->count();
        if ($nb > 0) {
            return redirect()->route('parametrage.emplois.index', ['panel' => 'categories'])
                ->with('error', "Impossible : {$nb} fiche(s) d'emploi liée(s) à cette catégorie.");
        }
        $nom = $categorie->nom;
        $categorie->delete();
        AuditLog::log('Emplois', 'suppression', "Catégorie «{$nom}» supprimée", null);
        return redirect()->route('parametrage.emplois.index', ['panel' => 'categories'])
            ->with('success', "Catégorie «{$nom}» supprimée.");
    }

    public function toggle(CategorieEmploye $categorie)
    {
        $categorie->update(['actif' => !$categorie->actif]);
        $etat = $categorie->fresh()->actif ? 'activée' : 'désactivée';
        return redirect()->route('parametrage.emplois.index', ['panel' => 'categories'])
            ->with('success', "Catégorie «{$categorie->nom}» {$etat}.");
    }
}
