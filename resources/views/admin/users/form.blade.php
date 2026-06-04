<x-app-layout>

<div class="page-header">
    <div class="page-title">
        {{ $user->exists ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
    </div>
    <a href="{{ route('admin.users.index') }}" class="tb-btn">← Retour</a>
</div>

<div class="list-card" style="max-width:540px;">
    <form method="POST"
          action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}"
          style="padding:24px;">
        @csrf
        @if($user->exists) @method('PUT') @endif

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:16px;">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <div class="form-group" style="margin-bottom:16px;">
            <label class="form-label">Nom complet</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                   class="form-control" required placeholder="Prénom Nom">
        </div>

        <div class="form-group" style="margin-bottom:16px;">
            <label class="form-label">Adresse email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                   class="form-control" required placeholder="prenom.nom@domaine.ma">
        </div>

        <div class="form-group" style="margin-bottom:16px;">
            <label class="form-label">Rôle</label>
            <select name="role" class="form-control" required>
                <option value="gestionnaire" {{ old('role', $user->role) === 'gestionnaire' ? 'selected' : '' }}>Gestionnaire</option>
                <option value="lecteur"      {{ old('role', $user->role) === 'lecteur'      ? 'selected' : '' }}>Lecteur (lecture seule)</option>
                <option value="admin"        {{ old('role', $user->role) === 'admin'        ? 'selected' : '' }}>Administrateur</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom:16px;">
            <label class="form-label">Équipe</label>
            <select name="team_id" class="form-control">
                <option value="">— Aucune équipe —</option>
                @foreach(\App\Models\Team::where('actif', true)->orderBy('name')->get() as $team)
                    <option value="{{ $team->id }}" {{ old('team_id', $user->team_id) == $team->id ? 'selected' : '' }}>
                        {{ $team->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group" style="margin-bottom:16px;">
            <label class="form-label">Téléphone</label>
            <input type="text" name="telephone" value="{{ old('telephone', $user->telephone) }}"
                   class="form-control" placeholder="+212 6XX XXX XXX">
        </div>

        <div class="form-group" style="margin-bottom:16px;">
            <label class="form-label">Mot de passe {{ $user->exists ? '(laisser vide pour ne pas changer)' : '' }}</label>
            <input type="password" name="password" class="form-control"
                   {{ $user->exists ? '' : 'required' }} placeholder="••••••••">
        </div>

        <div class="form-group" style="margin-bottom:24px;">
            <label class="form-label">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••">
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:24px;">
            <input type="checkbox" name="actif" id="actif" value="1"
                   {{ old('actif', $user->exists ? $user->actif : true) ? 'checked' : '' }}
                   style="accent-color:var(--accent);">
            <label for="actif" style="font-size:13px;color:var(--text-secondary);cursor:pointer;">Compte actif</label>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn-new">
                {{ $user->exists ? 'Enregistrer' : 'Créer l\'utilisateur' }}
            </button>
            <a href="{{ route('admin.users.index') }}" class="tb-btn">Annuler</a>
        </div>
    </form>
</div>

<style>
.form-group label.form-label { display:block;font-size:12px;font-weight:500;color:#334155;margin-bottom:6px; }
.form-control { width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;color:var(--text-primary);background:#fff;outline:none;transition:border-color 0.15s; }
.form-control:focus { border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft); }
</style>

</x-app-layout>
