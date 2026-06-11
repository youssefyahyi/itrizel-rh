<x-app-layout>
<x-rh.page-header
    title="Paramétrage — Contrats"
    :breadcrumbs="['Paramétrage' => route('parametrage.index'), 'Contrats' => null]">
    <a href="{{ route('parametrage.index') }}" class="tb-btn">← Retour</a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if(session('success'))<x-rh.alert type="success" :message="session('success')" />@endif

{{-- Tabs --}}
<div style="display:flex;gap:0;border-bottom:2px solid var(--border);margin-bottom:0;">
    @foreach(['CDI' => 'CDI', 'CDD' => 'CDD', 'avenant' => 'Avenant'] as $val => $label)
    <a href="{{ route('parametrage.contrats.index', ['type' => $val]) }}"
       style="padding:8px 18px;font-size:13px;font-weight:500;text-decoration:none;border-bottom:2px solid {{ $type === $val ? 'var(--accent)' : 'transparent' }};margin-bottom:-2px;color:{{ $type === $val ? 'var(--accent)' : 'var(--text-secondary)' }};">
        {{ $label }}
    </a>
    @endforeach
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

{{-- Liste des clauses --}}
<div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:13px;font-weight:600;">Clauses {{ $type }} ({{ $clauses->count() }})</span>
        </div>

        @if($clauses->isEmpty())
        <div style="padding:32px;text-align:center;color:var(--text-muted);font-size:13px;">
            Aucune clause {{ $type }} définie. Ajoutez-en une à droite.
        </div>
        @else
        @foreach($clauses as $cl)
        <div style="padding:14px 18px;border-bottom:1px solid var(--border-light);{{ !$cl->actif ? 'opacity:.5;' : '' }}" id="clause-{{ $cl->id }}">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <span style="font-size:11px;font-weight:600;color:var(--text-muted);min-width:24px;">{{ $cl->ordre }}.</span>
                        <span style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $cl->titre }}</span>
                        @if($cl->obligatoire)<span style="font-size:10px;padding:2px 6px;background:var(--accent-soft);color:var(--accent);border-radius:3px;font-weight:500;">obligatoire</span>@endif
                        @if(!$cl->actif)<span style="font-size:10px;padding:2px 6px;background:var(--surface-soft);color:var(--text-muted);border-radius:3px;">inactive</span>@endif
                    </div>
                    <div style="font-size:12px;color:var(--text-secondary);margin-left:32px;line-height:1.4;">
                        {{ Str::limit(strip_tags($cl->contenu), 140) }}
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:4px;flex-shrink:0;">
                    {{-- Monter / Descendre --}}
                    @if(!$loop->first)
                    <form method="POST" action="{{ route('parametrage.contrats.monter', $cl) }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-icon" title="Monter">↑</button>
                    </form>
                    @endif
                    @if(!$loop->last)
                    <form method="POST" action="{{ route('parametrage.contrats.descendre', $cl) }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn-icon" title="Descendre">↓</button>
                    </form>
                    @endif

                    <button type="button" class="btn-icon" onclick="editClause({{ $cl->id }}, {{ json_encode($cl->titre) }}, {{ json_encode($cl->contenu) }}, {{ $cl->obligatoire ? 'true' : 'false' }}, {{ $cl->actif ? 'true' : 'false' }})" title="Modifier">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>

                    <form method="POST" action="{{ route('parametrage.contrats.destroy', $cl) }}" style="display:inline;"
                          onsubmit="return confirm('Supprimer la clause « {{ $cl->titre }} » ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-icon btn-icon-danger" title="Supprimer">
                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
        @endif
    </div>
</div>

{{-- Panel droit : Ajouter + Variables --}}
<div style="display:flex;flex-direction:column;gap:16px;">

    {{-- Formulaire ajout / modif --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;">
        <div style="padding:14px 18px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);">
            <span style="font-size:13px;font-weight:600;" id="form-titre-label">Nouvelle clause {{ $type }}</span>
        </div>
        <div style="padding:16px;">
            <form method="POST" id="clause-form" action="{{ route('parametrage.contrats.store') }}"
                  style="display:flex;flex-direction:column;gap:10px;">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="">
                <input type="hidden" name="type_doc" value="{{ $type }}">

                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:12px;font-weight:500;color:var(--text-secondary);">Titre <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="titre" id="form-titre" class="form-control"
                           value="{{ old('titre') }}" placeholder="Ex: Article 1 — Engagement" required maxlength="150">
                    @error('titre')<span style="font-size:11px;color:var(--danger);">{{ $message }}</span>@enderror
                </div>

                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:12px;font-weight:500;color:var(--text-secondary);">Contenu <span style="color:var(--danger);">*</span></label>
                    <textarea name="contenu" id="form-contenu" class="form-control" rows="6"
                              style="height:auto;padding:8px 10px;resize:vertical;"
                              placeholder="Texte de la clause... Utilisez @{{variable.cle}} pour les variables."
                              required>{{ old('contenu') }}</textarea>
                    @error('contenu')<span style="font-size:11px;color:var(--danger);">{{ $message }}</span>@enderror
                </div>

                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                    <input type="checkbox" name="obligatoire" id="form-obligatoire" value="1"
                           {{ old('obligatoire') ? 'checked' : '' }}
                           style="width:14px;height:14px;accent-color:var(--accent);">
                    <span style="color:var(--text-secondary);">Clause obligatoire (incluse automatiquement)</span>
                </label>

                <div id="grp-actif" style="display:none;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;">
                        <input type="checkbox" name="actif" id="form-actif" value="1" checked
                               style="width:14px;height:14px;accent-color:var(--accent);">
                        <span style="color:var(--text-secondary);">Clause active</span>
                    </label>
                </div>

                <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:4px;">
                    <button type="button" id="btn-annuler" class="tb-btn" onclick="resetForm()" style="display:none;">Annuler</button>
                    <button type="submit" class="btn-new" id="btn-submit">
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Variables disponibles --}}
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;">
        <div style="padding:12px 18px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);">
            <span style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.4px;">Variables disponibles</span>
        </div>
        <div style="padding:12px 16px;display:flex;flex-direction:column;gap:10px;max-height:300px;overflow-y:auto;">
            @foreach($variables as $groupe => $vars)
            <div>
                <div style="font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px;">{{ $groupe }}</div>
                <div style="display:flex;flex-wrap:wrap;gap:4px;">
                    @foreach($vars as $var)
                    @php $varTag = '{{'.$var.'}}'; @endphp
                    <span class="var-chip" onclick="insererVariable('{{ $varTag }}')"
                          title="Cliquer pour copier">{{ $varTag }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>
</div>
</div>

<style>
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;transition:border-color .15s,box-shadow .15s;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
textarea.form-control{height:auto;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;}
.btn-icon{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);cursor:pointer;font-size:12px;color:var(--text-secondary);font-family:inherit;transition:border-color .15s,color .15s;}
.btn-icon:hover{border-color:var(--accent);color:var(--accent);}
.btn-icon-danger:hover{border-color:var(--danger,#e53e3e);color:var(--danger,#e53e3e);}
.var-chip{display:inline-block;padding:2px 6px;background:var(--surface-soft);border:1px solid var(--border-light);border-radius:3px;font-size:10px;font-family:monospace;cursor:pointer;color:var(--accent);transition:background .1s;}
.var-chip:hover{background:var(--accent-soft);}
</style>

<script>
let editingClauseId = null;

function editClause(id, titre, contenu, obligatoire, actif) {
    editingClauseId = id;
    document.getElementById('form-titre-label').textContent = 'Modifier la clause';
    document.getElementById('form-titre').value    = titre;
    document.getElementById('form-contenu').value  = contenu;
    document.getElementById('form-obligatoire').checked = obligatoire;
    document.getElementById('form-actif').checked  = actif;

    document.getElementById('clause-form').action    = '/parametrage/contrats/' + id;
    document.getElementById('form-method').value     = 'PUT';
    document.getElementById('btn-submit').textContent = 'Enregistrer';
    document.getElementById('btn-annuler').style.display = '';
    document.getElementById('grp-actif').style.display   = '';

    document.getElementById('form-titre').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function resetForm() {
    editingClauseId = null;
    document.getElementById('form-titre-label').textContent = 'Nouvelle clause {{ $type }}';
    document.getElementById('clause-form').reset();
    document.getElementById('clause-form').action  = '{{ route('parametrage.contrats.store') }}';
    document.getElementById('form-method').value   = '';
    document.getElementById('btn-submit').innerHTML = '<svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Ajouter';
    document.getElementById('btn-annuler').style.display = 'none';
    document.getElementById('grp-actif').style.display   = 'none';
}

function insererVariable(variable) {
    const ta = document.getElementById('form-contenu');
    const start = ta.selectionStart;
    const end   = ta.selectionEnd;
    ta.value = ta.value.substring(0, start) + variable + ta.value.substring(end);
    ta.selectionStart = ta.selectionEnd = start + variable.length;
    ta.focus();
}
</script>
</x-app-layout>
