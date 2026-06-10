<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasFicheData;
use App\Http\Requests\StoreContratRequest;
use App\Http\Requests\UpdateContratRequest;
use App\Models\AuditLog;
use App\Models\Contrat;
use App\Models\Employe;
use Illuminate\Http\Request;

class ContratController extends Controller
{
    use HasFicheData;

    public function index(Request $request)
    {
        $query = Contrat::with(['employe.fichePoste', 'fichePoste.poste'])->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('reference', 'like', "%{$q}%")
                  ->orWhereHas('employe', fn($e) =>
                      $e->where('nom', 'like', "%{$q}%")->orWhere('prenom', 'like', "%{$q}%")
                  );
            });
        }

        if ($request->filled('statut'))     $query->where('statut', $request->statut);
        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);

        $contrats = $query->paginate(20)->withQueryString();

        $stats = [
            'total'     => Contrat::actifs()->count(),
            'cdd'       => Contrat::actifs()->where('type', 'CDD')->count(),
            'cdi'       => Contrat::actifs()->where('type', 'CDI')->count(),
            'expirants' => Contrat::expirants(30)->count(),
        ];

        return view('contrats.index', compact('contrats', 'stats'));
    }

    public function create()
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        [$fiches, $categories] = $this->ficheData();
        return view('contrats.form', [
            'contrat'    => new Contrat,
            'mode'       => 'create',
            'employes'   => $employes,
            'fiches'     => $fiches,
            'categories' => $categories,
        ]);
    }

    public function store(StoreContratRequest $request)
    {
        $data = $request->validated();
        $data['reference']           = Contrat::generateReference();
        $data['statut']              = 'en_cours';
        $data['created_by']          = auth()->id();
        $data['renouvellement_auto'] = $request->boolean('renouvellement_auto');

        $contrat = Contrat::create($data);

        AuditLog::log('Contrats', 'creation', "Contrat {$contrat->reference} créé pour {$contrat->employe->nom_complet}", $contrat);

        return redirect()->route('contrats.show', $contrat)->with('success', "Contrat {$contrat->reference} créé.");
    }

    public function show(Contrat $contrat)
    {
        $contrat->load(['employe.fichePoste', 'fichePoste']);
        return view('contrats.show', compact('contrat'));
    }

    public function edit(Contrat $contrat)
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        [$fiches, $categories] = $this->ficheData();
        return view('contrats.form', [
            'contrat'    => $contrat,
            'mode'       => 'edit',
            'employes'   => $employes,
            'fiches'     => $fiches,
            'categories' => $categories,
        ]);
    }

    public function update(UpdateContratRequest $request, Contrat $contrat)
    {
        $data = $request->validated();
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
