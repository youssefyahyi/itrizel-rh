<x-app-layout>
<x-rh.page-header
    title="Paramètres RH"
    :breadcrumbs="['Paramétrage' => route('parametrage.index'), 'RH' => null]">
</x-rh.page-header>

@if(session('success'))
<div style="margin:12px 24px 0;">
    <x-rh.alert type="success" :message="session('success')" />
</div>
@endif

<form method="POST" action="{{ route('parametrage.rh.update') }}" style="padding:20px 24px;display:flex;flex-direction:column;gap:20px;">
@csrf

@php
    $pJours    = $params['rh.conges_jours_par_mois']  ?? null;
    $pHeures   = $params['rh.heures_semaine_base']    ?? null;
    $pQuotites = $params['rh.quotites_disponibles']   ?? null;
    $quotitesActives = $pQuotites ? (json_decode($pQuotites->valeur, true) ?? [100]) : [100];
    $quotitesStd     = [100, 80, 75, 50, 25];
@endphp

{{-- ═══ CONGÉS ANNUELS ═══ --}}
<div class="p-section">
    <div class="p-section-header">
        <div class="p-section-icon">🏖️</div>
        <div>
            <div class="p-section-title">Congés annuels</div>
            <div class="p-section-desc">Droits légaux selon le Code du Travail marocain (Art. 231)</div>
        </div>
    </div>
    <div class="p-grid">
        @if($pJours)
        <div class="p-field">
            <label class="p-label">
                {{ $pJours->libelle }}
                <span class="p-hint" title="{{ $pJours->description }}">?</span>
            </label>
            <div style="display:flex;align-items:center;gap:6px;">
                <input type="number" name="rh.conges_jours_par_mois"
                       class="form-control" value="{{ old('rh.conges_jours_par_mois', $pJours->valeur) }}"
                       step="0.5" min="0.5" max="5" style="max-width:140px;">
                <span class="p-unit">j / mois</span>
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                Soit {{ old('rh.conges_jours_par_mois', $pJours->valeur) * 12 }} jours / an à temps plein
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ═══ TEMPS DE TRAVAIL ═══ --}}
<div class="p-section">
    <div class="p-section-header">
        <div class="p-section-icon">⏱️</div>
        <div>
            <div class="p-section-title">Temps de travail</div>
            <div class="p-section-desc">Durée légale et quotités de temps partiel proposées aux employés</div>
        </div>
    </div>
    <div class="p-grid">

        {{-- Durée légale --}}
        @if($pHeures)
        <div class="p-field">
            <label class="p-label">
                {{ $pHeures->libelle }}
                <span class="p-hint" title="{{ $pHeures->description }}">?</span>
            </label>
            <div style="display:flex;align-items:center;gap:6px;">
                <input type="number" name="rh.heures_semaine_base"
                       class="form-control" value="{{ old('rh.heures_semaine_base', $pHeures->valeur) }}"
                       step="0.5" min="20" max="60" style="max-width:140px;">
                <span class="p-unit">h / semaine</span>
            </div>
        </div>
        @endif

        {{-- Quotités disponibles --}}
        @if($pQuotites)
        <div class="p-field p-field-full" style="margin-top:4px;">
            <label class="p-label">
                {{ $pQuotites->libelle }}
                <span class="p-hint" title="{{ $pQuotites->description }}">?</span>
            </label>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;">
                @foreach($quotitesStd as $q)
                <label class="quotite-chip {{ in_array($q, $quotitesActives) ? 'active' : '' }} {{ $q === 100 ? 'locked' : '' }}">
                    <input type="checkbox" name="quotites[]" value="{{ $q }}"
                           {{ in_array($q, $quotitesActives) ? 'checked' : '' }}
                           {{ $q === 100 ? 'disabled' : '' }}
                           style="display:none;">
                    {{ $q }} %{{ $q === 100 ? ' ✓' : '' }}
                    @if($q === 100)<input type="hidden" name="quotites[]" value="100">@endif
                </label>
                @endforeach
            </div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:6px;">
                100 % (temps plein) est toujours disponible et ne peut pas être désactivé.
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ═══ Bouton ═══ --}}
<div style="display:flex;justify-content:flex-end;padding-top:4px;">
    <button type="submit" class="btn-save">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Enregistrer les paramètres
    </button>
</div>

</form>

<style>
.p-section { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm); overflow:hidden; }
.p-section-header { display:flex; align-items:center; gap:14px; padding:14px 20px; border-bottom:1px solid var(--border-light); background:var(--surface-soft); }
.p-section-icon { font-size:22px; line-height:1; flex-shrink:0; }
.p-section-title { font-size:14px; font-weight:600; color:var(--text-primary); }
.p-section-desc  { font-size:12px; color:var(--text-muted); margin-top:2px; }
.p-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; padding:18px 20px; }
.p-field { display:flex; flex-direction:column; gap:5px; }
.p-field-full { grid-column:1 / -1; }
.p-label { font-size:12px; font-weight:500; color:var(--text-secondary); display:flex; align-items:center; gap:5px; }
.p-hint { display:inline-flex; align-items:center; justify-content:center; width:15px; height:15px; border-radius:50%; background:var(--border); color:var(--text-muted); font-size:10px; cursor:help; }
.p-unit { font-size:12px; color:var(--text-muted); white-space:nowrap; }

.quotite-chip {
    display:inline-flex; align-items:center; padding:6px 16px;
    border:1.5px solid var(--border); border-radius:20px;
    font-size:13px; font-weight:500; color:var(--text-secondary);
    cursor:pointer; transition:all .15s; user-select:none;
    background:var(--surface);
}
.quotite-chip:hover { border-color:var(--accent); color:var(--accent); }
.quotite-chip.active { border-color:var(--accent); background:var(--accent-light); color:var(--accent); }
.quotite-chip.locked { border-color:var(--success); background:var(--success-light); color:var(--success); cursor:default; }

.btn-save { display:inline-flex; align-items:center; gap:6px; padding:9px 20px; background:var(--accent); color:#fff; border:none; border-radius:var(--radius-sm); font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); }
.btn-save:hover { background:var(--accent-hover); }
</style>

<script>
document.querySelectorAll('.quotite-chip:not(.locked)').forEach(function(chip) {
    chip.addEventListener('click', function() {
        var cb = chip.querySelector('input[type=checkbox]');
        cb.checked = !cb.checked;
        chip.classList.toggle('active', cb.checked);
    });
});
</script>

</x-app-layout>
