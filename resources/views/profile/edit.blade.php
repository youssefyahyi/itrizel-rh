<x-app-layout>

<div class="page-header">
    <div class="page-title">Mon profil</div>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success') }}</div>
@endif
@if(session('success_password'))
    <div class="alert alert-success" style="margin-bottom:16px;">{{ session('success_password') }}</div>
@endif

<div style="display:grid;grid-template-columns:260px 1fr;gap:20px;align-items:start;">

    {{-- Carte identité --}}
    <div class="card">
        <div style="padding:24px;text-align:center;border-bottom:1px solid var(--border-light);">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--accent);color:#fff;font-size:22px;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div style="font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:6px;">{{ $user->name }}</div>
            <div style="font-size:12px;color:var(--text-muted);margin-bottom:10px;">{{ $user->email }}</div>
            <span class="badge {{ $user->role === 'admin' ? 'br' : ($user->role === 'gestionnaire' ? 'bb' : 'bgr') }}">
                {{ $user->role_label }}
            </span>
        </div>
        <div style="padding:16px 20px;">
            @if($user->team)
            <div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--border-light);">
                <div style="width:10px;height:10px;border-radius:50%;background:{{ $user->team->couleur }};flex-shrink:0;"></div>
                <div>
                    <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;">Équipe</div>
                    <div style="font-size:13px;font-weight:500;color:var(--text-primary);">{{ $user->team->name }}</div>
                </div>
            </div>
            @endif
            <div style="padding:8px 0;border-bottom:1px solid var(--border-light);">
                <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Dernière connexion</div>
                <div style="font-size:13px;color:var(--text-primary);">
                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais enregistrée' }}
                </div>
            </div>
            <div style="padding:8px 0;">
                <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:2px;">Membre depuis</div>
                <div style="font-size:13px;color:var(--text-primary);">{{ $user->created_at->format('d/m/Y') }}</div>
            </div>
        </div>
    </div>

    {{-- Formulaires --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Informations personnelles --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Informations personnelles</span></div>
            <form method="POST" action="{{ route('profile.update') }}" style="padding:20px;">
                @csrf @method('PATCH')

                @if($errors->has('name') || $errors->has('email') || $errors->has('telephone'))
                    <div class="alert alert-danger" style="margin-bottom:16px;">
                        @foreach($errors->only(['name','email','telephone']) as $e)<div>{{ $e }}</div>@endforeach
                    </div>
                @endif

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:500;color:#334155;margin-bottom:6px;">Nom complet *</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                               style="width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:500;color:#334155;margin-bottom:6px;">Adresse email *</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               style="width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;">
                    </div>
                </div>
                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:12px;font-weight:500;color:#334155;margin-bottom:6px;">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone', $user->telephone) }}"
                           placeholder="+212 6XX XXX XXX"
                           style="width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;">
                </div>
                <button type="submit" class="btn-new">Enregistrer les modifications</button>
            </form>
        </div>

        {{-- Sécurité / Mot de passe --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Sécurité</span></div>
            <form method="POST" action="{{ route('profile.password') }}" style="padding:20px;">
                @csrf @method('PATCH')

                @error('current_password')
                    <div class="alert alert-danger" style="margin-bottom:16px;">{{ $message }}</div>
                @enderror

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:500;color:#334155;margin-bottom:6px;">Mot de passe actuel *</label>
                        <input type="password" name="current_password" required placeholder="••••••••"
                               style="width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:500;color:#334155;margin-bottom:6px;">Nouveau mot de passe *</label>
                        <input type="password" name="password" required placeholder="Minimum 8 caractères"
                               style="width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:500;color:#334155;margin-bottom:6px;">Confirmer *</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••"
                               style="width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;outline:none;box-sizing:border-box;">
                    </div>
                </div>
                <button type="submit" class="btn-new">Changer le mot de passe</button>
            </form>
        </div>

    </div>
</div>

</x-app-layout>
