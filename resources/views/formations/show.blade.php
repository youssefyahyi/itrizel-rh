<x-app-layout>

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:8px;padding:12px 24px;border-bottom:1px solid var(--border-light);background:var(--surface);">
    <a href="{{ route('formations.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">Formations</a>
    <span style="color:var(--text-muted);">›</span>
    <a href="{{ route('personnel.show', $formation->employe) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">{{ $formation->employe->nom_complet }}</a>
    <span style="color:var(--text-muted);">›</span>
    <span style="font-size:13px;color:var(--text-primary);font-weight:500;">{{ Str::limit($formation->intitule, 50) }}</span>
</div>

{{-- Alerte succès --}}
@if(session('success'))
<div class="alert alert-success" style="margin:16px 24px 0;">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- En-tête fiche --}}
<div class="fiche-header" style="margin:16px 24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;">
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;">
                <span class="badge {{ $formation->statut_badge }}">
                    <span class="dot"></span>{{ $formation->statut_libelle }}
                </span>
                <span class="badge {{ $formation->type === 'externe' ? 'bp' : 'bb' }}">
                    {{ $formation->type_libelle }}
                </span>
            </div>
            <h1 style="font-size:18px;font-weight:600;color:var(--text-primary);margin-bottom:4px;">
                {{ $formation->intitule }}
            </h1>
            <div style="font-size:13px;color:var(--text-muted);">
                <a href="{{ route('personnel.show', $formation->employe) }}" style="color:var(--accent);text-decoration:none;">
                    {{ $formation->employe->nom_complet }}
                </a>
                &nbsp;·&nbsp;
                {{ $formation->date_debut->format('d/m/Y') }} → {{ $formation->date_fin->format('d/m/Y') }}
                &nbsp;·&nbsp;
                <strong>{{ number_format($formation->nb_heures, 1) }}h</strong>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;flex-shrink:0;">
            <a href="{{ route('formations.edit', $formation) }}" class="btn btn-outline btn-sm">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Modifier
            </a>
        </div>
    </div>
</div>

{{-- Contenu principal --}}
<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;padding:0 24px 24px;align-items:start;">

    {{-- Colonne gauche --}}
    <div>
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('infos', this)">Informations</button>
            <button class="tab-btn" onclick="switchTab('observations', this)">Observations</button>
        </div>

        {{-- Onglet Informations --}}
        <div class="tab-pane active" id="tab-infos">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Détail de la formation</span>
                </div>
                <div style="padding:4px 20px;">
                    <div class="info-row">
                        <span class="info-label">Employé</span>
                        <span class="info-value">
                            <a href="{{ route('personnel.show', $formation->employe) }}" class="link">
                                {{ $formation->employe->nom_complet }}
                            </a>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Matricule</span>
                        <span class="info-value muted">{{ $formation->employe->matricule }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Intitulé</span>
                        <span class="info-value" style="font-weight:500;">{{ $formation->intitule }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Organisme</span>
                        <span class="info-value">{{ $formation->organisme ?? '—' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Type</span>
                        <span class="info-value">
                            <span class="badge {{ $formation->type === 'externe' ? 'bp' : 'bb' }}">
                                {{ $formation->type_libelle }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date de début</span>
                        <span class="info-value">{{ $formation->date_debut->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date de fin</span>
                        <span class="info-value">{{ $formation->date_fin->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nombre d'heures</span>
                        <span class="info-value amount">{{ number_format($formation->nb_heures, 1) }} h</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Coût</span>
                        <span class="info-value amount">
                            @if($formation->cout > 0)
                                {{ number_format($formation->cout, 2, ',', ' ') }} DH
                            @else
                                <span class="muted">—</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Statut</span>
                        <span class="info-value">
                            <span class="badge {{ $formation->statut_badge }}">{{ $formation->statut_libelle }}</span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Créée le</span>
                        <span class="info-value muted">{{ $formation->created_at->format('d/m/Y à H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dernière modification</span>
                        <span class="info-value muted">{{ $formation->updated_at->format('d/m/Y à H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Onglet Observations --}}
        <div class="tab-pane" id="tab-observations">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Observations</span>
                </div>
                <div style="padding:16px 20px;">
                    @if($formation->observations)
                        <p style="font-size:13px;color:var(--text-primary);line-height:1.7;white-space:pre-wrap;margin:0;">{{ $formation->observations }}</p>
                    @else
                        <div class="empty-state">Aucune observation renseignée pour cette formation.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Colonne droite --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Résumé --}}
        <div class="card" style="padding:16px 20px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">Résumé</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Type</span>
                    <span style="font-weight:500;">{{ $formation->type_libelle }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Durée</span>
                    <span style="font-weight:700;color:var(--accent);">{{ number_format($formation->nb_heures, 1) }} h</span>
                </div>
                @if($formation->cout > 0)
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Coût</span>
                    <span style="font-weight:500;">{{ number_format($formation->cout, 0, ',', ' ') }} DH</span>
                </div>
                @endif
                <div style="border-top:1px solid var(--border-light);padding-top:8px;display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:var(--text-muted);">Du</span>
                    <span>{{ $formation->date_debut->format('d/m/Y') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:var(--text-muted);">Au</span>
                    <span>{{ $formation->date_fin->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card" style="padding:16px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Actions</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <a href="{{ route('formations.edit', $formation) }}"
                   class="tb-btn" style="justify-content:flex-start;font-size:12px;">
                    Modifier cette formation
                </a>
                <a href="{{ route('personnel.show', $formation->employe) }}"
                   class="tb-btn" style="justify-content:flex-start;font-size:12px;">
                    Voir le dossier employé
                </a>
                <form method="POST" action="{{ route('formations.destroy', $formation) }}"
                      onsubmit="return confirm('Supprimer définitivement cette formation ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="tb-btn"
                            style="width:100%;justify-content:flex-start;font-size:12px;color:var(--danger);">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
.tabs{display:flex;border-bottom:1px solid var(--border);margin-bottom:16px;}
.tab-btn{padding:10px 16px;border:none;background:none;font-size:13px;color:var(--text-muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;font-family:var(--font);}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent);font-weight:500;}
.tab-pane{display:none;}
.tab-pane.active{display:block;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;}
.card-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);}
.card-title{font-size:13px;font-weight:600;color:var(--text-primary);}
.info-row{display:flex;align-items:flex-start;gap:12px;padding:9px 0;border-bottom:1px solid var(--border-light);}
.info-row:last-child{border-bottom:none;}
.info-label{min-width:160px;font-size:12px;color:var(--text-muted);flex-shrink:0;padding-top:1px;}
.info-value{font-size:13px;color:var(--text-primary);flex:1;}
.muted{color:var(--text-muted);}
.amount{font-weight:600;}
.fiche-header{background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow-sm);padding:20px 24px;}
.empty-state{padding:24px;text-align:center;color:var(--text-muted);font-size:13px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:var(--font);border:none;}
.btn-sm{padding:5px 10px;font-size:12px;}
.btn-outline{background:var(--surface);color:var(--text-secondary);border:1px solid var(--border);}
.link{color:var(--accent);text-decoration:none;}
.link:hover{text-decoration:underline;}
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;display:flex;align-items:center;gap:8px;}
.alert-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
</style>

<script>
function switchTab(id, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    btn.classList.add('active');
}
</script>

</x-app-layout>
