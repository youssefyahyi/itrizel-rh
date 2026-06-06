<x-app-layout>

{{-- ── HEADER ─────────────────────────────────────────────────── --}}
<div class="page-header">
    <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:13px;color:var(--text-muted);">Paramétrage</span>
        <span style="color:var(--text-muted);">›</span>
        <span style="font-size:15px;font-weight:700;color:var(--text-primary);">Référentiel des emplois</span>
    </div>
    <style>.btn-new{display:inline-flex;}</style>
    <a href="{{ route('parametrage.emplois.index', ['action'=>'create']) }}" class="btn-new">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Nouvelle fiche d'emploi
    </a>
</div>

{{-- ── FLASH ────────────────────────────────────────────────────── --}}
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
@if($errors->has('combo'))
<div class="flash flash-danger" style="margin:12px 24px 0;">{{ $errors->first('combo') }}</div>
@endif

{{-- ── SPLIT LAYOUT ──────────────────────────────────────────────── --}}
<div class="emp-layout">

    {{-- ═══ PANNEAU GAUCHE : ARBRE ══════════════════════════════════ --}}
    <div class="emp-tree-panel">
        <div class="emp-tree-header">
            <span style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">
                Fiches d'emploi
            </span>
            <span style="font-size:11px;color:var(--text-muted);">{{ $fiches->count() }} fiche(s)</span>
        </div>

        <div class="emp-tree-body">
            @forelse($tree as $catId => $catNode)
            @php $catLabel = $catNode['label']; $catFonctions = $catNode['fonctions']; @endphp
            <div x-data="{ open: {{ !empty($catFonctions) ? 'true' : 'true' }} }">
                {{-- Nœud Catégorie --}}
                <div class="emp-cat-row" @click="open=!open">
                    <svg class="emp-toggle-icon" :class="open ? '' : 'rotated'"
                         width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                    <span class="emp-cat-label">{{ $catLabel->nom }}</span>
                    <span class="emp-node-count">
                        {{ collect($catFonctions)->sum(fn($f) => count($f['fiches'])) }}
                    </span>
                    @if(!$catLabel->actif)
                    <span class="emp-badge-inactive">off</span>
                    @endif
                </div>

                <div x-show="open" x-transition.duration.100ms>
                    @if(empty($catFonctions))
                    {{-- Catégorie vide --}}
                    <div class="emp-empty-cat">
                        <a href="{{ route('parametrage.emplois.index', ['action'=>'create']) }}"
                           style="font-size:11px;color:var(--accent);text-decoration:none;">
                            + Ajouter une fiche
                        </a>
                    </div>
                    @else
                    @foreach($catFonctions as $fonId => $fonNode)
                    @php $fonLabel = $fonNode['label']; $fonFiches = $fonNode['fiches']; @endphp
                    <div x-data="{ open2: true }">
                        {{-- Nœud Fonction --}}
                        <div class="emp-fn-row" @click="open2=!open2">
                            <svg class="emp-toggle-icon" :class="open2 ? '' : 'rotated'"
                                 width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                            <span class="emp-fn-label">{{ $fonLabel->nom }}</span>
                            <span class="emp-node-count">{{ count($fonFiches) }}</span>
                        </div>

                        <div x-show="open2">
                            @foreach($fonFiches as $fiche)
                            <a href="{{ route('parametrage.emplois.show', $fiche) }}"
                               class="emp-fiche-row {{ isset($selected) && $selected?->id === $fiche->id ? 'selected' : '' }}">
                                <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>{{ $fiche->poste->nom }}</span>
                                @if(!$fiche->actif)
                                <span class="emp-badge-inactive">off</span>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>
            @empty
            <div class="emp-empty-tree">
                <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <p>Référentiel vide.</p>
            </div>
            @endforelse
        </div>

        {{-- Footer : gérer les labels --}}
        <div class="emp-tree-footer">
            <a href="{{ route('parametrage.emplois.index', ['panel'=>'categories']) }}"
               class="emp-footer-btn {{ $panel==='categories' ? 'active' : '' }}" title="Gérer les catégories">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                Catégories
                <span class="emp-footer-count">{{ $categories->count() }}</span>
            </a>
            <a href="{{ route('parametrage.emplois.index', ['panel'=>'fonctions']) }}"
               class="emp-footer-btn {{ $panel==='fonctions' ? 'active' : '' }}" title="Gérer les fonctions">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Fonctions
                <span class="emp-footer-count">{{ $fonctions->count() }}</span>
            </a>
            <a href="{{ route('parametrage.emplois.index', ['panel'=>'postes']) }}"
               class="emp-footer-btn {{ $panel==='postes' ? 'active' : '' }}" title="Gérer les postes">
                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Postes
                <span class="emp-footer-count">{{ $postes->count() }}</span>
            </a>
        </div>
    </div>

    {{-- ═══ PANNEAU DROIT ═════════════════════════════════════════════ --}}
    <div class="emp-detail-panel">

        {{-- ─── FORMULAIRE NOUVELLE FICHE ─────────────────────────── --}}
        @if($mode === 'create')
        <div class="emp-panel-header">
            <div>
                <div style="font-size:15px;font-weight:700;color:var(--text-primary);">Nouvelle fiche d'emploi</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Définissez la combinaison Catégorie / Fonction / Poste</div>
            </div>
            <a href="{{ route('parametrage.emplois.index') }}" class="tb-btn">✕ Annuler</a>
        </div>

        @if($errors->any() && !$errors->has('combo'))
        <div class="flash flash-danger" style="margin:16px 24px 0;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('parametrage.emplois.store') }}" class="emp-form">
            @csrf
            <div class="emp-form-grid">
                <div class="form-group form-full">
                    <label class="form-label">Catégorie <span class="req">*</span></label>
                    <select name="categorie_id" class="form-control @error('categorie_id') is-invalid @enderror">
                        <option value="">— Sélectionner —</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('categorie_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nom }}
                        </option>
                        @endforeach
                    </select>
                    @error('categorie_id')<div class="form-error">{{ $message }}</div>@enderror
                    @if($categories->isEmpty())
                    <div class="form-hint warn">
                        Aucune catégorie —
                        <a href="{{ route('parametrage.emplois.index', ['panel'=>'categories']) }}">Créer d'abord les catégories</a>
                    </div>
                    @endif
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Fonction <span class="req">*</span></label>
                    <select name="fonction_id" class="form-control @error('fonction_id') is-invalid @enderror">
                        <option value="">— Sélectionner —</option>
                        @foreach($fonctions as $fn)
                        <option value="{{ $fn->id }}" {{ old('fonction_id') == $fn->id ? 'selected' : '' }}>
                            {{ $fn->nom }}
                        </option>
                        @endforeach
                    </select>
                    @error('fonction_id')<div class="form-error">{{ $message }}</div>@enderror
                    @if($fonctions->isEmpty())
                    <div class="form-hint warn">
                        Aucune fonction —
                        <a href="{{ route('parametrage.emplois.index', ['panel'=>'fonctions']) }}">Créer d'abord les fonctions</a>
                    </div>
                    @endif
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Poste / Intitulé <span class="req">*</span></label>
                    <select name="poste_id" class="form-control @error('poste_id') is-invalid @enderror">
                        <option value="">— Sélectionner —</option>
                        @foreach($postes as $p)
                        <option value="{{ $p->id }}" {{ old('poste_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nom }}
                        </option>
                        @endforeach
                    </select>
                    @error('poste_id')<div class="form-error">{{ $message }}</div>@enderror
                    @if($postes->isEmpty())
                    <div class="form-hint warn">
                        Aucun poste —
                        <a href="{{ route('parametrage.emplois.index', ['panel'=>'postes']) }}">Créer d'abord les postes</a>
                    </div>
                    @endif
                </div>
            </div>
            <div class="emp-form-actions">
                <a href="{{ route('parametrage.emplois.index') }}" class="tb-btn">Annuler</a>
                <button type="submit" class="btn-new">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Créer la fiche
                </button>
            </div>
        </form>

        {{-- ─── ÉDITION D'UNE FICHE ─────────────────────────────── --}}
        @elseif($mode === 'edit' && $selected)
        <div class="emp-panel-header">
            <div>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span class="badge bb">{{ $selected->categorie->nom }}</span>
                    <span style="color:var(--text-muted);font-size:12px;">›</span>
                    <span class="badge bp">{{ $selected->fonction->nom }}</span>
                    @if(!$selected->actif)
                    <span class="badge bgr">Inactif</span>
                    @endif
                </div>
                <div style="font-size:16px;font-weight:700;color:var(--text-primary);margin-top:6px;">
                    {{ $selected->poste->nom }}
                </div>
            </div>
            <div style="display:flex;gap:6px;">
                <form method="POST" action="{{ route('parametrage.emplois.toggle', $selected) }}" style="margin:0;">
                    @csrf @method('PATCH')
                    <button type="submit" class="tb-btn" title="{{ $selected->actif ? 'Désactiver' : 'Activer' }}">
                        {{ $selected->actif ? '⏸ Désactiver' : '▶ Activer' }}
                    </button>
                </form>
            </div>
        </div>

        @if($errors->any() && !$errors->has('combo'))
        <div class="flash flash-danger" style="margin:0 24px 16px;">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('parametrage.emplois.update', $selected) }}" class="emp-form">
            @csrf @method('PUT')
            <div class="emp-form-grid">
                <div class="form-group form-full">
                    <label class="form-label">Catégorie <span class="req">*</span></label>
                    <select name="categorie_id" class="form-control @error('categorie_id') is-invalid @enderror">
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('categorie_id', $selected->categorie_id) == $cat->id ? 'selected' : '' }}>
                            {{ $cat->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Fonction <span class="req">*</span></label>
                    <select name="fonction_id" class="form-control @error('fonction_id') is-invalid @enderror">
                        @foreach($fonctions as $fn)
                        <option value="{{ $fn->id }}" {{ old('fonction_id', $selected->fonction_id) == $fn->id ? 'selected' : '' }}>
                            {{ $fn->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Poste / Intitulé <span class="req">*</span></label>
                    <select name="poste_id" class="form-control @error('poste_id') is-invalid @enderror">
                        @foreach($postes as $p)
                        <option value="{{ $p->id }}" {{ old('poste_id', $selected->poste_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->nom }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="emp-form-actions">
                <form method="POST" action="{{ route('parametrage.emplois.destroy', $selected) }}"
                      onsubmit="return confirm('Supprimer cette fiche d\'emploi ?')" style="margin:0;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-danger-outline">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Supprimer
                    </button>
                </form>
                <button type="submit" class="btn-new">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Enregistrer
                </button>
            </div>
        </form>

        {{-- ─── PANEL CATÉGORIES ─────────────────────────────────── --}}
        @elseif($panel === 'categories')
        <div class="emp-panel-header">
            <div>
                <div style="font-size:15px;font-weight:700;">Catégories</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Niveaux de qualification ({{ $categories->count() }})</div>
            </div>
            <a href="{{ route('parametrage.emplois.index') }}" class="tb-btn">← Retour</a>
        </div>
        <div style="padding:0 24px 24px;" x-data="{ editId: null, editNom: '', editOrdre: 0 }">
            @foreach($categories as $cat)
            <div class="label-row">
                <template x-if="editId !== {{ $cat->id }}">
                    <div class="label-row-view">
                        <div style="display:flex;align-items:center;gap:8px;flex:1;">
                            <span class="label-ordre">{{ $cat->ordre }}</span>
                            <span class="label-nom {{ !$cat->actif ? 'inactive' : '' }}">{{ $cat->nom }}</span>
                            @if(!$cat->actif)<span class="badge bgr" style="font-size:10px;">inactif</span>@endif
                            @if($cat->fiches()->count() > 0)
                            <span style="font-size:11px;color:var(--text-muted);">· {{ $cat->fiches()->count() }} fiche(s)</span>
                            @endif
                        </div>
                        <div class="label-actions">
                            <button type="button" class="label-btn"
                                    @click="editId={{ $cat->id }}; editNom='{{ addslashes($cat->nom) }}'; editOrdre={{ $cat->ordre }}"
                                    title="Modifier">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('parametrage.emplois.categories.toggle', $cat) }}" style="margin:0;">
                                @csrf @method('PATCH')
                                <button type="submit" class="label-btn" title="{{ $cat->actif ? 'Désactiver' : 'Activer' }}">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cat->actif ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' }}"/></svg>
                                </button>
                            </form>
                            @if($cat->fiches()->count() === 0)
                            <form method="POST" action="{{ route('parametrage.emplois.categories.destroy', $cat) }}"
                                  onsubmit="return confirm('Supprimer «{{ addslashes($cat->nom) }}» ?')" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="label-btn danger" title="Supprimer">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </template>
                <template x-if="editId === {{ $cat->id }}">
                    <form method="POST" action="{{ route('parametrage.emplois.categories.update', $cat) }}" class="label-edit-form">
                        @csrf @method('PUT')
                        <input x-model="editNom" name="nom" class="form-control" placeholder="Nom" required maxlength="100" style="flex:1;">
                        <input x-model="editOrdre" name="ordre" type="number" class="form-control" min="0" max="99" style="width:60px;" required>
                        <button type="submit" class="btn-new" style="padding:5px 10px;font-size:12px;">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" class="tb-btn" style="padding:5px 8px;" @click="editId=null">✕</button>
                    </form>
                </template>
            </div>
            @endforeach
            {{-- Ajouter --}}
            <form method="POST" action="{{ route('parametrage.emplois.categories.store') }}" class="label-add-form">
                @csrf
                <input type="text" name="nom" class="form-control" placeholder="Nouvelle catégorie…" required maxlength="100">
                <input type="number" name="ordre" class="form-control" value="{{ $categories->max('ordre') + 1 }}" min="0" max="99" style="width:60px;" required>
                <button type="submit" class="btn-new" style="padding:5px 12px;font-size:12px;">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Ajouter
                </button>
            </form>
        </div>

        {{-- ─── PANEL FONCTIONS ──────────────────────────────────── --}}
        @elseif($panel === 'fonctions')
        <div class="emp-panel-header">
            <div>
                <div style="font-size:15px;font-weight:700;">Fonctions</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Domaines d'activité ({{ $fonctions->count() }})</div>
            </div>
            <a href="{{ route('parametrage.emplois.index') }}" class="tb-btn">← Retour</a>
        </div>
        <div style="padding:0 24px 24px;" x-data="{ editId: null, editNom: '', editOrdre: 0 }">
            @foreach($fonctions as $fn)
            <div class="label-row">
                <template x-if="editId !== {{ $fn->id }}">
                    <div class="label-row-view">
                        <div style="display:flex;align-items:center;gap:8px;flex:1;">
                            <span class="label-ordre">{{ $fn->ordre }}</span>
                            <span class="label-nom {{ !$fn->actif ? 'inactive' : '' }}">{{ $fn->nom }}</span>
                            @if(!$fn->actif)<span class="badge bgr" style="font-size:10px;">inactif</span>@endif
                            @if($fn->fiches()->count() > 0)
                            <span style="font-size:11px;color:var(--text-muted);">· {{ $fn->fiches()->count() }} fiche(s)</span>
                            @endif
                        </div>
                        <div class="label-actions">
                            <button type="button" class="label-btn"
                                    @click="editId={{ $fn->id }}; editNom='{{ addslashes($fn->nom) }}'; editOrdre={{ $fn->ordre }}"
                                    title="Modifier">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('parametrage.emplois.fonctions.toggle', $fn) }}" style="margin:0;">
                                @csrf @method('PATCH')
                                <button type="submit" class="label-btn" title="{{ $fn->actif ? 'Désactiver' : 'Activer' }}">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $fn->actif ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' }}"/></svg>
                                </button>
                            </form>
                            @if($fn->fiches()->count() === 0)
                            <form method="POST" action="{{ route('parametrage.emplois.fonctions.destroy', $fn) }}"
                                  onsubmit="return confirm('Supprimer «{{ addslashes($fn->nom) }}» ?')" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="label-btn danger" title="Supprimer">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </template>
                <template x-if="editId === {{ $fn->id }}">
                    <form method="POST" action="{{ route('parametrage.emplois.fonctions.update', $fn) }}" class="label-edit-form">
                        @csrf @method('PUT')
                        <input x-model="editNom" name="nom" class="form-control" placeholder="Nom" required maxlength="150" style="flex:1;">
                        <input x-model="editOrdre" name="ordre" type="number" class="form-control" min="0" max="99" style="width:60px;" required>
                        <button type="submit" class="btn-new" style="padding:5px 10px;font-size:12px;">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" class="tb-btn" style="padding:5px 8px;" @click="editId=null">✕</button>
                    </form>
                </template>
            </div>
            @endforeach
            <form method="POST" action="{{ route('parametrage.emplois.fonctions.store') }}" class="label-add-form">
                @csrf
                <input type="text" name="nom" class="form-control" placeholder="Nouvelle fonction…" required maxlength="150">
                <input type="number" name="ordre" class="form-control" value="{{ $fonctions->max('ordre') + 1 }}" min="0" max="99" style="width:60px;" required>
                <button type="submit" class="btn-new" style="padding:5px 12px;font-size:12px;">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Ajouter
                </button>
            </form>
        </div>

        {{-- ─── PANEL POSTES ─────────────────────────────────────── --}}
        @elseif($panel === 'postes')
        <div class="emp-panel-header">
            <div>
                <div style="font-size:15px;font-weight:700;">Postes</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">Intitulés de poste ({{ $postes->count() }})</div>
            </div>
            <a href="{{ route('parametrage.emplois.index') }}" class="tb-btn">← Retour</a>
        </div>
        <div style="padding:0 24px 24px;" x-data="{ editId: null, editNom: '' }">
            @foreach($postes as $p)
            <div class="label-row">
                <template x-if="editId !== {{ $p->id }}">
                    <div class="label-row-view">
                        <div style="display:flex;align-items:center;gap:8px;flex:1;">
                            <span class="label-nom {{ !$p->actif ? 'inactive' : '' }}">{{ $p->nom }}</span>
                            @if(!$p->actif)<span class="badge bgr" style="font-size:10px;">inactif</span>@endif
                            @if($p->fiches()->count() > 0)
                            <span style="font-size:11px;color:var(--text-muted);">· {{ $p->fiches()->count() }} fiche(s)</span>
                            @endif
                        </div>
                        <div class="label-actions">
                            <button type="button" class="label-btn"
                                    @click="editId={{ $p->id }}; editNom='{{ addslashes($p->nom) }}'"
                                    title="Modifier">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="{{ route('parametrage.emplois.postes.toggle', $p) }}" style="margin:0;">
                                @csrf @method('PATCH')
                                <button type="submit" class="label-btn" title="{{ $p->actif ? 'Désactiver' : 'Activer' }}">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $p->actif ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z' }}"/></svg>
                                </button>
                            </form>
                            @if($p->fiches()->count() === 0)
                            <form method="POST" action="{{ route('parametrage.emplois.postes.destroy', $p) }}"
                                  onsubmit="return confirm('Supprimer «{{ addslashes($p->nom) }}» ?')" style="margin:0;">
                                @csrf @method('DELETE')
                                <button type="submit" class="label-btn danger" title="Supprimer">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </template>
                <template x-if="editId === {{ $p->id }}">
                    <form method="POST" action="{{ route('parametrage.emplois.postes.update', $p) }}" class="label-edit-form">
                        @csrf @method('PUT')
                        <input x-model="editNom" name="nom" class="form-control" placeholder="Nom du poste" required maxlength="150" style="flex:1;">
                        <button type="submit" class="btn-new" style="padding:5px 10px;font-size:12px;">
                            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" class="tb-btn" style="padding:5px 8px;" @click="editId=null">✕</button>
                    </form>
                </template>
            </div>
            @endforeach
            <form method="POST" action="{{ route('parametrage.emplois.postes.store') }}" class="label-add-form">
                @csrf
                <input type="text" name="nom" class="form-control" placeholder="Nouvel intitulé de poste…" required maxlength="150">
                <button type="submit" class="btn-new" style="padding:5px 12px;font-size:12px;">
                    <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Ajouter
                </button>
            </form>
        </div>

        {{-- ─── ÉTAT ACCUEIL ─────────────────────────────────────── --}}
        @else
        <div class="emp-welcome">
            <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--border);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <h3>Référentiel des emplois</h3>
            <p>Définissez les combinaisons valides<br><strong>Catégorie › Fonction › Poste</strong></p>
            <div style="display:flex;gap:8px;margin-top:8px;justify-content:center;flex-wrap:wrap;">
                @if($categories->isEmpty() || $fonctions->isEmpty() || $postes->isEmpty())
                <div style="font-size:12px;color:var(--warning);background:var(--warning-light);padding:8px 14px;border-radius:var(--radius-sm);text-align:center;">
                    Commencez par créer les labels :
                    @if($categories->isEmpty())
                    <a href="{{ route('parametrage.emplois.index', ['panel'=>'categories']) }}" style="color:var(--accent);">Catégories</a>
                    @endif
                    @if($fonctions->isEmpty())
                    <a href="{{ route('parametrage.emplois.index', ['panel'=>'fonctions']) }}" style="color:var(--accent);margin-left:4px;">Fonctions</a>
                    @endif
                    @if($postes->isEmpty())
                    <a href="{{ route('parametrage.emplois.index', ['panel'=>'postes']) }}" style="color:var(--accent);margin-left:4px;">Postes</a>
                    @endif
                </div>
                @else
                <a href="{{ route('parametrage.emplois.index', ['action'=>'create']) }}" class="btn-new">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Créer la première fiche
                </a>
                @endif
            </div>

            {{-- Compteurs --}}
            <div style="display:flex;gap:16px;margin-top:20px;flex-wrap:wrap;justify-content:center;">
                <div class="emp-stat">
                    <span class="emp-stat-val">{{ $categories->count() }}</span>
                    <span class="emp-stat-lbl">Catégories</span>
                </div>
                <div class="emp-stat">
                    <span class="emp-stat-val">{{ $fonctions->count() }}</span>
                    <span class="emp-stat-lbl">Fonctions</span>
                </div>
                <div class="emp-stat">
                    <span class="emp-stat-val">{{ $postes->count() }}</span>
                    <span class="emp-stat-lbl">Postes</span>
                </div>
                <div class="emp-stat">
                    <span class="emp-stat-val">{{ $fiches->count() }}</span>
                    <span class="emp-stat-lbl">Fiches d'emploi</span>
                </div>
            </div>
        </div>
        @endif

    </div>{{-- /emp-detail-panel --}}
</div>{{-- /emp-layout --}}

<style>
/* ─── Layout ─────────────────────────────────────────────────────── */
.page-header{display:flex;align-items:center;justify-content:space-between;padding:14px 24px;background:var(--surface);border-bottom:1px solid var(--border);}
.emp-layout{display:grid;grid-template-columns:300px 1fr;height:calc(100vh - 57px - 57px);overflow:hidden;}

/* ─── Arbre ──────────────────────────────────────────────────────── */
.emp-tree-panel{border-right:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden;background:var(--surface-soft);}
.emp-tree-header{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid var(--border-light);flex-shrink:0;}
.emp-tree-body{flex:1;overflow-y:auto;padding:4px 0;}
.emp-empty-tree{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px 20px;gap:8px;color:var(--text-muted);font-size:13px;text-align:center;}

/* ─── Noeuds catégorie ───────────────────────────────────────────── */
.emp-cat-row{display:flex;align-items:center;gap:6px;padding:6px 10px;cursor:pointer;user-select:none;border-radius:4px;margin:1px 4px;transition:background .12s;}
.emp-cat-row:hover{background:rgba(0,0,0,.04);}
.emp-toggle-icon{flex-shrink:0;transition:transform .15s;}
.emp-toggle-icon.rotated{transform:rotate(-90deg);}
.emp-cat-label{font-size:12.5px;font-weight:600;color:var(--text-primary);flex:1;}
.emp-node-count{font-size:10px;color:var(--text-muted);background:var(--border-light);padding:1px 6px;border-radius:10px;flex-shrink:0;}
.emp-badge-inactive{font-size:9px;background:var(--border-light);color:var(--text-muted);padding:1px 5px;border-radius:3px;flex-shrink:0;}
.emp-empty-cat{padding:4px 14px 6px 32px;font-size:11px;color:var(--text-muted);}

/* ─── Noeuds fonction ────────────────────────────────────────────── */
.emp-fn-row{display:flex;align-items:center;gap:5px;padding:5px 10px 5px 22px;cursor:pointer;user-select:none;border-radius:4px;margin:1px 4px;transition:background .12s;}
.emp-fn-row:hover{background:rgba(0,0,0,.04);}
.emp-fn-label{font-size:12px;color:var(--text-secondary);flex:1;}

/* ─── Fiches (feuilles) ──────────────────────────────────────────── */
.emp-fiche-row{display:flex;align-items:center;gap:6px;padding:5px 10px 5px 36px;border-radius:4px;margin:1px 4px;font-size:12px;color:var(--text-secondary);text-decoration:none;transition:background .12s;}
.emp-fiche-row:hover{background:var(--surface);color:var(--accent);}
.emp-fiche-row.selected{background:var(--accent-light);color:var(--accent);font-weight:500;}

/* ─── Footer tree ────────────────────────────────────────────────── */
.emp-tree-footer{padding:8px 10px;border-top:1px solid var(--border-light);display:flex;gap:4px;flex-shrink:0;flex-wrap:wrap;}
.emp-footer-btn{display:inline-flex;align-items:center;gap:4px;padding:4px 8px;border-radius:var(--radius-sm);font-size:11px;color:var(--text-muted);text-decoration:none;transition:all .12s;}
.emp-footer-btn:hover{background:var(--border-light);color:var(--text-secondary);}
.emp-footer-btn.active{background:var(--accent-light);color:var(--accent);font-weight:500;}
.emp-footer-count{background:var(--border-light);color:var(--text-muted);font-size:10px;padding:1px 5px;border-radius:8px;}
.emp-footer-btn.active .emp-footer-count{background:rgba(37,99,235,.15);}

/* ─── Panneau droit ─────────────────────────────────────────────── */
.emp-detail-panel{overflow-y:auto;background:var(--surface);}
.emp-panel-header{display:flex;align-items:flex-start;justify-content:space-between;padding:20px 24px 16px;border-bottom:1px solid var(--border-light);}
.emp-welcome{display:flex;flex-direction:column;align-items:center;justify-content:center;height:calc(100% - 40px);gap:10px;color:var(--text-muted);padding:40px;text-align:center;}
.emp-welcome h3{font-size:15px;font-weight:600;color:var(--text-secondary);}
.emp-welcome p{font-size:13px;line-height:1.6;}

/* ─── Formulaire fiche ──────────────────────────────────────────── */
.emp-form{padding:20px 24px;}
.emp-form-grid{display:flex;flex-direction:column;gap:14px;}
.emp-form-actions{display:flex;align-items:center;justify-content:space-between;margin-top:20px;padding-top:16px;border-top:1px solid var(--border-light);}
.form-group{display:flex;flex-direction:column;gap:5px;}
.form-full{grid-column:1/-1;}
.form-label{font-size:12px;font-weight:500;color:var(--text-secondary);}
.req{color:var(--danger);}
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-control.is-invalid{border-color:var(--danger);}
.form-error{font-size:11px;color:var(--danger);}
.form-hint{font-size:11px;color:var(--text-muted);margin-top:3px;}
.form-hint.warn{color:var(--warning);}
.form-hint.warn a{color:var(--accent);}
select.form-control{cursor:pointer;}

/* ─── Labels management ─────────────────────────────────────────── */
.label-row{border-bottom:1px solid var(--border-light);padding:6px 0;}
.label-row:last-of-type{border-bottom:none;}
.label-row-view{display:flex;align-items:center;gap:8px;padding:4px 0;}
.label-ordre{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:var(--border-light);font-size:10px;font-weight:600;color:var(--text-muted);flex-shrink:0;}
.label-nom{font-size:13px;color:var(--text-primary);}
.label-nom.inactive{text-decoration:line-through;color:var(--text-muted);}
.label-actions{display:flex;align-items:center;gap:3px;flex-shrink:0;}
.label-btn{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border:1px solid transparent;border-radius:var(--radius-sm);background:none;cursor:pointer;color:var(--text-muted);transition:all .12s;}
.label-btn:hover{background:var(--bg);border-color:var(--border);color:var(--text-secondary);}
.label-btn.danger:hover{background:var(--danger-light);border-color:var(--danger);color:var(--danger);}
.label-edit-form{display:flex;align-items:center;gap:6px;padding:4px 0;}
.label-add-form{display:flex;align-items:center;gap:8px;padding:12px 0 4px;border-top:1px dashed var(--border-light);margin-top:8px;}

/* ─── Stat cards accueil ────────────────────────────────────────── */
.emp-stat{display:flex;flex-direction:column;align-items:center;padding:10px 16px;background:var(--surface-soft);border:1px solid var(--border-light);border-radius:var(--radius);min-width:80px;}
.emp-stat-val{font-size:22px;font-weight:700;color:var(--text-primary);}
.emp-stat-lbl{font-size:11px;color:var(--text-muted);margin-top:2px;}

/* ─── Boutons ────────────────────────────────────────────────────── */
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;text-decoration:none;}
.btn-new:hover{background:var(--accent-hover);}
.btn-danger-outline{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;background:transparent;color:var(--danger);border:1px solid var(--danger);border-radius:var(--radius-sm);font-size:12px;font-weight:500;cursor:pointer;font-family:inherit;}
.btn-danger-outline:not(:disabled):hover{background:var(--danger);color:#fff;}
.tb-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 10px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius-sm);font-size:12px;color:var(--text-secondary);cursor:pointer;font-family:inherit;text-decoration:none;}
.tb-btn:hover{border-color:var(--text-muted);}

/* ─── Flash ──────────────────────────────────────────────────────── */
.flash{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;display:flex;align-items:center;gap:8px;}
.flash-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
.flash-danger{background:#FEE2E2;color:var(--danger);border:1px solid #FECACA;}
</style>
</x-app-layout>
