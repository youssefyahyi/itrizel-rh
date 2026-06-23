<x-app-layout>
@php $libelle = $entite === 'employes' ? 'Employés' : 'Contrats'; @endphp
<x-rh.page-header
    :breadcrumbs="['Outils' => route('outils.index'), 'Reprise de données' => route('outils.import.index'), $libelle => null]">
</x-rh.page-header>

<div class="content" style="padding:20px 24px;">

    @include('outils.import._steps', ['active' => 2])

    {{-- Bilan --}}
    <div style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <div class="bilan-chip total">&#128203; {{ $stats['total'] }} ligne{{ $stats['total'] > 1 ? 's' : '' }}</div>
        <div class="bilan-chip ok">&#9989; {{ $stats['valides'] }} valide{{ $stats['valides'] > 1 ? 's' : '' }}</div>
        <div class="bilan-chip err">&#10060; {{ $stats['erreurs'] }} erreur{{ $stats['erreurs'] > 1 ? 's' : '' }}</div>
    </div>

    @if($stats['erreurs'] > 0)
    <div style="margin-bottom:16px;padding:10px 14px;background:#FFFBEB;border:1px solid #FCD34D;border-radius:8px;font-size:13px;color:#92400E;">
        Seules les lignes valides seront importées. Corrigez le fichier et re-uploadez si vous souhaitez importer les lignes en erreur.
    </div>
    @endif

    {{-- Tableau --}}
    <div style="overflow-x:auto;border-radius:var(--radius);border:1px solid var(--border);margin-bottom:20px;">
        <table class="data-table" style="min-width:600px;">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    @foreach($colonnes as $col)
                    <th>{{ str_replace(' *', '', $col['libelle']) }}</th>
                    @endforeach
                    <th style="width:80px;">Statut</th>
                    <th>Motif d'erreur</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lignes as $ligne)
                <tr class="{{ $ligne['_statut'] === 'erreur' ? 'row-err' : 'row-ok' }}">
                    <td style="text-align:center;color:var(--text-muted);font-size:11px;">{{ $ligne['_ligne'] }}</td>
                    @foreach($colonnes as $col)
                    <td>{{ $ligne[$col['col']] ?? '' }}</td>
                    @endforeach
                    <td style="text-align:center;">
                        @if($ligne['_statut'] === 'valide')
                            <span style="color:#16A34A;font-size:16px;">&#9989;</span>
                        @else
                            <span style="color:#DC2626;font-size:16px;">&#10060;</span>
                        @endif
                    </td>
                    <td style="color:#B91C1C;font-size:11px;line-height:1.6;">
                        @foreach($ligne['_erreurs'] as $err)
                            <div>— {{ $err }}</div>
                        @endforeach
                    </td>
                </tr>
                @empty
                <tr><td colspan="{{ count($colonnes) + 3 }}" style="text-align:center;padding:30px;color:var(--text-muted);">Aucune ligne trouvée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:10px;align-items:center;">
        <a href="{{ route('outils.import.charger', $entite) }}" class="btn btn-secondary btn-sm">
            ← Modifier le fichier
        </a>
        @if($stats['valides'] > 0)
        <form method="POST" action="{{ route('outils.import.importer', $entite) }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                Importer {{ $stats['valides'] }} ligne{{ $stats['valides'] > 1 ? 's' : '' }} valide{{ $stats['valides'] > 1 ? 's' : '' }} →
            </button>
        </form>
        @endif
    </div>

</div>

<style>
.bilan-chip { padding:6px 14px; border-radius:20px; font-size:13px; font-weight:500; }
.bilan-chip.total { background:var(--surface-soft); color:var(--text-secondary); border:1px solid var(--border); }
.bilan-chip.ok    { background:#F0FDF4; color:#16A34A; border:1px solid #BBF7D0; }
.bilan-chip.err   { background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; }
.row-ok td { background:#FAFFFE; }
.row-err td { background:#FFF9F9; }
</style>
</x-app-layout>
