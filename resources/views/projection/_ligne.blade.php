@php
$type      = $ligne['type'] ?? 'revalorisation';
$moisEffet = isset($ligne['mois_effet']) ? substr($ligne['mois_effet'], 0, 7) : '';

// Dériver le ciblage depuis les données stockées (revalorisation uniquement)
$ciblage = 'global';
if (!empty($ligne['employe_id']) && $type === 'revalorisation') $ciblage = 'employe';
elseif (!empty($ligne['categorie_id']) && $type === 'revalorisation') $ciblage = 'categorie';
@endphp

<div class="ligne-card">
    {{-- Type --}}
    <div style="min-width:155px;">
        <label class="uf-label">Type</label>
        <select name="lignes[{{ $i }}][type]" class="uf-control"
                onchange="onTypeChange('{{ $i }}')">
            <option value="revalorisation" {{ $type === 'revalorisation' ? 'selected' : '' }}>Revalorisation</option>
            <option value="embauche"       {{ $type === 'embauche'       ? 'selected' : '' }}>Embauche</option>
            <option value="depart"         {{ $type === 'depart'         ? 'selected' : '' }}>Départ</option>
        </select>
    </div>

    {{-- Mois d'effet --}}
    <div style="min-width:120px;">
        <label class="uf-label">À partir de</label>
        <input type="month" name="lignes[{{ $i }}][mois_effet]"
               value="{{ $moisEffet }}"
               class="uf-control" required>
    </div>

    {{-- ── REVALORISATION ─────────────────────────────────────────── --}}
    <div class="champ-rev" style="display:none;min-width:110px;">
        <label class="uf-label">Pourcentage %</label>
        <input type="number" name="lignes[{{ $i }}][pourcentage]"
               value="{{ $ligne['pourcentage'] ?? '' }}"
               class="uf-control" step="0.1" placeholder="Ex : 5">
    </div>

    <div class="champ-rev" style="display:none;min-width:145px;">
        <label class="uf-label">Ciblage</label>
        <select name="lignes[{{ $i }}][ciblage]" class="uf-control"
                onchange="onCiblageChange('{{ $i }}')">
            <option value="global"    {{ $ciblage === 'global'    ? 'selected' : '' }}>Global</option>
            <option value="categorie" {{ $ciblage === 'categorie' ? 'selected' : '' }}>Par catégorie</option>
            <option value="employe"   {{ $ciblage === 'employe'   ? 'selected' : '' }}>Par employé</option>
        </select>
    </div>

    {{-- Catégorie (revalorisation par catégorie) --}}
    <div class="champ-rev rev-cat-block" style="display:none;min-width:160px;">
        <label class="uf-label">Catégorie</label>
        <select name="lignes[{{ $i }}][categorie_id]" class="uf-control" {{ $ciblage !== 'categorie' ? 'disabled' : '' }}>
            <option value="">— Toutes —</option>
            @foreach($categories ?? [] as $cat)
            <option value="{{ $cat->id }}" {{ ($ligne['categorie_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                {{ $cat->nom }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- Employé (revalorisation par employé) --}}
    <div class="champ-rev rev-emp-block" style="display:none;flex:1;min-width:200px;">
        <label class="uf-label">Employé</label>
        <select name="lignes[{{ $i }}][employe_id]" class="uf-control" {{ $ciblage !== 'employe' ? 'disabled' : '' }}>
            <option value="">— Sélectionner —</option>
            @foreach($employes ?? [] as $emp)
            <option value="{{ $emp->id }}"
                {{ ($type === 'revalorisation' && ($ligne['employe_id'] ?? '') == $emp->id) ? 'selected' : '' }}>
                {{ $emp->nom }} {{ $emp->prenom }} ({{ $emp->matricule }})
            </option>
            @endforeach
        </select>
    </div>

    {{-- ── EMBAUCHE ────────────────────────────────────────────────── --}}
    <div class="champ-emb" style="display:none;min-width:160px;">
        <label class="uf-label">Catégorie</label>
        <select name="lignes[{{ $i }}][categorie_id]" class="uf-control" disabled>
            <option value="">— Catégorie —</option>
            @foreach($categories ?? [] as $cat)
            <option value="{{ $cat->id }}" {{ ($type === 'embauche' && ($ligne['categorie_id'] ?? '') == $cat->id) ? 'selected' : '' }}>
                {{ $cat->nom }}
            </option>
            @endforeach
        </select>
    </div>

    <div class="champ-emb" style="display:none;min-width:130px;">
        <label class="uf-label">Salaire estimé (DH)</label>
        <input type="number" name="lignes[{{ $i }}][salaire_estime]"
               value="{{ $ligne['salaire_estime'] ?? '' }}"
               class="uf-control" step="100" min="0" placeholder="Ex : 12000" disabled>
    </div>

    <div class="champ-emb" style="display:none;min-width:155px;">
        <label class="uf-label">Unité (optionnel)</label>
        <select name="lignes[{{ $i }}][unite_id]" class="uf-control" disabled>
            <option value="">— Non affectée —</option>
            @foreach($unites ?? [] as $unite)
            <option value="{{ $unite->id }}"
                {{ ($type === 'embauche' && ($ligne['unite_id'] ?? '') == $unite->id) ? 'selected' : '' }}>
                {{ $unite->nom }}
            </option>
            @endforeach
        </select>
    </div>

    {{-- ── DÉPART ──────────────────────────────────────────────────── --}}
    <div class="champ-dep" style="display:none;flex:1;min-width:200px;">
        <label class="uf-label">Employé</label>
        <select name="lignes[{{ $i }}][employe_id]" class="uf-control" disabled>
            <option value="">— Sélectionner —</option>
            @foreach($employes ?? [] as $emp)
            <option value="{{ $emp->id }}"
                {{ ($type === 'depart' && ($ligne['employe_id'] ?? '') == $emp->id) ? 'selected' : '' }}>
                {{ $emp->nom }} {{ $emp->prenom }} ({{ $emp->matricule }})
            </option>
            @endforeach
        </select>
    </div>

    {{-- Supprimer --}}
    <div style="padding-top:20px;">
        <button type="button" onclick="supprimerLigne(this)"
                style="background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:18px;line-height:1;padding:2px 6px;"
                title="Supprimer">×</button>
    </div>
</div>
