<x-app-layout>
<x-rh.page-header title="Projection Masse Salariale">
    <a href="{{ route('projection.comparer', ['a' => 'base', 'b' => 'base']) }}" class="btn btn-outline btn-sm">Comparer</a>
    <a href="{{ route('projection.create') }}" class="btn btn-primary btn-sm">+ Nouveau scénario</a>
</x-rh.page-header>

@php
    $labels       = array_column($situation, 'label');
    $couts        = array_map(fn($m) => round($m['global']['cout_employeur']), $situation);
    $bruts        = array_map(fn($m) => round($m['global']['brut']), $situation);
    $premiers     = $situation[0] ?? null;
    $derniers     = $situation[count($situation)-1] ?? null;
@endphp

<div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if(session('success'))<x-rh.alert type="success" :message="session('success')" />@endif

{{-- Sélecteur horizon + scénario --}}
<div class="info-card" style="padding:14px 20px;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <span style="font-size:13px;font-weight:500;color:var(--text-secondary);">Horizon :</span>
        <div style="display:flex;gap:6px;">
            @foreach([6, 12, 24] as $h)
            <a href="{{ route('projection.index', ['horizon' => $h, 'scenario' => $scenarioActif?->id]) }}"
               class="btn {{ $horizon === $h ? 'btn-primary' : 'btn-outline' }} btn-sm">
                {{ $h }} mois
            </a>
            @endforeach
        </div>
        <div style="width:1px;height:20px;background:var(--border);flex-shrink:0;"></div>
        <span style="font-size:13px;font-weight:500;color:var(--text-secondary);">Vue :</span>
        <select id="sel-scenario" onchange="window.location.href=this.value" style="padding:6px 10px;border:1px solid var(--border);border-radius:6px;font-size:13px;font-family:inherit;color:var(--text-primary);background:var(--surface);cursor:pointer;">
            <option value="{{ route('projection.index', ['horizon' => $horizon]) }}"
                {{ !$scenarioActif ? 'selected' : '' }}>Situation actuelle</option>
            @foreach($scenarios as $sc)
            <option value="{{ route('projection.index', ['horizon' => $horizon, 'scenario' => $sc->id]) }}"
                {{ $scenarioActif?->id == $sc->id ? 'selected' : '' }}>{{ $sc->nom }}</option>
            @endforeach
        </select>
        @if($scenarioActif)
        <a href="{{ route('projection.edit', $scenarioActif) }}" class="btn btn-outline btn-sm">Modifier</a>
        <form method="POST" action="{{ route('projection.destroy', $scenarioActif) }}" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline btn-sm" style="color:var(--danger);border-color:var(--danger);"
                onclick="return confirm('Supprimer « {{ addslashes($scenarioActif->nom) }} » ?')">Supprimer</button>
        </form>
        @endif
        <span style="font-size:12px;color:var(--text-muted);margin-left:auto;">
            {{ now()->addMonth()->format('m/Y') }} → {{ now()->addMonths($horizon)->format('m/Y') }}
        </span>
    </div>
</div>

{{-- KPIs --}}
@if($premiers && $derniers)
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
    <div class="info-card" style="padding:16px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Effectif actuel</div>
        <div style="font-size:22px;font-weight:700;color:var(--text-primary);">{{ $premiers['global']['effectif'] }}</div>
        <div style="font-size:11px;color:var(--text-muted);">employés actifs</div>
    </div>
    <div class="info-card" style="padding:16px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Coût employeur mois 1</div>
        <div style="font-size:22px;font-weight:700;color:var(--accent);">{{ number_format($premiers['global']['cout_employeur'], 0, ',', ' ') }}</div>
        <div style="font-size:11px;color:var(--text-muted);">DH / mois</div>
    </div>
    <div class="info-card" style="padding:16px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Coût employeur fin période</div>
        <div style="font-size:22px;font-weight:700;color:var(--text-primary);">{{ number_format($derniers['global']['cout_employeur'], 0, ',', ' ') }}</div>
        <div style="font-size:11px;color:var(--text-muted);">DH / mois en {{ $derniers['label'] }}</div>
    </div>
    <div class="info-card" style="padding:16px;">
        <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Total sur {{ $horizon }} mois</div>
        <div style="font-size:22px;font-weight:700;color:var(--text-primary);">{{ number_format(array_sum($couts), 0, ',', ' ') }}</div>
        <div style="font-size:11px;color:var(--text-muted);">DH coût employeur cumulé</div>
    </div>
</div>
@endif

{{-- Graphique --}}
<div class="info-card" style="padding:20px;">
    <div style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;">
        Évolution — Situation actuelle
    </div>
    <canvas id="chartProjection" style="max-height:240px;"></canvas>
</div>

{{-- Tableau mensuel --}}
<div class="info-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;">Détail mensuel — {{ $labelVue }}</span>
    </div>
    <div style="overflow-x:auto;">
    <table class="proj-table">
        <thead>
            <tr>
                <th>Mois</th>
                <th class="num">Effectif</th>
                <th class="num">Brut total</th>
                <th class="num">Charges sal.</th>
                <th class="num">IR</th>
                <th class="num">Net à payer</th>
                <th class="num">Charges pat.</th>
                <th class="num accent">Coût employeur</th>
            </tr>
        </thead>
        <tbody>
        @foreach($situation as $m)
        <tr class="expandable" data-mois="{{ $m['mois'] }}">
            <td style="white-space:nowrap;">
                @if(count($m['unites']) > 1)
                <button class="toggle-btn" onclick="toggleUnites('{{ $m['mois'] }}')">▶</button>
                @endif
                {{ $m['label'] }}
            </td>
            <td class="num">{{ $m['global']['effectif'] }}</td>
            <td class="num">{{ number_format($m['global']['brut'], 0, ',', ' ') }}</td>
            <td class="num">{{ number_format($m['global']['charges_sal'], 0, ',', ' ') }}</td>
            <td class="num">{{ number_format($m['global']['ir'], 0, ',', ' ') }}</td>
            <td class="num">{{ number_format($m['global']['net'], 0, ',', ' ') }}</td>
            <td class="num">{{ number_format($m['global']['charges_pat'], 0, ',', ' ') }}</td>
            <td class="num accent fw">{{ number_format($m['global']['cout_employeur'], 0, ',', ' ') }}</td>
        </tr>
        @if(count($m['unites']) > 1)
        @foreach($m['unites'] as $u)
        <tr class="unite-row hidden" data-parent="{{ $m['mois'] }}" style="background:var(--surface-soft);font-size:12px;">
            <td style="padding:6px 12px 6px 36px;border-top:1px solid var(--border-light);color:var(--text-secondary);">
                <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--accent);margin-right:6px;opacity:.4;"></span>
                {{ $u['nom'] }}
            </td>
            <td class="num">{{ $u['effectif'] }}</td>
            <td class="num">{{ number_format($u['brut'], 0, ',', ' ') }}</td>
            <td class="num">{{ number_format($u['charges_sal'], 0, ',', ' ') }}</td>
            <td class="num">{{ number_format($u['ir'], 0, ',', ' ') }}</td>
            <td class="num">{{ number_format($u['net'], 0, ',', ' ') }}</td>
            <td class="num">{{ number_format($u['charges_pat'], 0, ',', ' ') }}</td>
            <td class="num fw accent">{{ number_format($u['cout_employeur'], 0, ',', ' ') }}</td>
        </tr>
        @endforeach
        @endif
        @endforeach
        </tbody>
    </table>
    </div>
</div>


</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const labels = @json($labels);
const couts  = @json($couts);
const bruts  = @json($bruts);

const ctx = document.getElementById('chartProjection').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label: 'Coût employeur',
                data: couts,
                borderColor: 'rgba(37,99,235,0.9)',
                backgroundColor: 'rgba(37,99,235,0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointRadius: 3,
            },
            {
                label: 'Brut total',
                data: bruts,
                borderColor: 'rgba(16,185,129,0.8)',
                backgroundColor: 'transparent',
                borderWidth: 2,
                fill: false,
                tension: 0.3,
                pointRadius: 3,
                borderDash: [5, 3],
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
            y: {
                ticks: {
                    callback: v => (v / 1000).toFixed(0) + 'K',
                    font: { size: 11 }
                },
                grid: { color: 'rgba(0,0,0,0.04)' }
            },
            x: { ticks: { font: { size: 11 } } }
        }
    }
});

function toggleUnites(mois) {
    const rows = document.querySelectorAll('[data-parent="' + mois + '"]');
    const btn  = document.querySelector('[data-mois="' + mois + '"] .toggle-btn');
    let hidden = false;
    rows.forEach(r => { r.classList.toggle('hidden'); hidden = r.classList.contains('hidden'); });
    if (btn) btn.textContent = hidden ? '▶' : '▼';
}
</script>

<style>
.proj-table { width:100%; border-collapse:collapse; font-size:13px; }
.proj-table th { padding:10px 12px; text-align:left; font-size:11px; font-weight:600; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.4px; background:var(--surface-soft); border-bottom:1px solid var(--border); }
.proj-table td { padding:9px 12px; border-bottom:1px solid var(--border-light); color:var(--text-primary); }
.proj-table tr:hover td { background:var(--surface-soft); }
.proj-table .num { text-align:right; font-variant-numeric:tabular-nums; }
.proj-table .accent { color:var(--accent); }
.proj-table .fw { font-weight:600; }
.toggle-btn { background:none; border:none; cursor:pointer; color:var(--text-muted); font-size:10px; padding:0 4px 0 0; }
.hidden { display:none; }
</style>
</x-app-layout>
