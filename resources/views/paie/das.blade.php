<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>DAS — Déclaration Annuelle des Salaires {{ $annee }}</title>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Arial, sans-serif; font-size: 11px; color: #1a1a2e; background: #f5f5f5; }

/* ── Barre de contrôle ── */
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
.doc-title .subtitle { font-size: 10px; color: #555; margin-top: 2px; }
.doc-title .annee { font-size: 22px; font-weight: 700; color: #1e3a5f; margin-top: 4px; }

/* ── Info box ── */
.info-box { background: #f0f4ff; border: 1px solid #c7d4f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 16px; font-size: 10px; color: #3b5998; line-height: 1.6; }

/* ── Tableau ── */
table { width: 100%; border-collapse: collapse; font-size: 10px; }
thead th {
    background: #1e3a5f; color: #fff; padding: 7px 6px;
    font-weight: 600; font-size: 9px; text-transform: uppercase;
    letter-spacing: 0.3px; white-space: nowrap;
}
thead th.tl { text-align: left; }
thead th.tr { text-align: right; }
tbody tr:nth-child(even) { background: #f8f9fc; }
tbody tr:hover { background: #eef2ff; }
tbody td { padding: 6px 6px; border-bottom: 1px solid #e8ecf0; vertical-align: middle; }
tbody td.tr { text-align: right; font-variant-numeric: tabular-nums; }
tbody td.tc { text-align: center; }
tfoot td { padding: 8px 6px; font-weight: 700; background: #f0f4ff; border-top: 2px solid #1e3a5f; }
tfoot td.tr { text-align: right; color: #1e3a5f; }

/* ── Footer ── */
.doc-footer { margin-top: 20px; padding-top: 12px; border-top: 1px solid #dde3ec; display: flex; justify-content: space-between; font-size: 9px; color: #888; }
.legal { margin-top: 14px; font-size: 9px; color: #aaa; font-style: italic; }
</style>
</head>
<body>

{{-- Barre de contrôle --}}
<div class="controls">
    <a href="{{ route('paie.index') }}">&#8592; Retour à la liste</a>
    <form method="GET" action="{{ route('paie.das') }}">
        <label style="font-size:8pt;color:#555;">Année :</label>
        <select name="annee">
            @foreach($annees as $a)
            <option value="{{ $a }}" @selected($a == $annee)>{{ $a }}</option>
            @endforeach
        </select>
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
                @if(!empty($societe['societe.ice'])) &nbsp;·&nbsp; ICE {{ $societe['societe.ice'] }}@endif
                @if(!empty($societe['societe.if_fiscal'])) &nbsp;·&nbsp; IF {{ $societe['societe.if_fiscal'] }}@endif
            </div>
        </div>
        <div class="doc-title">
            <h1>Déclaration Annuelle des Salaires</h1>
            <div class="subtitle">Art. 79 CGI — À déposer avant le 1er mars de l'année suivante</div>
            <div class="annee">{{ $annee }}</div>
        </div>
    </div>

    <div class="info-box">
        Seuls les bulletins à statut <strong>Validé</strong> ou <strong>Payé</strong> sont inclus dans cette déclaration.
        @if($lignes->isEmpty()) Aucun bulletin validé ou payé pour l'année {{ $annee }}.
        @else {{ $lignes->count() }} salarié(s) — {{ $lignes->sum('nb_mois') }} bulletins agrégés.
        @endif
    </div>

    @if($lignes->isNotEmpty())

    @php
        $totBrut    = $lignes->sum('brut_annuel');
        $totIr      = $lignes->sum('ir_annuel');
        $totNetImp  = $lignes->sum('net_imposable_annuel');
        $totNet     = $lignes->sum('net_annuel');
    @endphp

    <table>
        <thead>
            <tr>
                <th class="tl" style="width:20px;">N°</th>
                <th class="tl" style="min-width:120px;">Nom & Prénom</th>
                <th class="tl" style="width:90px;">CIN</th>
                <th class="tl" style="width:75px;">Matricule</th>
                <th class="tl" style="width:70px;">N° CNSS</th>
                <th class="tc" style="width:30px;">Mois</th>
                <th class="tr" style="width:75px;">Brut annuel</th>
                <th class="tr" style="width:75px;">Net imposable</th>
                <th class="tr" style="width:70px;">IR retenu</th>
                <th class="tr" style="width:75px;">Net versé</th>
            </tr>
        </thead>
        <tbody>
        @foreach($lignes as $i => $l)
            <tr>
                <td class="tc">{{ $i + 1 }}</td>
                <td style="font-weight:500;">{{ $l->employe->nom_complet }}</td>
                <td class="tc">{{ $l->employe->cin ?? '—' }}</td>
                <td class="tc">{{ $l->employe->matricule }}</td>
                <td class="tc">{{ $l->employe->numero_cnss ?? '—' }}</td>
                <td class="tc">{{ $l->nb_mois }}</td>
                <td class="tr">{{ number_format($l->brut_annuel, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($l->net_imposable_annuel, 2, ',', ' ') }}</td>
                <td class="tr" style="color:#c0392b;">{{ number_format($l->ir_annuel, 2, ',', ' ') }}</td>
                <td class="tr" style="font-weight:600;">{{ number_format($l->net_annuel, 2, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="font-weight:700;color:#1e3a5f;">TOTAUX — {{ $lignes->count() }} salarié(s)</td>
                <td class="tr">{{ number_format($totBrut, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($totNetImp, 2, ',', ' ') }}</td>
                <td class="tr" style="color:#c0392b;">{{ number_format($totIr, 2, ',', ' ') }}</td>
                <td class="tr">{{ number_format($totNet, 2, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    @endif

    <p class="legal">DAS conforme à l'article 79 du Code Général des Impôts marocain. Les montants sont exprimés en dirhams (DH).</p>

    <div class="doc-footer">
        <span>Généré le {{ now()->format('d/m/Y à H:i') }}</span>
        <span>Itrizel RH — DAS {{ $annee }}</span>
    </div>

</div>
</body>
</html>
