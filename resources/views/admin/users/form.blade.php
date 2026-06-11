<x-app-layout>

@php
$isEdit = $user->exists;
$currentRole = old('role', $user->role ?? 'gestionnaire');

// Données employés pour l'auto-remplissage JS
$employes_json = $employes->mapWithKeys(fn($e) => [
    $e->id => [
        'nom_complet' => $e->nom . ' ' . $e->prenom,
        'telephone'   => $e->telephone ?? '',
        'email'       => $e->email ?? '',
        'prenom'      => $e->prenom,
        'nom'         => $e->nom,
        'matricule'   => $e->matricule,
    ]
]);
@endphp

<div class="page-header">
    <div class="page-title">
        {{ $isEdit ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}
    </div>

</div>

<div class="list-card" style="max-width:560px;">
    <form method="POST"
          action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}"
          style="padding:24px 28px;">
        @csrf
        @if($isEdit) @method('PUT') @endif

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:20px;">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        {{-- ── Rôle (en premier pour déclencher l'affichage) ── --}}
        <div class="uf-group">
            <label class="uf-label">Rôle <span class="req">*</span></label>
            <select name="role" id="role-select" class="uf-control" required onchange="onRoleChange()">
                <option value="gestionnaire" {{ $currentRole === 'gestionnaire' ? 'selected' : '' }}>Gestionnaire</option>
                <option value="lecteur"      {{ $currentRole === 'lecteur'      ? 'selected' : '' }}>Lecteur (lecture seule)</option>
                <option value="admin"        {{ $currentRole === 'admin'        ? 'selected' : '' }}>Administrateur</option>
                <option value="salarie"      {{ $currentRole === 'salarie'      ? 'selected' : '' }}>Salarié — accès à sa propre fiche</option>
            </select>
        </div>

        {{-- ── Sélection employé (visible si salarié) ── --}}
        <div id="employe-block" class="uf-group uf-salarie-field" style="display:{{ $currentRole === 'salarie' ? 'block' : 'none' }};background:var(--accent-light);border:1px solid rgba(37,99,235,0.15);border-radius:10px;padding:14px 16px;margin-bottom:0;">
            <label class="uf-label" style="color:var(--accent);">Employé à lier <span class="req">*</span></label>
            <select name="employe_id" id="employe-select" class="uf-control" onchange="onEmployeChange()">
                <option value="">— Sélectionner un employé —</option>
                @foreach($employes as $emp)
                    <option value="{{ $emp->id }}"
                            {{ old('employe_id', $user->employe_id ?? '') == $emp->id ? 'selected' : '' }}>
                        {{ $emp->nom }} {{ $emp->prenom }} — {{ $emp->matricule }}
                    </option>
                @endforeach
            </select>
            <div style="font-size:11px;color:var(--accent);margin-top:6px;opacity:0.8;">
                La sélection remplira automatiquement les champs ci-dessous.
            </div>
        </div>

        <div style="height:16px;"></div>

        {{-- ── Nom complet ── --}}
        <div class="uf-group">
            <label class="uf-label">Nom complet <span class="req">*</span></label>
            <input type="text" name="name" id="field-name"
                   value="{{ old('name', $user->name) }}"
                   class="uf-control" required placeholder="Prénom Nom">
        </div>

        {{-- ── Email ── --}}
        <div class="uf-group">
            <label class="uf-label">Adresse email <span class="req">*</span></label>
            <input type="email" name="email" id="field-email"
                   value="{{ old('email', $user->email) }}"
                   class="uf-control" required placeholder="prenom.nom@{{ $emailDomain }}">
        </div>

        {{-- ── Téléphone ── --}}
        <div class="uf-group">
            <label class="uf-label">Téléphone</label>
            <input type="text" name="telephone" id="field-telephone"
                   value="{{ old('telephone', $user->telephone) }}"
                   class="uf-control" placeholder="+212 6XX XXX XXX">
        </div>

        {{-- ── Mot de passe ── --}}
        <div class="uf-group">
            <label class="uf-label">
                Mot de passe
                @if($isEdit)<span style="font-weight:400;color:var(--text-muted);"> (laisser vide pour ne pas changer)</span>@endif
            </label>
            <input type="password" name="password" id="field-password"
                   class="uf-control" {{ $isEdit ? '' : 'required' }} placeholder="••••••••"
                   oninput="checkPasswordMatch()">
        </div>

        <div class="uf-group">
            <label class="uf-label">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" id="field-password-confirm"
                   class="uf-control" placeholder="••••••••"
                   oninput="checkPasswordMatch()">
            <div id="pwd-match-hint" style="font-size:11px;margin-top:4px;display:none;"></div>
        </div>

        {{-- ── Compte actif ── --}}
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:24px;padding:12px 14px;background:var(--surface-soft);border-radius:8px;border:1px solid var(--border-light);">
            <input type="checkbox" name="actif" id="actif" value="1"
                   {{ old('actif', $isEdit ? $user->actif : true) ? 'checked' : '' }}
                   style="accent-color:var(--accent);width:16px;height:16px;cursor:pointer;">
            <label for="actif" style="font-size:13px;color:var(--text-secondary);cursor:pointer;user-select:none;">Compte actif</label>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn-new">
                {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le compte' }}
            </button>
            <a href="{{ route('admin.users.index') }}" class="tb-btn">Annuler</a>
        </div>
    </form>
</div>

<style>
.uf-group { margin-bottom: 16px; }
.uf-label { display:block; font-size:12px; font-weight:500; color:var(--text-secondary); margin-bottom:6px; }
.uf-label .req { color:var(--danger); }
.uf-control { width:100%; padding:10px 13px; border:1px solid var(--border); border-radius:8px; font-family:inherit; font-size:13px; color:var(--text-primary); background:#fff; outline:none; transition:border-color 0.15s, box-shadow 0.15s; }
.uf-control:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft); }
.uf-control[readonly] { background:var(--surface-soft); color:var(--text-muted); }
</style>

<script>
const EMPLOYES = @json($employes_json);
const EMAIL_DOMAIN = '{{ $emailDomain }}';

function onRoleChange() {
    const role = document.getElementById('role-select').value;
    const block = document.getElementById('employe-block');
    block.style.display = role === 'salarie' ? 'block' : 'none';
    if (role !== 'salarie') {
        document.getElementById('employe-select').value = '';
    }
}

function slugify(str) {
    return str.normalize('NFD')
              .replace(/[̀-ͯ]/g, '')
              .toLowerCase()
              .replace(/\s+/g, '.');
}

function onEmployeChange() {
    const id = document.getElementById('employe-select').value;
    if (!id || !EMPLOYES[id]) return;

    const emp = EMPLOYES[id];

    // Nom complet
    document.getElementById('field-name').value = emp.nom_complet;

    // Téléphone
    if (emp.telephone) {
        document.getElementById('field-telephone').value = emp.telephone;
    }

    // Email — priorité : email propre de l'employé, sinon suggestion générée
    const emailField = document.getElementById('field-email');
    if (emp.email) {
        emailField.value = emp.email;
    } else {
        const suggested = slugify(emp.prenom) + '.' + slugify(emp.nom) + '@' + EMAIL_DOMAIN;
        emailField.value = suggested;
    }

    // Focus sur le champ mot de passe pour la suite
    document.getElementById('field-password').focus();
}

function checkPasswordMatch() {
    const pwd  = document.getElementById('field-password').value;
    const conf = document.getElementById('field-password-confirm').value;
    const hint = document.getElementById('pwd-match-hint');
    if (!conf) { hint.style.display = 'none'; return; }
    hint.style.display = 'block';
    if (pwd === conf) {
        hint.style.color = 'var(--success)';
        hint.textContent = '✓ Les mots de passe correspondent.';
    } else {
        hint.style.color = 'var(--danger)';
        hint.textContent = '✗ Les mots de passe ne correspondent pas.';
    }
}

// Init au chargement (cas edit avec rôle salarié déjà sélectionné)
document.addEventListener('DOMContentLoaded', onRoleChange);
</script>

</x-app-layout>
