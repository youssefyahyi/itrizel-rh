<x-app-layout>

{{-- BREADCRUMB --}}
<div style="display:flex;align-items:center;gap:8px;padding:12px 24px;border-bottom:1px solid var(--border-light);background:var(--surface);">
    <a href="{{ route('personnel.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">Personnel</a>
    <span style="color:var(--text-muted);">›</span>
    <span style="font-size:13px;color:var(--text-primary);font-weight:500;">{{ $employe->nom_complet }}</span>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin:16px 24px 0;">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- FICHE HEADER --}}
<div style="margin:16px 24px;background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow-sm);padding:20px 24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;">
        <div style="display:flex;align-items:flex-start;gap:16px;flex:1;">
            {{-- Avatar --}}
            @if($employe->photo)
            <img src="{{ Storage::url($employe->photo) }}" style="width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--border);flex-shrink:0;">
            @else
            <div style="width:56px;height:56px;border-radius:50%;background:var(--accent);color:#fff;font-size:18px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                {{ strtoupper(substr($employe->prenom,0,1).substr($employe->nom,0,1)) }}
            </div>
            @endif
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap;">
                    <span class="badge {{ $employe->statut_badge }}"><span class="dot"></span>{{ ucfirst($employe->statut) }}</span>
                    <span class="badge bb">{{ $employe->categorie_libelle }}</span>
                </div>
                <h1 style="font-size:20px;font-weight:600;color:var(--text-primary);margin-bottom:3px;">{{ $employe->nom_complet }}</h1>
                <div style="font-size:13px;color:var(--text-muted);">{{ $employe->poste }} — <span class="mono" style="font-size:12px;">{{ $employe->matricule }}</span></div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <form method="POST" action="{{ route('personnel.toggle-statut', $employe) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn btn-outline btn-sm">
                    {{ $employe->statut === 'actif' ? 'Désactiver' : 'Réactiver' }}
                </button>
            </form>
            <a href="{{ route('personnel.edit', $employe) }}" class="btn btn-primary btn-sm">Modifier</a>
        </div>
    </div>
</div>

{{-- LAYOUT DEUX COLONNES --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;padding:0 24px 24px;align-items:start;">

    {{-- COLONNE PRINCIPALE --}}
    <div>
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('infos',this)">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Informations
            </button>
            <button class="tab-btn" onclick="switchTab('contrats',this)">
                Contrats <span class="tab-count">{{ $employe->contrats->count() }}</span>
            </button>
            <button class="tab-btn" onclick="switchTab('conges',this)">
                Congés <span class="tab-count">{{ $employe->conges->count() }}</span>
            </button>
            <button class="tab-btn" onclick="switchTab('paie',this)">
                Paie <span class="tab-count">{{ $employe->bulletinsPaie->count() }}</span>
            </button>
            <button class="tab-btn" onclick="switchTab('historique',this)">Historique</button>
        </div>

        {{-- ONGLET INFOS --}}
        <div class="tab-pane active" id="tab-infos">
            <div class="card">
                <div class="card-header"><span class="card-title">Identité</span></div>
                <form id="form-inline" method="POST" action="{{ route('personnel.update', $employe) }}" style="display:none;">
                    @csrf @method('PUT')
                </form>
                <div style="padding:4px 20px;">
                    <div class="info-row">
                        <span class="info-label">Nom</span>
                        <span class="info-value inline-field" data-field="nom" data-type="text">{{ $employe->nom }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Prénom</span>
                        <span class="info-value inline-field" data-field="prenom" data-type="text">{{ $employe->prenom }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">CIN</span>
                        <span class="info-value inline-field" data-field="cin" data-type="text" style="font-family:monospace;">{{ $employe->cin }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date de naissance</span>
                        <span class="info-value inline-field" data-field="date_naissance" data-type="date" data-value="{{ $employe->date_naissance?->format('Y-m-d') }}">{{ $employe->date_naissance?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Sexe</span>
                        <span class="info-value inline-field" data-field="sexe" data-type="select" data-value="{{ $employe->sexe }}" data-options='{"M":"Masculin","F":"Féminin"}'>{{ $employe->sexe === 'M' ? 'Masculin' : 'Féminin' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Situation familiale</span>
                        <span class="info-value inline-field" data-field="situation_familiale" data-type="select" data-value="{{ $employe->situation_familiale }}" data-options='{"celibataire":"Célibataire","marie":"Marié(e)","divorce":"Divorcé(e)","veuf":"Veuf/Veuve"}'>{{ ucfirst($employe->situation_familiale) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nombre d'enfants</span>
                        <span class="info-value inline-field" data-field="nombre_enfants" data-type="number">{{ $employe->nombre_enfants }}</span>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:16px;">
                <div class="card-header"><span class="card-title">Poste & Affectation</span></div>
                <div style="padding:4px 20px;">
                    <div class="info-row">
                        <span class="info-label">Catégorie</span>
                        <span class="info-value inline-field" data-field="categorie" data-type="select" data-value="{{ $employe->categorie }}" data-options='@json(\App\Models\Employe::CATEGORIES)'>{{ $employe->categorie_libelle }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Poste</span>
                        <span class="info-value inline-field" data-field="poste" data-type="text">{{ $employe->poste }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date d'embauche</span>
                        <span class="info-value inline-field" data-field="date_embauche" data-type="date" data-value="{{ $employe->date_embauche?->format('Y-m-d') }}">{{ $employe->date_embauche?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Diplôme</span>
                        <span class="info-value inline-field" data-field="diplome" data-type="text">{{ $employe->diplome ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Spécialité</span>
                        <span class="info-value inline-field" data-field="specialite" data-type="text">{{ $employe->specialite ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value inline-field" data-field="email" data-type="text">{{ $employe->email ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Téléphone</span>
                        <span class="info-value inline-field" data-field="telephone" data-type="text">{{ $employe->telephone ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Ville</span>
                        <span class="info-value inline-field" data-field="ville" data-type="text">{{ $employe->ville ?? '—' }}</span>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-top:16px;">
                <div class="card-header"><span class="card-title">Banque & Affiliation sociale</span></div>
                <div style="padding:4px 20px;">
                    <div class="info-row">
                        <span class="info-label">Banque</span>
                        <span class="info-value inline-field" data-field="banque" data-type="text">{{ $employe->banque ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">RIB</span>
                        <span class="info-value inline-field" data-field="rib" data-type="text" style="font-family:monospace;font-size:12px;">{{ $employe->rib ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">N° CNSS</span>
                        <span class="info-value inline-field" data-field="numero_cnss" data-type="text">{{ $employe->numero_cnss ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">N° AMO</span>
                        <span class="info-value inline-field" data-field="numero_amo" data-type="text">{{ $employe->numero_amo ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ONGLET CONTRATS --}}
        <div class="tab-pane" id="tab-contrats">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Historique des contrats</span>
                    <a href="{{ route('contrats.create') }}?employe_id={{ $employe->id }}" style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">+ Nouveau contrat</a>
                </div>
                @forelse($employe->contrats as $contrat)
                <a href="{{ route('contrats.show', $contrat) }}" class="related-item" style="text-decoration:none;">
                    <div class="related-item-left">
                        <div class="related-icon" style="background:var(--accent-light);color:var(--accent);">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div>
                            <div class="related-name">{{ $contrat->reference }} — {{ $contrat->type }}</div>
                            <div class="related-sub">{{ $contrat->date_debut->format('d/m/Y') }} @if($contrat->date_fin)→ {{ $contrat->date_fin->format('d/m/Y') }}@endif — {{ number_format($contrat->salaire_base,0,',',' ') }} DH</div>
                        </div>
                    </div>
                    <span class="badge {{ $contrat->statut_badge }}">{{ $contrat->statut_libelle }}</span>
                </a>
                @empty
                <div class="empty-state">Aucun contrat enregistré.</div>
                @endforelse
            </div>
        </div>

        {{-- ONGLET CONGÉS --}}
        <div class="tab-pane" id="tab-conges">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Derniers congés</span>
                    <a href="{{ route('conges.index') }}?employe_id={{ $employe->id }}" style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">Voir tout</a>
                </div>
                @forelse($employe->conges as $conge)
                <div class="related-item">
                    <div class="related-item-left">
                        <div class="related-icon" style="background:var(--warning-light);color:var(--warning);">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="related-name">{{ ucfirst(str_replace('_',' ',$conge->type)) }}</div>
                            <div class="related-sub">{{ $conge->date_debut->format('d/m/Y') }} → {{ $conge->date_fin->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    <span class="badge {{ $conge->statut === 'approuve' ? 'bg' : ($conge->statut === 'refuse' ? 'br' : 'by') }}">{{ ucfirst($conge->statut) }}</span>
                </div>
                @empty
                <div class="empty-state">Aucun congé enregistré.</div>
                @endforelse
            </div>
        </div>

        {{-- ONGLET PAIE --}}
        <div class="tab-pane" id="tab-paie">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Bulletins de paie</span>
                    <a href="{{ route('paie.index') }}?employe_id={{ $employe->id }}" style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">Voir tout</a>
                </div>
                @forelse($employe->bulletinsPaie as $bulletin)
                <div class="related-item">
                    <div class="related-item-left">
                        <div class="related-icon" style="background:var(--success-light);color:var(--success);">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <div class="related-name">{{ $bulletin->mois ?? ($bulletin->periode ?? $bulletin->created_at->format('m/Y')) }}</div>
                            <div class="related-sub">Net : {{ number_format($bulletin->salaire_net ?? $bulletin->net_a_payer ?? 0, 0, ',', ' ') }} DH</div>
                        </div>
                    </div>
                    <span class="badge bg">Généré</span>
                </div>
                @empty
                <div class="empty-state">Aucun bulletin de paie.</div>
                @endforelse
            </div>
        </div>

        {{-- ONGLET HISTORIQUE --}}
        <div class="tab-pane" id="tab-historique">
            <div class="card">
                <div class="card-header"><span class="card-title">Journal des actions</span></div>
                @forelse($auditLogs as $log)
                <div style="display:flex;gap:12px;padding:12px 20px;border-bottom:1px solid var(--border-light);align-items:flex-start;">
                    <div style="width:28px;height:28px;border-radius:50%;background:var(--accent-soft);color:var(--accent);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;flex-shrink:0;">
                        {{ substr($log->user?->name ?? 'S', 0, 1) }}
                    </div>
                    <div>
                        <div style="font-size:13px;font-weight:500;color:var(--text-primary);">{{ $log->action }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ $log->user?->name ?? 'Système' }} — {{ $log->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                @empty
                <div class="empty-state">Aucun historique.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- COLONNE DROITE --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Contrat actif --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title" style="display:flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Contrat actif
                </span>
                <a href="{{ route('contrats.create') }}?employe_id={{ $employe->id }}" style="font-size:11px;color:var(--accent);text-decoration:none;font-weight:500;">+ Nouveau</a>
            </div>
            @php $ca = $employe->contrats->where('statut','en_cours')->first(); @endphp
            @if($ca)
            <a href="{{ route('contrats.show', $ca) }}" class="related-item" style="text-decoration:none;">
                <div class="related-item-left">
                    <div class="related-icon" style="background:var(--success-light);color:var(--success);">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="related-name">{{ $ca->type }} — {{ $ca->reference }}</div>
                        <div class="related-sub">{{ number_format($ca->salaire_base,0,',',' ') }} DH/mois</div>
                        @if($ca->date_fin)<div class="related-sub" style="{{ $ca->alert_expiration ? 'color:var(--warning);' : '' }}">→ {{ $ca->date_fin->format('d/m/Y') }}</div>@endif
                    </div>
                </div>
                <svg width="12" height="12" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @else
            <div class="empty-state" style="padding:16px;">Aucun contrat actif.</div>
            @endif
        </div>

        {{-- Résumé rapide --}}
        <div class="card" style="padding:16px 20px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">Résumé</div>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Matricule</span>
                    <span style="font-weight:600;font-family:monospace;font-size:12px;color:var(--text-primary);">{{ $employe->matricule }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Embauché le</span>
                    <span style="font-weight:500;color:var(--text-primary);">{{ $employe->date_embauche->format('d/m/Y') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Ancienneté</span>
                    <span style="font-weight:500;color:var(--text-primary);">{{ $employe->date_embauche->diffForHumans(null, true) }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Contrats</span>
                    <span style="font-weight:600;color:var(--accent);">{{ $employe->contrats->count() }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Congés pris</span>
                    <span style="font-weight:600;color:var(--text-primary);">{{ $employe->conges->where('statut','approuve')->count() }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Créé le</span>
                    <span style="font-weight:500;color:var(--text-primary);">{{ $employe->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="card" style="padding:16px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">Actions</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <a href="{{ route('contrats.create') }}?employe_id={{ $employe->id }}" class="tb-btn" style="justify-content:flex-start;font-size:12px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Nouveau contrat
                </a>
                <a href="{{ route('conges.index') }}?employe_id={{ $employe->id }}" class="tb-btn" style="justify-content:flex-start;font-size:12px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Demander un congé
                </a>
                <a href="{{ route('paie.index') }}?employe_id={{ $employe->id }}" class="tb-btn" style="justify-content:flex-start;font-size:12px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Générer bulletin de paie
                </a>
                <form method="POST" action="{{ route('personnel.destroy', $employe) }}" onsubmit="return confirm('Archiver cet employé ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="tb-btn" style="width:100%;justify-content:flex-start;font-size:12px;color:var(--danger);">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8l1 12a2 2 0 002 2h8a2 2 0 002-2L19 8M10 12v4M14 12v4"/></svg>
                        Archiver l'employé
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- BANDEAU MODIFICATIONS --}}
<div id="rh-unsaved-bar" style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:900;background:#1E293B;color:white;padding:14px 24px;align-items:center;justify-content:space-between;box-shadow:0 -4px 20px rgba(0,0,0,0.2);">
    <div style="display:flex;align-items:center;gap:10px;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:16px;height:16px;color:#FBBF24;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span style="font-size:13px;font-weight:500;">Modifications non enregistrées</span>
        <span style="font-size:12px;color:#94A3B8;" id="rh-unsaved-count"></span>
    </div>
    <div style="display:flex;gap:8px;">
        <button id="rh-btn-discard" type="button" style="padding:7px 16px;border-radius:6px;border:1px solid #475569;background:transparent;color:#CBD5E1;font-size:13px;cursor:pointer;font-family:var(--font);">Ignorer</button>
        <button id="rh-btn-save" type="button" style="padding:7px 16px;border-radius:6px;border:none;background:#2563EB;color:white;font-size:13px;font-weight:600;cursor:pointer;font-family:var(--font);">Enregistrer</button>
    </div>
</div>

<style>
.tabs{display:flex;gap:0;border-bottom:1px solid var(--border);margin-bottom:16px;overflow-x:auto;}
.tab-btn{padding:10px 16px;border:none;background:none;font-size:13px;color:var(--text-muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;white-space:nowrap;display:flex;align-items:center;gap:6px;font-family:var(--font);transition:all .15s;}
.tab-btn:hover{color:var(--text-primary);}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent);font-weight:500;}
.tab-count{background:var(--border-light);color:var(--text-muted);font-size:10px;padding:1px 6px;border-radius:10px;}
.tab-pane{display:none;}.tab-pane.active{display:block;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;}
.card-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);}
.card-title{font-size:13px;font-weight:600;color:var(--text-primary);}
.info-row{display:flex;align-items:flex-start;gap:12px;padding:9px 0;border-bottom:1px solid var(--border-light);}
.info-row:last-child{border-bottom:none;}
.info-label{min-width:150px;font-size:12px;color:var(--text-muted);flex-shrink:0;padding-top:1px;}
.info-value{font-size:13px;color:var(--text-primary);flex:1;}
.inline-field{cursor:pointer;border-radius:4px;padding:1px 4px;margin:-1px -4px;transition:background .15s;}
.inline-field:hover{background:var(--accent-light);outline:1px dashed var(--accent);}
.inline-field.editing{display:none!important;}
.inline-input-wrap{display:flex;align-items:center;gap:6px;flex:1;}
.inline-input-wrap input,.inline-input-wrap select,.inline-input-wrap textarea{flex:1;padding:4px 8px;border:1.5px solid var(--accent);border-radius:5px;font-size:13px;font-family:var(--font);background:white;box-shadow:0 0 0 3px var(--accent-soft);outline:none;min-width:0;}
.inline-input-wrap textarea{resize:vertical;min-height:60px;}
.related-item{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border-light);transition:background .15s;cursor:pointer;}
.related-item:last-child{border-bottom:none;}
.related-item:hover{background:var(--bg);}
.related-item-left{display:flex;align-items:center;gap:10px;}
.related-icon{width:30px;height:30px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.related-icon svg{width:14px;height:14px;}
.related-name{font-size:13px;font-weight:500;color:var(--text-primary);}
.related-sub{font-size:11px;color:var(--text-muted);margin-top:2px;}
.empty-state{padding:24px 20px;text-align:center;color:var(--text-muted);font-size:13px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:var(--font);border:none;transition:all .15s;}
.btn-sm{padding:5px 10px;font-size:12px;}
.btn-primary{background:var(--accent);color:#fff;}.btn-primary:hover{background:var(--accent-hover);}
.btn-outline{background:var(--surface);color:var(--text-secondary);border:1px solid var(--border);}.btn-outline:hover{background:var(--bg);}
.btn-danger{background:var(--danger);color:#fff;}
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;display:flex;align-items:center;gap:8px;}
.alert-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
</style>

<script>
(function() {
    var ROUTE = "{{ route('personnel.update', $employe) }}";
    var TOKEN = "{{ csrf_token() }}";
    var dirty = {};
    var pendingHref = null;

    var baseData = {
        _method: 'PUT', _token: TOKEN,
        nom:               "{{ addslashes($employe->nom) }}",
        prenom:            "{{ addslashes($employe->prenom) }}",
        cin:               "{{ $employe->cin }}",
        date_naissance:    "{{ $employe->date_naissance?->format('Y-m-d') }}",
        sexe:              "{{ $employe->sexe }}",
        situation_familiale: "{{ $employe->situation_familiale }}",
        nombre_enfants:    "{{ $employe->nombre_enfants }}",
        categorie:         "{{ $employe->categorie }}",
        poste:             "{{ addslashes($employe->poste) }}",
        date_embauche:     "{{ $employe->date_embauche->format('Y-m-d') }}",
        diplome:           "{{ addslashes($employe->diplome ?? '') }}",
        specialite:        "{{ addslashes($employe->specialite ?? '') }}",
        email:             "{{ $employe->email ?? '' }}",
        telephone:         "{{ $employe->telephone ?? '' }}",
        ville:             "{{ addslashes($employe->ville ?? '') }}",
        banque:            "{{ addslashes($employe->banque ?? '') }}",
        rib:               "{{ $employe->rib ?? '' }}",
        numero_cnss:       "{{ $employe->numero_cnss ?? '' }}",
        numero_amo:        "{{ $employe->numero_amo ?? '' }}",
    };

    var bar      = document.getElementById('rh-unsaved-bar');
    var btnSave  = document.getElementById('rh-btn-save');
    var btnDis   = document.getElementById('rh-btn-discard');
    var countEl  = document.getElementById('rh-unsaved-count');

    function showBar() {
        bar.style.display = 'flex';
        var n = Object.keys(dirty).length;
        countEl.textContent = '(' + n + ' champ' + (n>1?'s':'') + ')';
    }
    function hideBar() { bar.style.display = 'none'; }

    function markDirty(field, value, span, origText, origVal) {
        dirty[field] = { value, span, origText, origVal };
        showBar();
    }

    btnSave.addEventListener('click', function() {
        var data = Object.assign({}, baseData);
        Object.keys(dirty).forEach(function(f) { data[f] = dirty[f].value; baseData[f] = dirty[f].value; });
        btnSave.textContent = 'Enregistrement...'; btnSave.disabled = true;
        fetch(ROUTE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(data).toString()
        }).then(function(r) {
            if (r.ok || r.redirected) {
                Object.keys(dirty).forEach(function(f) {
                    updateDisplay(dirty[f].span, f, dirty[f].value);
                    dirty[f].span.style.background = '#D1FAE5';
                    setTimeout(function() { dirty[f].span.style.background = ''; }, 1500);
                });
                dirty = {}; hideBar();
                btnSave.textContent = 'Enregistrer'; btnSave.disabled = false;
                if (pendingHref) { window.location.href = pendingHref; pendingHref = null; }
            } else {
                btnSave.textContent = 'Erreur — Réessayer'; btnSave.disabled = false;
            }
        }).catch(function() { btnSave.textContent = 'Erreur réseau'; btnSave.disabled = false; });
    });

    btnDis.addEventListener('click', function() {
        Object.keys(dirty).forEach(function(f) {
            dirty[f].span.textContent = dirty[f].origText;
            dirty[f].span.dataset.value = dirty[f].origVal;
        });
        dirty = {}; hideBar();
        if (pendingHref) { window.location.href = pendingHref; pendingHref = null; }
    });

    document.addEventListener('click', function(e) {
        if (Object.keys(dirty).length === 0) return;
        var a = e.target.closest('a');
        if (a && a.href && !a.href.startsWith('#') && !a.href.startsWith('javascript')) {
            e.preventDefault(); pendingHref = a.href; showBar();
            bar.style.boxShadow = '0 -4px 20px rgba(37,99,235,0.4)';
            setTimeout(function() { bar.style.boxShadow = '0 -4px 20px rgba(0,0,0,0.2)'; }, 600);
        }
    }, true);

    window.addEventListener('beforeunload', function(e) {
        if (Object.keys(dirty).length > 0) { e.preventDefault(); e.returnValue = ''; }
    });

    document.querySelectorAll('.inline-field').forEach(function(span) {
        span.addEventListener('click', function() { openInline(span); });
    });

    function openInline(span) {
        if (span.nextSibling && span.nextSibling.className === 'inline-input-wrap') return;
        var field   = span.dataset.field;
        var type    = span.dataset.type;
        var current = span.dataset.value !== undefined ? span.dataset.value : span.textContent.trim();
        if (current === '—') current = '';
        var origText = span.textContent.trim();
        var origVal  = span.dataset.value || '';

        var input;
        if (type === 'select') {
            var opts = JSON.parse(span.dataset.options);
            input = document.createElement('select');
            Object.keys(opts).forEach(function(k) {
                var o = document.createElement('option');
                o.value = k; o.textContent = opts[k];
                if (k === span.dataset.value) o.selected = true;
                input.appendChild(o);
            });
        } else if (type === 'textarea') {
            input = document.createElement('textarea'); input.value = current; input.rows = 2;
        } else if (type === 'date') {
            input = document.createElement('input'); input.type = 'date'; input.value = current;
        } else if (type === 'number') {
            input = document.createElement('input'); input.type = 'number'; input.value = current;
        } else {
            input = document.createElement('input'); input.type = 'text'; input.value = current;
        }

        var wrap = document.createElement('div'); wrap.className = 'inline-input-wrap'; wrap.appendChild(input);
        span.classList.add('editing'); span.after(wrap);
        input.focus(); if (input.select) input.select();

        function doAccept() {
            var val = input.value;
            span.classList.remove('editing'); wrap.remove();
            updateDisplay(span, field, val);
            if (val !== origVal) markDirty(field, val, span, origText, origVal);
        }
        function doCancel() { span.classList.remove('editing'); wrap.remove(); }

        input.addEventListener('blur', function() { setTimeout(doAccept, 120); });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && type !== 'textarea') { e.preventDefault(); doAccept(); }
            if (e.key === 'Escape') { e.preventDefault(); doCancel(); }
        });
    }

    function updateDisplay(span, field, value) {
        if (!value && value !== '0') { span.textContent = '—'; span.dataset.value = ''; return; }
        span.dataset.value = value;
        if (field.indexOf('date') >= 0 && value) {
            var d = new Date(value + 'T12:00:00');
            span.textContent = ('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear();
        } else if (field === 'sexe') {
            span.textContent = value === 'M' ? 'Masculin' : 'Féminin';
        } else if (field === 'categorie') {
            var cats = @json(\App\Models\Employe::CATEGORIES);
            span.textContent = cats[value] || value;
        } else if (field === 'situation_familiale') {
            var sf = {celibataire:'Célibataire',marie:'Marié(e)',divorce:'Divorcé(e)',veuf:'Veuf/Veuve'};
            span.textContent = sf[value] || value;
        } else {
            span.textContent = value;
        }
    }
})();
</script>

</x-app-layout>
