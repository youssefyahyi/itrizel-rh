<x-app-layout>
<div style="display:flex;align-items:center;gap:8px;padding:12px 24px;border-bottom:1px solid var(--border-light);background:var(--surface);">
    <a href="{{ route('absences.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">Absences</a>
    <span style="color:var(--text-muted);">›</span>
    <span style="font-size:13px;color:var(--text-primary);font-weight:500;">{{ $absence->employe->nom_complet }} — {{ $absence->date->format('d/m/Y') }}</span>
</div>

@if(session('success'))<div class="alert alert-success" style="margin:16px 24px 0;display:flex;align-items:center;gap:8px;"><svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>{{ session('success') }}</div>@endif

<div class="fiche-header" style="margin:16px 24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                <span class="badge {{ $absence->statut_badge }}"><span class="dot"></span>{{ $absence->statut_libelle }}</span>
            </div>
            <h1 style="font-size:18px;font-weight:600;color:var(--text-primary);margin-bottom:3px;">
                <a href="{{ route('personnel.show', $absence->employe) }}" style="color:inherit;text-decoration:none;">{{ $absence->employe->nom_complet }}</a>
            </h1>
            <div style="font-size:13px;color:var(--text-muted);">{{ $absence->date->format('l d/m/Y') }} — {{ $absence->employe->poste }}</div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('absences.edit', $absence) }}" class="btn btn-outline btn-sm">Modifier</a>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;padding:0 24px 24px;align-items:start;">
    <div>
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('infos',this)">Informations</button>
            <button class="tab-btn" onclick="switchTab('historique',this)">Historique</button>
        </div>
        <div class="tab-pane active" id="tab-infos">
            <div class="card">
                <div class="card-header"><span class="card-title">Détail de l'absence</span></div>
                <div style="padding:4px 20px;">
                    <div class="info-row"><span class="info-label">Employé</span><span class="info-value"><a href="{{ route('personnel.show',$absence->employe) }}" class="link">{{ $absence->employe->nom_complet }}</a></span></div>
                    <div class="info-row"><span class="info-label">Date</span><span class="info-value">{{ $absence->date->format('d/m/Y') }}</span></div>
                    <div class="info-row"><span class="info-label">Statut</span><span class="info-value"><span class="badge {{ $absence->statut_badge }}">{{ $absence->statut_libelle }}</span></span></div>
                    <div class="info-row"><span class="info-label">Heure d'arrivée</span><span class="info-value mono">{{ $absence->heure_arrivee ? \Carbon\Carbon::parse($absence->heure_arrivee)->format('H:i') : '—' }}</span></div>
                    <div class="info-row"><span class="info-label">Heure de départ</span><span class="info-value mono">{{ $absence->heure_depart ? \Carbon\Carbon::parse($absence->heure_depart)->format('H:i') : '—' }}</span></div>
                    <div class="info-row"><span class="info-label">Heures prévues</span><span class="info-value">{{ $absence->heures_prevues }}h</span></div>
                    <div class="info-row"><span class="info-label">Heures réalisées</span><span class="info-value">{{ $absence->duree !== null ? number_format($absence->duree,1).'h' : '—' }}</span></div>
                    @if($absence->motif_absence)<div class="info-row"><span class="info-label">Motif absence</span><span class="info-value">{{ $absence->motif_absence }}</span></div>@endif
                    @if($absence->remarque)<div class="info-row"><span class="info-label">Remarque</span><span class="info-value">{{ $absence->remarque }}</span></div>@endif
                </div>
            </div>
        </div>
        <div class="tab-pane" id="tab-historique">
            <div class="card"><div class="empty-state">Aucun historique.</div></div>
        </div>
    </div>
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="padding:16px 20px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Résumé</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--text-muted);">Heures prévues</span><span style="font-weight:600;">{{ $absence->heures_prevues }}h</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--text-muted);">Heures réalisées</span><span style="font-weight:600;color:{{ $absence->duree < $absence->heures_prevues ? 'var(--warning)' : 'var(--success)' }};">{{ $absence->duree !== null ? number_format($absence->duree,1).'h' : '—' }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--text-muted);">Enregistré le</span><span>{{ $absence->created_at->format('d/m/Y') }}</span></div>
            </div>
        </div>
        <div class="card" style="padding:16px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Actions</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <a href="{{ route('absences.edit',$absence) }}" class="tb-btn" style="justify-content:flex-start;font-size:12px;">Modifier cette absence</a>
                <form method="POST" action="{{ route('absences.destroy',$absence) }}" onsubmit="return confirm('Supprimer ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="tb-btn" style="width:100%;justify-content:flex-start;font-size:12px;color:var(--danger);">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
.tabs{display:flex;border-bottom:1px solid var(--border);margin-bottom:16px;}
.tab-btn{padding:10px 16px;border:none;background:none;font-size:13px;color:var(--text-muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;font-family:var(--font);transition:all .15s;}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent);font-weight:500;}
.tab-pane{display:none;}.tab-pane.active{display:block;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;}
.card-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);}
.card-title{font-size:13px;font-weight:600;color:var(--text-primary);}
.info-row{display:flex;align-items:flex-start;gap:12px;padding:9px 0;border-bottom:1px solid var(--border-light);}
.info-row:last-child{border-bottom:none;}
.info-label{min-width:150px;font-size:12px;color:var(--text-muted);flex-shrink:0;padding-top:1px;}
.info-value{font-size:13px;color:var(--text-primary);flex:1;}
.fiche-header{background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow-sm);padding:20px 24px;}
.empty-state{padding:24px;text-align:center;color:var(--text-muted);font-size:13px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:var(--font);border:none;}
.btn-sm{padding:5px 10px;font-size:12px;}
.btn-outline{background:var(--surface);color:var(--text-secondary);border:1px solid var(--border);}
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;display:flex;align-items:center;gap:8px;}
.alert-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
</style>
</x-app-layout>
