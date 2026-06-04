<x-app-layout>
<x-rh.page-header
    :title="$contrat->reference"
    :breadcrumbs="['Contrats' => route('contrats.index'), $contrat->reference => null]">
    <a href="{{ route('contrats.edit', $contrat) }}" class="tb-btn">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Modifier
    </a>
    <a href="{{ route('contrats.index') }}" class="tb-btn">← Retour</a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if(session('success'))<x-rh.alert type="success" :message="session('success')" />@endif

@if($contrat->alert_expiration)
<div style="background:var(--warning-light);border:1px solid var(--warning);border-radius:var(--radius-sm);padding:10px 14px;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--warning);">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <strong>Attention :</strong> ce contrat expire le {{ $contrat->date_fin->format('d/m/Y') }}
    ({{ $contrat->jours_restants > 0 ? 'dans '.$contrat->jours_restants.' jour(s)' : 'expiré' }})
</div>
@endif

{{-- Hero --}}
<div class="emp-hero">
    <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
            <span style="font-size:18px;font-weight:700;color:var(--text-primary);font-family:monospace;">{{ $contrat->reference }}</span>
            <span class="badge {{ $contrat->statut_badge }}"><span class="dot"></span>{{ $contrat->statut_libelle }}</span>
            <span class="badge {{ $contrat->type === 'CDI' ? 'bg' : 'bb' }}">{{ $contrat->type }}</span>
        </div>
        <div style="font-size:13px;color:var(--text-secondary);">{{ $contrat->poste }} — <a href="{{ route('personnel.show', $contrat->employe) }}" class="link">{{ $contrat->employe->nom_complet }}</a></div>
        <div style="display:flex;gap:20px;margin-top:8px;flex-wrap:wrap;">
            <span class="hero-meta"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg> Du {{ $contrat->date_debut->format('d/m/Y') }} @if($contrat->date_fin) au {{ $contrat->date_fin->format('d/m/Y') }} @else (illimité) @endif</span>
            <span class="hero-meta"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg> {{ number_format($contrat->salaire_base, 0, ',', ' ') }} DH / mois</span>
        </div>
    </div>
</div>

{{-- Détails --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div class="info-card">
        <div class="info-card-title">Détails du contrat</div>
        <x-rh.detail-row label="Référence"      :value="$contrat->reference" />
        <x-rh.detail-row label="Type"           :value="$contrat->type" />
        <x-rh.detail-row label="Catégorie"      :value="\App\Models\Employe::CATEGORIES[$contrat->categorie] ?? $contrat->categorie" />
        <x-rh.detail-row label="Poste"          :value="$contrat->poste" />
        <x-rh.detail-row label="Date de début"  :value="$contrat->date_debut->format('d/m/Y')" />
        <x-rh.detail-row label="Date de fin"    :value="$contrat->date_fin?->format('d/m/Y') ?? 'CDI — Sans échéance'" />
        @if($contrat->duree_mois)<x-rh.detail-row label="Durée" :value="$contrat->duree_mois.' mois'" />@endif
        <x-rh.detail-row label="Renouvellement auto" :value="$contrat->renouvellement_auto ? 'Oui' : 'Non'" />
    </div>

    <div class="info-card">
        <div class="info-card-title">Employé</div>
        <x-rh.detail-row label="Nom complet"    :value="$contrat->employe->nom_complet" />
        <x-rh.detail-row label="Matricule"      :value="$contrat->employe->matricule" />
        <x-rh.detail-row label="Catégorie"      :value="$contrat->employe->categorie_libelle" />
        <div style="padding:12px 0 0;">
            <a href="{{ route('personnel.show', $contrat->employe) }}" class="tb-btn">Voir la fiche employé</a>
        </div>
    </div>
</div>

@if($contrat->observations)
<div class="info-card">
    <div class="info-card-title">Observations</div>
    <p style="font-size:13px;color:var(--text-secondary);line-height:1.6;padding-top:4px;">{{ $contrat->observations }}</p>
</div>
@endif

</div>

<style>
.emp-hero{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;display:flex;align-items:flex-start;gap:18px;box-shadow:var(--shadow-sm);}
.hero-meta{display:inline-flex;align-items:center;gap:5px;font-size:12px;color:var(--text-secondary);}
.info-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);}
.info-card-title{font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border-light);}
</style>
</x-app-layout>
