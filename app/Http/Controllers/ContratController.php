<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasFicheData;
use App\Http\Requests\StoreContratRequest;
use App\Http\Requests\UpdateContratRequest;
use App\Models\AuditLog;
use App\Models\ClauseContrat;
use App\Models\Contrat;
use App\Models\Employe;
use App\Services\ContratPdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    public function create(Request $request)
    {
        // Seuls les employés sans contrat actif peuvent faire l'objet d'un nouveau contrat
        $employes = Employe::actifs()
            ->whereDoesntHave('contrats', fn($q) => $q->where('statut', 'en_cours'))
            ->orderBy('nom')
            ->get();

        // Pré-sélection depuis la fiche employé (optionnel)
        $employePreselectionne = $request->filled('employe_id')
            ? Employe::find($request->employe_id)
            : null;

        [$fiches, $categories] = $this->ficheData();

        return view('contrats.form', [
            'contrat'               => new Contrat,
            'mode'                  => 'create',
            'employes'              => $employes,
            'employePreselectionne' => $employePreselectionne,
            'fiches'                => $fiches,
            'categories'            => $categories,
        ]);
    }

    public function store(StoreContratRequest $request)
    {
        $data = $request->validated();

        // Vérifier qu'il n'y a pas déjà un contrat actif pour cet employé
        if (Contrat::aDejaContratActif((int) $data['employe_id'])) {
            return back()->withInput()->withErrors([
                'employe_id' => 'Cet employé a déjà un contrat en cours.',
            ]);
        }

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
        $contrat->load(['employe.fichePoste', 'fichePoste', 'avenants', 'parent', 'renouvellements']);
        return view('contrats.show', compact('contrat'));
    }

    public function edit(Contrat $contrat)
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        [$fiches, $categories] = $this->ficheData();
        return view('contrats.form', [
            'contrat'               => $contrat,
            'mode'                  => 'edit',
            'employes'              => $employes,
            'employePreselectionne' => null,
            'fiches'                => $fiches,
            'categories'            => $categories,
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

    /**
     * Affiche le formulaire de renouvellement pré-rempli depuis le contrat parent.
     */
    public function renouveler(Contrat $contrat)
    {
        if ($contrat->statut !== 'en_cours') {
            return redirect()->route('contrats.show', $contrat)
                ->with('error', 'Seul un contrat en cours peut être renouvelé.');
        }

        [$fiches, $categories] = $this->ficheData();

        return view('contrats.renouveler', [
            'contratParent' => $contrat,
            'fiches'        => $fiches,
            'categories'    => $categories,
        ]);
    }

    /**
     * Crée le contrat de renouvellement, clôt l'ancien.
     */
    public function storeRenouvellement(Request $request, Contrat $contrat)
    {
        if ($contrat->statut !== 'en_cours') {
            return redirect()->route('contrats.show', $contrat)
                ->with('error', 'Ce contrat n\'est plus renouvelable.');
        }

        $data = $request->validate([
            'type'                => ['required', 'in:CDI,CDD,interim'],
            'fiche_poste_id'      => ['nullable', 'exists:fiche_postes,id'],
            'salaire_base'        => ['required', 'numeric', 'min:0'],
            'date_debut'          => ['required', 'date'],
            'date_fin'            => ['nullable', 'date', 'after:date_debut'],
            'duree_mois'          => ['nullable', 'integer', 'min:1'],
            'calendrier_conges'   => ['nullable', 'in:ouvrable,calendaire'],
            'renouvellement_auto' => ['boolean'],
            'observations'        => ['nullable', 'string'],
        ]);

        // Clôturer le contrat parent
        $contrat->update(['statut' => 'renouvele']);

        $nouveau = Contrat::create([
            'employe_id'          => $contrat->employe_id,
            'contrat_parent_id'   => $contrat->id,
            'reference'           => Contrat::generateReference(),
            'statut'              => 'en_cours',
            'created_by'          => auth()->id(),
            'renouvellement_auto' => $request->boolean('renouvellement_auto'),
            ...$data,
        ]);

        AuditLog::log('Contrats', 'renouvellement',
            "Contrat {$contrat->reference} renouvelé → {$nouveau->reference}", $nouveau);

        return redirect()->route('contrats.show', $nouveau)
            ->with('success', "Contrat renouvelé. Nouveau référence : {$nouveau->reference}.");
    }

    /**
     * Génère le PDF du contrat (CDI/CDD), le stocke et le télécharge.
     * L'interimaire n'a pas de template de clauses → refus.
     */
    public function genererPdf(Contrat $contrat, ContratPdfGenerator $generator)
    {
        if ($contrat->type === 'interim') {
            return redirect()->route('contrats.show', $contrat)
                ->with('error', 'Aucun document contractuel n\'est généré pour les intérimaires.');
        }

        $contrat->load(['employe', 'fichePoste.poste', 'fichePoste.categorie']);
        $clausesSelectionnees = request()->input('clauses', []);

        $path = $generator->genererContrat($contrat, $clausesSelectionnees);

        return Storage::download($path, $contrat->reference . '.pdf');
    }
}
