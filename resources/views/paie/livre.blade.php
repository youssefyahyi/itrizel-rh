<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Livre de Paie — {{ $moisNom }} {{ $annee }}</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
  font-family: Arial, sans-serif;
  font-size: 8pt;
  color: #1a1a1a;
  background: #f5f5f5;
}

/* ── CONTRÔLES ÉCRAN ── */
.controls {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  background: #fff;
  border-bottom: 1px solid #d1d5db;
  position: sticky;
  top: 0;
  z-index: 10;
}
.controls a   { font-size: 8.5pt; color: #1e3a5f; text-decoration: none; font-weight: 600; }
.controls form { display: flex; gap: 8px; align-items: center; margin-left: auto; }
.controls select, .controls input {
  border: 1px solid #d1d5db;
  border-radius: 4px;
  padding: 4px 8px;
  font-size: 8.5pt;
  color: #333;
}
.controls button {
  background: #1e3a5f;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 5px 14px;
  font-size: 8.5pt;
  cursor: pointer;
}
.btn-print {
  background: #166534;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 5px 14px;
  font-size: 8.5pt;
  cursor: pointer;
  font-weight: 600;
}

/* ── PAGE ── */
.page {
  width: 297mm;
  min-height: 210mm;
  margin: 16px auto;
  padding: 10mm 12mm;
  background: #fff;
  box-shadow: 0 1px 4px rgba(0,0,0,.1);
}

/* ── EN-TÊTE ── */
.header {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 12px;
  border-bottom: 2px solid #1e3a5f;
  padding-bottom: 8px;
  margin-bottom: 12px;
}
.soc-name  { font-size: 12pt; font-weight: 700; color: #1e3a5f; }
.soc-info  { font-size: 7pt; color: #777; margin-top: 3px; line-height: 1.5; }
.titre     { font-size: 13pt; font-weight: 700; color: #1e3a5f; text-transform: uppercase;
             letter-spacing: 2px; border: 2px solid #1e3a5f; padding: 5px 16px; white-space: nowrap; }
.header-right { text-align: right; font-size: 7.5pt; color: #555; line-height: 1.7; }
.periode-val  { font-size: 11pt; font-weight: 700; color: #1e3a5f; display: block; }

/* ── TABLEAU ── */
.livre-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 7pt;
  margin-bottom: 10px;
}

/* En-tête groupes */
.livre-table thead tr.grp th {
  padding: 3px 4px;
  font-size: 6.5pt;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  text-align: center;
  border: 1px solid #fff;
}
.grp .g-id    { background: #374151; color: #fff; }
.grp .g-emp   { background: #1e3a5f; color: #fff; }
.grp .g-gains { background: #1d4ed8; color: #fff; }
.grp .g-ret   { background: #991b1b; color: #fff; }
.grp .g-net   { background: #166534; color: #fff; }
.grp .g-pat   { background: #5b21b6; color: #fff; }

/* En-tête colonnes */
.livre-table thead tr.cols th {
  background: #f1f5f9;
  color: #374151;
  padding: 4px 4px;
  font-size: 6.5pt;
  font-weight: 700;
  text-align: right;
  border: 1px solid #d1d5db;
  white-space: nowrap;
}
.livre-table thead tr.cols th.left { text-align: left; }

/* Lignes données */
.livre-table tbody tr td {
  padding: 3px 4px;
  border: 1px solid #e5e7eb;
  text-align: right;
  vertical-align: middle;
  white-space: nowrap;
}
.livre-table tbody tr:nth-child(even) td { background: #fafafa; }
.livre-table tbody tr td.left { text-align: left; }
.livre-table tbody tr td.center { text-align: center; }
.livre-table tbody tr td.num { color: #9ca3af; font-size: 6.5pt; text-align: center; }

/* Ligne totaux */
.livre-table tfoot tr td {
  padding: 4px 4px;
  font-weight: 700;
  font-size: 7.5pt;
  border: 1px solid #cbd5e1;
  text-align: right;
  white-space: nowrap;
}
.livre-table tfoot .total-label { background: #1e3a5f; color: #fff; text-align: left; }
.livre-table tfoot .total-gains { background: #dbeafe; color: #1d4ed8; }
.livre-table tfoot .total-ret   { background: #fee2e2; color: #991b1b; }
.livre-table tfoot .total-net   { background: #dcfce7; color: #166534; font-size: 8.5pt; }
.livre-table tfoot .total-pat   { background: #ede9fe; color: #5b21b6; }
.livre-table tfoot .total-blank { background: #f1f5f9; }

/* ── FOOTER ── */
.footer-legal {
  border-top: 1px solid #e5e7eb;
  padding-top: 6px;
  font-size: 6.5pt;
  color: #bbb;
  text-align: center;
  line-height: 1.7;
}

/* ── RÉSUMÉ RAPIDE ── */
.recap {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 8px;
  margin-bottom: 10px;
}
.rc {
  border-radius: 4px;
  padding: 6px 10px;
  text-align: center;
  border: 1px solid;
}
.rc .lbl { font-size: 6.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; display: block; margin-bottom: 2px; }
.rc .val { font-size: 11pt; font-weight: 800; }
.rc-emp  { background: #eff6ff; border-color: #93c5fd; }
.rc-emp  .lbl { color: #1d4ed8; } .rc-emp  .val { color: #1e40af; }
.rc-brut { background: #f0f9ff; border-color: #7dd3fc; }
.rc-brut .lbl { color: #0369a1; } .rc-brut .val { color: #0c4a6e; }
.rc-ret  { background: #fff1f2; border-color: #fca5a5; }
.rc-ret  .lbl { color: #b91c1c; } .rc-ret  .val { color: #7f1d1d; }
.rc-net  { background: #f0fdf4; border-color: #86efac; }
.rc-net  .lbl { color: #166534; } .rc-net  .val { color: #14532d; }
.rc-pat  { background: #faf5ff; border-color: #c4b5fd; }
.rc-pat  .lbl { color: #5b21b6; } .rc-pat  .val { color: #3b0764; }

.amount { font-variant-numeric: tabular-nums; }

/* ── PRINT ── */
@media print {
  @page { size: A4 landscape; margin: 8mm 10mm; }
  html, body { background: #fff; font-size: 7pt; }
  .controls { display: none !important; }
  .page { margin: 0; padding: 0; width: auto; box-shadow: none; }
  .livre-table thead tr.grp th,
  .livre-table tfoot tr td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  .recap { margin-bottom: 8px; }
  .rc { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
</head>
<body>

@php
  // Totaux collection
  $t = [
    'nb'         => $bulletins->count(),
    'jours'      => $bulletins->sum('jours_travailles'),
    'base'       => $bulletins->sum(fn($b) => (float)$b->salaire_base),
    'anciennete' => $bulletins->sum(fn($b) => (float)$b->prime_anciennete),
    'primes'     => $bulletins->sum(fn($b) => (float)$b->total_primes),
    'brut'       => $bulletins->sum(fn($b) => $b->brut),
    'cnss'       => $bulletins->sum(fn($b) => (float)$b->cnss_salarie),
    'amo'        => $bulletins->sum(fn($b) => (float)$b->amo_salarie),
    'cimr'       => $bulletins->sum(fn($b) => (float)$b->cimr_salarie),
    'ir'         => $bulletins->sum(fn($b) => (float)$b->ir_mensuel),
    'avances'    => $bulletins->sum(fn($b) => (float)$b->avances_deduites),
    'retenues'   => $bulletins->sum(fn($b) => (float)$b->total_retenues),
    'net'        => $bulletins->sum(fn($b) => (float)$b->net_a_payer),
    'patronal'   => $bulletins->sum(fn($b) => (float)$b->total_patronal),
    'cout'       => $bulletins->sum(fn($b) => $b->cout_employeur),
  ];
@endphp

{{-- ── CONTRÔLES ÉCRAN ── --}}
<div class="controls no-print">
  <a href="{{ route('paie.index') }}">&#8592; Retour à la liste</a>

  <form method="GET" action="{{ route('paie.livre') }}">
    <label style="font-size:8pt;color:#555">Période :</label>
    <select name="mois">
      @foreach($moisNoms as $i => $nom)
        @if($i > 0)
          <option value="{{ $i }}" @selected($i === $mois)>{{ $nom }}</option>
        @endif
      @endforeach
    </select>
    <input type="number" name="annee" value="{{ $annee }}" min="2020" max="2099" style="width:70px">
    <button type="submit">Afficher</button>
  </form>

  <button class="btn-print" onclick="window.print()">&#9113; Imprimer</button>
</div>

<div class="page">

  {{-- ── EN-TÊTE ── --}}
  <div class="header">
    <div>
      <div class="soc-name">{{ $societe['societe.nom'] ?? config('app.name') }}</div>
      <div class="soc-info">
        @if(!empty($societe['societe.adresse'])){{ $societe['societe.adresse'] }}<br>@endif
        @if(!empty($societe['societe.ville'])){{ $societe['societe.ville'] }}@endif
        @if(!empty($societe['societe.cnss_employeur'])) &nbsp;&middot;&nbsp; CNSS empl. {{ $societe['societe.cnss_employeur'] }}@endif
        @if(!empty($societe['societe.ice'])) &nbsp;&middot;&nbsp; ICE {{ $societe['societe.ice'] }}@endif
      </div>
    </div>

    <div class="titre">Livre de Paie</div>

    <div class="header-right">
      <span class="periode-val">{{ $moisNom }} {{ $annee }}</span>
      Édité le {{ now()->format('d/m/Y') }}<br>
      {{ $t['nb'] }} bulletin(s) &nbsp;&middot;&nbsp; {{ $t['jours'] }} jours travaillés
    </div>
  </div>

  {{-- ── RÉSUMÉ ── --}}
  <div class="recap">
    <div class="rc rc-emp">
      <span class="lbl">Effectif payé</span>
      <span class="val">{{ $t['nb'] }}</span>
    </div>
    <div class="rc rc-brut">
      <span class="lbl">Masse Brute</span>
      <span class="val amount">{{ number_format($t['brut'], 0, ',', ' ') }} DH</span>
    </div>
    <div class="rc rc-ret">
      <span class="lbl">Total Retenues</span>
      <span class="val amount">{{ number_format($t['retenues'], 0, ',', ' ') }} DH</span>
    </div>
    <div class="rc rc-net">
      <span class="lbl">Masse Nette</span>
      <span class="val amount">{{ number_format($t['net'], 0, ',', ' ') }} DH</span>
    </div>
    <div class="rc rc-pat">
      <span class="lbl">Charges Patronales</span>
      <span class="val amount">{{ number_format($t['patronal'], 0, ',', ' ') }} DH</span>
    </div>
  </div>

  {{-- ── TABLEAU ── --}}
  <table class="livre-table">
    <thead>
      {{-- Groupes --}}
      <tr class="grp">
        <th class="g-id"    colspan="2">Identification</th>
        <th class="g-emp"   colspan="3">Employé</th>
        <th class="g-gains" colspan="4">Gains</th>
        <th class="g-ret"   colspan="5">Retenues salariales</th>
        <th class="g-net"   colspan="1">Net</th>
        <th class="g-pat"   colspan="2">Patronal</th>
      </tr>
      {{-- Colonnes --}}
      <tr class="cols">
        <th class="left">Matr.</th>
        <th class="left">Statut</th>
        <th class="left" style="min-width:110px">Nom &amp; Prénom</th>
        <th class="left" style="min-width:80px">Poste</th>
        <th class="center">Jrs</th>
        <th>Sal. Base</th>
        <th>Anc.</th>
        <th>Primes</th>
        <th>BRUT</th>
        <th>CNSS</th>
        <th>AMO</th>
        <th>CIMR</th>
        <th>IR</th>
        <th>Avances</th>
        <th style="background:#fef2f2">Total Ret.</th>
        <th style="background:#dcfce7;font-size:7.5pt">NET À PAYER</th>
        <th>Part Pat.</th>
        <th>Coût Empl.</th>
      </tr>
    </thead>
    <tbody>
      @forelse($bulletins as $b)
      <tr>
        <td class="left">{{ $b->employe->matricule }}</td>
        <td class="center">
          <span style="font-size:6pt;font-weight:600;color:{{ $b->statut === 'paye' ? '#166534' : ($b->statut === 'valide' ? '#1d4ed8' : '#b91c1c') }}">
            {{ strtoupper(substr($b->statut_libelle, 0, 3)) }}
          </span>
        </td>
        <td class="left">{{ $b->employe->nom_complet }}</td>
        <td class="left" style="color:#555">{{ $b->employe->fichePoste?->intitule ?? '—' }}</td>
        <td class="center">{{ $b->jours_travailles }}</td>
        <td class="amount">{{ number_format((float)$b->salaire_base, 2, ',', ' ') }}</td>
        <td class="amount">{{ (float)$b->prime_anciennete > 0 ? number_format((float)$b->prime_anciennete, 2, ',', ' ') : '—' }}</td>
        <td class="amount">{{ (float)$b->total_primes > 0 ? number_format((float)$b->total_primes, 2, ',', ' ') : '—' }}</td>
        <td class="amount" style="font-weight:600;color:#1e3a5f">{{ number_format($b->brut, 2, ',', ' ') }}</td>
        <td class="amount">{{ number_format((float)$b->cnss_salarie, 2, ',', ' ') }}</td>
        <td class="amount">{{ number_format((float)$b->amo_salarie, 2, ',', ' ') }}</td>
        <td class="amount">{{ (float)$b->cimr_salarie > 0 ? number_format((float)$b->cimr_salarie, 2, ',', ' ') : '—' }}</td>
        <td class="amount">{{ number_format((float)$b->ir_mensuel, 2, ',', ' ') }}</td>
        <td class="amount">{{ (float)$b->avances_deduites > 0 ? number_format((float)$b->avances_deduites, 2, ',', ' ') : '—' }}</td>
        <td class="amount" style="color:#991b1b;font-weight:600">{{ number_format((float)$b->total_retenues, 2, ',', ' ') }}</td>
        <td class="amount" style="color:#166534;font-weight:700;font-size:7.5pt">{{ number_format((float)$b->net_a_payer, 2, ',', ' ') }}</td>
        <td class="amount" style="color:#5b21b6">{{ number_format((float)$b->total_patronal, 2, ',', ' ') }}</td>
        <td class="amount" style="color:#5b21b6;font-weight:600">{{ number_format($b->cout_employeur, 2, ',', ' ') }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="18" style="text-align:center;padding:20px;color:#9ca3af">
          Aucun bulletin pour {{ $moisNom }} {{ $annee }}
        </td>
      </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr>
        <td class="total-label" colspan="4">TOTAUX &mdash; {{ $t['nb'] }} employés &mdash; {{ $t['jours'] }} jours</td>
        <td class="total-blank"></td>
        <td class="total-gains amount">{{ number_format($t['base'], 2, ',', ' ') }}</td>
        <td class="total-gains amount">{{ number_format($t['anciennete'], 2, ',', ' ') }}</td>
        <td class="total-gains amount">{{ number_format($t['primes'], 2, ',', ' ') }}</td>
        <td class="total-gains amount" style="font-size:8pt">{{ number_format($t['brut'], 2, ',', ' ') }}</td>
        <td class="total-ret amount">{{ number_format($t['cnss'], 2, ',', ' ') }}</td>
        <td class="total-ret amount">{{ number_format($t['amo'], 2, ',', ' ') }}</td>
        <td class="total-ret amount">{{ number_format($t['cimr'], 2, ',', ' ') }}</td>
        <td class="total-ret amount">{{ number_format($t['ir'], 2, ',', ' ') }}</td>
        <td class="total-ret amount">{{ number_format($t['avances'], 2, ',', ' ') }}</td>
        <td class="total-ret amount">{{ number_format($t['retenues'], 2, ',', ' ') }}</td>
        <td class="total-net amount">{{ number_format($t['net'], 2, ',', ' ') }}</td>
        <td class="total-pat amount">{{ number_format($t['patronal'], 2, ',', ' ') }}</td>
        <td class="total-pat amount">{{ number_format($t['cout'], 2, ',', ' ') }}</td>
      </tr>
    </tfoot>
  </table>

  {{-- ── FOOTER ── --}}
  <div class="footer-legal">
    Livre de paie établi conformément à l'article 371 du Code du Travail marocain &mdash; Conservation obligatoire : 5 ans.<br>
    {{ $societe['societe.nom'] ?? config('app.name') }}
    @if(!empty($societe['societe.ice'])) &nbsp;&middot;&nbsp; ICE : {{ $societe['societe.ice'] }}@endif
    @if(!empty($societe['societe.cnss_employeur'])) &nbsp;&middot;&nbsp; CNSS employeur : {{ $societe['societe.cnss_employeur'] }}@endif
    &nbsp;&middot;&nbsp; Période : {{ $moisNom }} {{ $annee }}
  </div>

</div>{{-- /page --}}
</body>
</html>
