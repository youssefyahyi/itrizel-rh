<?php
namespace App\Http\Controllers;
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