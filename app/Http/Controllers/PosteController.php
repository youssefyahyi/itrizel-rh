<?php
namespace App\Http\Controllers;

use App\Models\Poste;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PosteController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:150|unique:postes,nom',
        ], [
            'nom.unique' => 'Ce poste existe déjà.',
        ]);
        $data['actif'] = true;
        $poste = Poste::create($data);
        AuditLog::log('Emplois', 'creation', "Poste «{$poste->nom}» ajouté", $poste);
        return redirect()->route('parametrage.emplois.index', ['panel' => 'postes'])
            ->with('success', "Poste «{$poste->nom}» ajouté.");
    }

    public function update(Request $request, Poste $poste)
    {
        $data = $request->validate([
            'nom' => 'required|string|max:150|unique:postes,nom,' . $poste->id,
        ], [
            'nom.unique' => 'Ce poste existe déjà.',
        ]);
        $poste->update($data);
        AuditLog::log('Emplois', 'modification', "Poste «{$poste->nom}» modifié", $poste);
        return redirect()->route('parametrage.emplois.index', ['panel' => 'postes'])
            ->with('success', 'Poste mis à jour.');
    }

    public function destroy(Poste $poste)
    {
        $nb = $poste->fiches()->count();
        if ($nb > 0) {
            return redirect()->route('parametrage.emplois.index', ['panel' => 'postes'])
                ->with('error', "Impossible : {$nb} fiche(s) d'emploi liée(s) à ce poste.");
        }
        $nom = $poste->nom;
        $poste->delete();
        AuditLog::log('Emplois', 'suppression', "Poste «{$nom}» supprimé", null);
        return redirect()->route('parametrage.emplois.index', ['panel' => 'postes'])
            ->with('success', "Poste «{$nom}» supprimé.");
    }

    public function toggle(Poste $poste)
    {
        $poste->update(['actif' => !$poste->actif]);
        $etat = $poste->fresh()->actif ? 'activé' : 'désactivé';
        return redirect()->route('parametrage.emplois.index', ['panel' => 'postes'])
            ->with('success', "Poste «{$poste->nom}» {$etat}.");
    }
}
