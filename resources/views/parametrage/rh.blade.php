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
    $pJours      = $params['rh.conges_jours_par_mois']  ?? null;
    $pHeures     = $params['rh.heures_semaine_base']    ?? null;
    $pQuotites   = $params['rh.quotites_disponibles']   ?? null;
    $pCalendrier = $params['rh.calendrier_conges']      ?? null;
    $calendrierVal   = $pCalendrier ? $pCalendrier->valeur : 'ouvrable';
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

{{-- ═══ CALENDRIER CONGÉS ═══ --}}
<div class="p-section">
    <div class="p-section-header">
        <div class="p-section-icon">📅</div>
        <div>
            <div class="p-section-title">Calendrier de décompte</div>
            <div class="p-section-desc">Définit comment sont comptés les jours de congé (Code du Travail marocain, art. 231)</div>
        </div>
    </div>
    <div class="p-grid" style="grid-template-columns:1fr;">
        <div class="p-field">
            <label class="p-label">Mode de décompte</label>
            <div style="display:flex;gap:12px;margin-top:4px;flex-wrap:wrap;">
                <label class="cal-option {{ $calendrierVal === 'ouvrable' ? 'active' : '' }}">
                    <input type="radio" name="rh.calendrier_conges" value="ouvrable" {{ $calendrierVal === 'ouvrable' ? 'checked' : '' }} style="display:none;">
                    <div class="cal-icon">Lun → Ven</div>
                    <div class="cal-label">Jours ouvrables</div>
                    <div class="cal-desc">Sam et dim exclus, + jours fériés</div>
                </label>
                <label class="cal-option {{ $calendrierVal === 'calendaire' ? 'active' : '' }}">
                    <input type="radio" name="rh.calendrier_conges" value="calendaire" {{ $calendrierVal === 'calendaire' ? 'checked' : '' }} style="display:none;">
                    <div class="cal-icon">7 j / 7</div>
                    <div class="cal-label">Jours calendaires</div>
                    <div class="cal-desc">Tous les jours, sauf jours fériés</div>
                </label>
            </div>
        </div>
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

{{-- ═══ JOURS FÉRIÉS (hors formulaire principal) ═══ --}}
<div class="p-section" style="margin:0 24px 24px;">
    <div class="p-section-header" style="justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:14px;">
            <div class="p-section-icon">🇲🇦</div>
            <div>
                <div class="p-section-title">Jours fériés</div>
                <div class="p-section-desc">Fêtes nationales exclues du décompte des congés</div>
            </div>
        </div>
        <button type="button" class="btn-add-ferie" onclick="gcmToggle('frm-add-ferie',event)">+ Ajouter</button>
    </div>

    {{-- Formulaire d'ajout --}}
    <div id="frm-add-ferie" style="display:none;padding:16px 20px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);">
        <form method="POST" action="{{ route('parametrage.jours-feries.store') }}" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
            @csrf
            @if($errors->has('date') || $errors->has('libelle'))
            <div style="width:100%;font-size:12px;color:var(--danger);">{{ $errors->first('date') ?: $errors->first('libelle') }}</div>
            @endif
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label class="p-label">Date</label>
                <input type="date" name="date" class="form-control" style="width:160px;" required>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;flex:1;min-width:200px;">
                <label class="p-label">Libellé</label>
                <input type="text" name="libelle" class="form-control" placeholder="ex: Aïd Al Fitr" required>
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label class="p-label">Type</label>
                <select name="type" class="form-control" style="width:140px;">
                    <option value="fixe">Fixe</option>
                    <option value="variable">Variable</option>
                </select>
            </div>
            <button type="submit" class="btn-save" style="padding:7px 14px;">Enregistrer</button>
        </form>
    </div>

    {{-- Liste par année --}}
    @forelse($jours_feries as $annee => $feries)
    <div style="padding:12px 20px;border-bottom:1px solid var(--border-light);">
        <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">{{ $annee }}</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:6px;">
            @foreach($feries as $ferie)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 10px;background:var(--surface-soft);border:1px solid var(--border-light);border-radius:var(--radius-sm);">
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="font-size:12px;color:var(--text-muted);min-width:72px;">{{ $ferie->date->format('d/m/Y') }}</span>
                    <span style="font-size:13px;color:var(--text-primary);">{{ $ferie->libelle }}</span>
                    <span style="font-size:10px;padding:2px 6px;border-radius:10px;background:{{ $ferie->type === 'fixe' ? 'var(--accent-light)' : 'var(--warning-light)' }};color:{{ $ferie->type === 'fixe' ? 'var(--accent)' : 'var(--warning)' }};">{{ $ferie->type }}</span>
                </div>
                <form method="POST" action="{{ route('parametrage.jours-feries.destroy', $ferie) }}" onsubmit="return confirm('Supprimer ce jour férié ?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:14px;padding:2px 4px;" title="Supprimer">×</button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @empty
    <div style="padding:24px;text-align:center;color:var(--text-muted);font-size:13px;">Aucun jour férié enregistré.</div>
    @endforelse
</div>

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

.cal-option { display:flex;flex-direction:column;gap:3px;padding:12px 16px;min-width:180px;border:2px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:all .15s;background:var(--surface); }
.cal-option:hover { border-color:var(--accent); }
.cal-option.active { border-color:var(--accent);background:var(--accent-light); }
.cal-icon { font-size:12px;font-weight:700;color:var(--accent); }
.cal-label { font-size:13px;font-weight:600;color:var(--text-primary); }
.cal-desc { font-size:11px;color:var(--text-muted); }
.btn-add-ferie { display:inline-flex;align-items:center;padding:6px 12px;background:var(--surface);color:var(--text-secondary);border:1px solid var(--border);border-radius:var(--radius-sm);font-size:12px;font-weight:500;cursor:pointer;font-family:var(--font); }
.btn-add-ferie:hover { border-color:var(--accent);color:var(--accent); }
</style>

<script>
document.querySelectorAll('.cal-option').forEach(function(opt) {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.cal-option').forEach(o => o.classList.remove('active'));
        opt.classList.add('active');
        opt.querySelector('input[type=radio]').checked = true;
    });
});

document.querySelectorAll('.quotite-chip:not(.locked)').forEach(function(chip) {
    chip.addEventListener('click', function() {
        var cb = chip.querySelector('input[type=checkbox]');
        cb.checked = !cb.checked;
        chip.classList.toggle('active', cb.checked);
    });
});
</script>

</x-app-layout>
