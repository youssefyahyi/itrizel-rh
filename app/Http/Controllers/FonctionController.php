<?php
namespace App\Http\Controllers;

use App\Models\Fonction;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class FonctionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'   => 'required|string|max:150|unique:fonctions,nom',
            'ordre' => 'required|integer|min:0|max:99',
        ], [
            'nom.unique' => 'Cette fonction existe déjà.',
        ]);
        $data['actif'] = true;
        $fn = Fonction::create($data);
        AuditLog::log('Emplois', 'creation', "Fonction «{$fn->nom}» ajoutée", $fn);
        return redirect()->route('parametrage.emplois.index', ['panel' => 'fonctions'])
            ->with('success', "Fonction «{$fn->nom}» ajoutée.");
    }

    public function update(Request $request, Fonction $fonction)
    {
        $data = $request->validate([
            'nom'   => 'required|string|max:150|unique:fonctions,nom,' . $fonction->id,
            'ordre' => 'required|integer|min:0|max:99',
        ], [
            'nom.unique' => 'Cette fonction existe déjà.',
        ]);
        $fonction->update($data);
        AuditLog::log('Emplois', 'modification', "Fonction «{$fonction->nom}» modifiée", $fonction);
        return redirect()->route('parametrage.emplois.index', ['panel' => 'fonctions'])
            ->with('success', 'Fonction mise à jour.');
    }

    public function destroy(Fonction $fonction)
    {
        $nb = $fonction->fiches()->count();
        if ($nb > 0) {
            return redirect()->route('parametrage.emplois.index', ['panel' => 'fonctions'])
                ->with('error', "Impossible : {$nb} fiche(s) d'emploi liée(s) à cette fonction.");
        }
        $nom = $fonction->nom;
        $fonction->delete();
        AuditLog::log('Emplois', 'suppression', "Fonction «{$nom}» supprimée", null);
        return redirect()->route('parametrage.emplois.index', ['panel' => 'fonctions'])
            ->with('success', "Fonction «{$nom}» supprimée.");
    }

    public function toggle(Fonction $fonction)
    {
        $fonction->update(['actif' => !$fonction->actif]);
        $etat = $fonction->fresh()->actif ? 'activée' : 'désactivée';
        return redirect()->route('parametrage.emplois.index', ['panel' => 'fonctions'])
            ->with('success', "Fonction «{$fonction->nom}» {$etat}.");
    }
}
