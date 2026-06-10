<x-app-layout>
<x-rh.page-header
    title="Paramètres paie"
    :breadcrumbs="['Paramétrage' => route('parametrage.index'), 'Paie' => null]">
</x-rh.page-header>

@if(session('success'))
<div style="margin:12px 24px 0;">
    <x-rh.alert type="success" :message="session('success')" />
</div>
@endif

<form method="POST" action="{{ route('parametrage.paie.update') }}" style="padding:20px 24px;display:flex;flex-direction:column;gap:20px;">
@csrf

{{-- ═══ SOCIÉTÉ ═══ --}}
<div class="p-section">
    <div class="p-section-header">
        <div class="p-section-icon">🏢</div>
        <div>
            <div class="p-section-title">Informations société</div>
            <div class="p-section-desc">Affichées en en-tête sur chaque bulletin de paie</div>
        </div>
    </div>
    <div class="p-grid">
        @foreach($societe as $param)
        <div class="p-field {{ in_array($param->cle, ['societe.adresse','societe.nom']) ? 'p-field-full' : '' }}">
            <label class="p-label">
                {{ $param->libelle }}
                @if($param->description)
                <span class="p-hint" title="{{ $param->description }}">?</span>
                @endif
            </label>
            <input type="text"
                   name="params[{{ $param->id }}]"
                   class="form-control"
                   value="{{ old('params.'.$param->id, $param->valeur) }}"
                   placeholder="{{ $param->description ?? '' }}">
        </div>
        @endforeach
    </div>
</div>

{{-- ═══ TAUX COTISATIONS ═══ --}}
<div class="p-section">
    <div class="p-section-header">
        <div class="p-section-icon">📊</div>
        <div>
            <div class="p-section-title">Taux de cotisations sociales</div>
            <div class="p-section-desc">CNSS, AMO, CIMR — à mettre à jour selon la loi de finances en vigueur</div>
        </div>
    </div>
    <div class="p-grid">
        @foreach($cotisations as $param)
        <div class="p-field">
            <label class="p-label">
                {{ $param->libelle }}
                @if($param->description)<span class="p-hint" title="{{ $param->description }}">?</span>@endif
            </label>
            @if($param->cle === 'paie.cimr_actif')
                <select name="params[{{ $param->id }}]" class="form-control">
                    <option value="0" {{ $param->valeur == '0' ? 'selected' : '' }}>Non — CIMR désactivé</option>
                    <option value="1" {{ $param->valeur == '1' ? 'selected' : '' }}>Oui — CIMR activé</option>
                </select>
            @else
                <div style="display:flex;align-items:center;gap:6px;">
                    <input type="number"
                           name="params[{{ $param->id }}]"
                           class="form-control"
                           value="{{ old('params.'.$param->id, $param->valeur) }}"
                           step="0.01" min="0"
                           style="max-width:180px;">
                    @if(str_contains($param->cle, 'taux'))
                        <span class="p-unit">%</span>
                    @elseif(str_contains($param->cle, 'plafond'))
                        <span class="p-unit">DH / mois</span>
                    @endif
                </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ═══ IR ═══ --}}
<div class="p-section">
    <div class="p-section-header">
        <div class="p-section-icon">🧾</div>
        <div>
            <div class="p-section-title">Impôt sur le Revenu (IR)</div>
            <div class="p-section-desc">Abattement professionnel, charges familiales déductibles, mode de paiement par défaut</div>
        </div>
    </div>
    <div class="p-grid">
        @foreach($ir as $param)
        <div class="p-field">
            <label class="p-label">
                {{ $param->libelle }}
                @if($param->description)<span class="p-hint" title="{{ $param->description }}">?</span>@endif
            </label>
            <div style="display:flex;align-items:center;gap:6px;">
                <input
                    type="{{ $param->cle === 'paie.mode_paiement_defaut' ? 'text' : 'number' }}"
                    name="params[{{ $param->id }}]"
                    class="form-control"
                    value="{{ old('params.'.$param->id, $param->valeur) }}"
                    @if($param->cle !== 'paie.mode_paiement_defaut') step="0.01" min="0" style="max-width:180px;" @endif>
                @if(str_contains($param->cle, 'taux'))
                    <span class="p-unit">%</span>
                @elseif(in_array($param->cle, ['paie.ir_abattement_min','paie.ir_abattement_max','paie.ir_deduction_charge_annuel']))
                    <span class="p-unit">DH / an</span>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- ═══ BARÈME IR (lecture seule) ═══ --}}
@if($baremeParam)
@php $tranches = json_decode($baremeParam->valeur, true) ?? []; @endphp
<div class="p-section">
    <div class="p-section-header">
        <div class="p-section-icon">📋</div>
        <div>
            <div class="p-section-title">Barème IR — tranches annuelles</div>
            <div class="p-section-desc">Barème progressif en vigueur. Formule appliquée : <strong>IR annuel = Net imposable annuel × taux − somme à déduire</strong></div>
        </div>
    </div>
    <div style="overflow-x:auto;padding:0 20px 16px;">
        <table class="p-bareme-table">
            <thead>
                <tr>
                    <th>Tranche (DH / an)</th>
                    <th style="text-align:right;">Taux</th>
                    <th style="text-align:right;">Somme à déduire</th>
                </tr>
            </thead>
            <tbody>
                @foreach($tranches as $t)
                <tr>
                    <td>
                        <span style="font-weight:500;">{{ number_format($t['min'], 0, ',', ' ') }}</span>
                        —
                        <span style="font-weight:500;">{{ $t['max'] ? number_format($t['max'], 0, ',', ' ') : '∞' }}</span>
                        DH
                    </td>
                    <td style="text-align:right;font-weight:600;color:var(--accent);">{{ $t['taux'] }} %</td>
                    <td style="text-align:right;">{{ number_format($t['somme_a_deduire'], 0, ',', ' ') }} DH</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:8px;font-size:11px;color:var(--text-muted);">
            Pour modifier le barème IR, relancer :
            <code>php artisan db:seed --class=ParametragePaieSeeder</code>
        </div>
    </div>
    <input type="hidden" name="params[{{ $baremeParam->id }}]" value="{{ $baremeParam->valeur }}">
</div>
@endif

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
.p-section {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.p-section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--border-light);
    background: var(--surface-soft);
}
.p-section-icon { font-size: 22px; line-height: 1; flex-shrink: 0; }
.p-section-title { font-size: 14px; font-weight: 600; color: var(--text-primary); }
.p-section-desc  { font-size: 12px; color: var(--text-muted); margin-top: 2px; }

.p-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    padding: 18px 20px;
}
.p-field { display: flex; flex-direction: column; gap: 5px; }
.p-field-full { grid-column: 1 / -1; }

.p-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 5px;
}
.p-hint {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 15px; height: 15px;
    border-radius: 50%;
    background: var(--border);
    font-size: 9px;
    color: var(--text-muted);
    cursor: help;
    flex-shrink: 0;
}
.p-unit {
    font-size: 12px;
    color: var(--text-muted);
    white-space: nowrap;
}

.form-control {
    height: 34px;
    padding: 0 10px;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    font-size: 13px;
    color: var(--text-primary);
    background: var(--surface);
    font-family: inherit;
    width: 100%;
    transition: border-color .15s, box-shadow .15s;
}
.form-control:focus {
    outline: none;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--accent-soft);
}
select.form-control { cursor: pointer; }

.p-bareme-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
    margin-top: 2px;
}
.p-bareme-table th {
    background: var(--surface-soft);
    padding: 7px 12px;
    text-align: left;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: .4px;
    border-bottom: 1px solid var(--border);
}
.p-bareme-table td {
    padding: 8px 12px;
    border-bottom: 1px solid var(--border-light);
    color: var(--text-primary);
}
.p-bareme-table tr:last-child td { border-bottom: none; }
.p-bareme-table tr:hover td { background: var(--surface-soft); }

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 18px;
    background: var(--accent);
    color: #fff;
    border: none;
    border-radius: var(--radius-sm);
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    font-family: inherit;
}
.btn-save:hover { opacity: .9; }

code {
    background: var(--surface-soft);
    border: 1px solid var(--border-light);
    border-radius: 3px;
    padding: 1px 5px;
    font-size: 10.5px;
    color: var(--accent);
    font-family: monospace;
}
</style>
</x-app-layout>
