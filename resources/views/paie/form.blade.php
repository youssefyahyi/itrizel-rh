<x-app-layout>
<x-rh.page-header
    :title="$mode === 'create' ? 'Nouveau bulletin de paie' : 'Modifier bulletin — '.$bulletin->periode_libelle"
    :breadcrumbs="['Paie' => route('paie.index'), $mode === 'create' ? 'Nouveau' : 'Modifier' => null]">
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
@if($errors->any())<x-rh.alert type="danger" :message="'Veuillez corriger les '.$errors->count().' erreur(s).'" />@endif

<form method="POST" action="{{ $mode === 'create' ? route('paie.store') : route('paie.update',$bulletin) }}" style="display:flex;flex-direction:column;gap:16px;">
@csrf
@if($mode === 'edit') @method('PUT') @endif

<x-rh.form-section title="Employé & Période">
    <x-rh.form-field label="Employé" name="employe_id" :required="true">
        @if($mode === 'create')
        <select name="employe_id" class="form-control @error('employe_id') is-invalid @enderror">
            <option value="">— Sélectionner —</option>
            @foreach($employes as $emp)
            <option value="{{ $emp->id }}" {{ old('employe_id') == $emp->id ? 'selected' : '' }}>{{ $emp->nom_complet }}</option>
            @endforeach
        </select>
        @else
        <input type="text" class="form-control" value="{{ $bulletin->employe->nom_complet }}" disabled>
        @endif
    </x-rh.form-field>
    <x-rh.form-field label="Mois" name="periode_mois" :required="true">
        @if($mode === 'create')
        <select name="periode_mois" class="form-control">
            @foreach(['1'=>'Janvier','2'=>'Février','3'=>'Mars','4'=>'Avril','5'=>'Mai','6'=>'Juin','7'=>'Juillet','8'=>'Août','9'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'] as $m => $l)
            <option value="{{ $m }}" {{ old('periode_mois', date('n')) == $m ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        @else
        <input type="text" class="form-control" value="{{ $bulletin->periode_libelle }}" disabled>
        @endif
    </x-rh.form-field>
    @if($mode === 'create')
    <x-rh.form-field label="Année" name="periode_annee" :required="true">
        <input type="number" name="periode_annee" class="form-control" value="{{ old('periode_annee', date('Y')) }}" min="2020" max="2030">
    </x-rh.form-field>
    @endif
</x-rh.form-section>

<x-rh.form-section title="Rémunération">
    <x-rh.form-field label="Salaire de base (DH)" name="salaire_base" :required="true">
        <input type="number" name="salaire_base" class="form-control @error('salaire_base') is-invalid @enderror" value="{{ old('salaire_base', $bulletin->salaire_base) }}" step="0.01" min="0">
    </x-rh.form-field>
    <x-rh.form-field label="Total primes (DH)" name="total_primes">
        <input type="number" name="total_primes" class="form-control" value="{{ old('total_primes', $bulletin->total_primes ?? 0) }}" step="0.01" min="0">
    </x-rh.form-field>
</x-rh.form-section>

<x-rh.form-section title="Retenues & Avances">
    <x-rh.form-field label="Avances déduites (DH)" name="avances_deduites">
        <input type="number" name="avances_deduites" class="form-control" value="{{ old('avances_deduites', $bulletin->avances_deduites ?? 0) }}" step="0.01" min="0">
    </x-rh.form-field>
    @if($mode === 'edit')
    <x-rh.form-field label="Statut" name="statut">
        <select name="statut" class="form-control">
            @foreach(\App\Models\BulletinPaie::STATUTS as $v => $s)
            <option value="{{ $v }}" {{ old('statut', $bulletin->statut) === $v ? 'selected' : '' }}>{{ $s['label'] }}</option>
            @endforeach
        </select>
    </x-rh.form-field>
    <x-rh.form-field label="Date de paiement" name="date_paiement">
        <input type="date" name="date_paiement" class="form-control" value="{{ old('date_paiement', $bulletin->date_paiement?->format('Y-m-d')) }}">
    </x-rh.form-field>
    @endif
</x-rh.form-section>

{{-- Simulation de calcul (lecture seule, mis à jour par JS) --}}
<div class="form-card" id="sim-card" style="display:none;">
    <div class="form-card-header" style="justify-content:space-between;">
        <span class="form-card-title">Simulation de calcul</span>
        <span style="font-size:11px;color:var(--text-muted);">Calculé par PaieCalculateur · non modifiable</span>
    </div>
    <div class="form-card-body">
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
            <div class="sim-cell"><span class="sim-label">CNSS salarié</span><span class="sim-val" id="s-cnss">—</span></div>
            <div class="sim-cell"><span class="sim-label">AMO salarié</span><span class="sim-val" id="s-amo">—</span></div>
            <div class="sim-cell"><span class="sim-label">CIMR salarié</span><span class="sim-val" id="s-cimr">—</span></div>
            <div class="sim-cell"><span class="sim-label">IR mensuel</span><span class="sim-val" id="s-ir">—</span></div>
            <div class="sim-cell"><span class="sim-label">Prime ancienneté</span><span class="sim-val" id="s-anc">—</span></div>
            <div class="sim-cell"><span class="sim-label">Total retenues</span><span class="sim-val" id="s-ret">—</span></div>
            <div class="sim-cell" style="grid-column:span 2;background:var(--accent-light);border-radius:var(--radius-sm);padding:10px;">
                <span class="sim-label" style="color:var(--accent);">Net à payer</span>
                <span class="sim-val" id="s-net" style="font-size:16px;color:var(--accent);">—</span>
            </div>
        </div>
    </div>
</div>

<div style="display:flex;justify-content:flex-end;gap:10px;">
    <a href="{{ route('paie.index') }}" class="tb-btn">Annuler</a>
    <button type="submit" class="btn-new">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $mode === 'create' ? 'Créer le bulletin' : 'Enregistrer' }}
    </button>
</div>
</form>
</div>
<script>
(async function () {
    let taux = null;
    const fmt = v => new Intl.NumberFormat('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(v) + ' DH';

    async function loadTaux() {
        if (taux) return taux;
        const r = await fetch('{{ route("paie.taux") }}');
        taux = await r.json();
        return taux;
    }

    async function simulate() {
        const base   = parseFloat(document.querySelector('[name=salaire_base]')?.value  || 0);
        const primes = parseFloat(document.querySelector('[name=total_primes]')?.value  || 0);
        if (base <= 0) { document.getElementById('sim-card').style.display = 'none'; return; }

        const t = await loadTaux();

        // Prime ancienneté (côté JS : 0 — le vrai calcul est serveur, on l'affiche pour info)
        const anc = 0;
        const brut = base + anc + primes;

        const assietteCnss = Math.min(brut, t.cnss_plafond);
        const cnss = Math.round(assietteCnss * t.cnss_taux_salarie / 100 * 100) / 100;
        const amo  = Math.round(brut * t.amo_taux_salarie / 100 * 100) / 100;
        const cimr = t.cimr_actif ? Math.round(brut * t.cimr_taux_salarie / 100 * 100) / 100 : 0;

        const baseImp  = brut - cnss - amo;
        const abat     = Math.max(t.ir_abattement_min / 12, Math.min(t.ir_abattement_max / 12, baseImp * t.ir_abattement_taux / 100));
        const netImpAn = Math.max(0, baseImp - abat) * 12;
        let irAn = 0;
        for (const tr of t.ir_bareme) { if (netImpAn > tr.min) irAn = netImpAn * tr.taux / 100 - tr.somme_a_deduire; }
        const ir = Math.round(Math.max(0, irAn) / 12 * 100) / 100;

        const totalRet = cnss + amo + cimr + ir;
        const avances  = parseFloat(document.querySelector('[name=avances_deduites]')?.value || 0);
        const net      = Math.round((brut - totalRet - avances) * 100) / 100;

        document.getElementById('s-cnss').textContent = fmt(cnss);
        document.getElementById('s-amo').textContent  = fmt(amo);
        document.getElementById('s-cimr').textContent = fmt(cimr);
        document.getElementById('s-ir').textContent   = fmt(ir);
        document.getElementById('s-anc').textContent  = '— (calculé serveur)';
        document.getElementById('s-ret').textContent  = fmt(totalRet);
        document.getElementById('s-net').textContent  = fmt(net);
        document.getElementById('sim-card').style.display = '';
    }

    ['salaire_base','total_primes','avances_deduites'].forEach(name => {
        document.querySelector('[name=' + name + ']')?.addEventListener('input', simulate);
    });

    simulate();
})();
</script>
<style>
.form-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;}
.form-card-header{display:flex;align-items:center;padding:14px 18px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);}
.form-card-title{font-size:13px;font-weight:600;color:var(--text-primary);}
.form-card-body{padding:18px;}
.form-grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;}
.form-group{display:flex;flex-direction:column;gap:5px;}
.form-group.form-grid-full{grid-column:1/-1;}
.form-label{font-size:12px;font-weight:500;color:var(--text-secondary);}
.form-required{color:var(--danger);margin-left:2px;}
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;transition:border-color .15s,box-shadow .15s;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-control.is-invalid{border-color:var(--danger);}
.form-error{font-size:11px;color:var(--danger);}
select.form-control{cursor:pointer;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;}
.sim-cell{display:flex;flex-direction:column;gap:4px;padding:8px 10px;background:var(--bg);border-radius:var(--radius-sm);}
.sim-label{font-size:11px;color:var(--text-muted);font-weight:500;}
.sim-val{font-size:13px;font-weight:600;color:var(--text-primary);}
</style>
</x-app-layout>
