<x-app-layout>

<div class="page-header">
    <div class="page-title">
        Utilisateurs
        <span class="badge-count">{{ $users->count() }}</span>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.users.create') }}" class="btn-new">
            <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Nouvel utilisateur
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="margin-bottom:16px;">{{ session('error') }}</div>
@endif

<div class="list-card">
    <div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th><span class="th-in">Nom</span></th>
                <th><span class="th-in">Email</span></th>
                <th><span class="th-in">Rôle</span></th>
                <th><span class="th-in">Employé lié</span></th>
                <th class="tc"><span class="th-in">Statut</span></th>
                <th><span class="th-in">Dernière connexion</span></th>
                <th class="tc"><span class="th-in">Actions</span></th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $user)
        <tr style="{{ !$user->actif ? 'opacity:0.6;' : '' }}">
            <td>
                <div style="font-weight:500;color:var(--text-primary);">{{ $user->name }}</div>
                @if($user->telephone)
                    <div style="font-size:11px;color:var(--text-muted);">{{ $user->telephone }}</div>
                @endif
            </td>
            <td class="muted">{{ $user->email }}</td>
            <td>
                @php
                    $roleClass = match($user->role) {
                        'admin'        => 'br',
                        'gestionnaire' => 'bb',
                        'salarie'      => 'bpu',
                        default        => 'bgr',
                    };
                @endphp
                <span class="badge {{ $roleClass }}">{{ $user->role_label }}</span>
            </td>
            <td>
                @if($user->employe_id && $user->employe)
                    <a href="{{ route('personnel.show', $user->employe_id) }}" class="link" style="font-size:12px;">
                        {{ $user->employe->nom_complet }}
                    </a>
                @else
                    <span class="muted">—</span>
                @endif
            </td>
            <td class="tc">
                @if($user->actif)
                    <span class="badge bg"><span class="dot"></span>Actif</span>
                @else
                    <span class="badge bgr"><span class="dot"></span>Inactif</span>
                @endif
            </td>
            <td class="muted">
                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : '—' }}
            </td>
            <td class="tc">
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;">
                    <a href="{{ route('admin.users.edit', $user) }}" class="link" style="font-size:12px;">Modifier</a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit" style="background:none;border:none;cursor:pointer;font-size:12px;color:{{ $user->actif ? 'var(--warning)' : 'var(--success)' }};">
                            {{ $user->actif ? 'Désactiver' : 'Activer' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                          onsubmit="return confirm('Supprimer {{ $user->name }} ?')" style="display:inline;">
                        @csrf @method('DELETE')
                        <button type="submit" style="background:none;border:none;cursor:pointer;font-size:12px;color:var(--danger);">Supprimer</button>
                    </form>
                    @endif
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px;">Aucun utilisateur.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

</x-app-layout>
