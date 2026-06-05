<x-app-layout>

{{-- ── HEADER ─────────────────────────────────────────────── --}}
<div class="page-header">
    <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:13px;color:var(--text-muted);">Paramétrage</span>
        <span style="color:var(--text-muted);">›</span>
        <span style="font-size:15px;font-weight:700;color:var(--text-primary);">Organisation</span>
    </div>
    <a href="{{ route('parametrage.organisation.index', ['action'=>'create']) }}" class="btn-new">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Nouvelle entité
    </a>
</div>

{{-- ── FLASH ────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="flash flash-success" style="margin:12px 24px 0;">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="flash flash-danger" style="margin:12px 24px 0;">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- ── SPLIT LAYOUT ─────────────────────────────────────────── --}}
<div class="org-layout">

    {{-- ═══ PANNEAU GAUCHE : ARBRE ════════════════════════════ --}}
    <div class="org-tree-panel">
        <div class="org-tree-header">
            <span style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">
                Structure
            </span>
            <span style="font-size:11px;color:var(--text-muted);">{{ $racines->count() }} entité(s) racine</span>
        </div>

        <div class="org-tree-body">
            @forelse($racines as $node)
                @include('parametrage.organisation._node', ['node' => $node, 'depth' => 0, 'selected' => $selected])
            @empty
                <div class="org-empty-tree">
                    <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                    <p>Aucune entité créée.</p>
                    <a href="{{ route('parametrage.organisation.index', ['action'=>'create']) }}" class="btn-new" style="font-size:12px;padding:6px 12px;">
                        Créer la première entité
                    </a>
                </div>
            @endforelse
        </div>

        {{-- Légende types --}}
        <div class="org-legend">
            @foreach(\App\Models\UniteOrganisationnelle::TYPES as $k => $t)
            <span class="org-legend-item">
                <span style="width:7px;height:7px;border-radius:50%;background:{{ $t['color'] }};display:inline-block;"></span>
                {{ $t['label'] }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- ═══ PANNEAU DROIT : DÉTAIL / FORMULAIRE ════════════════ --}}
    <div class="org-detail-panel">

        @if($mode === 'create')
        {{-- ─── FORMULAIRE CRÉATION ─────────────────────────── --}}
        <div class="org-panel-header">
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--text-primary);">
                    Nouvelle entité
                    @if($parent)
                        <span style="font-weight:400;color:var(--text-muted);font-size:13px;">
                            › sous <strong>{{ $parent->nom }}</strong>
                        </span>
                    @endif
                </div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Définissez le code, le nom et le type</div>
            </div>
            <a href="{{ route('parametrage.organisation.index') }}" class="tb-btn">✕ Annuler</a>
        </div>

        @if($errors->any())
        <div class="flash flash-danger" style="margin:16px 24px 0;">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('parametrage.organisation.store') }}" class="org-form">
            @csrf
            @if($parent)<input type="hidden" name="parent_id" value="{{ $parent->id }}">@endif

            <div class="org-form-grid">
                <div class="form-group">
                    <label class="form-label">Code <span class="req">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                        value="{{ old('code') }}" placeholder="ex: DG, RH, COM-SUD" maxlength="20"
                        style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
                    @error('code')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Nom <span class="req">*</span></label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                        value="{{ old('nom') }}" placeholder="ex: Direction Générale">
                    @error('nom')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Type <span class="req">*</span></label>
                    <select name="type" class="form-control @error('type') is-invalid @enderror">
                        @foreach(\App\Models\UniteOrganisationnelle::TYPES as $k => $t)
                        <option value="{{ $k }}" {{ old('type') === $k ? 'selected' : '' }}
                            style="color:{{ $t['color'] }};">{{ $t['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!$parent)
                <div class="form-group">
                    <label class="form-label">Rattachée à</label>
                    <select name="parent_id" class="form-control">
                        <option value="">— Aucune (entité racine) —</option>
                        @foreach($toutes as $u)
                        <option value="{{ $u->id }}" {{ old('parent_id') == $u->id ? 'selected' : '' }}>
                            {{ str_repeat('  ', $u->niveau) }}{{ $u->nom }} ({{ $u->type_libelle }})
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="form-group">
                    <label class="form-label">Responsable</label>
                    <select name="responsable_id" class="form-control">
                        <option value="">— Aucun —</option>
                        @foreach($employes as $emp)
                        <option value="{{ $emp->id }}" {{ old('responsable_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nom_complet }} — {{ $emp->poste }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" name="ordre" class="form-control" value="{{ old('ordre', 0) }}" min="0" max="999">
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" style="height:auto;padding:8px 10px;">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="org-form-actions">
                <a href="{{ route('parametrage.organisation.index') }}" class="tb-btn">Annuler</a>
                <button type="submit" class="btn-new">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Créer l'entité
                </button>
            </div>
        </form>

        @elseif($mode === 'edit' && $selected)
        {{-- ─── FORMULAIRE ÉDITION ──────────────────────────── --}}
        <div class="org-panel-header">
            <div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="org-type-badge" style="background:{{ $selected->type_color }}20;color:{{ $selected->type_color }};">
                        {{ $selected->type_libelle }}
                    </span>
                    @if(!$selected->actif)
                    <span class="badge bgr">Inactif</span>
                    @endif
                </div>
                <div style="font-size:16px;font-weight:700;color:var(--text-primary);margin-top:4px;">{{ $selected->nom }}</div>
                <div style="font-size:12px;color:var(--text-muted);">{{ $selected->chemin }}</div>
            </div>
            <div style="display:flex;gap:6px;">
                <a href="{{ route('parametrage.organisation.index', ['action'=>'create','parent_id'=>$selected->id]) }}"
                   class="tb-btn" style="font-size:12px;">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Sous-entité
                </a>
            </div>
        </div>

        @if($errors->any())
        <div class="flash flash-danger" style="margin:0 24px 16px;">{{ $errors->first() }}</div>
        @endif

        <div style="padding:0 24px;">

        {{-- Tabs --}}
        <div class="tabs" style="margin-bottom:20px;">
            <button class="tab-btn active" onclick="switchTab('infos',this)">Informations</button>
            <button class="tab-btn" onclick="switchTab('employes',this)">
                Employés <span class="badge-count" style="margin-left:4px;">{{ $selected->employes->count() }}</span>
            </button>
            @if($selected->enfants->count())
            <button class="tab-btn" onclick="switchTab('sous',this)">
                Sous-entités <span class="badge-count" style="margin-left:4px;">{{ $selected->enfants->count() }}</span>
            </button>
            @endif
        </div>

        {{-- Tab : Infos --}}
        <div class="tab-pane active" id="tab-infos">
        <form method="POST" action="{{ route('parametrage.organisation.update', $selected) }}" class="org-form" style="padding:0;">
            @csrf @method('PUT')
            <div class="org-form-grid">
                <div class="form-group">
                    <label class="form-label">Code <span class="req">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror"
                        value="{{ old('code', $selected->code) }}" maxlength="20"
                        style="text-transform:uppercase;" oninput="this.value=this.value.toUpperCase()">
                    @error('code')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Nom <span class="req">*</span></label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                        value="{{ old('nom', $selected->nom) }}">
                    @error('nom')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Type <span class="req">*</span></label>
                    <select name="type" class="form-control">
                        @foreach(\App\Models\UniteOrganisationnelle::TYPES as $k => $t)
                        <option value="{{ $k }}" {{ old('type', $selected->type) === $k ? 'selected' : '' }}
                            style="color:{{ $t['color'] }};">{{ $t['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Rattachée à</label>
                    <select name="parent_id" class="form-control @error('parent_id') is-invalid @enderror">
                        <option value="">— Aucune (racine) —</option>
                        @foreach($toutes as $u)
                        <option value="{{ $u->id }}" {{ old('parent_id', $selected->parent_id) == $u->id ? 'selected' : '' }}>
                            {{ str_repeat('  ', $u->niveau) }}{{ $u->nom }} ({{ $u->type_libelle }})
                        </option>
                        @endforeach
                    </select>
                    @error('parent_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Responsable</label>
                    <select name="responsable_id" class="form-control">
                        <option value="">— Aucun —</option>
                        @foreach($employes as $emp)
                        <option value="{{ $emp->id }}" {{ old('responsable_id', $selected->responsable_id) == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nom_complet }} — {{ $emp->poste }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ordre</label>
                    <input type="number" name="ordre" class="form-control" value="{{ old('ordre', $selected->ordre) }}" min="0">
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" style="height:auto;padding:8px 10px;">{{ old('description', $selected->description) }}</textarea>
                </div>
                <div class="form-group form-full">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="hidden" name="actif" value="0">
                        <input type="checkbox" name="actif" value="1" {{ old('actif', $selected->actif) ? 'checked' : '' }}
                            style="width:15px;height:15px;accent-color:var(--accent);">
                        Entité active
                    </label>
                </div>
            </div>
            <div class="org-form-actions">
                <form method="POST" action="{{ route('parametrage.organisation.destroy', $selected) }}"
                    onsubmit="return confirm('Supprimer « {{ $selected->nom }} » ?')" style="margin:0;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger-outline"
                        @if($selected->enfants->count() || $selected->employes->count()) disabled title="Impossible : sous-entités ou employés rattachés" @endif>
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Supprimer
                    </button>
                </form>
                <button type="submit" class="btn-new" form="">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Enregistrer
                </button>
            </div>
        </form>
        </div>

        {{-- Tab : Employés --}}
        <div class="tab-pane" id="tab-employes">
            @if($selected->employes->count())
            <div class="org-emp-list">
                @foreach($selected->employes->sortBy('nom') as $emp)
                <div class="org-emp-row">
                    <div class="org-emp-avatar">{{ mb_strtoupper(mb_substr($emp->prenom,0,1)).mb_strtoupper(mb_substr($emp->nom,0,1)) }}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:500;color:var(--text-primary);">
                            <a href="{{ route('personnel.show', $emp) }}" class="link">{{ $emp->nom_complet }}</a>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted);">{{ $emp->poste }}</div>
                    </div>
                    <span class="badge {{ $emp->statut_badge }}">{{ $emp->statut }}</span>
                </div>
                @endforeach
            </div>
            @else
            <div class="org-empty-tab">
                <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p>Aucun employé directement rattaché à cette entité.</p>
                <a href="{{ route('personnel.index') }}" class="tb-btn" style="font-size:12px;">Gérer le personnel →</a>
            </div>
            @endif
        </div>

        {{-- Tab : Sous-entités --}}
        @if($selected->enfants->count())
        <div class="tab-pane" id="tab-sous">
            <div class="org-emp-list">
                @foreach($selected->enfants->sortBy('ordre') as $enfant)
                <div class="org-emp-row">
                    <span style="color:{{ $enfant->type_color }};flex-shrink:0;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    </span>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:500;">
                            <a href="{{ route('parametrage.organisation.show', $enfant) }}" class="link">{{ $enfant->nom }}</a>
                            <span style="font-size:11px;color:var(--text-muted);margin-left:6px;">{{ $enfant->code }}</span>
                        </div>
                        <div style="font-size:12px;color:var(--text-muted);">{{ $enfant->type_libelle }} · {{ $enfant->employes->count() }} employé(s)</div>
                    </div>
                    <span class="badge {{ $enfant->actif ? 'bg' : 'bgr' }}">{{ $enfant->actif ? 'Actif' : 'Inactif' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        </div>{{-- /padding --}}

        @else
        {{-- ─── ÉTAT VIDE ───────────────────────────────────── --}}
        <div class="org-welcome">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--border);"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <h3>Structure organisationnelle</h3>
            <p>Sélectionnez une entité dans l'arbre pour la consulter ou la modifier,<br>ou créez une nouvelle entité.</p>
            <div style="display:flex;gap:8px;margin-top:8px;justify-content:center;">
                <a href="{{ route('parametrage.organisation.index', ['action'=>'create']) }}" class="btn-new">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Créer une entité racine
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
/* ─── Layout ──────────────────────────────────────────────── */
.page-header{display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:var(--surface);border-bottom:1px solid var(--border);}
.org-layout{display:grid;grid-template-columns:300px 1fr;height:calc(100vh - 57px - 57px);overflow:hidden;}

/* ─── Arbre (panneau gauche) ──────────────────────────────── */
.org-tree-panel{border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;background:var(--surface-soft);}
.org-tree-header{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid var(--border-light);flex-shrink:0;}
.org-tree-body{flex:1;overflow-y:auto;padding:6px 0;}
.org-legend{padding:10px 14px;border-top:1px solid var(--border-light);display:flex;flex-wrap:wrap;gap:8px;flex-shrink:0;}
.org-legend-item{font-size:11px;color:var(--text-muted);display:flex;align-items:center;gap:4px;}
.org-empty-tree{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;gap:10px;color:var(--text-muted);}
.org-empty-tree p{font-size:13px;text-align:center;}

/* ─── Nœuds de l'arbre ───────────────────────────────────── */
.org-node-row{display:flex;align-items:center;gap:6px;padding:5px 8px 5px 0;cursor:pointer;border-radius:4px;transition:background .12s;margin:1px 4px;}
.org-node-row:hover{background:var(--surface);}
.org-node-row.selected{background:var(--accent-light)!important;}
.org-toggle{width:18px;height:18px;display:flex;align-items:center;justify-content:center;border:none;background:none;padding:0;cursor:pointer;flex-shrink:0;border-radius:3px;}
.org-toggle:hover{background:var(--border-light);}
.org-type-icon{display:flex;align-items:center;flex-shrink:0;}
.org-node-label{flex:1;font-size:13px;color:var(--text-primary);text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;gap:6px;}
.org-node-label:hover{color:var(--accent);}
.org-node-label.active{color:var(--accent);font-weight:600;}
.org-node-code{font-size:10px;color:var(--text-muted);background:var(--border-light);padding:1px 5px;border-radius:3px;font-family:monospace;}
.org-count{font-size:10px;color:var(--text-muted);background:var(--border-light);padding:1px 6px;border-radius:10px;flex-shrink:0;}
.org-add-btn{display:flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:3px;color:var(--text-muted);text-decoration:none;opacity:0;transition:opacity .15s;flex-shrink:0;}
.org-node-row:hover .org-add-btn{opacity:1;}
.org-add-btn:hover{background:var(--accent-light);color:var(--accent);}

/* ─── Panneau droit ───────────────────────────────────────── */
.org-detail-panel{overflow-y:auto;background:var(--surface);}
.org-panel-header{display:flex;align-items:flex-start;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid var(--border-light);}
.org-type-badge{font-size:11px;font-weight:600;padding:3px 8px;border-radius:20px;}
.org-welcome{display:flex;flex-direction:column;align-items:center;justify-content:center;height:calc(100% - 60px);gap:12px;color:var(--text-muted);padding:40px;}
.org-welcome h3{font-size:15px;font-weight:600;color:var(--text-secondary);}
.org-welcome p{font-size:13px;text-align:center;line-height:1.6;}

/* ─── Formulaire ──────────────────────────────────────────── */
.org-form{padding:20px 24px;}
.org-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-group{display:flex;flex-direction:column;gap:5px;}
.form-full{grid-column:1/-1;}
.form-label{font-size:12px;font-weight:500;color:var(--text-secondary);}
.req{color:var(--danger);}
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-control.is-invalid{border-color:var(--danger);}
.form-error{font-size:11px;color:var(--danger);}
select.form-control{cursor:pointer;}
.org-form-actions{display:flex;align-items:center;justify-content:space-between;margin-top:20px;padding-top:16px;border-top:1px solid var(--border-light);}

/* ─── Tabs ────────────────────────────────────────────────── */
.tabs{display:flex;border-bottom:1px solid var(--border);}
.tab-btn{padding:8px 14px;border:none;background:none;font-size:13px;color:var(--text-muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;font-family:var(--font);}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent);font-weight:500;}
.tab-pane{display:none;}.tab-pane.active{display:block;}

/* ─── Employés dans la tab ────────────────────────────────── */
.org-emp-list{display:flex;flex-direction:column;gap:1px;}
.org-emp-row{display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--border-light);}
.org-emp-row:last-child{border-bottom:none;}
.org-emp-avatar{width:32px;height:32px;border-radius:50%;background:var(--accent-light);color:var(--accent);font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.org-empty-tab{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;gap:10px;color:var(--text-muted);}
.org-empty-tab p{font-size:13px;text-align:center;}

/* ─── Boutons ─────────────────────────────────────────────── */
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;text-decoration:none;}
.btn-new:hover{background:var(--accent-hover);}
.btn-danger-outline{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:transparent;color:var(--danger);border:1px solid var(--danger);border-radius:var(--radius-sm);font-size:12px;font-weight:500;cursor:pointer;font-family:inherit;}
.btn-danger-outline:disabled{opacity:.4;cursor:not-allowed;}
.btn-danger-outline:not(:disabled):hover{background:var(--danger);color:#fff;}
.tb-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 10px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);font-size:12px;color:var(--text-secondary);cursor:pointer;font-family:inherit;text-decoration:none;}
.tb-btn:hover{border-color:var(--text-muted);}
.badge-count{background:var(--accent-light);color:var(--accent);font-size:11px;font-weight:600;padding:1px 6px;border-radius:20px;}

/* ─── Flash ───────────────────────────────────────────────── */
.flash{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;display:flex;align-items:center;gap:8px;}
.flash-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
.flash-danger{background:#FEE2E2;color:var(--danger);border:1px solid #FECACA;}
</style>
</x-app-layout>
