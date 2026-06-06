<x-app-layout>
<x-rh.page-header
    :title="$mode === 'create' ? 'Nouveau contrat' : 'Modifier — '.$contrat->reference"
    :breadcrumbs="['Contrats' => route('contrats.index'), $mode === 'create' ? 'Nouveau' : 'Modifier' => null]">
    <a href="{{ $mode === 'edit' ? route('contrats.show', $contrat) : route('contrats.index') }}" class="tb-btn">← Retour</a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if($errors->any())
<x-rh.alert type="danger" :message="'Veuillez corriger les '.$errors->count().' erreur(s).'" />
@endif

<form method="POST"
      action="{{ $mode === 'create' ? route('contrats.store') : route('contrats.update', $contrat) }}"
      style="display:flex;flex-direction:column;gap:16px;">
@csrf
@if($mode === 'edit') @method('PUT') @endif

<x-rh.form-section title="Employé & Type">
    <x-rh.form-field label="Employé" name="employe_id" :required="true">
        <select name="employe_id" class="form-control @error('employe_id') is-invalid @enderror" {{ $mode === 'edit' ? 'disabled' : '' }}>
            <option value="">— Sélectionner un employé —</option>
            @foreach($employes as $emp)
            <option value="{{ $emp->id }}" {{ old('employe_id', $contrat->employe_id) == $emp->id ? 'selected' : '' }}>
                {{ $emp->nom_complet }} — {{ $emp->matricule }}
            </option>
            @endforeach
        </select>
    </x-rh.form-field>

    <x-rh.form-field label="Type de contrat" name="type" :required="true">
        <select name="type" class="form-control @error('type') is-invalid @enderror" id="sel-type" onchange="toggleDateFin()">
            <option value="">— Choisir —</option>
            @foreach(\App\Models\Contrat::TYPES as $v => $l)
            <option value="{{ $v }}" {{ old('type', $contrat->type) === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </x-rh.form-field>

    <x-rh.form-field label="Poste" name="poste_id" :required="true">
        <select name="poste_id" class="form-control @error('poste_id') is-invalid @enderror">
            <option value="">— Sélectionner un poste —</option>
            @foreach($postes as $p)
            <option value="{{ $p->id }}" {{ old('poste_id', $contrat->poste_id) == $p->id ? 'selected' : '' }}>
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

    <x-rh.form-field label="Catégorie" name="categorie" :required="true">
        <select name="categorie" class="form-control @error('categorie') is-invalid @enderror">
            <option value="">— Choisir —</option>
            @foreach(\App\Models\Employe::CATEGORIES as $v => $l)
            <option value="{{ $v }}" {{ old('categorie', $contrat->categorie) === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </x-rh.form-field>
</x-rh.form-section>

<x-rh.form-section title="Dates & Rémunération">
    <x-rh.form-field label="Date de début" name="date_debut" :required="true">
        <input type="date" name="date_debut" class="form-control @error('date_debut') is-invalid @enderror"
               @if($mode === 'create') min="{{ date('Y-m-d') }}" @endif
               value="{{ old('date_debut', $contrat->date_debut?->format('Y-m-d')) }}">
    </x-rh.form-field>

    <x-rh.form-field label="Date de fin" name="date_fin" id="grp-date-fin">
        <input type="date" name="date_fin" class="form-control @error('date_fin') is-invalid @enderror"
               value="{{ old('date_fin', $contrat->date_fin?->format('Y-m-d')) }}">
    </x-rh.form-field>

    <x-rh.form-field label="Salaire de base (DH)" name="salaire_base" :required="true">
        <input type="number" name="salaire_base" class="form-control @error('salaire_base') is-invalid @enderror"
               value="{{ old('salaire_base', $contrat->salaire_base) }}" min="0" step="0.01" placeholder="Ex: 5000">
    </x-rh.form-field>

    <x-rh.form-field label="Durée (mois)" name="duree_mois">
        <input type="number" name="duree_mois" class="form-control @error('duree_mois') is-invalid @enderror"
               value="{{ old('duree_mois', $contrat->duree_mois) }}" min="1" placeholder="Ex: 12">
    </x-rh.form-field>

    <x-rh.form-field label="Renouvellement automatique" name="renouvellement_auto">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;height:34px;">
            <input type="checkbox" name="renouvellement_auto" value="1" {{ old('renouvellement_auto', $contrat->renouvellement_auto) ? 'checked' : '' }}
                   style="width:16px;height:16px;accent-color:var(--accent);">
            <span style="font-size:13px;color:var(--text-secondary);">Activer le renouvellement automatique</span>
        </label>
    </x-rh.form-field>

    @if($mode === 'edit')
    <x-rh.form-field label="Statut" name="statut" :required="true">
        <select name="statut" class="form-control @error('statut') is-invalid @enderror">
            @foreach(\App\Models\Contrat::STATUTS as $v => $s)
            <option value="{{ $v }}" {{ old('statut', $contrat->statut) === $v ? 'selected' : '' }}>{{ $s['label'] }}</option>
            @endforeach
        </select>
    </x-rh.form-field>

    <x-rh.form-field label="Motif de résiliation" name="motif_resiliation">
        <input type="text" name="motif_resiliation" class="form-control"
               value="{{ old('motif_resiliation', $contrat->motif_resiliation) }}" placeholder="Si résilié...">
    </x-rh.form-field>
    @endif
</x-rh.form-section>

<x-rh.form-section title="Observations">
    <x-rh.form-field label="Notes / Observations" name="observations" :full="true">
        <textarea name="observations" class="form-control" rows="3"
                  style="height:auto;padding:8px 10px;">{{ old('observations', $contrat->observations) }}</textarea>
    </x-rh.form-field>
</x-rh.form-section>

<div style="display:flex;justify-content:flex-end;gap:10px;padding-bottom:8px;">
    <a href="{{ $mode === 'edit' ? route('contrats.show', $contrat) : route('contrats.index') }}" class="tb-btn">Annuler</a>
    <button type="submit" class="btn-new">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $mode === 'create' ? 'Créer le contrat' : 'Enregistrer' }}
    </button>
</div>
</form>
</div>

<style>
.form-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;}
.form-card-header{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);}
.form-card-title{font-size:13px;font-weight:600;color:var(--text-primary);}
.form-card-body{padding:18px;}
.form-grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;}
.form-group{display:flex;flex-direction:column;gap:5px;}
.form-group.form-grid-full{grid-column:1/-1;}
.form-label{font-size:12px;font-weight:500;color:var(--text-secondary);}
.form-required{color:var(--danger);margin-left:2px;}
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;transition:border-color .15s,box-shadow .15s;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-control.is-invalid{border-color:var(--danger);}
.form-error{font-size:11px;color:var(--danger);}
select.form-control{cursor:pointer;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;}
</style>

<script>
function toggleDateFin() {
    var type = document.getElementById('sel-type').value;
    var grp  = document.getElementById('grp-date-fin');
    if (grp) grp.style.opacity = type === 'CDI' ? '0.4' : '1';
}
document.addEventListener('DOMContentLoaded', toggleDateFin);
</script>
</x-app-layout>
