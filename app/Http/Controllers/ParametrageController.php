<?php
namespace App\Http\Controllers;
use App\Models\JourFerie;
use App\Models\ParametrageRh;
use Illuminate\Http\Request;

class ParametrageController extends Controller
{
    public function index() { return view('parametrage.index'); }

    /** Page paramétrage paie (société + taux). */
    public function paie()
    {
        $societe   = ParametrageRh::where('groupe', 'societe')->orderBy('id')->get();
        $clesCotis = ['paie.cnss_taux_salarie','paie.cnss_plafond','paie.cnss_taux_patron',
                      'paie.amo_taux_salarie','paie.amo_taux_patron',
                      'paie.cimr_actif','paie.cimr_taux_salarie','paie.cimr_taux_patron'];
        $clesIr    = ['paie.ir_abattement_taux','paie.ir_abattement_min','paie.ir_abattement_max',
                      'paie.ir_deduction_charge_annuel','paie.ir_deduction_max_personnes',
                      'paie.mode_paiement_defaut'];
        $cotisations = ParametrageRh::whereIn('cle', $clesCotis)->orderBy('id')->get();
        $ir          = ParametrageRh::whereIn('cle', $clesIr)->orderBy('id')->get();
        $baremeParam = ParametrageRh::where('cle', 'paie.ir_bareme')->first();
        return view('parametrage.paie', compact('societe', 'cotisations', 'ir', 'baremeParam'));
    }

    /** Page paramétrage RH (congés & temps de travail). */
    public function rh()
    {
        $params = ParametrageRh::whereIn('cle', [
            'rh.conges_jours_par_mois',
            'rh.heures_semaine_base',
            'rh.quotites_disponibles',
            'rh.calendrier_conges',
        ])->get()->keyBy('cle');

        $jours_feries = JourFerie::parAnnee();

        return view('parametrage.rh', compact('params', 'jours_feries'));
    }

    /** Enregistre les paramètres RH congés & temps de travail. */
    public function updateRh(Request $request)
    {
        foreach (['rh.conges_jours_par_mois', 'rh.heures_semaine_base'] as $cle) {
            $val = $request->input($cle);
            if ($val !== null) ParametrageRh::where('cle', $cle)->update(['valeur' => $val]);
        }

        // Calendrier congés
        $calendrier = $request->input('rh.calendrier_conges', 'ouvrable');
        ParametrageRh::set('rh.calendrier_conges', in_array($calendrier, ['ouvrable', 'calendaire']) ? $calendrier : 'ouvrable');

        // Quotités — cases à cocher → JSON array
        $quotites = array_map('intval', (array) $request->input('quotites', []));
        if (!in_array(100, $quotites)) $quotites[] = 100;
        sort($quotites);
        ParametrageRh::where('cle', 'rh.quotites_disponibles')
            ->update(['valeur' => json_encode(array_values(array_unique($quotites)))]);

        ParametrageRh::clearCache();

        return redirect()->route('parametrage.rh')->with('success', 'Paramètres RH enregistrés.');
    }

    /** Ajoute un jour férié. */
    public function storeJourFerie(Request $request)
    {
        $request->validate([
            'date'    => 'required|date|unique:jours_feries,date',
            'libelle' => 'required|string|max:100',
            'type'    => 'required|in:fixe,variable',
        ], [
            'date.unique' => 'Cette date est déjà enregistrée.',
        ]);

        JourFerie::create([
            'date'    => $request->date,
            'libelle' => $request->libelle,
            'annee'   => (int) date('Y', strtotime($request->date)),
            'type'    => $request->type,
        ]);

        return redirect()->route('parametrage.rh')->with('success', 'Jour férié ajouté.');
    }

    /** Supprime un jour férié. */
    public function destroyJourFerie(JourFerie $jourFerie)
    {
        $jourFerie->delete();
        return redirect()->route('parametrage.rh')->with('success', 'Jour férié supprimé.');
    }

    /** Enregistre les modifications des paramètres paie. */
    public function updatePaie(Request $request)
    {
        foreach ($request->input('params', []) as $id => $valeur) {
            ParametrageRh::where('id', $id)->update(['valeur' => $valeur]);
        }

        // Invalider le cache après chaque écriture
        ParametrageRh::clearCache();

        return redirect()->route('parametrage.paie')
            ->with('success', 'Paramètres enregistrés avec succès.');
    }
}