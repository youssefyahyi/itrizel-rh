<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11pt; color: #1a1a1a; line-height: 1.55; }
.page { padding: 30mm 22mm 25mm 22mm; }

.header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24pt; padding-bottom: 12pt; border-bottom: 2px solid #1e3a5f; }
.societe-nom { font-size: 16pt; font-weight: bold; color: #1e3a5f; }
.societe-info { font-size: 9pt; color: #555; margin-top: 4pt; }
.ref-block { text-align: right; }
.ref-label { font-size: 8pt; color: #888; text-transform: uppercase; letter-spacing: 0.5pt; }
.ref-value { font-size: 11pt; font-weight: bold; font-family: 'Courier New', monospace; color: #1e3a5f; }

.titre { text-align: center; margin: 18pt 0; }
.titre h1 { font-size: 15pt; font-weight: bold; color: #1e3a5f; text-transform: uppercase; letter-spacing: 1pt; }
.titre .sous-titre { font-size: 10pt; color: #555; margin-top: 4pt; }

.info-box { background: #f4f7fb; border: 1pt solid #d0dce8; border-radius: 3pt; padding: 10pt 14pt; margin-bottom: 16pt; }
.info-box-titre { font-size: 9pt; font-weight: bold; color: #1e3a5f; text-transform: uppercase; letter-spacing: 0.3pt; margin-bottom: 8pt; }
.info-row { display: flex; gap: 16pt; font-size: 10pt; margin-bottom: 4pt; }
.info-label { color: #666; min-width: 100pt; font-size: 9.5pt; }
.info-value { font-weight: 600; color: #1a1a1a; }

.modification-box { background: #fffbf0; border: 1pt solid #e8c840; border-radius: 3pt; padding: 10pt 14pt; margin-bottom: 16pt; }
.mod-label { font-size: 9pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3pt; color: #7a6000; margin-bottom: 8pt; }
.mod-row { display: flex; align-items: center; gap: 14pt; font-size: 10pt; margin-top: 6pt; }
.mod-before { color: #888; text-decoration: line-through; }
.mod-arrow { font-size: 14pt; color: #1e3a5f; }
.mod-after { color: #1e3a5f; font-weight: bold; font-size: 12pt; }

.clause { margin-bottom: 14pt; page-break-inside: avoid; }
.clause-titre { font-size: 10.5pt; font-weight: bold; color: #1e3a5f; text-transform: uppercase; letter-spacing: 0.3pt; margin-bottom: 5pt; border-bottom: 1px solid #d0dce8; padding-bottom: 3pt; }
.clause-contenu { font-size: 10pt; color: #222; text-align: justify; line-height: 1.6; }

.signature-block { margin-top: 36pt; page-break-inside: avoid; }
.signature-titre { font-size: 10pt; font-weight: bold; color: #555; text-transform: uppercase; letter-spacing: 0.5pt; margin-bottom: 14pt; text-align: center; }
.signature-grid { display: flex; justify-content: space-between; gap: 20pt; }
.signature-col { flex: 1; }
.signature-label { font-size: 9pt; font-weight: bold; color: #333; text-align: center; margin-bottom: 6pt; }
.signature-zone { height: 48pt; border-bottom: 1px solid #aaa; margin-bottom: 4pt; }
.signature-note { font-size: 8pt; color: #888; text-align: center; }

.footer { position: fixed; bottom: 0; left: 22mm; right: 22mm; text-align: center; font-size: 8pt; color: #aaa; padding-top: 6pt; border-top: 1px solid #ddd; }
</style>
</head>
<body>
<div class="page">

@php $societe = App\Models\ParametrageRh::getGroupe('societe'); $contrat = $avenant->contrat; @endphp

<div class="header">
    <div>
        <div class="societe-nom">{{ $societe['societe.nom'] ?? config('app.name') }}</div>
        <div class="societe-info">
            {{ $societe['societe.forme_juridique'] ?? '' }}
            @if(!empty($societe['societe.capital'])) — Capital : {{ $societe['societe.capital'] }} DH @endif<br>
            {{ $societe['societe.adresse'] ?? '' }}@if(!empty($societe['societe.ville'])), {{ $societe['societe.ville'] }}@endif
        </div>
    </div>
    <div class="ref-block">
        <div class="ref-label">Référence avenant</div>
        <div class="ref-value">{{ $avenant->reference }}</div>
        <div style="font-size:9pt;color:#888;margin-top:4pt;">{{ now()->locale('fr')->isoFormat('D MMMM YYYY') }}</div>
    </div>
</div>

<div class="titre">
    <h1>Avenant au contrat de travail</h1>
    <div class="sous-titre">{{ App\Models\Avenant::NATURES[$avenant->nature] ?? $avenant->nature }}</div>
</div>

<div class="info-box">
    <div class="info-box-titre">Parties concernées</div>
    <div class="info-row">
        <span class="info-label">Employeur :</span>
        <span class="info-value">{{ $societe['societe.nom'] ?? config('app.name') }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Salarié :</span>
        <span class="info-value">{{ $contrat->employe->nom_complet }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Matricule :</span>
        <span class="info-value">{{ $contrat->employe->matricule }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Contrat de base :</span>
        <span class="info-value">{{ $contrat->reference }} ({{ $contrat->type }})</span>
    </div>
    <div class="info-row">
        <span class="info-label">Date d'effet :</span>
        <span class="info-value">{{ $avenant->date_effet->format('d/m/Y') }}</span>
    </div>
</div>

@if($avenant->ancienne_valeur || $avenant->nouvelle_valeur)
<div class="modification-box">
    <div class="mod-label">Modification apportée</div>
    <div class="mod-row">
        <span class="mod-before">{{ $avenant->ancienne_valeur }}</span>
        <span class="mod-arrow">→</span>
        <span class="mod-after">{{ $avenant->nouvelle_valeur }}</span>
    </div>
</div>
@endif

@if($avenant->motif)
<div class="clause">
    <div class="clause-titre">Motif</div>
    <div class="clause-contenu">{{ $avenant->motif }}</div>
</div>
@endif

@foreach($clauses as $clause)
<div class="clause">
    <div class="clause-titre">{{ $clause['titre'] }}</div>
    <div class="clause-contenu">{!! nl2br(e($clause['contenu'])) !!}</div>
</div>
@endforeach

<div style="font-size:10pt;color:#333;margin-bottom:20pt;line-height:1.7;">
    Toutes les autres clauses du contrat de travail {{ $contrat->reference }} en date du {{ $contrat->date_debut->format('d/m/Y') }} demeurent inchangées et continuent de produire leurs effets.
</div>

<div class="signature-block">
    <div class="signature-titre">Signatures des parties</div>
    <div class="signature-grid">
        <div class="signature-col">
            <div class="signature-label">Pour l'Employeur</div>
            <div class="signature-zone"></div>
            <div class="signature-note">Nom, prénom, qualité et cachet</div>
        </div>
        <div class="signature-col">
            <div class="signature-label">Le Salarié</div>
            <div class="signature-zone"></div>
            <div class="signature-note">{{ $contrat->employe->nom_complet }} — Lu et approuvé</div>
        </div>
    </div>
    <div style="text-align:center;font-size:9pt;color:#888;margin-top:10pt;">
        Fait en deux exemplaires originaux, à {{ $societe['societe.ville'] ?? '___________' }}, le {{ now()->locale('fr')->isoFormat('D MMMM YYYY') }}
    </div>
</div>

</div>

<div class="footer">
    {{ $societe['societe.nom'] ?? config('app.name') }} — Avenant {{ $avenant->reference }} — Confidentiel
</div>
</body>
</html>
