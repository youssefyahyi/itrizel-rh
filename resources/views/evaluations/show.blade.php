<x-app-layout>

{{-- Breadcrumb --}}
<div style="display:flex;align-items:center;gap:8px;padding:12px 24px;border-bottom:1px solid var(--border-light);background:var(--surface);">
    <a href="{{ route('evaluations.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">Évaluations</a>
    <span style="color:var(--text-muted);">›</span>
    <a href="{{ route('personnel.show', $evaluation->employe) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">{{ $evaluation->employe->nom_complet }}</a>
    <span style="color:var(--text-muted);">›</span>
    <span style="font-size:13px;color:var(--text-primary);font-weight:500;">{{ $evaluation->type_libelle }}</span>
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
                <span class="badge {{ $evaluation->statut_badge }}">
                    <span class="dot"></span>
                    {{ $evaluation->statut === 'finalise' ? 'Finalisée' : 'Brouillon' }}
                </span>
                <span class="badge bb">{{ $evaluation->type_libelle }}</span>
                <span class="badge {{ $evaluation->decision_badge }}">{{ $evaluation->decision_libelle }}</span>
            </div>
            <h1 style="font-size:18px;font-weight:600;color:var(--text-primary);margin-bottom:4px;">
                <a href="{{ route('personnel.show', $evaluation->employe) }}" style="color:inherit;text-decoration:none;">
                    {{ $evaluation->employe->nom_complet }}
                </a>
            </h1>
            <div style="font-size:13px;color:var(--text-muted);">
                Période : {{ $evaluation->periode_debut->format('d/m/Y') }} → {{ $evaluation->periode_fin->format('d/m/Y') }}
                @if($evaluation->note_globale !== null)
                    &nbsp;·&nbsp; Note :
                    <strong style="color:{{ $evaluation->note_globale >= 14 ? 'var(--success)' : ($evaluation->note_globale >= 10 ? 'var(--accent)' : 'var(--danger)') }};">
                        {{ number_format($evaluation->note_globale, 2) }} / 20
                    </strong>
                @endif
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;flex-shrink:0;">
            <a href="{{ route('evaluations.edit', $evaluation) }}" class="btn btn-outline btn-sm">
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
                    <span class="card-title">Détail de l'évaluation</span>
                </div>
                <div style="padding:4px 20px;">
                    <div class="info-row">
                        <span class="info-label">Employé</span>
                        <span class="info-value">
                            <a href="{{ route('personnel.show', $evaluation->employe) }}" class="link">
                                {{ $evaluation->employe->nom_complet }}
                            </a>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Matricule</span>
                        <span class="info-value muted">{{ $evaluation->employe->matricule }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Évaluateur</span>
                        <span class="info-value">{{ $evaluation->evaluateur->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Type</span>
                        <span class="info-value">{{ $evaluation->type_libelle }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Début de période</span>
                        <span class="info-value">{{ $evaluation->periode_debut->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fin de période</span>
                        <span class="info-value">{{ $evaluation->periode_fin->format('d/m/Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Note globale</span>
                        <span class="info-value">
                            @if($evaluation->note_globale !== null)
                                <strong style="font-size:15px;color:{{ $evaluation->note_globale >= 14 ? 'var(--success)' : ($evaluation->note_globale >= 10 ? 'var(--accent)' : 'var(--danger)') }};">
                                    {{ number_format($evaluation->note_globale, 2) }} / 20
                                </strong>
                            @else
                                <span class="muted">Non attribuée</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Décision</span>
                        <span class="info-value">
                            <span class="badge {{ $evaluation->decision_badge }}">{{ $evaluation->decision_libelle }}</span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Statut</span>
                        <span class="info-value">
                            <span class="badge {{ $evaluation->statut_badge }}">
                                {{ $evaluation->statut === 'finalise' ? 'Finalisée' : 'Brouillon' }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Créée le</span>
                        <span class="info-value muted">{{ $evaluation->created_at->format('d/m/Y à H:i') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Dernière modification</span>
                        <span class="info-value muted">{{ $evaluation->updated_at->format('d/m/Y à H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Onglet Observations --}}
        <div class="tab-pane" id="tab-observations">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">Observations de l'évaluateur</span>
                </div>
                <div style="padding:16px 20px;">
                    @if($evaluation->observations)
                        <p style="font-size:13px;color:var(--text-primary);line-height:1.7;white-space:pre-wrap;margin:0;">{{ $evaluation->observations }}</p>
                    @else
                        <div class="empty-state">Aucune observation renseignée pour cette évaluation.</div>
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
                    <span style="font-weight:500;">{{ $evaluation->type_libelle }}</span>
                </div>
                @if($evaluation->note_globale !== null)
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Note</span>
                    <span style="font-weight:700;color:{{ $evaluation->note_globale >= 14 ? 'var(--success)' : ($evaluation->note_globale >= 10 ? 'var(--accent)' : 'var(--danger)') }};">
                        {{ number_format($evaluation->note_globale, 2) }} / 20
                    </span>
                </div>
                @endif
                <div style="display:flex;justify-content:space-between;font-size:13px;">
                    <span style="color:var(--text-muted);">Décision</span>
                    <span style="font-weight:500;">{{ $evaluation->decision_libelle }}</span>
                </div>
                <div style="border-top:1px solid var(--border-light);padding-top:8px;display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:var(--text-muted);">Du</span>
                    <span>{{ $evaluation->periode_debut->format('d/m/Y') }}</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:12px;">
                    <span style="color:var(--text-muted);">Au</span>
                    <span>{{ $evaluation->periode_fin->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card" style="padding:16px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Actions</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <a href="{{ route('evaluations.edit', $evaluation) }}"
                   class="tb-btn" style="justify-content:flex-start;font-size:12px;">
                    Modifier cette évaluation
                </a>
                <a href="{{ route('personnel.show', $evaluation->employe) }}"
                   class="tb-btn" style="justify-content:flex-start;font-size:12px;">
                    Voir le dossier employé
                </a>
                <form method="POST" action="{{ route('evaluations.destroy', $evaluation) }}"
                      onsubmit="return confirm('Supprimer définitivement cette évaluation ?')">
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
