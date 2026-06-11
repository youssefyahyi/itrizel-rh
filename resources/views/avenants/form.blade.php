<x-app-layout>
<x-rh.page-header
    title="Nouvel avenant"
    :breadcrumbs="['Contrats' => route('contrats.index'), $contrat->reference => route('contrats.show', $contrat), 'Avenant' => null]">
    <a href="{{ route('contrats.show', $contrat) }}" class="tb-btn">← Retour</a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if($errors->any())
<x-rh.alert type="danger" :message="'Veuillez corriger les '.$errors->count().' erreur(s).'" />
@endif

{{-- Info contrat --}}
<div style="background:var(--surface-soft);border:1px solid var(--border-light);border-radius:var(--radius);padding:14px 18px;font-size:13px;">
    <div style="font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Contrat concerné</div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;align-items:center;">
        <span class="mono" style="font-size:13px;font-weight:700;">{{ $contrat->reference }}</span>
        <span>{{ $contrat->employe->nom_complet }}</span>
        <span style="color:var(--text-secondary);">{{ $contrat->type }} — {{ number_format($contrat->salaire_base, 0, ',', ' ') }} DH/mois</span>
    </div>
</div>

<form method="POST" action="{{ route('avenants.store', $contrat) }}"
      style="display:flex;flex-direction:column;gap:16px;">
@csrf

<x-rh.form-section title="Détails de l'avenant">
    <x-rh.form-field label="Nature de la modification" name="nature" :required="true">
        <select name="nature" class="form-control @error('nature') is-invalid @enderror" id="sel-nature" onchange="toggleChamps()">
            <option value="">— Sélectionner —</option>
            @foreach(App\Models\Avenant::NATURES as $val => $libelle)
            <option value="{{ $val }}" {{ old('nature') === $val ? 'selected' : '' }}>{{ $libelle }}</option>
            @endforeach
        </select>
    </x-rh.form-field>

    <x-rh.form-field label="Date d'effet" name="date_effet" :required="true">
        <input type="date" name="date_effet" class="form-control @error('date_effet') is-invalid @enderror"
               value="{{ old('date_effet', now()->format('Y-m-d')) }}">
    </x-rh.form-field>

    <x-rh.form-field label="Ancienne valeur" name="ancienne_valeur" id="grp-ancienne">
        <input type="text" name="ancienne_valeur" class="form-control @error('ancienne_valeur') is-invalid @enderror"
               value="{{ old('ancienne_valeur') }}" id="inp-ancienne" placeholder="Valeur actuelle...">
        <div class="form-hint" id="hint-ancienne"></div>
    </x-rh.form-field>

    <x-rh.form-field label="Nouvelle valeur" name="nouvelle_valeur" id="grp-nouvelle">
        <input type="text" name="nouvelle_valeur" class="form-control @error('nouvelle_valeur') is-invalid @enderror"
               value="{{ old('nouvelle_valeur') }}" id="inp-nouvelle" placeholder="Nouvelle valeur...">
        <div class="form-hint" id="hint-nouvelle"></div>
    </x-rh.form-field>
</x-rh.form-section>

<x-rh.form-section title="Motif">
    <x-rh.form-field label="Motif / Justification" name="motif" :full="true">
        <textarea name="motif" class="form-control" rows="3"
                  style="height:auto;padding:8px 10px;"
                  placeholder="Décrivez les raisons de cet avenant...">{{ old('motif') }}</textarea>
    </x-rh.form-field>
</x-rh.form-section>

{{-- Sélection des clauses --}}
@if($clauses->count())
<div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);">
    <div style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border-light);">
        Clauses à inclure dans le PDF
    </div>
    <div style="display:flex;flex-direction:column;gap:8px;">
        @foreach($clauses as $cl)
        <label style="display:flex;align-items:flex-start;gap:10px;cursor:{{ $cl->obligatoire ? 'default' : 'pointer' }};font-size:13px;">
            <input type="checkbox" name="clauses[]" value="{{ $cl->id }}"
                   {{ $cl->obligatoire ? 'checked disabled' : (old('clauses') && in_array($cl->id, old('clauses')) ? 'checked' : '') }}
                   style="margin-top:2px;width:14px;height:14px;accent-color:var(--accent);">
            <div>
                <span style="font-weight:500;">{{ $cl->titre }}</span>
                @if($cl->obligatoire)<span style="font-size:11px;color:var(--text-muted);margin-left:6px;">(obligatoire)</span>@endif
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ Str::limit(strip_tags($cl->contenu), 80) }}</div>
            </div>
        </label>
        @endforeach
    </div>
</div>
@endif

<div style="display:flex;justify-content:flex-end;gap:10px;padding-bottom:8px;">
    <a href="{{ route('contrats.show', $contrat) }}" class="tb-btn">Annuler</a>
    <button type="submit" class="btn-new">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        Créer l'avenant
    </button>
</div>
</form>
</div>

<style>
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;transition:border-color .15s,box-shadow .15s;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-control.is-invalid{border-color:var(--danger);}
.form-hint{font-size:11px;color:var(--text-muted);margin-top:3px;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;}
</style>

<script>
const SALAIRE_ACTUEL = '{{ $contrat->salaire_base }}';
const DATE_FIN_ACTUELLE = '{{ $contrat->date_fin?->format("Y-m-d") ?? "" }}';

function toggleChamps() {
    const nature   = document.getElementById('sel-nature').value;
    const ancienne = document.getElementById('inp-ancienne');
    const nouvelle = document.getElementById('inp-nouvelle');
    const hintA    = document.getElementById('hint-ancienne');
    const hintN    = document.getElementById('hint-nouvelle');

    if (nature === 'salaire') {
        ancienne.value       = SALAIRE_ACTUEL;
        ancienne.placeholder = 'Salaire actuel (DH)';
        nouvelle.placeholder = 'Nouveau salaire (DH)';
        hintA.textContent    = 'Valeur actuelle du salaire de base';
        hintN.textContent    = 'Nouveau montant à appliquer dès la date d\'effet';
    } else if (nature === 'date_fin') {
        ancienne.value       = DATE_FIN_ACTUELLE;
        ancienne.placeholder = 'Date de fin actuelle';
        nouvelle.placeholder = 'Nouvelle date de fin (YYYY-MM-DD)';
        hintA.textContent    = 'Date de fin actuelle du contrat';
        hintN.textContent    = 'Nouvelle date de fin après prolongation';
    } else {
        ancienne.value       = '';
        ancienne.placeholder = 'Valeur actuelle...';
        nouvelle.placeholder = 'Nouvelle valeur...';
        hintA.textContent    = '';
        hintN.textContent    = '';
    }
}
document.addEventListener('DOMContentLoaded', toggleChamps);
</script>
</x-app-layout>
