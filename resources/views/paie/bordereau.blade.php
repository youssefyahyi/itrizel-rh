<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Bordereau CNSS/AMO — {{ $moisNom }} {{ $annee }}</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #f5f5f5; }

/* ── Barre de contrôle (masquée à l'impression) ── */
.controls {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 20px; background: #fff;
    border-bottom: 1px solid #d1d5db;
    position: sticky; top: 0; z-index: 10;
}
.controls a { font-size: 8.5pt; color: #1e3a5f; text-decoration: none; font-weight: 600; }
.controls form { display: flex; gap: 8px; align-items: center; margin-left: auto; }
.controls select, .controls input {
    border: 1px solid #d1d5db; border-radius: 4px;
    padding: 4px 8px; font-size: 8.5pt; color: #333;
}
.controls button { background: #1e3a5f; color: #fff; border: none; border-radius: 4px; padding: 5px 14px; font-size: 8.5pt; cursor: pointer; }
.btn-print { background: #166534; color: #fff; border: none; border-radius: 4px; padding: 5px 14px; font-size: 8.5pt; cursor: pointer; font-weight: 600; }
@media print { .controls { display: none; } }

/* ── Page ── */
.page { max-width: 297mm; margin: 16px auto; background: #fff; padding: 20mm 16mm; }
@media print {
    body { background: #fff; }
    .page { margin: 0; padding: 12mm 10mm; max-width: none; box-shadow: none; }
}

/* ── En-tête ── */
.doc-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; padding-bottom: 14px; border-bottom: 2px solid #1e3a5f; }
.soc-name { font-size: 14px; font-weight: 700; color: #1e3a5f; }
.soc-info { font-size: 10px; color: #555; margin-top: 2px; line-height: 1.5; }
.doc-title { text-align: right; }
.doc-title h1 { font-size: 15px; font-weight: 700; color: #1e3a5f; text-transform: uppercase; letter-spacing: 0.5px; }
.doc-title .subtitle { font-size: 11px; color: #555; margin-top: 3px; }
.doc-title .periode { font-size: 12px; font-weight: 600; color: #1e3a5f; margin-top: 4px; }

/* ── Tableau ── */
table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 10px; }
thead th {
    background: #1e3a5f; color: #fff; padding: 6px 6px;
    text-align: center; font-weight: 600; font-size: 9px;
    text-transform: uppercase; letter-spacing: 0.3px; white-space: nowrap;
}
thead th.tl { text-align: left; }
thead .th-group { background: #2d5086; font-size: 8px; letter-spacing: 0.5px; }
tbody tr:nth-child(even) { background: #f8f9fc; }
tbody tr:hover { background: #eef2ff; }
tbody td { padding: 5px 6px; border-bottom: 1px solid #e8ecf0; vertical-align: middle; }
tbody td.tr { text-align: right; font-variant-numeric: tabular-nums; }
tbody td.tc { text-align: center; }
tfoot td { padding: 7px 6px; font-weight: 700; background: #f0f4ff; border-top: 2px solid #1e3a5f; font-size: 10px; }
tfoot td.tr { text-align: right; color: #1e3a5f; }

/* ── Footer ── */
.doc-footer { margin-top: 20px; padding-top: 12px; border-top: 1px solid #dde3ec; display: flex; justify-content: space-between; font-size: 9px; color: #888; }
</style>
</head>
<body>

{{-- Barre de contrôle --}}
<div class="controls">
    <a href="{{ route('paie.index') }}">&#8592; Retour à la liste</a>
    <form method="GET" action="{{ route('paie.bordereau') }}">
        <label style="font-size:8pt;color:#555;">Période :</label>
        <select name="mois">
            @foreach($moisNoms as $n => $l)
                @if($n > 0)
                <option value="{{ $n }}" @selected($n === $mois)>{{ $l }}</option>
                @endif
            @endforeach
        </select>
        <input type="number" name="annee" value="{{ $annee }}" min="2020" max="2099" style="width:70px;">
        <button type="submit">Afficher</button>
    </form>
    <button class="btn-print" onclick="window.print()">&#9113; Imprimer</button>
</div>

<div class="page">

    {{-- En-tête --}}
    <div class="doc-header">
        <div>
            <div class="soc-name">{{ $societe['societe.nom'] ?? config('app.name') }}</div>
            <div class="soc-info">
                @if(!empty($societe['societe.adresse'])){{ $societe['societe.adresse'] }}<br>@endif
                @if(!empty($societe['societe.ville'])){{ $societe['societe.ville'] }}@endif
                @if(!empty($societe['societe.cnss_employeur'])) &nbsp;·&nbsp; CNSS empl. {{ $societe['societe.cnss_employeur'] }}@endif
                @if(!empty($societe['societe.ice'])) &nbsp;·&nbsp; ICE {{ $societe['societe.ice'] }}@endif
            </div>
        </div>
        <div class="doc-title">
            <h1>Bordereau CNSS / AMO</h1>
            <div class="subtitle">Déclaration mensuelle des cotisations sociales</div>
            <div class="periode">{{ $moisNom }} {{ $annee }}</div>
        </div>
    </div>

    @if($bulletins->isEmpty())
        <p style="text-align:center;color:#888;padding:40px 0;">Aucun bulletin de paie pour cette période.</p>
    @else

    @php
        $totBrut     = 0; $totAssiette = 0;
        $totCnssSal  = 0; $totCnssPtr  = 0;
        $totAmoSal   = 0; $totAmoPtr   = 0;
        $totCotTot   = 0;
    @endphp

    <table>
        <thead>
            <tr class="th-group">
                <th class="tl" rowspan="2" style="width:24px;">N°</th>
                <th class="tl" rowspan="2" style="min-width:130px;">Nom & Prénom</th>
                <th class="tl" rowspan="2" style="width:80px;">N° CNSS</th>
                <th rowspan="2" style="width:70px;">Brut (DH)</th>
                <th rowspan="2" style="width:70px;">Assiette CNSS</th>
                <th colspan="2" style="border-bottom:1px solid rgba(255,255,255,0.3);">CNSS</th>
                <th colspan="2" style="border-bottom:1px solid rgba(255,255,255,0.3);">AMO</th>
                <th rowspan="2" style="width:70px;">Total cotis.</th>
            </tr>
            <tr>
                <th style="width:60px;">Salarié</th>
                <th style="width:60px;">Patronal</th>
                <th style="width:60px;">Salarié</th>
                <th style="width:60px;">Patronal</th>
            </tr>
        </thead>
        <tbody>
        @foreach($bulletins as $i => $b)
            @php
                $assiette = min($b->brut, $cnssPlafond);
                $totBrut     += $b->brut;
                $totAssiette += $assiette;
                $totCnssSal  += $b->cnss_salarie;
                $totCnssPtr  += $b->cnss_patronal;
                $totAmoSal   += $b->amo_salarie;
                $totAmoPtr   += $b->amo_patronal;
                $totCotTot   += $b->cnss_salarie + $b->cnss_patronal + $b->amo_salarie + $b->amo_patronal;
            @endphp
            <tr>
                <td class="tc">{{ $i + 1 }}</td>
                <td>{{ $b->employe->nom_complet }}</td>
                <td class="tc">{{ $b->employe->numero_cnss ?? '—' }}</td>
                <td class="tr">{{ number_format($b->brut, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($assiette, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($b->cnss_salarie, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($b->cnss_patronal, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($b->amo_salarie, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($b->amo_patronal, 2, ',', ' ') }}</td>
                <td class="tr" style="font-weight:600;">{{ number_format($b->cnss_salarie + $b->cnss_patronal + $b->amo_salarie + $b->amo_patronal, 2, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="font-weight:700;color:#1e3a5f;">TOTAUX — {{ $bulletins->count() }} salarié(s)</td>
                <td class="tr">{{ number_format($totBrut, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($totAssiette, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($totCnssSal, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($totCnssPtr, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($totAmoSal, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($totAmoPtr, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($totCotTot, 2, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="doc-footer">
        <span>Généré le {{ now()->format('d/m/Y à H:i') }}</span>
        <span>Itrizel RH — Bordereau CNSS/AMO {{ $moisNom }} {{ $annee }}</span>
    </div>

</div>
</body>
</html>
