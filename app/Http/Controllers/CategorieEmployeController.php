<?php
namespace App\Http\Controllers;

use App\Models\CategorieEmploye;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class CategorieEmployeController extends Controller
{
    public function index()
    {
        $categories = CategorieEmploye::withCount('employes')->orderBy('ordre')->get();
        return view('parametrage.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'   => 'required|string|max:100|unique:categories_employe,nom',
            'ordre' => 'required|integer|min:0',
        ], ['nom.unique' => 'Cette catégorie existe déjà.']);

        $data['actif'] = true;
        $cat = CategorieEmploye::create($data);
        AuditLog::log('Catégories', 'creation', "Catégorie «{$cat->nom}» créée", $cat);

        return back()->with('success', "Catégorie «{$cat->nom}» ajoutée.");
    }

    public function update(Request $request, CategorieEmploye $category)
    {
        $data = $request->validate([
            'nom'   => 'required|string|max:100|unique:categories_employe,nom,'.$category->id,
            'ordre' => 'required|integer|min:0',
        ]);

        $category->update($data);
        AuditLog::log('Catégories', 'modification', "Catégorie «{$category->nom}» modifiée", $category);

        return back()->with('success', "Catégorie mise à jour.");
    }

    public function destroy(CategorieEmploye $category)
    {
        $nb = $category->employes()->count();
        if ($nb > 0) {
            return back()->with('error', "Impossible : {$nb} employé(s) dans cette catégorie.");
        }
        $nom = $category->nom;
        $category->delete();
        AuditLog::log('Catégories', 'suppression', "Catégorie «{$nom}» supprimée", null);
        return back()->with('success', "Catégorie «{$nom}» supprimée.");
    }

    public function toggle(CategorieEmploye $category)
    {
        $category->update(['actif' => !$category->actif]);
        $etat = $category->fresh()->actif ? 'activée' : 'désactivée';
        return back()->with('success', "Catégorie «{$category->nom}» {$etat}.");
    }
}
