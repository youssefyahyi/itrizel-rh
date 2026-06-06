<x-app-layout>
<div class="page-header">
    <div class="page-title">Catégories d'employés</div>
</div>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if(session('success'))
    <x-rh.alert type="success" :message="session('success')" />
@endif
@if(session('error'))
    <x-rh.alert type="danger" :message="session('error')" />
@endif

<div style="display:grid;grid-template-columns:1fr 340px;gap:16px;align-items:start;">

    {{-- ── Liste ────────────────────────────────────────────────── --}}
    <div class="card" style="overflow:hidden;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center;">Ordre</th>
                    <th>Catégorie</th>
                    <th style="width:100px;text-align:center;">Employés</th>
                    <th style="width:80px;text-align:center;">Statut</th>
                    <th style="width:120px;text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($categories as $cat)
                <tr>
                    <td style="text-align:center;color:var(--text-muted);font-size:12px;">{{ $cat->ordre }}</td>
                    <td style="font-weight:500;color:var(--text-primary);">{{ $cat->nom }}</td>
                    <td style="text-align:center;">
                        <span style="font-weight:600;">{{ $cat->employes_count }}</span>
                    </td>
                    <td style="text-align:center;">
                        <form method="POST" action="{{ route('parametrage.categories.toggle', $cat) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="badge {{ $cat->actif ? 'bg' : 'br' }}"
                                    style="border:none;cursor:pointer;">
                                {{ $cat->actif ? 'Actif' : 'Inactif' }}
                            </button>
                        </form>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                            {{-- Édition inline --}}
                            <button type="button"
                                    onclick="openEdit({{ $cat->id }}, '{{ addslashes($cat->nom) }}', {{ $cat->ordre }})"
                                    class="tb-btn tb-btn-sm" title="Modifier">
                                <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            @if($cat->employes_count === 0)
                            <form method="POST" action="{{ route('parametrage.categories.destroy', $cat) }}"
                                  onsubmit="return confirm('Supprimer «{{ $cat->nom }}» ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="tb-btn tb-btn-sm tb-btn-danger" title="Supprimer">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="padding:32px;text-align:center;color:var(--text-muted);">Aucune catégorie.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── Formulaire ajout / édition ──────────────────────────── --}}
    <div class="card" style="padding:20px;" id="form-card">
        <div style="font-size:13px;font-weight:600;margin-bottom:14px;" id="form-title">Nouvelle catégorie</div>

        {{-- Formulaire création --}}
        <form method="POST" action="{{ route('parametrage.categories.store') }}" id="form-create">
            @csrf
            <div style="display:flex;flex-direction:column;gap:10px;">
                <div>
                    <label class="form-label">Intitulé <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
                           value="{{ old('nom') }}" placeholder="Ex : Technicien supérieur" required>
                    @error('nom') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Ordre d'affichage <span style="color:var(--danger)">*</span></label>
                    <input type="number" name="ordre" class="form-control"
                           value="{{ old('ordre', $categories->count() + 1) }}" min="0" required>
                </div>
                <button type="submit" class="btn-new" style="margin-top:4px;width:100%;justify-content:center;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    Ajouter
                </button>
            </div>
        </form>

        {{-- Formulaire édition (caché par défaut) --}}
        <form method="POST" id="form-edit" style="display:none;flex-direction:column;gap:10px;">
            @csrf @method('PUT')
            <div>
                <label class="form-label">Intitulé <span style="color:var(--danger)">*</span></label>
                <input type="text" name="nom" id="edit-nom" class="form-control" required>
            </div>
            <div>
                <label class="form-label">Ordre</label>
                <input type="number" name="ordre" id="edit-ordre" class="form-control" min="0">
            </div>
            <div style="display:flex;gap:8px;margin-top:4px;">
                <button type="button" onclick="cancelEdit()" class="tb-btn" style="flex:1;justify-content:center;">Annuler</button>
                <button type="submit" class="btn-new" style="flex:2;justify-content:center;">Enregistrer</button>
            </div>
        </form>
    </div>

</div>
</div>

<style>
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;text-decoration:none;}
.btn-new:hover{background:var(--accent-hover);}
.form-label{font-size:12px;font-weight:500;color:var(--text-secondary);display:block;margin-bottom:4px;}
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-error{font-size:11px;color:var(--danger);margin-top:2px;}
.badge{display:inline-flex;align-items:center;padding:3px 8px;border-radius:20px;font-size:11px;font-weight:600;}
.bg{background:#dcfce7;color:#16a34a;}.br{background:#fee2e2;color:#dc2626;}
.data-table{width:100%;border-collapse:collapse;}
.data-table th{padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);background:var(--surface-soft);border-bottom:1px solid var(--border);}
.data-table td{padding:12px 14px;font-size:13px;color:var(--text-secondary);border-bottom:1px solid var(--border-light);vertical-align:middle;}
.data-table tbody tr:hover{background:var(--surface-soft);}
.tb-btn-sm{padding:4px 8px !important;}
.tb-btn-danger{color:var(--danger) !important;}
</style>

<script>
function openEdit(id, nom, ordre) {
    document.getElementById('form-title').textContent = 'Modifier la catégorie';
    document.getElementById('form-create').style.display = 'none';
    var form = document.getElementById('form-edit');
    form.style.display = 'flex';
    form.action = '/parametrage/categories/' + id;
    document.getElementById('edit-nom').value   = nom;
    document.getElementById('edit-ordre').value = ordre;
}
function cancelEdit() {
    document.getElementById('form-title').textContent = 'Nouvelle catégorie';
    document.getElementById('form-create').style.display = 'flex';
    document.getElementById('form-edit').style.display   = 'none';
}
</script>
</x-app-layout>
