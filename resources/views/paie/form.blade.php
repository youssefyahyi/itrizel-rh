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
        <input type="text" class="form-control" value="{{ $bulletin->employe?->nom_complet ?? '—' }}" disabled>
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
(function () {
    const fmt = v => new Intl.NumberFormat('fr-FR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(v) + ' DH';
    let timer = null;

    async function simulate() {
        const base   = parseFloat(document.querySelector('[name=salaire_base]')?.value  || 0);
        const primes = parseFloat(document.querySelector('[name=total_primes]')?.value  || 0);
        if (base <= 0) { document.getElementById('sim-card').style.display = 'none'; return; }

        const body = new URLSearchParams({
            salaire_base:      base,
            total_primes:      primes,
            avances_deduites:  parseFloat(document.querySelector('[name=avances_deduites]')?.value || 0),
            employe_id:        document.querySelector('[name=employe_id]')?.value || '',
            periode_mois:      document.querySelector('[name=periode_mois]')?.value  || '{{ now()->month }}',
            periode_annee:     document.querySelector('[name=periode_annee]')?.value || '{{ now()->year }}',
            _token:            document.querySelector('meta[name=csrf-token]')?.content || '{{ csrf_token() }}',
        });

        const r = await fetch('{{ route("paie.simuler") }}', { method: 'POST', body });
        if (!r.ok) return;
        const d = await r.json();

        document.getElementById('s-cnss').textContent = fmt(d.cnss_salarie);
        document.getElementById('s-amo').textContent  = fmt(d.amo_salarie);
        document.getElementById('s-cimr').textContent = fmt(d.cimr_salarie);
        document.getElementById('s-ir').textContent   = fmt(d.ir_mensuel);
        document.getElementById('s-anc').textContent  = fmt(d.prime_anciennete);
        document.getElementById('s-ret').textContent  = fmt(d.total_retenues);
        document.getElementById('s-net').textContent  = fmt(d.net_a_payer);
        document.getElementById('sim-card').style.display = '';
    }

    function debounce() { clearTimeout(timer); timer = setTimeout(simulate, 400); }

    ['salaire_base','total_primes','avances_deduites'].forEach(name => {
        document.querySelector('[name=' + name + ']')?.addEventListener('input', debounce);
    });
    document.querySelector('[name=employe_id]')?.addEventListener('change', simulate);
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
