<x-app-layout>
<x-rh.page-header
    :title="$mode === 'create' ? 'Nouvel employé' : 'Modifier — '.$employe->nom_complet"
    :breadcrumbs="['Personnel' => route('personnel.index'), $mode === 'create' ? 'Nouveau' : 'Modifier' => null]">
    <a href="{{ $mode === 'edit' ? route('personnel.show', $employe) : route('personnel.index') }}" class="tb-btn">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Retour
    </a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if($errors->any())
<x-rh.alert type="danger" :message="'Veuillez corriger les '.$errors->count().' erreur(s) ci-dessous.'" />
@endif

<form method="POST"
      action="{{ $mode === 'create' ? route('personnel.store') : route('personnel.update', $employe) }}"
      enctype="multipart/form-data"
      style="display:flex;flex-direction:column;gap:16px;">
@csrf
@if($mode === 'edit') @method('PUT') @endif

{{-- ── Identité ──────────────────────────────────────────────────── --}}
<x-rh.form-section title="Identité">
    <x-rh.form-field label="Nom" name="nom" :required="true">
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
               value="{{ old('nom', $employe->nom) }}" placeholder="Nom de famille">
    </x-rh.form-field>
    <x-rh.form-field label="Prénom" name="prenom" :required="true">
        <input type="text" name="prenom" class="form-control @error('prenom') is-invalid @enderror"
               value="{{ old('prenom', $employe->prenom) }}" placeholder="Prénom">
    </x-rh.form-field>
    <x-rh.form-field label="CIN" name="cin" :required="true">
        <input type="text" name="cin" class="form-control @error('cin') is-invalid @enderror"
               value="{{ old('cin', $employe->cin) }}" placeholder="Ex: AB123456">
    </x-rh.form-field>
    <x-rh.form-field label="Date de naissance" name="date_naissance" :required="true">
        <input type="date" name="date_naissance" class="form-control @error('date_naissance') is-invalid @enderror"
               max="{{ date('Y-m-d') }}"
               value="{{ old('date_naissance', $employe->date_naissance?->format('Y-m-d')) }}">
    </x-rh.form-field>
    <x-rh.form-field label="Sexe" name="sexe" :required="true">
        <select name="sexe" class="form-control @error('sexe') is-invalid @enderror">
            <option value="">— Choisir —</option>
            <option value="M" {{ old('sexe', $employe->sexe) === 'M' ? 'selected' : '' }}>Masculin</option>
            <option value="F" {{ old('sexe', $employe->sexe) === 'F' ? 'selected' : '' }}>Féminin</option>
        </select>
    </x-rh.form-field>
    <x-rh.form-field label="Situation familiale" name="situation_familiale" :required="true">
        <select name="situation_familiale" class="form-control @error('situation_familiale') is-invalid @enderror">
            <option value="">— Choisir —</option>
            @foreach(['celibataire'=>'Célibataire','marie'=>'Marié(e)','divorce'=>'Divorcé(e)','veuf'=>'Veuf/Veuve'] as $v => $l)
            <option value="{{ $v }}" {{ old('situation_familiale', $employe->situation_familiale) === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </x-rh.form-field>
    <x-rh.form-field label="Nombre d'enfants" name="nombre_enfants" :required="true">
        <input type="number" name="nombre_enfants" class="form-control @error('nombre_enfants') is-invalid @enderror"
               value="{{ old('nombre_enfants', $employe->nombre_enfants ?? 0) }}" min="0" max="20">
    </x-rh.form-field>
    <x-rh.form-field label="Photo" name="photo">
        <input type="file" name="photo" class="form-control @error('photo') is-invalid @enderror" accept="image/*">
        @if($employe->photo)
        <img src="{{ Storage::url($employe->photo) }}" style="height:40px;width:40px;border-radius:50%;object-fit:cover;margin-top:6px;border:1px solid var(--border);">
        @endif
    </x-rh.form-field>
</x-rh.form-section>

{{-- ── Poste ─────────────────────────────────────────────────────── --}}
<x-rh.form-section title="Poste & Affectation">
    <x-rh.form-field label="Catégorie" name="categorie" :required="true">
        <select name="categorie" class="form-control @error('categorie') is-invalid @enderror">
            <option value="">— Choisir —</option>
            @foreach(\App\Models\Employe::CATEGORIES as $v => $l)
            <option value="{{ $v }}" {{ old('categorie', $employe->categorie) === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </x-rh.form-field>
    <x-rh.form-field label="Poste / Fonction" name="poste_id" :required="true">
        <select name="poste_id" class="form-control @error('poste_id') is-invalid @enderror">
            <option value="">— Sélectionner un poste —</option>
            @foreach($postes as $p)
            <option value="{{ $p->id }}" {{ old('poste_id', $employe->poste_id) == $p->id ? 'selected' : '' }}>
                {{ $p->numero }} — {{ $p->nom }}
            </option>
            @endforeach
        </select>
        @if($postes->isEmpty())
        <div class="form-hint" style="color:var(--warning,#d97706);">
            ⚠️ Aucun poste actif —
            <a href="{{ route('parametrage.postes.create') }}" target="_blank">Créer le référentiel</a>
        </div>
        @endif
    </x-rh.form-field>
    <x-rh.form-field label="Date d'embauche" name="date_embauche" :required="true">
        <input type="date" name="date_embauche" class="form-control @error('date_embauche') is-invalid @enderror"
               max="{{ date('Y-m-d') }}"
               value="{{ old('date_embauche', $employe->date_embauche?->format('Y-m-d')) }}">
    </x-rh.form-field>
    <x-rh.form-field label="Diplôme" name="diplome">
        <input type="text" name="diplome" class="form-control @error('diplome') is-invalid @enderror"
               value="{{ old('diplome', $employe->diplome) }}" placeholder="Ex: Bac+2, Licence...">
    </x-rh.form-field>
    <x-rh.form-field label="Spécialité" name="specialite">
        <input type="text" name="specialite" class="form-control @error('specialite') is-invalid @enderror"
               value="{{ old('specialite', $employe->specialite) }}" placeholder="Domaine de spécialité">
    </x-rh.form-field>
</x-rh.form-section>

{{-- ── Coordonnées ───────────────────────────────────────────────── --}}
<x-rh.form-section title="Coordonnées">
    <x-rh.form-field label="Email" name="email">
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $employe->email) }}" placeholder="email@exemple.com">
    </x-rh.form-field>
    <x-rh.form-field label="Téléphone" name="telephone">
        <input type="text" name="telephone" class="form-control @error('telephone') is-invalid @enderror"
               value="{{ old('telephone', $employe->telephone) }}" placeholder="+212 6 XX XX XX XX">
    </x-rh.form-field>
    <x-rh.form-field label="Ville" name="ville">
        <input type="text" name="ville" class="form-control @error('ville') is-invalid @enderror"
               value="{{ old('ville', $employe->ville) }}" placeholder="Kénitra, Rabat...">
    </x-rh.form-field>
    <x-rh.form-field label="Adresse" name="adresse" :full="true">
        <input type="text" name="adresse" class="form-control @error('adresse') is-invalid @enderror"
               value="{{ old('adresse', $employe->adresse) }}" placeholder="Adresse complète">
    </x-rh.form-field>
</x-rh.form-section>

{{-- ── Banque & Affiliation ──────────────────────────────────────── --}}
<x-rh.form-section title="Banque & Affiliation sociale">
    <x-rh.form-field label="Banque" name="banque">
        <input type="text" name="banque" class="form-control @error('banque') is-invalid @enderror"
               value="{{ old('banque', $employe->banque) }}" placeholder="CIH, Attijariwafa...">
    </x-rh.form-field>
    <x-rh.form-field label="RIB" name="rib">
        <input type="text" name="rib" class="form-control @error('rib') is-invalid @enderror"
               value="{{ old('rib', $employe->rib) }}" placeholder="007 780 000XXXXXXXXXX 00">
    </x-rh.form-field>
    <x-rh.form-field label="N° CNSS" name="numero_cnss">
        <input type="text" name="numero_cnss" class="form-control @error('numero_cnss') is-invalid @enderror"
               value="{{ old('numero_cnss', $employe->numero_cnss) }}">
    </x-rh.form-field>
    <x-rh.form-field label="N° AMO" name="numero_amo">
        <input type="text" name="numero_amo" class="form-control @error('numero_amo') is-invalid @enderror"
               value="{{ old('numero_amo', $employe->numero_amo) }}">
    </x-rh.form-field>
</x-rh.form-section>

{{-- ── Actions ───────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;padding-bottom:8px;">
    <a href="{{ $mode === 'edit' ? route('personnel.show', $employe) : route('personnel.index') }}" class="tb-btn">Annuler</a>
    <button type="submit" class="btn-new">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $mode === 'create' ? "Créer l'employé" : 'Enregistrer' }}
    </button>
</div>

</form>
</div>

<style>
.form-card { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm); overflow:hidden; }
.form-card-header { display:flex; align-items:center; gap:10px; padding:14px 18px; border-bottom:1px solid var(--border-light); background:var(--surface-soft); }
.form-card-title { font-size:13px; font-weight:600; color:var(--text-primary); }
.form-card-body { padding:18px; }
.form-grid-2 { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; }
.form-group { display:flex; flex-direction:column; gap:5px; }
.form-group.form-grid-full { grid-column:1/-1; }
.form-label { font-size:12px; font-weight:500; color:var(--text-secondary); }
.form-required { color:var(--danger); margin-left:2px; }
.form-control { height:34px; padding:0 10px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px; color:var(--text-primary); background:var(--surface); font-family:inherit; transition:border-color 0.15s,box-shadow 0.15s; width:100%; }
.form-control:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft); }
.form-control.is-invalid { border-color:var(--danger); }
.form-error { font-size:11px; color:var(--danger); }
.form-hint { font-size:11px; color:var(--text-muted); }
select.form-control { cursor:pointer; }
</style>
</x-app-layout>
