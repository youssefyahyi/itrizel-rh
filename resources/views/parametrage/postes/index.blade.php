<x-app-layout>
<x-rh.page-header
    title="Référentiel Postes"
    :breadcrumbs="['Paramétrage' => route('parametrage.index'), 'Postes' => null]">
    <a href="{{ route('parametrage.postes.create') }}" class="btn-new">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Nouveau poste
    </a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if(session('success'))
    <x-rh.alert type="success" :message="session('success')" />
@endif
@if(session('error'))
    <x-rh.alert type="danger" :message="session('error')" />
@endif

{{-- Filtres --}}
<form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher num. ou nom…"
           class="form-control" style="width:220px;">
    <select name="actif" class="form-control" style="width:150px;">
        <option value="">Tous les statuts</option>
        <option value="1" {{ request('actif') === '1' ? 'selected' : '' }}>Actif</option>
        <option value="0" {{ request('actif') === '0' ? 'selected' : '' }}>Inactif</option>
    </select>
    <button type="submit" class="tb-btn">Filtrer</button>
    @if(request()->hasAny(['q','actif']))
    <a href="{{ route('parametrage.postes.index') }}" class="tb-btn">✕ Effacer</a>
    @endif
</form>

{{-- Tableau --}}
<div class="card" style="overflow:hidden;">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:90px;">Numéro</th>
                <th>Nom du poste</th>
                <th style="width:120px;text-align:center;">Compétences</th>
                <th style="width:100px;text-align:center;">Employés</th>
                <th style="width:80px;text-align:center;">Statut</th>
                <th style="width:90px;text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($postes as $poste)
            <tr>
                <td>
                    <span style="font-family:monospace;font-weight:600;color:var(--accent);font-size:12px;">
                        {{ $poste->numero }}
                    </span>
                </td>
                <td>
                    <a href="{{ route('parametrage.postes.show', $poste) }}"
                       style="font-weight:500;color:var(--text-primary);text-decoration:none;">
                        {{ $poste->nom }}
                    </a>
                    @if($poste->description)
                    <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                        {{ Str::limit($poste->description, 60) }}
                    </div>
                    @endif
                </td>
                <td style="text-align:center;">
                    @if(!empty($poste->competences))
                        <span class="badge bg-secondary">{{ count($poste->competences) }}</span>
                    @else
                        <span style="color:var(--text-muted);font-size:12px;">—</span>
                    @endif
                </td>
                <td style="text-align:center;">
                    <span style="font-weight:600;color:var(--text-primary);">
                        {{ $poste->employes_count }}
                    </span>
                </td>
                <td style="text-align:center;">
                    <form method="POST" action="{{ route('parametrage.postes.toggle', $poste) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="badge {{ $poste->actif ? 'bg' : 'br' }}"
                                style="border:none;cursor:pointer;font-size:11px;">
                            {{ $poste->actif ? 'Actif' : 'Inactif' }}
                        </button>
                    </form>
                </td>
                <td style="text-align:right;">
                    <div style="display:flex;gap:6px;justify-content:flex-end;">
                        <a href="{{ route('parametrage.postes.edit', $poste) }}" class="tb-btn tb-btn-sm" title="Modifier">
                            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        @if($poste->employes_count === 0)
                        <form method="POST" action="{{ route('parametrage.postes.destroy', $poste) }}"
                              onsubmit="return confirm('Supprimer «{{ $poste->nom }}» ?')">
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
            <tr>
                <td colspan="6">
                    <x-rh.empty-state
                        message="Aucun poste défini"
                        sub="Créez votre référentiel pour structurer les fonctions de l'entreprise." />
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{ $postes->links('components.pagination') }}

</div>

<style>
.tb-btn-sm { padding:4px 8px !important; }
.tb-btn-danger { color:var(--danger) !important; border-color:var(--danger-light,#fecaca) !important; }
.tb-btn-danger:hover { background:var(--danger-light,#fef2f2) !important; }
.data-table { width:100%;border-collapse:collapse; }
.data-table th { padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);background:var(--surface-soft);border-bottom:1px solid var(--border); }
.data-table td { padding:12px 14px;font-size:13px;color:var(--text-secondary);border-bottom:1px solid var(--border-light);vertical-align:middle; }
.data-table tbody tr:hover { background:var(--surface-soft); }
.badge { display:inline-flex;align-items:center;padding:3px 8px;border-radius:20px;font-size:11px;font-weight:600; }
.bg  { background:#dcfce7;color:#16a34a; }
.br  { background:#fee2e2;color:#dc2626; }
</style>
</x-app-layout>
