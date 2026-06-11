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

.preambule { background: #f4f7fb; border-left: 3pt solid #1e3a5f; padding: 10pt 14pt; margin-bottom: 16pt; font-size: 10pt; color: #333; border-radius: 0 3pt 3pt 0; }

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

<div class="header">
    <div>
        @php $societe = App\Models\ParametrageRh::getGroupe('societe'); @endphp
        <div class="societe-nom">{{ $societe['societe.nom'] ?? config('app.name') }}</div>
        <div class="societe-info">
            {{ $societe['societe.forme_juridique'] ?? '' }}<br>
            {{ $societe['societe.adresse'] ?? '' }}@if(!empty($societe['societe.ville'])), {{ $societe['societe.ville'] }}@endif<br>
            @if(!empty($societe['societe.ice']))ICE : {{ $societe['societe.ice'] }}@endif
            @if(!empty($societe['societe.rc'])) — RC : {{ $societe['societe.rc'] }}@endif
            @if(!empty($societe['societe.cnss_patron'])) — CNSS : {{ $societe['societe.cnss_patron'] }}@endif
        </div>
    </div>
    <div class="ref-block">
        <div class="ref-label">Référence contrat</div>
        <div class="ref-value">{{ $contrat->reference }}</div>
        <div style="font-size:9pt;color:#888;margin-top:4pt;">{{ now()->locale('fr')->isoFormat('D MMMM YYYY') }}</div>
    </div>
</div>

<div class="titre">
    <h1>Contrat de travail — {{ $contrat->type }}</h1>
    <div class="sous-titre">
        @if($contrat->type === 'CDI') Contrat à durée indéterminée
        @else Contrat à durée déterminée — {{ $contrat->duree_mois ? $contrat->duree_mois.' mois' : ($contrat->date_fin ? 'jusqu\'au '.$contrat->date_fin->format('d/m/Y') : '') }}
        @endif
    </div>
</div>

<div class="preambule">
    Entre la société <strong>{{ $societe['societe.nom'] ?? config('app.name') }}</strong>{{ !empty($societe['societe.forme_juridique']) ? ', '.$societe['societe.forme_juridique'] : '' }},
    ci-après dénommée « l'Employeur »,<br>
    et <strong>{{ $contrat->employe->nom_complet }}</strong>, Matricule n° {{ $contrat->employe->matricule }}{{ $contrat->employe->cin ? ', CIN '.$contrat->employe->cin : '' }},
    ci-après dénommé(e) « le Salarié »,<br>
    il a été convenu ce qui suit :
</div>

@foreach($clauses as $clause)
<div class="clause">
    <div class="clause-titre">{{ $clause['titre'] }}</div>
    <div class="clause-contenu">{!! nl2br(e($clause['contenu'])) !!}</div>
</div>
@endforeach

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
    {{ $societe['societe.nom'] ?? config('app.name') }} — Contrat {{ $contrat->reference }} — Confidentiel
</div>
</body>
</html>
