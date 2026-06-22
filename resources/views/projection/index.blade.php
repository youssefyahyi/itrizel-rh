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

{{-- Sélecteur horizon --}}
<div class="info-card" style="padding:14px 20px;">
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
        <span style="font-size:13px;font-weight:500;color:var(--text-secondary);">Horizon de projection :</span>
        <div style="display:flex;gap:6px;">
            @foreach([6, 12, 24] as $h)
            <a href="{{ route('projection.index', ['horizon' => $h]) }}"
               class="btn {{ $horizon === $h ? 'btn-primary' : 'btn-outline' }} btn-sm">
                {{ $h }} mois
            </a>
            @endforeach
        </div>
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
        <span style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;">Détail mensuel — Situation actuelle</span>
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
            <td>
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
        <tr class="unites-row hidden" id="unites-{{ $m['mois'] }}">
            <td colspan="8" style="padding:0 0 0 24px;background:var(--surface-soft);">
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    @foreach($m['unites'] as $u)
                    <tr style="border-top:1px solid var(--border-light);">
                        <td style="padding:6px 12px;color:var(--text-secondary);padding-left:32px;">
                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--accent);margin-right:6px;opacity:.5;"></span>
                            {{ $u['nom'] }}
                        </td>
                        <td class="num" style="padding:6px 8px;">{{ $u['effectif'] }}</td>
                        <td class="num" style="padding:6px 8px;">{{ number_format($u['brut'], 0, ',', ' ') }}</td>
                        <td class="num" style="padding:6px 8px;">{{ number_format($u['charges_sal'], 0, ',', ' ') }}</td>
                        <td class="num" style="padding:6px 8px;">{{ number_format($u['ir'], 0, ',', ' ') }}</td>
                        <td class="num" style="padding:6px 8px;">{{ number_format($u['net'], 0, ',', ' ') }}</td>
                        <td class="num" style="padding:6px 8px;">{{ number_format($u['charges_pat'], 0, ',', ' ') }}</td>
                        <td class="num fw" style="padding:6px 8px;">{{ number_format($u['cout_employeur'], 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>
        @endif
        @endforeach
        </tbody>
    </table>
    </div>
</div>

{{-- Scénarios sauvegardés --}}
<div class="info-card" style="padding:0;overflow:hidden;">
    <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;">
            Scénarios ({{ $scenarios->count() }}/10)
        </span>
        <a href="{{ route('projection.create') }}" class="tb-btn">+ Nouveau</a>
    </div>
    @if($scenarios->isEmpty())
    <div style="padding:32px;text-align:center;color:var(--text-muted);font-size:13px;">
        Aucun scénario — créez-en un pour simuler des changements.
    </div>
    @else
    @foreach($scenarios as $sc)
    <div style="display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border-light);">
        <div style="flex:1;min-width:0;">
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $sc->nom }}</div>
            @if($sc->description)
            <div style="font-size:12px;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $sc->description }}</div>
            @endif
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">{{ $sc->horizon }} mois · {{ $sc->lignes_count ?? $sc->lignes()->count() }} ligne(s)</div>
        </div>
        <div style="display:flex;gap:6px;flex-shrink:0;">
            <a href="{{ route('projection.comparer', ['a' => $sc->id, 'b' => 'base']) }}" class="btn btn-primary btn-sm">Comparer</a>
            <a href="{{ route('projection.edit', $sc) }}" class="btn btn-outline btn-sm">Modifier</a>
            <form method="POST" action="{{ route('projection.archiver', $sc) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm"
                    onclick="return confirm('Archiver ce scénario ?')">Archiver</button>
            </form>
        </div>
    </div>
    @endforeach
    @endif
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
    const row = document.getElementById('unites-' + mois);
    const btn = document.querySelector('[data-mois="' + mois + '"] .toggle-btn');
    if (!row) return;
    row.classList.toggle('hidden');
    btn.textContent = row.classList.contains('hidden') ? '▶' : '▼';
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
