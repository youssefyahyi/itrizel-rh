<x-app-layout>

<div class="page-header">
    <div class="page-title">Équipes <span class="badge-count">{{ $teams->count() }}</span></div>
    <a href="{{ route('admin.teams.create') }}" class="btn-new">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Nouvelle équipe
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif

<div class="list-card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th><span class="th-in">Équipe</span></th>
                <th><span class="th-in">Description</span></th>
                <th class="tc"><span class="th-in">Membres</span></th>
                <th class="tc"><span class="th-in">Statut</span></th>
                <th class="tc"><span class="th-in">Actions</span></th>
            </tr>
        </thead>
        <tbody>
        @forelse($teams as $team)
        <tr>
            <td>
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:12px;height:12px;border-radius:50%;background:{{ $team->couleur }};flex-shrink:0;"></div>
                    <span style="font-weight:500;color:var(--text-primary);">{{ $team->name }}</span>
                </div>
            </td>
            <td class="muted">{{ $team->description ?? '—' }}</td>
            <td class="tc">
                <span class="badge bb">{{ $team->users_count }} membre{{ $team->users_count > 1 ? 's' : '' }}</span>
            </td>
            <td class="tc">
                @if($team->actif)
                    <span class="badge bg"><span class="dot"></span>Active</span>
                @else
                    <span class="badge bgr"><span class="dot"></span>Inactive</span>
                @endif
            </td>
            <td class="tc">
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;">
                    <a href="{{ route('admin.teams.show', $team) }}" class="link" style="font-size:12px;">Voir</a>
                    <a href="{{ route('admin.teams.edit', $team) }}" class="link" style="font-size:12px;">Modifier</a>
                    <form method="POST" action="{{ route('admin.teams.destroy', $team) }}"
                          onsubmit="return confirm('Supprimer l\'équipe {{ $team->name }} ?')" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none;border:none;cursor:pointer;font-size:12px;color:var(--danger);">Supprimer</button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:40px;">Aucune équipe.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

</x-app-layout>
