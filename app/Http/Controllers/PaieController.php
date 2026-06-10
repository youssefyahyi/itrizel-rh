<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaieRequest;
use App\Http\Requests\UpdatePaieRequest;
use App\Models\AuditLog;
use App\Models\BulletinPaie;
use App\Models\Employe;
use App\Models\ParametrageRh;
use App\Services\PaieCalculateur;
use Illuminate\Http\Request;

class PaieController extends Controller
{
    public function index(Request $request)
    {
        $query = BulletinPaie::with('employe')->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->whereHas('employe', fn($e) =>
                $e->where('nom', 'like', "%{$q}%")->orWhere('prenom', 'like', "%{$q}%")
            );
        }

        if ($request->filled('statut'))     $query->where('statut', $request->statut);
        if ($request->filled('mois'))       $query->where('periode_mois', $request->mois);
        if ($request->filled('annee'))      $query->where('periode_annee', $request->annee);
        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);

        $bulletins = $query->paginate(20)->withQueryString();

        $stats = [
            'brouillons' => BulletinPaie::where('statut', 'brouillon')->count(),
            'valides'    => BulletinPaie::where('statut', 'valide')->count(),
            'payes'      => BulletinPaie::where('statut', 'paye')->count(),
            'masse'      => (float) BulletinPaie::where('statut', 'paye')
                                ->where('periode_mois', now()->month)
                                ->where('periode_annee', now()->year)
                                ->sum('net_a_payer'),
        ];

        return view('paie.index', compact('bulletins', 'stats'));
    }

    public function create()
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        return view('paie.form', ['bulletin' => new BulletinPaie, 'mode' => 'create', 'employes' => $employes]);
    }

    public function store(StorePaieRequest $request)
    {
        $employe = Employe::findOrFail($request->employe_id);
        $calcul  = (new PaieCalculateur())->calculer(
            (float) $request->salaire_base,
            (float) $request->input('total_primes', 0),
            $employe,
            (int) $request->periode_mois,
            (int) $request->periode_annee
        );

        $bulletin = BulletinPaie::create(array_merge($calcul, [
            'employe_id'        => $employe->id,
            'periode_mois'      => $request->periode_mois,
            'periode_annee'     => $request->periode_annee,
            'avances_deduites'  => (float) $request->input('avances_deduites', 0),
            'jours_travailles'  => 26,
            'heures_travailles' => 191,
            'statut'            => 'brouillon',
            'created_by'        => auth()->id(),
        ]));

        AuditLog::log('Paie', 'creation', "Bulletin {$bulletin->periode_libelle} créé pour {$bulletin->employe->nom_complet}", $bulletin);

        return redirect()->route('paie.show', $bulletin)->with('success', 'Bulletin créé.');
    }

    public function show(BulletinPaie $paie)
    {
        $paie->load('employe');
        return view('paie.show', ['bulletin' => $paie]);
    }

    public function edit(BulletinPaie $paie)
    {
        $employes = Employe::actifs()->orderBy('nom')->get();
        return view('paie.form', ['bulletin' => $paie, 'mode' => 'edit', 'employes' => $employes]);
    }

    public function update(UpdatePaieRequest $request, BulletinPaie $paie)
    {
        // Recalcul complet via le service centralisé
        $calcul = (new PaieCalculateur())->calculer(
            (float) $request->salaire_base,
            (float) $request->input('total_primes', 0),
            $paie->employe,
            (int) $paie->periode_mois,
            (int) $paie->periode_annee
        );

        $paie->update(array_merge($calcul, [
            'avances_deduites' => (float) $request->input('avances_deduites', 0),
            'statut'           => $request->statut,
            'date_paiement'    => $request->date_paiement,
        ]));

        AuditLog::log('Paie', 'modification', "Bulletin {$paie->periode_libelle} modifié pour {$paie->employe->nom_complet}", $paie);

        return redirect()->route('paie.show', $paie)->with('success', 'Bulletin mis à jour.');
    }

    public function destroy(BulletinPaie $paie)
    {
        $paie->delete();
        return redirect()->route('paie.index')->with('success', 'Bulletin supprimé.');
    }

    /**
     * Fiche de paie imprimable — format marocain complet.
     * Recalcule à la volée pour afficher toutes les colonnes détaillées.
     */
    public function print(BulletinPaie $paie)
    {
        $paie->load('employe.fichePoste', 'employe.unite', 'employe.contratActif');
        $societe = ParametrageRh::getGroupe('societe');

        $calcul = (new PaieCalculateur())->calculer(
            (float) $paie->salaire_base,
            (float) $paie->total_primes,
            $paie->employe,
            (int) $paie->periode_mois,
            (int) $paie->periode_annee
        );

        return view('paie.print', compact('paie', 'societe', 'calcul'));
    }

    /**
     * Livre de paie mensuel — registre légal Art. 371 C.T. marocain.
     */
    public function livre(Request $request)
    {
        // Défaut : dernière période qui a des bulletins
        $dernier = BulletinPaie::selectRaw('periode_mois, periode_annee')
            ->orderByDesc('periode_annee')->orderByDesc('periode_mois')
            ->first();

        $mois  = (int) $request->get('mois',  $dernier?->periode_mois  ?? now()->month);
        $annee = (int) $request->get('annee', $dernier?->periode_annee ?? now()->year);

        $bulletins = BulletinPaie::with('employe.fichePoste')
            ->where('periode_mois', $mois)
            ->where('periode_annee', $annee)
            ->join('employes', 'bulletins_paie.employe_id', '=', 'employes.id')
            ->orderBy('employes.nom')
            ->select('bulletins_paie.*')
            ->get();

        $societe  = ParametrageRh::getGroupe('societe');
        $moisNoms = ["","Janvier","Février","Mars","Avril","Mai","Juin",
                     "Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
        $moisNom  = $moisNoms[$mois] ?? $mois;

        return view('paie.livre', compact('bulletins', 'mois', 'annee', 'societe', 'moisNom', 'moisNoms'));
    }

    /**
     * Bordereau de déclaration CNSS/AMO mensuel.
     */
    public function bordereau(Request $request)
    {
        $dernier = BulletinPaie::selectRaw('periode_mois, periode_annee')
            ->orderByDesc('periode_annee')->orderByDesc('periode_mois')
            ->first();

        $mois  = (int) $request->get('mois',  $dernier?->periode_mois  ?? now()->month);
        $annee = (int) $request->get('annee', $dernier?->periode_annee ?? now()->year);

        $bulletins = BulletinPaie::with('employe')
            ->where('periode_mois', $mois)
            ->where('periode_annee', $annee)
            ->join('employes', 'bulletins_paie.employe_id', '=', 'employes.id')
            ->orderBy('employes.nom')
            ->select('bulletins_paie.*')
            ->get();

        $societe      = ParametrageRh::getGroupe('societe');
        $cnssPlafond  = ParametrageRh::getFloat('paie.cnss_plafond', 6000);
        $moisNoms     = ["","Janvier","Février","Mars","Avril","Mai","Juin",
                         "Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
        $moisNom      = $moisNoms[$mois] ?? $mois;

        return view('paie.bordereau', compact('bulletins','mois','annee','societe','cnssPlafond','moisNom','moisNoms'));
    }

    /**
     * Déclaration Annuelle des Salaires (DAS) — DGI.
     */
    public function das(Request $request)
    {
        $annee = (int) $request->get('annee', now()->year);

        $lignes = BulletinPaie::with('employe')
            ->where('periode_annee', $annee)
            ->whereIn('statut', ['valide', 'paye'])
            ->get()
            ->groupBy('employe_id')
            ->map(function ($bulletins) {
                $employe = $bulletins->first()->employe;
                return (object) [
                    'employe'             => $employe,
                    'nb_mois'             => $bulletins->count(),
                    'brut_annuel'         => $bulletins->sum(fn($b) => $b->brut),
                    'prime_anc_annuelle'  => $bulletins->sum('prime_anciennete'),
                    'ir_annuel'           => $bulletins->sum('ir_mensuel'),
                    'net_imposable_annuel'=> $bulletins->sum('net_imposable'),
                    'net_annuel'          => $bulletins->sum('net_a_payer'),
                ];
            })
            ->sortBy(fn($l) => $l->employe->nom);

        $societe   = ParametrageRh::getGroupe('societe');
        $annees    = BulletinPaie::selectRaw('DISTINCT periode_annee')
                        ->orderByDesc('periode_annee')->pluck('periode_annee');

        return view('paie.das', compact('lignes','annee','societe','annees'));
    }

    /**
     * Retourne les taux actuels en JSON (utilisé par le formulaire JS).
     */
    public function taux()
    {
        return response()->json([
            'cnss_taux_salarie'          => ParametrageRh::getFloat('paie.cnss_taux_salarie', 4.48),
            'cnss_plafond'               => ParametrageRh::getFloat('paie.cnss_plafond', 6000),
            'amo_taux_salarie'           => ParametrageRh::getFloat('paie.amo_taux_salarie', 2.26),
            'cimr_actif'                 => (int) ParametrageRh::get('paie.cimr_actif', '0'),
            'cimr_taux_salarie'          => ParametrageRh::getFloat('paie.cimr_taux_salarie', 3.00),
            'ir_abattement_taux'         => ParametrageRh::getFloat('paie.ir_abattement_taux', 20),
            'ir_abattement_min'          => ParametrageRh::getFloat('paie.ir_abattement_min', 2500),
            'ir_abattement_max'          => ParametrageRh::getFloat('paie.ir_abattement_max', 30000),
            'ir_deduction_charge_annuel' => ParametrageRh::getFloat('paie.ir_deduction_charge_annuel', 360),
            'ir_bareme'                  => ParametrageRh::getJson('paie.ir_bareme', []),
        ]);
    }
}
