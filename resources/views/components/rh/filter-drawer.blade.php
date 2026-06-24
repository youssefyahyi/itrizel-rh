@props(['fields' => [], 'active' => [], 'savedViews' => collect(), 'module' => ''])

@php
    $active      = array_filter($active, fn($v) => $v !== '' && $v !== null);
    $activeCount = count($active);

    $chips = [];
    foreach ($active as $key => $val) {
        $field  = collect($fields)->firstWhere('key', $key);
        $label  = $field['label'] ?? $key;
        $valLabel = isset($field['options'][$val]) ? $field['options'][$val] : $val;
        $chips[$key] = ['label' => $label, 'value' => $valLabel];
    }
@endphp

{{-- ── Bouton ──────────────────────────────────────────────────────── --}}
<button type="button" class="tb-btn {{ $activeCount ? 'active' : '' }}" onclick="fdOpen()">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="13" height="13">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
    </svg>
    Filtres @if($activeCount)<span class="tb-count">{{ $activeCount }}</span>@endif
</button>

{{-- ── Chips ────────────────────────────────────────────────────────── --}}
@foreach($chips as $key => $chip)
<span class="chip">
    <span style="color:var(--text-muted);margin-right:2px;">{{ $chip['label'] }}:</span>
    {{ $chip['value'] }}
    <button type="button" onclick="fdRemoveChip('{{ $key }}')">×</button>
</span>
@endforeach

{{-- ── Overlay ──────────────────────────────────────────────────────── --}}
<div id="fd-overlay" onclick="fdClose()"></div>

{{-- ── Drawer ───────────────────────────────────────────────────────── --}}
<div id="fd-drawer" role="dialog" aria-label="Filtres">
    <div class="fd-header">
        <span>Filtres</span>
        <button type="button" class="fd-close-btn" onclick="fdClose()">×</button>
    </div>

    {{-- ── Vues sauvegardées ──────────────────────────────────────── --}}
    @if($savedViews->isNotEmpty() || $module)
    <div class="fd-views-section">
        <div class="fd-section-label">Vues enregistrées</div>
        <div class="fd-views-list">
            @forelse($savedViews as $vue)
            <div class="fd-view-item">
                <button type="button" class="fd-view-btn" onclick="fdLoadView({{ json_encode($vue->filtres) }})">
                    <span class="fd-view-icon">
                        @if($vue->visibilite === 'public') &#127760;
                        @elseif($vue->visibilite === 'equipe') &#128101;
                        @else &#128274;
                        @endif
                    </span>
                    {{ $vue->nom }}
                </button>
                @if($vue->user_id === auth()->id())
                <form method="POST" action="{{ route('vues.destroy', $vue) }}" style="display:inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="fd-view-del" title="Supprimer">×</button>
                </form>
                @endif
            </div>
            @empty
            <div class="fd-views-empty">Aucune vue enregistrée</div>
            @endforelse
        </div>
    </div>
    @endif

    <div class="fd-body">
        <div class="fd-section-label" style="margin-bottom:8px;">Filtres</div>
        <div id="fd-rows"></div>
        <button type="button" class="fd-add-btn" id="fd-add-btn" onclick="fdAddRow()">
            + Ajouter un filtre
        </button>
    </div>

    <div class="fd-footer">
        <button type="button" class="fd-clear-btn" onclick="fdClear()">Effacer tout</button>
        <div style="display:flex;gap:6px;align-items:center;">
            @if($module && $activeCount)
            <button type="button" class="fd-save-btn" onclick="fdOpenSave()">Enregistrer vue</button>
            @endif
            <button type="button" class="btn btn-primary btn-sm" onclick="fdApply()">Appliquer →</button>
        </div>
    </div>

    {{-- Modal enregistrer vue --}}
    @if($module)
    <div id="fd-save-modal" style="display:none;position:absolute;bottom:70px;right:16px;left:16px;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;box-shadow:0 4px 20px rgba(0,0,0,.15);z-index:10;">
        <div style="font-size:13px;font-weight:600;margin-bottom:10px;color:var(--text-primary);">Enregistrer cette vue</div>
        <input type="text" id="fd-save-nom" placeholder="Nom de la vue…" class="fd-key-sel" style="width:100%;margin-bottom:8px;">
        <select id="fd-save-visibilite" class="fd-key-sel" style="width:100%;margin-bottom:10px;">
            <option value="prive">&#128274; Privée (moi uniquement)</option>
            <option value="equipe">&#128101; Équipe (mon unité)</option>
            <option value="public">&#127760; Publique (tous les gestionnaires)</option>
        </select>
        <div style="display:flex;gap:6px;justify-content:flex-end;">
            <button type="button" class="fd-clear-btn" onclick="fdCloseSave()">Annuler</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="fdSaveView()">Enregistrer</button>
        </div>
    </div>
    <form id="fd-save-form" method="POST" action="{{ route('vues.store') }}" style="display:none;">
        @csrf
        <input type="hidden" name="module" value="{{ $module }}">
        <input type="hidden" name="nom" id="fd-save-form-nom">
        <input type="hidden" name="visibilite" id="fd-save-form-vis">
        <input type="hidden" name="filtres" id="fd-save-form-filtres">
        <input type="hidden" name="_retour" id="fd-save-form-retour">
    </form>
    @endif
</div>

<script>
(function () {
    const FD_FIELDS = @json($fields);
    const FD_ACTIVE = @json($active);
    let   fdCtr     = 0;

    // ── Ouvrir / fermer ──────────────────────────────────────────────
    window.fdOpen = function () {
        document.getElementById('fd-overlay').classList.add('fd-on');
        document.getElementById('fd-drawer').classList.add('fd-open');
    };
    window.fdClose = function () {
        document.getElementById('fd-overlay').classList.remove('fd-on');
        document.getElementById('fd-drawer').classList.remove('fd-open');
    };

    // ── Clés déjà utilisées ──────────────────────────────────────────
    function usedKeys() {
        return [...document.querySelectorAll('.fd-key-sel')]
            .map(s => s.value).filter(v => v);
    }

    // ── Met à jour les options de tous les sélecteurs de champ ───────
    function refreshKeyOptions() {
        const used = usedKeys();
        document.querySelectorAll('.fd-key-sel').forEach(sel => {
            const own = sel.value;
            [...sel.options].forEach(opt => {
                if (!opt.value) return;
                opt.hidden = opt.value !== own && used.includes(opt.value);
            });
        });
        // Masquer "+ Ajouter" si tous les champs sont utilisés
        const allUsed = FD_FIELDS.every(f => used.includes(f.key));
        const addBtn  = document.getElementById('fd-add-btn');
        if (addBtn) addBtn.style.display = allUsed ? 'none' : '';
    }

    // ── Construire le select de valeur ────────────────────────────────
    function buildValueHtml(field, curVal) {
        if (!field) return '<div class="fd-val-empty">—</div>';
        if (field.type === 'text') {
            const v = (curVal || '').replace(/"/g, '&quot;');
            return `<input type="text" class="fd-value fd-input" value="${v}" placeholder="Valeur…">`;
        }
        // select (default)
        let html = '<select class="fd-value fd-select">';
        for (const [val, label] of Object.entries(field.options ?? {})) {
            const sel = val == curVal ? ' selected' : '';
            html += `<option value="${val}"${sel}>${label}</option>`;
        }
        return html + '</select>';
    }

    // ── Quand le champ change ─────────────────────────────────────────
    window.fdOnKeyChange = function (id) {
        const row   = document.getElementById('fd-row-' + id);
        if (!row) return;
        const key   = row.querySelector('.fd-key-sel').value;
        const field = FD_FIELDS.find(f => f.key === key);
        row.querySelector('.fd-val-wrap').innerHTML = buildValueHtml(field, '');
        refreshKeyOptions();
    };

    // ── Supprimer une ligne ───────────────────────────────────────────
    window.fdRemoveRow = function (id) {
        const row = document.getElementById('fd-row-' + id);
        if (row) { row.remove(); refreshKeyOptions(); }
    };

    // ── Ajouter une ligne ─────────────────────────────────────────────
    window.fdAddRow = function (key, value) {
        key   = key   ?? '';
        value = value ?? '';
        const id    = ++fdCtr;
        const used  = usedKeys();
        const field = FD_FIELDS.find(f => f.key === key);

        let keyOpts = '<option value="">— Choisir un champ —</option>';
        FD_FIELDS.forEach(f => {
            const hidden = !key && used.includes(f.key) ? ' hidden' : '';
            const sel    = f.key === key ? ' selected' : '';
            keyOpts += `<option value="${f.key}"${sel}${hidden}>${f.label}</option>`;
        });

        const html = `
        <div class="fd-row" id="fd-row-${id}">
            <select class="fd-key-sel" onchange="fdOnKeyChange(${id})">${keyOpts}</select>
            <div class="fd-val-wrap">${buildValueHtml(field, value)}</div>
            <button type="button" class="fd-row-rm" onclick="fdRemoveRow(${id})">×</button>
        </div>`;

        document.getElementById('fd-add-btn').insertAdjacentHTML('beforebegin', html);
        if (key) document.getElementById('fd-row-' + id).querySelector('.fd-key-sel').value = key;
        refreshKeyOptions();
    };

    // ── Appliquer ─────────────────────────────────────────────────────
    window.fdApply = function () {
        const url = new URL(window.location.href);
        FD_FIELDS.forEach(f => url.searchParams.delete(f.key));
        document.querySelectorAll('.fd-row').forEach(row => {
            const key = row.querySelector('.fd-key-sel').value;
            const el  = row.querySelector('.fd-value');
            const val = el ? el.value.trim() : '';
            if (key && val) url.searchParams.set(key, val);
        });
        window.location.href = url.toString();
    };

    // ── Effacer tout ──────────────────────────────────────────────────
    window.fdClear = function () {
        const url = new URL(window.location.href);
        FD_FIELDS.forEach(f => url.searchParams.delete(f.key));
        window.location.href = url.toString();
    };

    // ── Retirer un chip ───────────────────────────────────────────────
    window.fdRemoveChip = function (key) {
        const url = new URL(window.location.href);
        url.searchParams.delete(key);
        window.location.href = url.toString();
    };

    // ── Recherche texte ───────────────────────────────────────────────
    window.fdSearch = function (val) {
        const url = new URL(window.location.href);
        val.trim() ? url.searchParams.set('q', val.trim()) : url.searchParams.delete('q');
        window.location.href = url.toString();
    };

    // ── Charger une vue ───────────────────────────────────────────────
    window.fdLoadView = function (filtres) {
        const url = new URL(window.location.href);
        FD_FIELDS.forEach(f => url.searchParams.delete(f.key));
        Object.entries(filtres).forEach(([k, v]) => { if (v) url.searchParams.set(k, v); });
        window.location.href = url.toString();
    };

    // ── Enregistrer une vue ───────────────────────────────────────────
    window.fdOpenSave = function () {
        document.getElementById('fd-save-nom').value = '';
        document.getElementById('fd-save-modal').style.display = 'block';
        document.getElementById('fd-save-nom').focus();
    };
    window.fdCloseSave = function () {
        document.getElementById('fd-save-modal').style.display = 'none';
    };
    window.fdSaveView = function () {
        const nom = document.getElementById('fd-save-nom').value.trim();
        if (!nom) { document.getElementById('fd-save-nom').focus(); return; }

        // Lire les filtres depuis l'URL courante (filtres déjà appliqués)
        const url = new URL(window.location.href);
        const filtres = {};
        FD_FIELDS.forEach(f => {
            const val = url.searchParams.get(f.key);
            if (val) filtres[f.key] = val;
        });

        document.getElementById('fd-save-form-nom').value     = nom;
        document.getElementById('fd-save-form-vis').value     = document.getElementById('fd-save-visibilite').value;
        document.getElementById('fd-save-form-filtres').value = JSON.stringify(filtres);
        document.getElementById('fd-save-form-retour').value  = window.location.href;
        document.getElementById('fd-save-form').submit();
    };

    // ── Init : pré-remplir depuis les filtres actifs ──────────────────
    document.addEventListener('DOMContentLoaded', function () {
        Object.entries(FD_ACTIVE).forEach(([k, v]) => { if (v) fdAddRow(k, v); });
    });
})();
</script>

<style>
#fd-overlay {
    display:none; position:fixed; inset:0; background:rgba(0,0,0,.3); z-index:900;
}
#fd-overlay.fd-on { display:block; }

#fd-drawer {
    position:fixed; top:0; right:0; bottom:0; width:340px;
    background:var(--surface); border-left:1px solid var(--border);
    box-shadow:-6px 0 32px rgba(0,0,0,.12); z-index:901;
    display:flex; flex-direction:column;
    transform:translateX(100%); transition:transform .22s cubic-bezier(.4,0,.2,1);
}
#fd-drawer.fd-open { transform:translateX(0); }

.fd-header {
    display:flex; align-items:center; justify-content:space-between;
    padding:16px 20px; border-bottom:1px solid var(--border);
    font-size:14px; font-weight:700; color:var(--text-primary); flex-shrink:0;
}
.fd-close-btn {
    background:none; border:none; font-size:20px; cursor:pointer;
    color:var(--text-muted); line-height:1; padding:2px 4px; font-family:inherit;
}
.fd-close-btn:hover { color:var(--text-primary); }

.fd-body {
    flex:1; overflow-y:auto; padding:16px;
    display:flex; flex-direction:column; gap:8px;
}

.fd-row {
    display:flex; gap:6px; align-items:center;
    padding:10px 10px; background:var(--surface-soft);
    border:1px solid var(--border-light); border-radius:8px;
}
.fd-key-sel, .fd-value {
    flex:1; min-width:0; padding:7px 9px;
    border:1px solid var(--border); border-radius:6px;
    font-size:12px; color:var(--text-primary);
    background:var(--surface); font-family:inherit; outline:none;
    cursor:pointer;
}
.fd-key-sel:focus, .fd-value:focus { border-color:var(--accent); }
.fd-input { cursor:text; }
.fd-val-empty { flex:1; font-size:12px; color:var(--text-muted); padding:7px 4px; }

.fd-row-rm {
    width:26px; height:26px; flex-shrink:0; border:none; background:none;
    cursor:pointer; color:var(--text-muted); font-size:16px;
    display:flex; align-items:center; justify-content:center;
    border-radius:4px; font-family:inherit; line-height:1;
}
.fd-row-rm:hover { background:#FEE2E2; color:var(--danger); }

.fd-add-btn {
    background:none; border:1px dashed var(--border); border-radius:8px;
    padding:9px 12px; font-size:12px; color:var(--accent); cursor:pointer;
    font-family:inherit; width:100%; text-align:left; transition:background .1s, border-color .1s;
    flex-shrink:0;
}
.fd-add-btn:hover { background:var(--accent-soft,rgba(59,130,246,.05)); border-color:var(--accent); }

.fd-footer {
    padding:12px 16px; border-top:1px solid var(--border);
    display:flex; justify-content:space-between; align-items:center; flex-shrink:0;
}
.fd-clear-btn {
    background:none; border:none; font-size:12px; color:var(--text-muted);
    cursor:pointer; font-family:inherit; text-decoration:underline;
}
.fd-clear-btn:hover { color:var(--danger); }

.fd-views-section {
    padding:12px 16px 0;
    border-bottom:1px solid var(--border);
    flex-shrink:0;
}
.fd-section-label {
    font-size:11px;font-weight:600;text-transform:uppercase;
    letter-spacing:.06em;color:var(--text-muted);margin-bottom:8px;
}
.fd-views-list { display:flex;flex-direction:column;gap:4px;padding-bottom:12px; }
.fd-view-item  { display:flex;align-items:center;gap:4px; }
.fd-view-btn   {
    flex:1;text-align:left;background:none;border:1px solid var(--border-light);
    border-radius:6px;padding:6px 10px;font-size:12px;color:var(--text-primary);
    cursor:pointer;font-family:inherit;transition:background .1s,border-color .1s;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.fd-view-btn:hover { background:var(--accent-soft,rgba(59,130,246,.06));border-color:var(--accent); }
.fd-view-icon  { margin-right:5px;font-size:11px; }
.fd-view-del   {
    width:22px;height:22px;flex-shrink:0;border:none;background:none;
    cursor:pointer;color:var(--text-muted);font-size:14px;
    border-radius:4px;font-family:inherit;line-height:1;
    display:flex;align-items:center;justify-content:center;
}
.fd-view-del:hover { background:#FEE2E2;color:var(--danger); }
.fd-views-empty { font-size:12px;color:var(--text-muted);font-style:italic;padding:4px 2px; }
.fd-save-btn {
    background:none;border:1px solid var(--border);border-radius:6px;
    padding:6px 12px;font-size:12px;color:var(--text-secondary);
    cursor:pointer;font-family:inherit;transition:border-color .1s,color .1s;
}
.fd-save-btn:hover { border-color:var(--accent);color:var(--accent); }
</style>
