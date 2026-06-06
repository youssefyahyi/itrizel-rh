<?php
namespace App\Http\Controllers;

use App\Models\Poste;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PosteController extends Controller
{
    public function index(Request $request)
    {
        $query = Poste::withCount('employes');

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom',    'like', "%{$request->q}%")
                  ->orWhere('numero', 'like', "%{$request->q}%");
            });
        }

        if ($request->filled('actif')) {
            $query->where('actif', (bool) $request->actif);
        }

        $postes = $query->orderBy('numero')->paginate(25)->withQueryString();

        return view('parametrage.postes.index', compact('postes'));
    }

    public function create()
    {
        $nextNumero = Poste::nextNumero();
        return view('parametrage.postes.form', [
            'poste'       => new Poste,
            'mode'        => 'create',
            'nextNumero'  => $nextNumero,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero'      => 'required|string|max:20|unique:postes,numero',
            'nom'         => 'required|string|max:150',
            'competences' => 'nullable|string',
            'description' => 'nullable|string',
            'actif'       => 'boolean',
        ], [
            'numero.unique' => 'Ce numéro de poste est déjà utilisé.',
        ]);

        $data['competences'] = Poste::parseCompetences($request->input('competences'));
        $data['actif']       = $request->boolean('actif', true);

        $poste = Poste::create($data);
        AuditLog::log('Postes', 'creation', "Poste {$poste->numero} — {$poste->nom} créé", $poste);

        return redirect()
            ->route('parametrage.postes.show', $poste)
            ->with('success', "Poste «{$poste->nom}» créé avec succès.");
    }

    public function show(Poste $poste)
    {
        $poste->load(['employes' => fn($q) => $q->orderBy('nom')->limit(50)]);
        return view('parametrage.postes.show', compact('poste'));
    }

    public function edit(Poste $poste)
    {
        return view('parametrage.postes.form', [
            'poste' => $poste,
            'mode'  => 'edit',
        ]);
    }

    public function update(Request $request, Poste $poste)
    {
        $data = $request->validate([
            'numero'      => 'required|string|max:20|unique:postes,numero,'.$poste->id,
            'nom'         => 'required|string|max:150',
            'competences' => 'nullable|string',
            'description' => 'nullable|string',
            'actif'       => 'boolean',
        ]);

        $data['competences'] = Poste::parseCompetences($request->input('competences'));
        $data['actif']       = $request->boolean('actif', true);

        $poste->update($data);
        AuditLog::log('Postes', 'modification', "Poste {$poste->numero} — {$poste->nom} modifié", $poste);

        return redirect()
            ->route('parametrage.postes.show', $poste)
            ->with('success', "Poste «{$poste->nom}» mis à jour.");
    }

    public function destroy(Poste $poste)
    {
        $nb = $poste->employes()->count();
        if ($nb > 0) {
            return back()->with('error',
                "Impossible de supprimer «{$poste->nom}» : {$nb} employé(s) occupent ce poste.");
        }

        $nom = $poste->nom;
        $poste->delete();
        AuditLog::log('Postes', 'suppression', "Poste {$nom} supprimé", null);

        return redirect()
            ->route('parametrage.postes.index')
            ->with('success', "Poste «{$nom}» supprimé.");
    }

    public function toggle(Poste $poste)
    {
        $poste->update(['actif' => !$poste->actif]);
        $etat = $poste->fresh()->actif ? 'activé' : 'désactivé';
        AuditLog::log('Postes', 'statut', "Poste {$poste->nom} {$etat}", $poste);
        return back()->with('success', "Poste «{$poste->nom}» {$etat}.");
    }
}
