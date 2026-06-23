<?php

namespace App\Http\Controllers;

use App\Models\CodificationParametre;
use App\Services\CodificationService;
use Illuminate\Http\Request;

class CodificationController extends Controller
{
    public function index()
    {
        $configs = CodificationParametre::orderBy('entite')->get();
        return view('parametrage.codification', compact('configs'));
    }

    public function update(Request $request, CodificationParametre $codification)
    {
        $data = $request->validate([
            'prefixe'       => ['nullable', 'string', 'max:20'],
            'separateur'    => ['required', 'string', 'max:5'],
            'inclure_annee' => ['boolean'],
            'format_annee'  => ['required', 'in:YYYY,YY'],
            'nb_chiffres'   => ['required', 'integer', 'min:2', 'max:8'],
            'reset_annuel'  => ['boolean'],
        ]);

        $data['prefixe']       = $data['prefixe'] ?? '';
        $data['inclure_annee'] = $request->boolean('inclure_annee');
        $data['reset_annuel']  = $request->boolean('reset_annuel');

        $codification->update($data);

        return back()->with('success', "Codification « {$codification->libelle} » mise à jour.");
    }

    public function reinitialiser(CodificationParametre $codification)
    {
        $codification->update([
            'compteur_courant' => 0,
            'annee_courante'   => (int) now()->format('Y'),
        ]);

        return back()->with('success', "Compteur « {$codification->libelle} » remis à zéro.");
    }

    public function apercu(Request $request)
    {
        $entite = $request->input('entite');
        $config = CodificationParametre::where('entite', $entite)->first();

        if (!$config) return response()->json(['apercu' => '—']);

        // Simulation avec les valeurs du formulaire
        $config->fill([
            'prefixe'       => $request->input('prefixe', ''),
            'separateur'    => $request->input('separateur', '-'),
            'inclure_annee' => $request->boolean('inclure_annee'),
            'format_annee'  => $request->input('format_annee', 'YYYY'),
            'nb_chiffres'   => (int) $request->input('nb_chiffres', 4),
        ]);

        return response()->json(['apercu' => $config->apercu()]);
    }
}
