<x-app-layout>

{{-- Fil d'ariane --}}
<div style="display:flex;align-items:center;gap:8px;font-size:13px;margin-bottom:16px;">
    <a href="{{ route('admin.teams.index') }}" style="color:var(--text-muted);text-decoration:none;">Équipes</a>
    <span style="color:var(--text-muted);">›</span>
    <span style="color:var(--text-primary);font-weight:500;">{{ $team->name }}</span>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif

{{-- En-tête équipe --}}
<div class="card" style="margin-bottom:20px;">
    <div style="padding:20px 24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="width:44px;height:44px;border-radius:12px;background:{{ $team->couleur }}20;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <div style="width:18px;height:18px;border-radius:50%;background:{{ $team->couleur }};"></div>
                </div>
                <div>
                    <h1 style="font-size:18px;font-weight:600;color:var(--text-primary);margin-bottom:4px;">{{ $team->name }}</h1>
                    <div style="font-size:13px;color:var(--text-muted);">{{ $team->description ?? 'Aucune description' }}</div>
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <a href="{{ route('admin.teams.edit', $team) }}" class="tb-btn">Modifier</a>
                <form method="POST" action="{{ route('admin.teams.destroy', $team) }}"
                      onsubmit="return confirm('Supprimer l\'équipe {{ $team->name }} ?')">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:none;border:1px solid var(--border);border-radius:var(--radius-sm);padding:6px 10px;font-size:12px;color:var(--danger);cursor:pointer;font-family:inherit;">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border-light);">
            <div>
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Membres</div>
                <div style="font-size:22px;font-weight:700;color:var(--text-primary);">{{ $members->count() }}</div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Statut</div>
                <div style="margin-top:4px;">
                    @if($team->actif)
                        <span class="badge bg"><span class="dot"></span>Active</span>
                    @else
                        <span class="badge bgr"><span class="dot"></span>Inactive</span>
                    @endif
                </div>
            </div>
            <div>
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">Créée le</div>
                <div style="font-size:13px;font-weight:500;color:var(--text-primary);">{{ $team->created_at->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    {{-- Membres de l'équipe --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Membres ({{ $members->count() }})</span>
        </div>
        @forelse($members as $user)
        <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border-light);">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:34px;height:34px;border-radius:50%;background:var(--accent);color:#fff;font-size:12px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div style="font-size:13px;font-weight:500;color:var(--text-primary);">{{ $user->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $user->email }}</div>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                @php
                    $rc = match($user->role) { 'admin'=>'br','gestionnaire'=>'bb',default=>'bgr' };
                @endphp
                <span class="badge {{ $rc }}">{{ $user->role_label }}</span>
                <form method="POST" action="{{ route('admin.teams.members.remove', [$team, $user]) }}">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--text-muted);padding:4px;border-radius:4px;display:flex;transition:color 0.15s;" title="Retirer de l'équipe" onmouseover="this.style.color='var(--danger)'" onmouseout="this.style.color='var(--text-muted)'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:30px;">Aucun membre dans cette équipe.</div>
        @endforelse
    </div>

    {{-- Ajouter un membre --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Ajouter un membre</span>
        </div>
        <div style="padding:16px;">
            @forelse($nonMembers as $user)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border-light);">
                <div>
                    <div style="font-size:13px;font-weight:500;color:var(--text-primary);">{{ $user->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">
                        {{ $user->role_label }}
                        @if($user->team) · <span style="color:var(--warning);">{{ $user->team->name }}</span>@endif
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.teams.members.add', [$team, $user]) }}">
                    @csrf
                    <button type="submit" class="tb-btn" style="padding:4px 10px;font-size:11px;">Ajouter</button>
                </form>
            </div>
            @empty
            <div style="font-size:13px;color:var(--text-muted);padding:8px 0;">Tous les utilisateurs sont déjà membres.</div>
            @endforelse
        </div>
    </div>

</div>

</x-app-layout>
