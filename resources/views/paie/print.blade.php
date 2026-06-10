<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Bulletin de Paie — {{ $paie->employe->nom_complet }} — {{ $paie->periode_libelle }}</title>
<style>
/* ═══════════════════════════════════════════════
   BASE
═══════════════════════════════════════════════ */
* { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
  font-family: Arial, sans-serif;
  font-size: 9pt;
  color: #1a1a1a;
  background: #f5f5f5;
}

.page {
  width: 210mm;
  min-height: 297mm;
  margin: 0 auto;
  padding: 10mm 12mm;
  background: #fff;
}

.amount { font-variant-numeric: tabular-nums; }
.fw7    { font-weight: 700; }
.muted  { color: #aaa; }

/* ═══════════════════════════════════════════════
   EN-TÊTE — 3 colonnes : société | titre | réf
═══════════════════════════════════════════════ */
.header {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  align-items: center;
  gap: 12px;
  padding-bottom: 8px;
  border-bottom: 2px solid #1e3a5f;
  margin-bottom: 10px;
}

.soc-name {
  font-size: 13pt;
  font-weight: 700;
  color: #1e3a5f;
  line-height: 1.2;
}
.soc-info {
  font-size: 7pt;
  color: #777;
  margin-top: 4px;
  line-height: 1.6;
}

.titre-bp {
  font-size: 13pt;
  font-weight: 700;
  color: #1e3a5f;
  text-transform: uppercase;
  letter-spacing: 3px;
  border: 2px solid #1e3a5f;
  padding: 6px 18px;
  white-space: nowrap;
}

.header-right {
  text-align: right;
  font-size: 8pt;
  color: #555;
  line-height: 1.7;
}
.periode-val {
  font-size: 11pt;
  font-weight: 700;
  color: #1e3a5f;
  display: block;
  margin-bottom: 2px;
}

/* ═══════════════════════════════════════════════
   GRILLE EMPLOYÉ — style WELCON épuré
═══════════════════════════════════════════════ */
.emp-grid {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 6px;
  font-size: 8pt;
}
.emp-grid th {
  background: #1e3a5f;
  color: #fff;
  padding: 4px 6px;
  font-size: 7pt;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.3px;
  border: 1px solid #1e3a5f;
  text-align: center;
  white-space: nowrap;
}
.emp-grid td {
  border: 1px solid #d1d5db;
  padding: 5px 7px;
  background: #f9fafb;
  text-align: center;
  color: #333;
}
.emp-grid td.emp-nom {
  background: #eff6ff;
  font-weight: 700;
  font-size: 10pt;
  color: #1e3a5f;
  text-align: left;
  letter-spacing: 0.2px;
}

/* ═══════════════════════════════════════════════
   TABLEAU RUBRIQUES
═══════════════════════════════════════════════ */
.rubr {
  width: 100%;
  border-collapse: collapse;
  font-size: 8.5pt;
  margin-bottom: 0;
}

/* En-tête colonnes */
.rubr thead th {
  background: #1e3a5f;
  color: #fff;
  padding: 5px 8px;
  font-size: 7.5pt;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  border: 1px solid #1e3a5f;
}
.rubr thead th.col-gains    { background: #1e3a5f; }
.rubr thead th.col-retenues { background: #3b1f1f; }

/* Lignes section */
.rubr tr.sep td {
  background: #f0f4f8;
  padding: 3px 8px;
  font-size: 7pt;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  color: #1e3a5f;
  border: 1px solid #d1d5db;
}

/* Lignes données */
.rubr tbody tr td {
  padding: 4px 8px;
  border: 1px solid #e5e7eb;
  vertical-align: middle;
}
.rubr tbody tr:nth-child(even):not(.sep) td { background: #fafafa; }

td.c-rub  { text-align: center; color: #9ca3af; font-size: 7.5pt; width: 5%; }
td.c-lib  { width: 38%; }
td.c-base { text-align: right; color: #6b7280; width: 15%; }
td.c-taux { text-align: center; color: #6b7280; width: 9%; white-space: nowrap; }
td.c-gain { text-align: right; font-weight: 600; width: 16%; color: #1e3a5f; }
td.c-ret  { text-align: right; font-weight: 600; width: 17%; color: #991b1b; }

/* Pied de tableau */
.rubr tfoot td {
  background: #f0f4f8;
  border: 1px solid #cbd5e1;
  font-weight: 700;
  font-size: 9pt;
  padding: 6px 8px;
}
.rubr tfoot .total-gains   { text-align: right; color: #1e3a5f; }
.rubr tfoot .total-ret     { text-align: right; color: #991b1b; }

/* ═══════════════════════════════════════════════
   BANDE RÉCAP — style ligne de synthèse WELCON
═══════════════════════════════════════════════ */
.recap {
  display: grid;
  grid-template-columns: repeat(8, 1fr);
  border: 1.5px solid #1e3a5f;
  margin-top: 10px;
  border-radius: 2px;
  overflow: hidden;
}
.rc {
  text-align: center;
  padding: 0;
  border-right: 1px solid #c9d4e0;
}
.rc:last-child { border-right: none; }
.rc-lbl {
  display: block;
  font-size: 6.5pt;
  font-weight: 700;
  text-transform: uppercase;
  color: #fff;
  background: #1e3a5f;
  padding: 3px 4px;
  letter-spacing: 0.2px;
  border-bottom: 1px solid #2d4f7a;
}
.rc-val {
  display: block;
  font-size: 8.5pt;
  font-weight: 700;
  color: #1a1a1a;
  padding: 5px 4px;
}

/* ═══════════════════════════════════════════════
   BOX NET À PAYER
═══════════════════════════════════════════════ */
.net-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #1e3a5f;
  color: #fff;
  padding: 10px 22px;
  margin-top: 10px;
  border-radius: 3px;
}
.net-left {}
.net-label {
  font-size: 8.5pt;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 2px;
  opacity: .75;
}
.net-amount {
  font-size: 13pt;
  font-weight: 700;
  line-height: 1.2;
  letter-spacing: .3px;
}
.net-sub {
  font-size: 7pt;
  opacity: .6;
  margin-top: 3px;
}
.net-right { text-align: right; }
.cout-lbl  { font-size: 7pt; opacity: .65; text-transform: uppercase; letter-spacing: .4px; }
.cout-val  { font-size: 12pt; font-weight: 700; }

/* ═══════════════════════════════════════════════
   SIGNATURES
═══════════════════════════════════════════════ */
.signatures {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 20px;
  margin-top: 14px;
  padding-top: 10px;
  border-top: 1px solid #d1d5db;
}
.sig-block {
  text-align: center;
  font-size: 7.5pt;
  color: #777;
}
.sig-titre {
  font-size: 8pt;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .4px;
  color: #1e3a5f;
  margin-bottom: 26px;
}
.sig-line {
  border-top: 1px solid #d1d5db;
  padding-top: 4px;
  font-style: italic;
  color: #bbb;
}

/* ═══════════════════════════════════════════════
   FOOTER LÉGAL
═══════════════════════════════════════════════ */
.footer-legal {
  margin-top: 10px;
  border-top: 1px solid #e5e7eb;
  padding-top: 6px;
  font-size: 7pt;
  color: #bbb;
  text-align: center;
  line-height: 1.7;
}

/* ═══════════════════════════════════════════════
   IMPRESSION
═══════════════════════════════════════════════ */
@media print {
  @page { size: A4 portrait; margin: 8mm 10mm; }
  html, body { background: #fff; }
  .page { padding: 0; width: auto; box-shadow: none; }
  .no-print { display: none !important; }
  .net-box,
  .rubr thead th,
  .recap .rc-lbl,
  .rubr tr.sep td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}

/* ═══════════════════════════════════════════════
   BOUTON IMPRESSION
═══════════════════════════════════════════════ */
.btn-print {
  position: fixed;
  top: 16px; right: 16px;
  background: #1e3a5f;
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 8px 18px;
  font-size: 9pt;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
  z-index: 999;
  letter-spacing: .3px;
}
.btn-print:hover { background: #162d4a; }
@media print { .btn-print { display: none; } }
</style>
</head>
<body>

@php
  $emp  = $paie->employe;
  $brut = $calcul['_brut'];
@endphp

<a class="btn-print no-print" href="#" onclick="window.print();return false;">&#9113; Imprimer</a>

<div class="page">

{{-- ══════════════════════════════════════════
     EN-TÊTE
══════════════════════════════════════════ --}}
<div class="header">

  <div>
    <div class="soc-name">{{ $societe['societe.nom'] ?? config('app.name') }}</div>
    <div class="soc-info">
      @if(!empty($societe['societe.adresse'])){{ $societe['societe.adresse'] }}<br>@endif
      @if(!empty($societe['societe.ville'])){{ $societe['societe.ville'] }}@endif
      @if(!empty($societe['societe.telephone'])) &nbsp;&middot;&nbsp; Tél. {{ $societe['societe.telephone'] }}@endif
      <br>
      @if(!empty($societe['societe.ice']))ICE : {{ $societe['societe.ice'] }}@endif
      @if(!empty($societe['societe.if'])) &nbsp;&middot;&nbsp; IF : {{ $societe['societe.if'] }}@endif
      @if(!empty($societe['societe.cnss_employeur'])) &nbsp;&middot;&nbsp; CNSS empl. : {{ $societe['societe.cnss_employeur'] }}@endif
    </div>
  </div>

  <div class="titre-bp">Bulletin de Paie</div>

  <div class="header-right">
    <span class="periode-val">{{ $paie->periode_libelle }}</span>
    Réf : BP-{{ str_pad($paie->id, 5, '0', STR_PAD_LEFT) }}<br>
    Jours : {{ $paie->jours_travailles ?? 26 }} &nbsp;&middot;&nbsp; Heures : {{ $paie->heures_travailles ?? 191 }}<br>
    @if($paie->date_paiement)Payé le : {{ $paie->date_paiement->format('d/m/Y') }}<br>@endif
    Mode : Virement bancaire
  </div>

</div>{{-- /header --}}


{{-- ══════════════════════════════════════════
     GRILLE EMPLOYÉ — ligne 1
══════════════════════════════════════════ --}}
<table class="emp-grid">
  <thead>
    <tr>
      <th>Mle</th>
      <th style="width:30%">Nom &amp; Prénom</th>
      <th>CIN</th>
      <th>N° CNSS</th>
      <th>Situation</th>
      <th>Enf.</th>
      <th>Date Emb.</th>
      <th>Salaire Base</th>
      <th>Jours</th>
      <th>Heures</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>{{ $emp->matricule }}</td>
      <td class="emp-nom">{{ $emp->nom_complet }}</td>
      <td>{{ $emp->cin ?? '—' }}</td>
      <td>{{ $emp->numero_cnss ?? '—' }}</td>
      <td>{{ ucfirst($emp->situation_familiale ?? '—') }}</td>
      <td>{{ $emp->nombre_enfants ?? 0 }}</td>
      <td>{{ $emp->date_embauche?->format('d/m/Y') ?? '—' }}</td>
      <td class="amount fw7">{{ number_format($paie->salaire_base, 2, ',', ' ') }}</td>
      <td>{{ $paie->jours_travailles ?? 26 }}</td>
      <td>{{ $paie->heures_travailles ?? 191 }}</td>
    </tr>
  </tbody>
</table>

{{-- ligne 2 --}}
<table class="emp-grid" style="margin-bottom:10px">
  <thead>
    <tr>
      <th>Date Nais.</th>
      <th style="width:30%">Fonction / Poste</th>
      <th colspan="2">Département / Unité</th>
      <th>Ancienneté</th>
      <th colspan="2">Prime ancienneté</th>
      <th>N° AMO</th>
      <th colspan="2">Statut bulletin</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>{{ $emp->date_naissance?->format('d/m/Y') ?? '—' }}</td>
      <td>{{ $emp->fichePoste?->intitule ?? '—' }}</td>
      <td colspan="2">{{ $emp->unite?->nom ?? '—' }}</td>
      <td>{{ $calcul['_anciennete_ans'] }} an(s)</td>
      <td colspan="2">
        @if($calcul['_taux_anciennete'] > 0)
          {{ number_format($calcul['_taux_anciennete'], 0) }}% &mdash;
          <span class="amount fw7">{{ number_format($paie->prime_anciennete ?? 0, 2, ',', ' ') }} DH</span>
        @else
          —
        @endif
      </td>
      <td>{{ $emp->numero_amo ?? '—' }}</td>
      <td colspan="2">
        <span style="font-weight:600;text-transform:capitalize">{{ $paie->statut }}</span>
      </td>
    </tr>
  </tbody>
</table>


{{-- ══════════════════════════════════════════
     TABLEAU RUBRIQUES
══════════════════════════════════════════ --}}
<table class="rubr">
  <thead>
    <tr>
      <th style="width:5%;text-align:center">RUB</th>
      <th style="width:38%;text-align:left">LIBELLÉ</th>
      <th style="width:15%;text-align:right">NBR / BASE</th>
      <th style="width:9%;text-align:center">TAUX</th>
      <th class="col-gains"   style="width:16%;text-align:right">GAINS (DH)</th>
      <th class="col-retenues" style="width:17%;text-align:right;background:#3b1f1f">RETENUES (DH)</th>
    </tr>
  </thead>
  <tbody>

    {{-- ── GAINS ── --}}
    <tr class="sep"><td colspan="6">Gains</td></tr>

    <tr>
      <td class="c-rub">010</td>
      <td class="c-lib">Salaire de base ({{ $paie->jours_travailles ?? 26 }} jours travaillés)</td>
      <td class="c-base amount">{{ number_format($paie->jours_travailles ?? 26, 0) }}&nbsp;J</td>
      <td class="c-taux">—</td>
      <td class="c-gain amount">{{ number_format($paie->salaire_base, 2, ',', ' ') }}</td>
      <td class="c-ret muted">—</td>
    </tr>

    @if(($paie->prime_anciennete ?? 0) > 0)
    <tr>
      <td class="c-rub">015</td>
      <td class="c-lib">Prime d'ancienneté ({{ $calcul['_anciennete_ans'] }} ans)</td>
      <td class="c-base amount">{{ number_format($paie->salaire_base, 2, ',', ' ') }}</td>
      <td class="c-taux">{{ number_format($calcul['_taux_anciennete'], 0) }}%</td>
      <td class="c-gain amount">{{ number_format($paie->prime_anciennete, 2, ',', ' ') }}</td>
      <td class="c-ret muted">—</td>
    </tr>
    @endif

    @if(($paie->total_primes ?? 0) > 0)
    <tr>
      <td class="c-rub">019</td>
      <td class="c-lib">Primes et indemnités imposables</td>
      <td class="c-base amount">—</td>
      <td class="c-taux">—</td>
      <td class="c-gain amount">{{ number_format($paie->total_primes, 2, ',', ' ') }}</td>
      <td class="c-ret muted">—</td>
    </tr>
    @endif

    {{-- ── COTISATIONS ── --}}
    <tr class="sep"><td colspan="6">Cotisations sociales &amp; Impôt sur le revenu</td></tr>

    <tr>
      <td class="c-rub">196</td>
      <td class="c-lib">AMO &mdash; Assurance Maladie Obligatoire</td>
      <td class="c-base amount">{{ number_format($brut, 2, ',', ' ') }}</td>
      <td class="c-taux">{{ number_format($calcul['_amo_taux_sal'], 2) }}%</td>
      <td class="c-gain muted">—</td>
      <td class="c-ret amount">{{ number_format($paie->amo_salarie, 2, ',', ' ') }}</td>
    </tr>

    <tr>
      <td class="c-rub">197</td>
      <td class="c-lib">
        CNSS &mdash; Caisse Nationale de Sécurité Sociale
        @if($calcul['_base_assiet_cnss'] < $brut)
          <small class="muted">(plaf. {{ number_format($calcul['_cnss_plafond'], 0, ',', ' ') }} DH/mois)</small>
        @endif
      </td>
      <td class="c-base amount">{{ number_format($calcul['_base_assiet_cnss'], 2, ',', ' ') }}</td>
      <td class="c-taux">{{ number_format($calcul['_cnss_taux_sal'], 2) }}%</td>
      <td class="c-gain muted">—</td>
      <td class="c-ret amount">{{ number_format($paie->cnss_salarie, 2, ',', ' ') }}</td>
    </tr>

    @if($calcul['_cimr_actif'])
    <tr>
      <td class="c-rub">199</td>
      <td class="c-lib">CIMR &mdash; Caisse Inter. Marocaine des Retraites</td>
      <td class="c-base amount">{{ number_format($paie->salaire_base, 2, ',', ' ') }}</td>
      <td class="c-taux">{{ number_format($calcul['_cimr_taux_sal'], 2) }}%</td>
      <td class="c-gain muted">—</td>
      <td class="c-ret amount">{{ number_format($paie->cimr_salarie, 2, ',', ' ') }}</td>
    </tr>
    @endif

    <tr>
      <td class="c-rub">198</td>
      <td class="c-lib">
        IR &mdash; Impôt sur le Revenu (barème progressif)
        @if($calcul['_nb_charges'] > 0)
          <small class="muted">&mdash; {{ $calcul['_nb_charges'] }} charge(s) familiale(s) déduites</small>
        @endif
      </td>
      <td class="c-base amount">{{ number_format($calcul['net_imposable'], 2, ',', ' ') }}</td>
      <td class="c-taux">—</td>
      <td class="c-gain muted">—</td>
      <td class="c-ret amount">{{ number_format($paie->ir_mensuel, 2, ',', ' ') }}</td>
    </tr>

    @if(($paie->avances_deduites ?? 0) > 0)
    <tr>
      <td class="c-rub">210</td>
      <td class="c-lib">Avances sur salaire / Acomptes</td>
      <td class="c-base amount">—</td>
      <td class="c-taux">—</td>
      <td class="c-gain muted">—</td>
      <td class="c-ret amount">{{ number_format($paie->avances_deduites, 2, ',', ' ') }}</td>
    </tr>
    @endif

  </tbody>
  <tfoot>
    <tr>
      <td colspan="4" class="fw7" style="text-align:left;font-size:9pt">Total :</td>
      <td class="total-gains amount">{{ number_format($brut, 2, ',', ' ') }}</td>
      <td class="total-ret amount">{{ number_format($paie->total_retenues, 2, ',', ' ') }}</td>
    </tr>
  </tfoot>
</table>


{{-- ══════════════════════════════════════════
     BANDE RÉCAP — style WELCON
══════════════════════════════════════════ --}}
<div class="recap">
  <div class="rc">
    <span class="rc-lbl">Jours</span>
    <span class="rc-val amount">{{ $paie->jours_travailles ?? 26 }}</span>
  </div>
  <div class="rc">
    <span class="rc-lbl">Brut</span>
    <span class="rc-val amount">{{ number_format($brut, 2, ',', ' ') }}</span>
  </div>
  <div class="rc">
    <span class="rc-lbl">CNSS</span>
    <span class="rc-val amount">{{ number_format($paie->cnss_salarie, 2, ',', ' ') }}</span>
  </div>
  <div class="rc">
    <span class="rc-lbl">AMO</span>
    <span class="rc-val amount">{{ number_format($paie->amo_salarie, 2, ',', ' ') }}</span>
  </div>
  <div class="rc">
    <span class="rc-lbl">Net Impos.</span>
    <span class="rc-val amount">{{ number_format($calcul['net_imposable'], 2, ',', ' ') }}</span>
  </div>
  <div class="rc">
    <span class="rc-lbl">IGR / IR</span>
    <span class="rc-val amount">{{ number_format($paie->ir_mensuel, 2, ',', ' ') }}</span>
  </div>
  <div class="rc">
    <span class="rc-lbl">Retenues</span>
    <span class="rc-val amount">{{ number_format($paie->total_retenues, 2, ',', ' ') }}</span>
  </div>
  <div class="rc">
    <span class="rc-lbl">Coût Empl.</span>
    <span class="rc-val amount">{{ number_format($calcul['_cout_employeur'], 2, ',', ' ') }}</span>
  </div>
</div>


{{-- ══════════════════════════════════════════
     NET À PAYER
══════════════════════════════════════════ --}}
<div class="net-box">
  <div class="net-left">
    <div class="net-label">Net à Payer</div>
    <div class="net-amount amount">{{ number_format($paie->net_a_payer, 2, ',', ' ') }} DH</div>
    <div class="net-sub">
      Brut {{ number_format($brut, 2, ',', ' ') }} DH
      &minus; Retenues {{ number_format($paie->total_retenues, 2, ',', ' ') }} DH
    </div>
  </div>
  <div class="net-right">
    <div class="cout-lbl">Coût total employeur</div>
    <div class="cout-val amount">{{ number_format($calcul['_cout_employeur'], 2, ',', ' ') }} DH</div>
    <div class="net-sub">
      Brut + Patronal {{ number_format($paie->total_patronal, 2, ',', ' ') }} DH
    </div>
  </div>
</div>



{{-- ══════════════════════════════════════════
     PIED LÉGAL
══════════════════════════════════════════ --}}
<div class="footer-legal">
  Bulletin de paie établi conformément au Code du Travail marocain (Art. 370 et suivants) et à la Loi de Finances en vigueur.
  Délai de contestation : 3 ans (Art. 395 C.T.) &mdash; La remise du bulletin de paie est obligatoire.<br>
  CNSS salarié {{ number_format($calcul['_cnss_taux_sal'], 2) }}% (plafond {{ number_format($calcul['_cnss_plafond'], 0, ',', ' ') }} DH/mois)
  &nbsp;&middot;&nbsp; AMO {{ number_format($calcul['_amo_taux_sal'], 2) }}%
  &nbsp;&middot;&nbsp; IR barème progressif DGI &mdash; Exercice {{ $paie->periode_annee }}
  @if($calcul['_cimr_actif'])&nbsp;&middot;&nbsp; CIMR salarié {{ number_format($calcul['_cimr_taux_sal'], 2) }}%@endif
</div>

</div>{{-- /page --}}
</body>
</html>
