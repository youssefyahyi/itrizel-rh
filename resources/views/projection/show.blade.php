<x-app-layout>
<x-rh.page-header
    :breadcrumbs="['Projection' => route('projection.index'), $scenario->nom => null]">
    <a href="{{ route('projection.edit', $scenario) }}" class="btn btn-outline btn-sm">Modifier</a>
</x-rh.page-header>

@php
    $labels     = array_column($situation, 'label');
    $coutsBase  = array_map(fn($m) => round($m['global']['cout_employeur']), $situation);
    $coutsScen  = array_map(fn($m) => round($m['global']['cout_employeur']), $projection);
    $brutsBase  = array_map(fn($m) => round($m['global']['brut']), $situation);
    $brutsScen  = array_map(fn($m) => round($m['global']['brut']), $projection);

    $deltaTotal = array_sum($coutsScen) - array_sum($coutsBase);
    $deltaMois1 = ($projection[0]['global']['cout_employeur'] ?? 0) - ($situation[0]['global']['cout_employeur'] ?? 0);
@endphp

<div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if(session('success'))<x-rh.alert type="success" :message="session('success')" />@endif

{{-- En-tête scénario --}}
<div class="info-card" style="padding:16px 20px;">
    <div style="display:flex;align-items:flex-start;gap:20px;flex-wrap:wrap;">
        <div style="flex:1;">
            <div style="font-size:15px;font-weight:700;color:var(--text-primary);margin-bottom:4px;">{{ $scenario->nom }}</div>
            @if($scenario->description)
            <div style="font-size:13px;color:var(--text-secondary);">{{ $scenario->description }}</div>
            @endif
            <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:8px;">
                @foreach($scenario->lignes as $ligne)
                <span style="font-size:11px;background:var(--accent-light);color:var(--accent);border:1px solid rgba(37,99,235,0.2);border-radius:20px;padding:3px 10px;">
                    {{ $ligne->libelle }}
                </span>
                @endforeach
                @if($scenario->lignes->isEmpty())
                <span style="font-size:12px;color:var(--text-muted);">Aucune hypothèse — identique à la situation actuelle</span>
                @endif
            </div>
        </div>
        <div style="display:flex;gap:10px;flex-shrink:0;">
            <div style="text-align:right;">
                <div style="font-size:11px;color:var(--text-secondary);">Impact mensuel (mois 1)</div>
                <div style="font-size:18px;font-weight:700;color:{{ $deltaMois1 >= 0 ? 'var(--danger)' : 'var(--success)' }};">
                    {{ $deltaMois1 >= 0 ? '+' : '' }}{{ number_format($deltaMois1, 0, ',', ' ') }} DH
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:11px;color:var(--text-secondary);">Impact cumulé {{ $horizon }} mois</div>
                <div style="font-size:18px;font-weight:700;color:{{ $deltaTotal >= 0 ? 'var(--danger)' : 'var(--success)' }};">
                    {{ $deltaTotal >= 0 ? '+' : '' }}{{ number_format($deltaTotal, 0, ',', ' ') }} DH
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Graphique comparaison --}}
<div class="info-card" style="padding:20px;">
    <div style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;">
        Comparaison — Coût employeur
    </div>
    <canvas id="chartComparaison" style="max-height:260px;"></canvas>
</div>

{{-- Tableau comparatif --}}
<div class="info-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
        <span style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;">
            Détail mensuel — Coût employeur
        </span>
    </div>
    <div style="overflow-x:auto;">
    <table class="proj-table">
        <thead>
            <tr>
                <th>Mois</th>
                <th class="num">Base (effectif)</th>
                <th class="num">Base — brut</th>
                <th class="num">Base — coût</th>
                <th class="num">Scénario (effectif)</th>
                <th class="num">Scénario — brut</th>
                <th class="num accent">Scénario — coût</th>
                <th class="num">Δ coût</th>
            </tr>
        </thead>
        <tbody>
        @foreach($situation as $idx => $base)
        @php
            $scen  = $projection[$idx] ?? null;
            $delta = $scen ? $scen['global']['cout_employeur'] - $base['global']['cout_employeur'] : 0;
        @endphp
        <tr>
            <td>{{ $base['label'] }}</td>
            <td class="num">{{ $base['global']['effectif'] }}</td>
            <td class="num">{{ number_format($base['global']['brut'], 0, ',', ' ') }}</td>
            <td class="num">{{ number_format($base['global']['cout_employeur'], 0, ',', ' ') }}</td>
            @if($scen)
            <td class="num">{{ $scen['global']['effectif'] }}</td>
            <td class="num">{{ number_format($scen['global']['brut'], 0, ',', ' ') }}</td>
            <td class="num accent fw">{{ number_format($scen['global']['cout_employeur'], 0, ',', ' ') }}</td>
            <td class="num {{ $delta > 0 ? 'red' : ($delta < 0 ? 'green' : '') }}">
                {{ $delta !== 0 ? ($delta > 0 ? '+' : '') . number_format($delta, 0, ',', ' ') : '—' }}
            </td>
            @else
            <td colspan="4" class="num" style="color:var(--text-muted);">—</td>
            @endif
        </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels    = @json($labels);
const coutsBase = @json($coutsBase);
const coutsScen = @json($coutsScen);

new Chart(document.getElementById('chartComparaison').getContext('2d'), {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label: 'Situation actuelle',
                data: coutsBase,
                borderColor: 'rgba(107,114,128,0.8)',
                backgroundColor: 'rgba(107,114,128,0.06)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                borderDash: [5, 3],
            },
            {
                label: '{{ addslashes($scenario->nom) }}',
                data: coutsScen,
                borderColor: 'rgba(37,99,235,0.9)',
                backgroundColor: 'rgba(37,99,235,0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 3,
            },
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 12 }, boxWidth: 16 } },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.dataset.label + ' : ' + ctx.raw.toLocaleString('fr-MA') + ' DH'
                }
            }
        },
        scales: {
            y: { ticks: { callback: v => (v/1000).toFixed(0) + 'K', font: { size: 11 } }, grid: { color: 'rgba(0,0,0,0.04)' } },
            x: { ticks: { font: { size: 11 } } }
        }
    }
});
</script>

<style>
.proj-table { width:100%; border-collapse:collapse; font-size:13px; }
.proj-table th { padding:10px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.4px; background:var(--surface-soft); border-bottom:1px solid var(--border); }
.proj-table td { padding:9px 12px; border-bottom:1px solid var(--border-light); }
.proj-table tr:hover td { background:var(--surface-soft); }
.proj-table .num { text-align:right; font-variant-numeric:tabular-nums; }
.proj-table .accent { color:var(--accent); }
.proj-table .fw { font-weight:600; }
.proj-table .red { color:var(--danger); font-weight:600; }
.proj-table .green { color:var(--success); font-weight:600; }
</style>
</x-app-layout>
