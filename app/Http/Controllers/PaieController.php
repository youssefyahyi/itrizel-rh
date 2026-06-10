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
