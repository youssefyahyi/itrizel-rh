<?php
namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Employe;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    public function index(Request $request)
    {
        $query = Contrat::with('employe')->latest();

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', "%{$request->q}%")
                  ->orWhereHas('employe', fn($e) =>
                      $e->where('nom', 'like', "%{$request->q}%")
                        ->orWhere('prenom', 'like', "%{$request->q}%")
                  );
            });
        }

        if ($request->filled('statut'))  $query->where('statut', $request->statut);
        if ($request->filled('type'))    $query->where('type', $request->type);
        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);

        $contrats = $query->paginate(20)->withQueryString();

        $stats = [
            'total'     => Contrat::where('statut', 'en_cours')->count(),
            'cdd'       => Contrat::where('statut', 'en_cours')->where('type', 'CDD')->count(),
            'cdi'       => Contrat::where('statut', 'en_cours')->where('type', 'CDI')->count(),
            'expirants' => Contrat::expirants(30)->count(),
        ];

        return view('contrats.index', compact('contrats', 'stats'));
    }

    public function create()
    {
        $employes = Employe::where('statut', 'actif')->orderBy('nom')->get();
        return view('contrats.form', ['contrat' => new Contrat, 'mode' => 'create', 'employes' => $employes]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employe_id'          => 'required|exists:employes,id',
            'type'                => 'required|in:CDD,CDI,interim,vacataire',
            'poste'               => 'required|string|max:150',
            'categorie'           => 'required|in:commercial,chauffeur,magasinier,logisticien,administratif,cadre',
            'salaire_base'        => 'required|numeric|min:0',
            'date_debut'          => 'required|date|after_or_equal:today',
            'date_fin'            => 'nullable|date|after:date_debut',
            'duree_mois'          => 'nullable|integer|min:1',
            'renouvellement_auto' => 'boolean',
            'observations'        => 'nullable|string',
        ], [
            'date_debut.after_or_equal' => 'La date de début du contrat ne peut pas être dans le passé.',
            'date_fin.after'            => 'La date de fin doit être postérieure à la date de début.',
        ]);

        $data['reference']  = (new Contrat)->generateReference();
        $data['statut']     = 'en_cours';
        $data['created_by'] = auth()->id();
        $data['renouvellement_auto'] = $request->boolean('renouvellement_auto');

        $contrat = Contrat::create($data);

        AuditLog::log('Contrats', 'creation', "Contrat {$contrat->reference} créé pour {$contrat->employe->nom_complet}", $contrat);

        return redirect()->route('contrats.show', $contrat)->with('success', "Contrat {$contrat->reference} créé.");
    }

    public function show(Contrat $contrat)
    {
        $contrat->load('employe');
        return view('contrats.show', compact('contrat'));
    }

    public function edit(Contrat $contrat)
    {
        $employes = Employe::where('statut', 'actif')->orderBy('nom')->get();
        return view('contrats.form', ['contrat' => $contrat, 'mode' => 'edit', 'employes' => $employes]);
    }

    public function update(Request $request, Contrat $contrat)
    {
        $data = $request->validate([
            'poste'               => 'required|string|max:150',
            'categorie'           => 'required|in:commercial,chauffeur,magasinier,logisticien,administratif,cadre',
            'salaire_base'        => 'required|numeric|min:0',
            'date_debut'          => 'required|date',
            'date_fin'            => 'nullable|date|after:date_debut',
            'duree_mois'          => 'nullable|integer|min:1',
            'renouvellement_auto' => 'boolean',
            'statut'              => 'required|in:en_cours,expire,renouvele,resilie,cloture',
            'motif_resiliation'   => 'nullable|string|max:255',
            'observations'        => 'nullable|string',
        ]);

        $data['renouvellement_auto'] = $request->boolean('renouvellement_auto');
        $contrat->update($data);

        AuditLog::log('Contrats', 'modification', "Contrat {$contrat->reference} modifié", $contrat);

        return redirect()->route('contrats.show', $contrat)->with('success', 'Contrat mis à jour.');
    }

    public function destroy(Contrat $contrat)
    {
        AuditLog::log('Contrats', 'suppression', "Contrat {$contrat->reference} supprimé", $contrat);
        $contrat->delete();
        return redirect()->route('contrats.index')->with('success', 'Contrat supprimé.');
    }
}
