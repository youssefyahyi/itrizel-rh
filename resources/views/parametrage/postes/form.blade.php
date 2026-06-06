<x-app-layout>
<x-rh.page-header
    :title="$mode === 'create' ? 'Nouveau poste' : 'Modifier — '.$poste->nom"
    :breadcrumbs="['Paramétrage' => route('parametrage.index'), 'Postes' => route('parametrage.postes.index'), $mode === 'create' ? 'Nouveau' : 'Modifier' => null]">
    <a href="{{ $mode === 'edit' ? route('parametrage.postes.show', $poste) : route('parametrage.postes.index') }}" class="tb-btn">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Retour
    </a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if($errors->any())
<x-rh.alert type="danger" :message="'Veuillez corriger les '.$errors->count().' erreur(s) ci-dessous.'" />
@endif

<form method="POST"
      action="{{ $mode === 'create' ? route('parametrage.postes.store') : route('parametrage.postes.update', $poste) }}"
      style="display:flex;flex-direction:column;gap:16px;">
@csrf
@if($mode === 'edit') @method('PUT') @endif

{{-- ── Identification ─────────────────────────────────────────── --}}
<x-rh.form-section title="Identification du poste">

    <x-rh.form-field label="Numéro" name="numero" :required="true">
        <input type="text" name="numero"
               class="form-control @error('numero') is-invalid @enderror"
               value="{{ old('numero', $poste->numero ?? $nextNumero ?? '') }}"
               placeholder="P001"
               style="font-family:monospace;letter-spacing:.5px;">
        <div class="form-hint">Identifiant unique du poste — ex : P001, DIR-ADM, RH-02</div>
        @error('numero') <div class="form-error">{{ $message }}</div> @enderror
    </x-rh.form-field>

    <x-rh.form-field label="Intitulé du poste" name="nom" :required="true">
        <input type="text" name="nom"
               class="form-control @error('nom') is-invalid @enderror"
               value="{{ old('nom', $poste->nom) }}"
               placeholder="Ex : Responsable commercial, Chauffeur livreur…">
        @error('nom') <div class="form-error">{{ $message }}</div> @enderror
    </x-rh.form-field>

    <x-rh.form-field label="Statut" name="actif">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;height:34px;">
            <input type="checkbox" name="actif" value="1"
                   {{ old('actif', $poste->actif ?? true) ? 'checked' : '' }}
                   style="width:16px;height:16px;accent-color:var(--accent);">
            <span style="font-size:13px;color:var(--text-secondary);">Poste actif (disponible à l'affectation)</span>
        </label>
    </x-rh.form-field>

</x-rh.form-section>

{{-- ── Compétences ────────────────────────────────────────────── --}}
<x-rh.form-section title="Compétences requises">

    <x-rh.form-field label="Compétences" name="competences" :full="true">
        <textarea name="competences"
                  class="form-control @error('competences') is-invalid @enderror"
                  rows="6"
                  style="height:auto;padding:8px 10px;resize:vertical;"
                  placeholder="Une compétence par ligne :&#10;Maîtrise des outils bureautiques&#10;Permis de conduire catégorie B&#10;Sens du relationnel client">{{ old('competences', $poste->competences_texte ?? '') }}</textarea>
        <div class="form-hint">Saisissez une compétence par ligne. Elles seront affichées sous forme de liste.</div>
        @error('competences') <div class="form-error">{{ $message }}</div> @enderror
    </x-rh.form-field>

</x-rh.form-section>

{{-- ── Description ────────────────────────────────────────────── --}}
<x-rh.form-section title="Description & Missions">

    <x-rh.form-field label="Description du poste" name="description" :full="true">
        <textarea name="description"
                  class="form-control @error('description') is-invalid @enderror"
                  rows="4"
                  style="height:auto;padding:8px 10px;resize:vertical;"
                  placeholder="Missions principales, responsabilités, périmètre d'action…">{{ old('description', $poste->description) }}</textarea>
        @error('description') <div class="form-error">{{ $message }}</div> @enderror
    </x-rh.form-field>

</x-rh.form-section>

{{-- ── Actions ─────────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:flex-end;gap:10px;padding-bottom:8px;">
    <a href="{{ $mode === 'edit' ? route('parametrage.postes.show', $poste) : route('parametrage.postes.index') }}"
       class="tb-btn">Annuler</a>
    <button type="submit" class="btn-new">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $mode === 'create' ? 'Créer le poste' : 'Enregistrer' }}
    </button>
</div>

</form>
</div>

<style>
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;transition:border-color .15s,box-shadow .15s;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-control.is-invalid{border-color:var(--danger);}
.form-hint{font-size:11px;color:var(--text-muted);margin-top:4px;}
.form-error{font-size:11px;color:var(--danger);margin-top:2px;}
textarea.form-control{height:auto;}
</style>
</x-app-layout>
