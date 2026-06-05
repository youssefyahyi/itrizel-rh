<?php
namespace App\Http\Controllers;
use App\Models\BulletinPaie;
use App\Models\Employe;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PaieController extends Controller
{
    public function index(Request $request)
    {
        $query = BulletinPaie::with('employe')->latest();
        if ($request->filled('q'))         $query->whereHas('employe', fn($e) => $e->where('nom','like',"%{$request->q}%")->orWhere('prenom','like',"%{$request->q}%"));
        if ($request->filled('statut'))    $query->where('statut', $request->statut);
        if ($request->filled('mois'))      $query->where('periode_mois', $request->mois);
        if ($request->filled('annee'))     $query->where('periode_annee', $request->annee);
        if ($request->filled('employe_id'))$query->where('employe_id', $request->employe_id);
        $bulletins = $query->paginate(20)->withQueryString();
        $stats = [
            'brouillons' => BulletinPaie::where('statut','brouillon')->count(),
            'valides'    => BulletinPaie::where('statut','valide')->count(),
            'payes'      => BulletinPaie::where('statut','paye')->count(),
            'masse'      => (float) BulletinPaie::where('statut','paye')->where('periode_mois',now()->month)->where('periode_annee',now()->year)->sum('net_a_payer'),
        ];
        return view('paie.index', compact('bulletins','stats'));
    }
    public function create()
    {
        $employes = Employe::where('statut','actif')->orderBy('nom')->get();
        return view('paie.form',['bulletin'=>new BulletinPaie,'mode'=>'create','employes'=>$employes]);
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'employe_id'      => 'required|exists:employes,id',
            'periode_mois'    => 'required|integer|min:1|max:12',
            'periode_annee'   => 'required|integer|min:2020|max:2030',
            'salaire_base'    => 'required|numeric|min:0',
            'total_primes'    => 'nullable|numeric|min:0',
            'ir_mensuel'      => 'nullable|numeric|min:0',
            'amo_salarie'     => 'nullable|numeric|min:0',
            'cnss_salarie'    => 'nullable|numeric|min:0',
            'cimr_salarie'    => 'nullable|numeric|min:0',
            'avances_deduites'=> 'nullable|numeric|min:0',
            'net_a_payer'     => 'required|numeric|min:0',
        ]);
        $data['total_primes']    = $data['total_primes']    ?? 0;
        $data['ir_mensuel']      = $data['ir_mensuel']      ?? 0;
        $data['amo_salarie']     = $data['amo_salarie']     ?? 0;
        $data['cnss_salarie']    = $data['cnss_salarie']    ?? 0;
        $data['cimr_salarie']    = $data['cimr_salarie']    ?? 0;
        $data['avances_deduites']= $data['avances_deduites']?? 0;
        $data['total_retenues']  = $data['ir_mensuel'] + $data['amo_salarie'] + $data['cnss_salarie'] + $data['cimr_salarie'] + $data['avances_deduites'];
        $data['net_imposable']   = $data['salaire_base'] + $data['total_primes'] - $data['amo_salarie'] - $data['cnss_salarie'];
        $data['statut']          = 'brouillon';
        $data['created_by']      = auth()->id();
        $bulletin = BulletinPaie::create($data);
        AuditLog::log('Paie','creation',"Bulletin {$bulletin->periode_libelle} cree pour {$bulletin->employe->nom_complet}",$bulletin);
        return redirect()->route('paie.show',$bulletin)->with('success','Bulletin cree.');
    }
    public function show(BulletinPaie $paie) { $paie->load('employe'); return view('paie.show',['bulletin'=>$paie]); }
    public function edit(BulletinPaie $paie)
    {
        $employes = Employe::where('statut','actif')->orderBy('nom')->get();
        return view('paie.form',['bulletin'=>$paie,'mode'=>'edit','employes'=>$employes]);
    }
    public function update(Request $request, BulletinPaie $paie)
    {
        $data = $request->validate([
            'salaire_base'    => 'required|numeric|min:0',
            'total_primes'    => 'nullable|numeric|min:0',
            'ir_mensuel'      => 'nullable|numeric|min:0',
            'amo_salarie'     => 'nullable|numeric|min:0',
            'cnss_salarie'    => 'nullable|numeric|min:0',
            'cimr_salarie'    => 'nullable|numeric|min:0',
            'avances_deduites'=> 'nullable|numeric|min:0',
            'net_a_payer'     => 'required|numeric|min:0',
            'statut'          => 'required|in:brouillon,valide,paye',
            'date_paiement'   => 'nullable|date',
        ]);
        $paie->update($data);
        return redirect()->route('paie.show',$paie)->with('success','Bulletin mis a jour.');
    }
    public function destroy(BulletinPaie $paie)
    {
        $paie->delete();
        return redirect()->route('paie.index')->with('success','Bulletin supprime.');
    }
}