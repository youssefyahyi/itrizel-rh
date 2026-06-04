<x-app-layout>

<div class="page-header">
    <div class="page-title">{{ $team->exists ? 'Modifier l\'équipe' : 'Nouvelle équipe' }}</div>
    <a href="{{ route('admin.teams.index') }}" class="tb-btn">← Retour</a>
</div>

<div class="list-card" style="max-width:480px;">
    <form method="POST"
          action="{{ $team->exists ? route('admin.teams.update', $team) : route('admin.teams.store') }}"
          style="padding:24px;">
        @csrf
        @if($team->exists) @method('PUT') @endif

        @if($errors->any())
            <div class="alert alert-danger" style="margin-bottom:16px;">
                @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
            </div>
        @endif

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:500;color:#334155;margin-bottom:6px;">Nom de l'équipe</label>
            <input type="text" name="name" value="{{ old('name', $team->name) }}"
                   style="width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;outline:none;"
                   required placeholder="Ex : Équipe technique">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:500;color:#334155;margin-bottom:6px;">Description</label>
            <input type="text" name="description" value="{{ old('description', $team->description) }}"
                   style="width:100%;padding:10px 13px;border:1px solid var(--border);border-radius:8px;font-family:inherit;font-size:13px;outline:none;"
                   placeholder="Optionnel">
        </div>

        <div style="margin-bottom:16px;">
            <label style="display:block;font-size:12px;font-weight:500;color:#334155;margin-bottom:6px;">Couleur</label>
            <div style="display:flex;align-items:center;gap:10px;">
                <input type="color" name="couleur" value="{{ old('couleur', $team->couleur ?? '#2563EB') }}"
                       style="width:40px;height:36px;border:1px solid var(--border);border-radius:6px;padding:2px;cursor:pointer;">
                <span style="font-size:12px;color:var(--text-muted);">Couleur de l'équipe dans l'interface</span>
            </div>
        </div>

        <div style="margin-bottom:24px;display:flex;align-items:center;gap:10px;">
            <input type="checkbox" name="actif" id="actif" value="1"
                   {{ old('actif', $team->exists ? $team->actif : true) ? 'checked' : '' }}
                   style="accent-color:var(--accent);">
            <label for="actif" style="font-size:13px;color:var(--text-secondary);cursor:pointer;">Équipe active</label>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn-new">
                {{ $team->exists ? 'Enregistrer' : 'Créer l\'équipe' }}
            </button>
            <a href="{{ route('admin.teams.index') }}" class="tb-btn">Annuler</a>
        </div>
    </form>
</div>

</x-app-layout>
