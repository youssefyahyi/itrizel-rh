<x-app-layout>
@php $isEdit = $scenario->exists; @endphp

<x-rh.page-header
    :breadcrumbs="['Projection' => route('projection.index'), $isEdit ? 'Modifier le scénario' : 'Nouveau scénario' => null]">
</x-rh.page-header>

<div style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;max-width:860px;">

@if($errors->any())
<x-rh.alert type="danger" message="{{ implode(' — ', $errors->all()) }}" />
@endif

<form method="POST"
      action="{{ $isEdit ? route('projection.update', $scenario) : route('projection.store') }}">
    @csrf
    @if($isEdit) @method('PUT') @endif

    {{-- En-tête scénario --}}
    <div class="info-card" style="padding:20px;display:flex;flex-direction:column;gap:14px;">
        <div style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">
            Informations du scénario
        </div>

        <div style="display:grid;grid-template-columns:1fr 180px;gap:12px;">
            <div>
                <label class="uf-label">Nom <span style="color:var(--danger)">*</span></label>
                <input type="text" name="nom" value="{{ old('nom', $scenario->nom) }}"
                       class="uf-control" required placeholder="Ex : Augmentation Q3 2026">
            </div>
            <div>
                <label class="uf-label">Horizon</label>
                <select name="horizon" class="uf-control">
                    @foreach([6, 12, 24] as $h)
                    <option value="{{ $h }}" {{ old('horizon', $scenario->horizon) == $h ? 'selected' : '' }}>
                        {{ $h }} mois
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="uf-label">Description (optionnel)</label>
            <input type="text" name="description" value="{{ old('description', $scenario->description) }}"
                   class="uf-control" placeholder="Contexte, hypothèses…" maxlength="500">
        </div>
    </div>

    {{-- Lignes --}}
    <div class="info-card" style="padding:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
            <span style="font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;">
                Hypothèses du scénario
            </span>
            <button type="button" onclick="ajouterLigne()" class="btn btn-outline btn-sm">+ Ajouter</button>
        </div>

        <div id="lignes-container" style="display:flex;flex-direction:column;gap:10px;">
            @php $existantes = old('lignes', $isEdit ? $scenario->lignes->toArray() : []); @endphp
            @forelse($existantes as $i => $ligne)
            <div class="ligne-row" data-idx="{{ $i }}">
                @include('projection._ligne', ['i' => $i, 'ligne' => $ligne])
            </div>
            @empty
            <div id="empty-hint" style="font-size:13px;color:var(--text-muted);text-align:center;padding:20px;">
                Aucune hypothèse — cliquez sur <strong>+ Ajouter</strong> pour créer une ligne.
            </div>
            @endforelse
        </div>
    </div>

    {{-- Actions --}}
    <div style="display:flex;gap:10px;align-items:center;">
        <button type="submit" class="btn btn-primary btn-sm">
            {{ $isEdit ? 'Enregistrer les modifications' : 'Créer le scénario' }}
        </button>
        <a href="{{ route('projection.index') }}" class="tb-btn">Annuler</a>
        @if($isEdit)
        <form method="POST" action="{{ route('projection.destroy', $scenario) }}" style="margin-left:auto;">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline btn-sm" style="color:var(--danger);border-color:var(--danger);"
                onclick="return confirm('Supprimer ce scénario définitivement ?')">
                Supprimer
            </button>
        </form>
        @endif
    </div>

</form>
</div>

{{-- Template ligne (hidden) --}}
<template id="tpl-ligne">
    <div class="ligne-row" data-idx="__IDX__">
        @include('projection._ligne', ['i' => '__IDX__', 'ligne' => []])
    </div>
</template>

<script>
const CATEGORIES = @json($categories->map(fn($c) => ['id' => $c->id, 'nom' => $c->nom]));
const EMPLOYES   = @json($employes->map(fn($e) => ['id' => $e->id, 'nom' => $e->nom . ' ' . $e->prenom, 'mat' => $e->matricule]));

let idxCounter = {{ count($existantes ?? []) }};

function ajouterLigne() {
    document.getElementById('empty-hint')?.remove();
    const tpl = document.getElementById('tpl-ligne').innerHTML.replaceAll('__IDX__', idxCounter);
    const div = document.createElement('div');
    div.innerHTML = tpl;
    const newRow = div.firstElementChild;
    document.getElementById('lignes-container').appendChild(newRow);
    onTypeChange(idxCounter);
    idxCounter++;
}

function supprimerLigne(btn) {
    btn.closest('.ligne-row').remove();
    if (!document.querySelector('.ligne-row')) {
        const hint = document.createElement('div');
        hint.id = 'empty-hint';
        hint.style = 'font-size:13px;color:var(--text-muted);text-align:center;padding:20px;';
        hint.innerHTML = 'Aucune hypothèse — cliquez sur <strong>+ Ajouter</strong> pour créer une ligne.';
        document.getElementById('lignes-container').appendChild(hint);
    }
}

function onTypeChange(idx) {
    const row = document.querySelector('[data-idx="' + idx + '"]');
    if (!row) return;
    const type = row.querySelector('[name="lignes[' + idx + '][type]"]')?.value;

    // Masquer et désactiver tout
    row.querySelectorAll('.champ-rev, .champ-emb, .champ-dep').forEach(el => {
        el.style.display = 'none';
        el.querySelectorAll('input, select').forEach(f => f.disabled = true);
    });

    const show = (cls) => row.querySelectorAll(cls).forEach(el => {
        el.style.display = '';
        el.querySelectorAll('input, select').forEach(f => f.disabled = false);
    });

    if (type === 'revalorisation') {
        show('.champ-rev');
        onCiblageChange(idx); // applique l'état du ciblage
    }
    if (type === 'embauche') show('.champ-emb');
    if (type === 'depart')   show('.champ-dep');
}

function onCiblageChange(idx) {
    const row = document.querySelector('[data-idx="' + idx + '"]');
    if (!row) return;
    const ciblage = row.querySelector('[name="lignes[' + idx + '][ciblage]"]')?.value;

    const catBlock = row.querySelector('.rev-cat-block');
    const empBlock = row.querySelector('.rev-emp-block');

    if (catBlock) {
        catBlock.style.display = ciblage === 'categorie' ? '' : 'none';
        catBlock.querySelectorAll('select').forEach(f => f.disabled = ciblage !== 'categorie');
    }
    if (empBlock) {
        empBlock.style.display = ciblage === 'employe' ? '' : 'none';
        empBlock.querySelectorAll('select').forEach(f => f.disabled = ciblage !== 'employe');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.ligne-row').forEach(row => {
        onTypeChange(row.dataset.idx);
    });
});
</script>

<style>
.uf-label { display:block; font-size:12px; font-weight:500; color:var(--text-secondary); margin-bottom:5px; }
.uf-control { width:100%; padding:9px 12px; border:1px solid var(--border); border-radius:8px; font-family:inherit; font-size:13px; color:var(--text-primary); background:#fff; outline:none; }
.uf-control:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft); }
.ligne-card { background:var(--surface-soft); border:1px solid var(--border-light); border-radius:8px; padding:12px 14px; display:flex; gap:10px; align-items:flex-start; }
</style>
</x-app-layout>
