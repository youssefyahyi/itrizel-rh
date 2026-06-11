<x-app-layout>
<x-rh.page-header
    :breadcrumbs="['Contrats' => route('contrats.index'), $contrat->reference => null]">
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if(session('success'))<x-rh.alert type="success" :message="session('success')" />@endif
@if(session('error'))<x-rh.alert type="danger" :message="session('error')" />@endif

@if($contrat->alert_expiration)
<div style="background:var(--warning-light);border:1px solid var(--warning);border-radius:var(--radius-sm);padding:10px 14px;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--warning);">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    <strong>Attention :</strong> ce contrat expire le {{ $contrat->date_fin->format('d/m/Y') }}
    ({{ $contrat->jours_restants > 0 ? 'dans '.$contrat->jours_restants.' jour(s)' : 'expiré' }})
</div>
@endif

@if($contrat->parent)
<div style="background:var(--surface-soft);border:1px solid var(--border-light);border-radius:var(--radius-sm);padding:10px 14px;font-size:13px;color:var(--text-secondary);">
    Renouvellement du contrat
    <a href="{{ route('contrats.show', $contrat->parent) }}" class="link mono">{{ $contrat->parent->reference }}</a>
</div>
@endif

{{-- Hero --}}
<div class="emp-hero">
    <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
            <span style="font-size:18px;font-weight:700;color:var(--text-primary);font-family:monospace;">{{ $contrat->reference }}</span>
            <span class="badge {{ $contrat->statut_badge }}"><span class="dot"></span>{{ $contrat->statut_libelle }}</span>
            <span class="badge {{ $contrat->type === 'CDI' ? 'bg' : 'bb' }}">{{ $contrat->type }}</span>
            @if($contrat->pdf_path)<span class="badge bb">PDF stocké</span>@endif
        </div>
        <div style="font-size:13px;color:var(--text-secondary);">
            {{ $contrat->fichePoste?->poste->nom ?? '—' }} —
            <span style="font-weight:500;color:var(--text-primary);">{{ $contrat->employe->nom_complet }}</span>
        </div>
        <div style="display:flex;gap:20px;margin-top:8px;flex-wrap:wrap;">
            <span class="hero-meta">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Du {{ $contrat->date_debut->format('d/m/Y') }} @if($contrat->date_fin) au {{ $contrat->date_fin->format('d/m/Y') }} @else (illimité) @endif
            </span>
            <span class="hero-meta">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                {{ number_format($contrat->salaire_base, 0, ',', ' ') }} DH / mois
            </span>
        </div>
    </div>
    {{-- Boutons d'action --}}
    <div style="display:flex;gap:8px;flex-shrink:0;align-items:flex-start;">
        @if($contrat->statut === 'en_cours' && $contrat->type !== 'interim')
        <form method="POST" action="{{ route('contrats.pdf', $contrat) }}" style="display:inline;">
            @csrf
            @foreach(App\Models\ClauseContrat::actives()->pourType($contrat->type)->where('obligatoire', false)->get() as $cl)
            <input type="hidden" name="clauses[]" value="{{ $cl->id }}">
            @endforeach
            <button type="submit" class="btn btn-outline btn-sm">PDF</button>
        </form>
        <a href="{{ route('avenants.create', $contrat) }}" class="btn btn-outline btn-sm">Avenant</a>
        <a href="{{ route('contrats.renouveler', $contrat) }}" class="btn btn-outline btn-sm">Renouveler</a>
        @endif
        <a href="{{ route('contrats.edit', $contrat) }}" class="btn btn-primary btn-sm">Modifier</a>
    </div>
</div>

{{-- Détails --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div class="info-card">
        <div class="info-card-title">Détails du contrat</div>
        <x-rh.detail-row label="Référence"           :value="$contrat->reference" />
        <x-rh.detail-row label="Type"                :value="$contrat->type" />
        <x-rh.detail-row label="Catégorie"           :value="$contrat->fichePoste?->categorie->nom ?? '—'" />
        <x-rh.detail-row label="Poste"               :value="$contrat->fichePoste?->poste->nom ?? '—'" />
        <x-rh.detail-row label="Date de début"       :value="$contrat->date_debut->format('d/m/Y')" />
        <x-rh.detail-row label="Date de fin"         :value="$contrat->date_fin?->format('d/m/Y') ?? 'CDI — Sans échéance'" />
        @if($contrat->duree_mois)<x-rh.detail-row label="Durée" :value="$contrat->duree_mois.' mois'" />@endif
        <x-rh.detail-row label="Calendrier congés"   :value="$contrat->calendrier_conges ? ucfirst($contrat->calendrier_conges) : 'Par défaut société'" />
        <x-rh.detail-row label="Renouvellement auto" :value="$contrat->renouvellement_auto ? 'Oui' : 'Non'" />
    </div>

    <div class="info-card">
        <div class="info-card-title">Employé</div>
        <x-rh.detail-row label="Nom complet" :value="$contrat->employe->nom_complet" />
        <x-rh.detail-row label="Matricule"   :value="$contrat->employe->matricule" />
        <x-rh.detail-row label="Catégorie"   :value="$contrat->employe->fichePoste?->categorie->nom ?? '—'" />
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

{{-- Avenants --}}
@if($contrat->avenants->count())
<div class="info-card">
    <div class="info-card-title">Avenants ({{ $contrat->avenants->count() }})</div>
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="color:var(--text-secondary);font-size:11px;text-transform:uppercase;letter-spacing:.5px;">
                <th style="padding:6px 0;text-align:left;font-weight:600;">Référence</th>
                <th style="padding:6px 0;text-align:left;font-weight:600;">Nature</th>
                <th style="padding:6px 0;text-align:left;font-weight:600;">Date d'effet</th>
                <th style="padding:6px 0;text-align:left;font-weight:600;">Modification</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($contrat->avenants as $av)
        <tr style="border-top:1px solid var(--border-light);">
            <td style="padding:8px 0;font-family:monospace;font-size:12px;">{{ $av->reference }}</td>
            <td style="padding:8px 0;">{{ App\Models\Avenant::NATURES[$av->nature] ?? $av->nature }}</td>
            <td style="padding:8px 0;">{{ $av->date_effet->format('d/m/Y') }}</td>
            <td style="padding:8px 0;color:var(--text-secondary);">{{ $av->ancienne_valeur }} → {{ $av->nouvelle_valeur }}</td>
            <td style="padding:8px 0;text-align:right;">
                <a href="{{ route('avenants.show', $av) }}" class="link" style="font-size:12px;">Voir</a>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Renouvellements --}}
@if($contrat->renouvellements->count())
<div class="info-card">
    <div class="info-card-title">Renouvellements ({{ $contrat->renouvellements->count() }})</div>
    @foreach($contrat->renouvellements as $rv)
    <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-top:1px solid var(--border-light);font-size:13px;">
        <a href="{{ route('contrats.show', $rv) }}" class="link mono">{{ $rv->reference }}</a>
        <span class="badge {{ $rv->statut_badge }}">{{ $rv->statut_libelle }}</span>
        <span style="color:var(--text-secondary);">du {{ $rv->date_debut->format('d/m/Y') }}</span>
    </div>
    @endforeach
</div>
@endif

</div>

<style>
.emp-hero{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;display:flex;align-items:flex-start;gap:18px;box-shadow:var(--shadow-sm);}
.hero-meta{display:inline-flex;align-items:center;gap:5px;font-size:12px;color:var(--text-secondary);}
.info-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);}
.info-card-title{font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border-light);}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;text-decoration:none;}
</style>
</x-app-layout>
