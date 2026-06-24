<?php

namespace App\Http\Controllers;

use App\Models\SavedView;
use Illuminate\Http\Request;

class SavedViewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'module'     => 'required|string|max:50',
            'nom'        => 'required|string|max:100',
            'filtres'    => 'required|string',
            'visibilite' => 'required|in:prive,equipe,public',
        ]);

        $filtres = json_decode($data['filtres'], true);
        if (!is_array($filtres) || empty($filtres)) {
            return back()->with('error', 'Aucun filtre actif à enregistrer.');
        }
        $data['filtres'] = $filtres;

        $user = auth()->user();

        $count = SavedView::where('module', $data['module'])
            ->where('user_id', $user->id)
            ->count();

        if ($count >= 5) {
            return back()->with('error', 'Limite de 5 vues par module atteinte.');
        }

        SavedView::create([...$data, 'user_id' => $user->id]);

        $retour = $request->input('_retour', back()->getTargetUrl());
        return redirect($retour)->with('success', 'Vue "' . $data['nom'] . '" enregistrée.');
    }

    public function destroy(SavedView $vue)
    {
        if ($vue->user_id !== auth()->id()) abort(403);
        $vue->delete();
        return back()->with('success', 'Vue supprimée.');
    }
}
