<x-app-layout>
<x-rh.page-header title="Comparaison de projections">
    <a href="{{ route('projection.index') }}" class="btn btn-outline btn-sm">← Projections</a>
</x-rh.page-header>

@php
    $labels    = array_column($dataA, 'label');
    $coutsA    = array_map(fn($m) => round($m['global']['cout_employeur']), $dataA);
    $coutsB    = array_map(fn($m) => round($m['global']['cout_employeur']), $dataB);
    $deltaTotal = array_sum($coutsB) - array_sum($coutsA);
    $deltaMois1 = ($dataB[0]['global']['cout_employeur'] ?? 0) - ($dataA[0]['global']['cout_employeur'] ?? 0);
    $totalA    = array_sum($coutsA);
    $totalB    = array_sum($coutsB);
@endphp

<div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

{{-- Sélecteurs --}}
<form method="GET" action="{{ route('projection.comparer') }}" id="form-comparer">
<div class="info-card" style="padding:16px 20px;">
    <div style="display:flex;align-items:flex-end;gap:16px;flex-wrap:wrap;">

        <div style="flex:1;min-width:180px;">
            <label style="font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">
                Projection A
            </label>
            <select name="a" class="uf-control" onchange="document.getElementById('form-comparer').submit()">
                <option value="base" {{ $aParam === 'base' ? 'selected' : '' }}>Situation actuelle</option>
                @foreach($scenarios as $sc)
                <option value="{{ $sc->id }}" {{ $aParam == $sc->id ? 'selected' : '' }}>
                    {{ $sc->nom }}
                </option>
                @endforeach
            </select>
        </div>

        <div style="padding-bottom:10px;font-size:18px;color:var(--text-muted);font-weight:300;">vs</div>

        <div style="flex:1;min-width:180px;">
            <label style="font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">
                Projection B
            </label>
            <select name="b" class="uf-control" onchange="document.getElementById('form-comparer').submit()">
                <option value="base" {{ $bParam === 'base' ? 'selected' : '' }}>Situation actuelle</option>
                @foreach($scenarios as $sc)
                <option value="{{ $sc->id }}" {{ $bParam == $sc->id ? 'selected' : '' }}>
                    {{ $sc->nom }}
                </option>
                @endforeach
            </select>
        </div>

        <div style="min-width:140px;">
            <label style="font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">
                Horizon
            </label>
            <select name="horizon" class="uf-control" onchange="document.getElementById('form-comparer').submit()">
                @foreach([6, 12, 24] as $h)
                <option value="{{ $h }}" {{ $horizon == $h ? 'selected' : '' }}>{{ $h }} mois</option>
                @endforeach
            </select>
        </div>

    </div>
</div>
</form>

{{-- KPIs delta --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
    <div class="info-card" style="padding:16px;border-left:3px solid var(--accent);">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">{{ $labelA }} — mois 1</div>
        <div style="font-size:20px;font-weight:700;color:var(--text-primary);">{{ number_format($dataA[0]['global']['cout_employeur'] ?? 0, 0, ',', ' ') }}</div>
        <div style="font-size:11px;color:var(--text-muted);">DH coût employeur</div>
    </div>
    <div class="info-card" style="padding:16px;border-left:3px solid #10b981;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">{{ $labelB }} — mois 1</div>
        <div style="font-size:20px;font-weight:700;color:var(--text-primary);">{{ number_format($dataB[0]['global']['cout_employeur'] ?? 0, 0, ',', ' ') }}</div>
        <div style="font-size:11px;color:var(--text-muted);">DH coût employeur</div>
    </div>
    <div class="info-card" style="padding:16px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Écart mois 1</div>
        <div style="font-size:20px;font-weight:700;color:{{ $deltaMois1 > 0 ? 'var(--danger)' : ($deltaMois1 < 0 ? 'var(--success)' : 'var(--text-primary)') }};">
            {{ $deltaMois1 >= 0 ? '+' : '' }}{{ number_format($deltaMois1, 0, ',', ' ') }}
        </div>
        <div style="font-size:11px;color:var(--text-muted);">DH (B − A)</div>
    </div>
    <div class="info-card" style="padding:16px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Écart cumulé {{ $horizon }} mois</div>
        <div style="font-size:20px;font-weight:700;color:{{ $deltaTotal > 0 ? 'var(--danger)' : ($deltaTotal < 0 ? 'var(--success)' : 'var(--text-primary)') }};">
            {{ $deltaTotal >= 0 ? '+' : '' }}{{ number_format($deltaTotal, 0, ',', ' ') }}
        </div>
        <div style="font-size:11px;color:var(--text-muted);">DH (B − A)</div>
    </div>
</div>

{{-- Hypothèses --}}
@if($scenarioA || $scenarioB)
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
    @foreach([['sc' => $scenarioA, 'label' => $labelA, 'color' => 'var(--accent)'], ['sc' => $scenarioB, 'label' => $labelB, 'color' => '#10b981']] as $side)
    <div class="info-card" style="padding:14px 16px;border-top:3px solid {{ $side['color'] }};">
        <div style="font-size:12px;font-weight:600;color:var(--text-secondary);margin-bottom:8px;">{{ $side['label'] }}</div>
        @if($side['sc'])
            @if($side['sc']->description)
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:8px;">{{ $side['sc']->description }}</div>
            @endif
            @forelse($side['sc']->lignes as $ligne)
            <div style="font-size:12px;padding:3px 0;color:var(--text-secondary);">· {{ $ligne->libelle }}</div>
            @empty
            <div style="font-size:12px;color:var(--text-muted);">Aucune hypothèse</div>
            @endforelse
        @else
            <div style="font-size:12px;color:var(--text-muted);">Données courantes sans modification</div>
        @endif
    </div>
    @endforeach
</div>
@endif

{{-- Graphique --}}
<div class="info-card" style="padding:20px;">
    <div style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;">
        Évolution du coût employeur
    </div>
    <canvas id="chartCompare" style="max-height:260px;"></canvas>
</div>

{{-- Tableau --}}
<div class="info-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
        <span style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;">
            Détail mensuel
        </span>
    </div>
    <div style="overflow-x:auto;">
    <table class="proj-table">
        <thead>
            <tr>
                <th style="min-width:110px;">Mois</th>
                <th class="num" style="color:var(--accent);">A — effectif</th>
                <th class="num" style="color:var(--accent);">A — brut</th>
                <th class="num" style="color:var(--accent);">A — coût</th>
                <th class="num" style="color:#10b981;">B — effectif</th>
                <th class="num" style="color:#10b981;">B — brut</th>
                <th class="num" style="color:#10b981;">B — coût</th>
                <th class="num">Δ coût (B−A)</th>
            </tr>
        </thead>
        <tbody>
        @foreach($dataA as $idx => $rowA)
        @php
            $rowB   = $dataB[$idx] ?? null;
            $delta  = $rowB ? round($rowB['global']['cout_employeur'] - $rowA['global']['cout_employeur']) : 0;
            $moisId = 'u-' . $idx;

            // Fusionner les unités de A et B par nom
            $unitesA = collect($rowA['unites'])->keyBy('nom');
            $unitesB = collect($rowB['unites'] ?? [])->keyBy('nom');
            $nomsUnites = $unitesA->keys()->merge($unitesB->keys())->unique()->sort()->values();
            $hasUnites  = $nomsUnites->count() > 1;
        @endphp
        <tr class="row-global" data-mois="{{ $moisId }}">
            <td>
                @if($hasUnites)
                <button class="toggle-btn" onclick="toggleUnites('{{ $moisId }}')">▶</button>
                @endif
                <span style="font-weight:500;">{{ $rowA['label'] }}</span>
            </td>
            <td class="num">{{ $rowA['global']['effectif'] }}</td>
            <td class="num">{{ number_format($rowA['global']['brut'], 0, ',', ' ') }}</td>
            <td class="num fw">{{ number_format($rowA['global']['cout_employeur'], 0, ',', ' ') }}</td>
            @if($rowB)
            <td class="num">{{ $rowB['global']['effectif'] }}</td>
            <td class="num">{{ number_format($rowB['global']['brut'], 0, ',', ' ') }}</td>
            <td class="num fw">{{ number_format($rowB['global']['cout_employeur'], 0, ',', ' ') }}</td>
            <td class="num {{ $delta > 0 ? 'red' : ($delta < 0 ? 'green' : '') }}">
                {{ $delta !== 0 ? ($delta > 0 ? '+' : '') . number_format($delta, 0, ',', ' ') : '—' }}
            </td>
            @else
            <td colspan="4" class="num" style="color:var(--text-muted);">—</td>
            @endif
        </tr>
        @if($hasUnites)
        @foreach($nomsUnites as $nomU)
        @php
            $uA = $unitesA->get($nomU);
            $uB = $unitesB->get($nomU);
            $dU = ($uB['cout_employeur'] ?? 0) - ($uA['cout_employeur'] ?? 0);
        @endphp
        <tr class="row-unite hidden" data-parent="{{ $moisId }}" style="background:var(--surface-soft);font-size:12px;">
            <td style="padding:6px 12px 6px 36px;border-top:1px solid var(--border-light);color:var(--text-secondary);">
                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--accent);margin-right:6px;opacity:.4;"></span>
                {{ $nomU }}
            </td>
            <td class="num" style="color:var(--accent);">{{ $uA['effectif'] ?? '—' }}</td>
            <td class="num" style="color:var(--accent);">{{ $uA ? number_format($uA['brut'], 0, ',', ' ') : '—' }}</td>
            <td class="num fw" style="color:var(--accent);">{{ $uA ? number_format($uA['cout_employeur'], 0, ',', ' ') : '—' }}</td>
            <td class="num" style="color:#10b981;">{{ $uB['effectif'] ?? '—' }}</td>
            <td class="num" style="color:#10b981;">{{ $uB ? number_format($uB['brut'], 0, ',', ' ') : '—' }}</td>
            <td class="num fw" style="color:#10b981;">{{ $uB ? number_format($uB['cout_employeur'], 0, ',', ' ') : '—' }}</td>
            <td class="num {{ $dU > 0 ? 'red' : ($dU < 0 ? 'green' : '') }}">
                {{ $dU != 0 ? ($dU > 0 ? '+' : '') . number_format($dU, 0, ',', ' ') : '—' }}
            </td>
        </tr>
        @endforeach
        @endif
        @endforeach
        </tbody>
        <tfoot>
            <tr style="background:var(--surface-soft);font-weight:600;">
                <td style="padding:10px 12px;font-size:12px;color:var(--text-secondary);">TOTAL {{ $horizon }} mois</td>
                <td class="num">—</td>
                <td class="num">—</td>
                <td class="num">{{ number_format($totalA, 0, ',', ' ') }}</td>
                <td class="num">—</td>
                <td class="num">—</td>
                <td class="num">{{ number_format($totalB, 0, ',', ' ') }}</td>
                <td class="num {{ $deltaTotal > 0 ? 'red' : ($deltaTotal < 0 ? 'green' : '') }}">
                    {{ $deltaTotal >= 0 ? '+' : '' }}{{ number_format($deltaTotal, 0, ',', ' ') }}
                </td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chartCompare').getContext('2d'), {
    type: 'line',
    data: {
        labels: @json($labels),
        datasets: [
            {
                label: '{{ addslashes($labelA) }}',
                data: @json($coutsA),
                borderColor: 'rgba(37,99,235,0.9)',
                backgroundColor: 'rgba(37,99,235,0.07)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 3,
            },
            {
                label: '{{ addslashes($labelB) }}',
                data: @json($coutsB),
                borderColor: 'rgba(16,185,129,0.9)',
                backgroundColor: 'rgba(16,185,129,0.07)',
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

<script>
function toggleUnites(moisId) {
    const rows = document.querySelectorAll('[data-parent="' + moisId + '"]');
    const btn  = document.querySelector('[data-mois="' + moisId + '"] .toggle-btn');
    let hidden = false;
    rows.forEach(r => { r.classList.toggle('hidden'); hidden = r.classList.contains('hidden'); });
    if (btn) btn.textContent = hidden ? '▶' : '▼';
}
</script>

<style>
.proj-table { width:100%; border-collapse:collapse; font-size:13px; }
.proj-table th { padding:10px 12px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; background:var(--surface-soft); border-bottom:1px solid var(--border); }
.proj-table td { padding:9px 12px; border-bottom:1px solid var(--border-light); color:var(--text-primary); }
.proj-table tfoot td { padding:10px 12px; border-top:2px solid var(--border); border-bottom:none; font-size:12px; }
.proj-table tr:hover td { background:var(--surface-soft); }
.proj-table .num { text-align:right; font-variant-numeric:tabular-nums; }
.proj-table .fw { font-weight:600; }
.proj-table .red { color:var(--danger); font-weight:600; }
.proj-table .green { color:var(--success); font-weight:600; }
.uf-control { width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px; font-family:inherit; font-size:13px; color:var(--text-primary); background:#fff; outline:none; }
.uf-control:focus { border-color:var(--accent); }
</style>
</x-app-layout>
