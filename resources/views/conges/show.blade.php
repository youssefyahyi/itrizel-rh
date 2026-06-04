<x-app-layout>
<div style="display:flex;align-items:center;gap:8px;padding:12px 24px;border-bottom:1px solid var(--border-light);background:var(--surface);">
    <a href="{{ route('conges.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">Congés</a>
    <span style="color:var(--text-muted);">›</span>
    <span style="font-size:13px;color:var(--text-primary);font-weight:500;">{{ $conge->employe->nom_complet }} — {{ $conge->type_conge_libelle }}</span>
</div>

@if(session('success'))<div class="alert alert-success" style="margin:16px 24px 0;">{{ session('success') }}</div>@endif

<div class="fiche-header" style="margin:16px 24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                <span class="badge {{ $conge->statut_badge }}"><span class="dot"></span>{{ $conge->statut_libelle }}</span>
                <span class="badge bb">{{ $conge->type_conge_libelle }}</span>
            </div>
            <h1 style="font-size:18px;font-weight:600;color:var(--text-primary);margin-bottom:3px;">
                <a href="{{ route('personnel.show',$conge->employe) }}" style="color:inherit;text-decoration:none;">{{ $conge->employe->nom_complet }}</a>
            </h1>
            <div style="font-size:13px;color:var(--text-muted);">{{ $conge->date_debut->format('d/m/Y') }} → {{ $conge->date_fin->format('d/m/Y') }} — {{ $conge->nb_jours }} jour(s)</div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            @if(in_array($conge->statut, ['soumis','en_validation']))
            <form method="POST" action="{{ route('conges.approuver',$conge) }}">@csrf @method('PATCH')
                <button type="submit" class="btn btn-success btn-sm">✓ Approuver</button>
            </form>
            <form method="POST" action="{{ route('conges.rejeter',$conge) }}">@csrf @method('PATCH')
                <button type="submit" class="btn btn-danger btn-sm">✗ Rejeter</button>
            </form>
            @endif
            <a href="{{ route('conges.edit',$conge) }}" class="btn btn-outline btn-sm">Modifier</a>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;padding:0 24px 24px;align-items:start;">
    <div>
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('infos',this)">Informations</button>
            <button class="tab-btn" onclick="switchTab('workflow',this)">Validation</button>
        </div>

        <div class="tab-pane active" id="tab-infos">
            <div class="card">
                <div class="card-header"><span class="card-title">Détail de la demande</span></div>
                <div style="padding:4px 20px;">
                    <div class="info-row"><span class="info-label">Employé</span><span class="info-value"><a href="{{ route('personnel.show',$conge->employe) }}" class="link">{{ $conge->employe->nom_complet }}</a></span></div>
                    <div class="info-row"><span class="info-label">Type</span><span class="info-value">{{ $conge->type_conge_libelle }}</span></div>
                    <div class="info-row"><span class="info-label">Date de début</span><span class="info-value">{{ $conge->date_debut->format('d/m/Y') }}</span></div>
                    <div class="info-row"><span class="info-label">Date de fin</span><span class="info-value">{{ $conge->date_fin->format('d/m/Y') }}</span></div>
                    <div class="info-row"><span class="info-label">Nombre de jours</span><span class="info-value amount">{{ $conge->nb_jours }} jour(s)</span></div>
                    <div class="info-row"><span class="info-label">Statut</span><span class="info-value"><span class="badge {{ $conge->statut_badge }}">{{ $conge->statut_libelle }}</span></span></div>
                    @if($conge->motif)<div class="info-row"><span class="info-label">Motif</span><span class="info-value">{{ $conge->motif }}</span></div>@endif
                    <div class="info-row"><span class="info-label">Soumis le</span><span class="info-value muted">{{ $conge->created_at->format('d/m/Y à H:i') }}</span></div>
                </div>
            </div>
        </div>

        <div class="tab-pane" id="tab-workflow">
            <div class="card">
                <div class="card-header"><span class="card-title">Workflow de validation</span></div>
                <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px;">
                    @foreach(['soumis' => 'Demande soumise', 'en_validation' => 'En cours de validation', 'approuve' => 'Approuvée', 'rejete' => 'Rejetée'] as $s => $l)
                    @php
                        $statuts = array_keys(\App\Models\Conge::STATUTS);
                        $currentIdx = array_search($conge->statut, $statuts);
                        $stepIdx = array_search($s, $statuts);
                        $done = $stepIdx <= $currentIdx;
                        $current = $s === $conge->statut;
                    @endphp
                    <div style="display:flex;align-items:center;gap:12px;">
                        <div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:{{ $current ? 'var(--accent)' : ($done ? 'var(--success)' : 'var(--border-light)') }};color:{{ ($current || $done) ? '#fff' : 'var(--text-muted)' }};">
                            @if($done && !$current)<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @else<span style="font-size:11px;font-weight:700;">{{ $stepIdx + 1 }}</span>@endif
                        </div>
                        <span style="font-size:13px;font-weight:{{ $current ? '600' : '400' }};color:{{ $current ? 'var(--text-primary)' : 'var(--text-secondary)' }};">{{ $l }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="padding:16px 20px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Résumé</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--text-muted);">Type</span><span style="font-weight:500;">{{ $conge->type_conge_libelle }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--text-muted);">Durée</span><span style="font-weight:600;color:var(--accent);">{{ $conge->nb_jours }} jour(s)</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--text-muted);">Du</span><span>{{ $conge->date_debut->format('d/m/Y') }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--text-muted);">Au</span><span>{{ $conge->date_fin->format('d/m/Y') }}</span></div>
            </div>
        </div>
        <div class="card" style="padding:16px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Actions</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                @if(in_array($conge->statut,['soumis','en_validation']))
                <form method="POST" action="{{ route('conges.approuver',$conge) }}">@csrf @method('PATCH')<button type="submit" class="tb-btn" style="width:100%;justify-content:flex-start;font-size:12px;color:var(--success);">✓ Approuver</button></form>
                <form method="POST" action="{{ route('conges.rejeter',$conge) }}">@csrf @method('PATCH')<button type="submit" class="tb-btn" style="width:100%;justify-content:flex-start;font-size:12px;color:var(--danger);">✗ Rejeter</button></form>
                @endif
                <a href="{{ route('conges.edit',$conge) }}" class="tb-btn" style="justify-content:flex-start;font-size:12px;">Modifier</a>
                <form method="POST" action="{{ route('conges.destroy',$conge) }}" onsubmit="return confirm('Supprimer ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="tb-btn" style="width:100%;justify-content:flex-start;font-size:12px;color:var(--danger);">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.tabs{display:flex;border-bottom:1px solid var(--border);margin-bottom:16px;}
.tab-btn{padding:10px 16px;border:none;background:none;font-size:13px;color:var(--text-muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;font-family:var(--font);}
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
.btn-success{background:var(--success);color:#fff;}
.btn-danger{background:var(--danger);color:#fff;}
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;display:flex;align-items:center;gap:8px;}
.alert-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
</style>
</x-app-layout>
