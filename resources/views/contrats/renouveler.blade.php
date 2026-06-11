<x-app-layout>
<x-rh.page-header
    title="Renouveler le contrat"
    :breadcrumbs="['Contrats' => route('contrats.index'), $contratParent->reference => route('contrats.show', $contratParent), 'Renouveler' => null]">
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if($errors->any())
<x-rh.alert type="danger" :message="'Veuillez corriger les '.$errors->count().' erreur(s).'" />
@endif

{{-- Récapitulatif contrat parent --}}
<div style="background:var(--surface-soft);border:1px solid var(--border-light);border-radius:var(--radius);padding:14px 18px;font-size:13px;">
    <div style="font-size:11px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Contrat en cours — sera clôturé après renouvellement</div>
    <div style="display:flex;gap:20px;flex-wrap:wrap;align-items:center;">
        <span class="mono" style="font-size:13px;font-weight:700;">{{ $contratParent->reference }}</span>
        <span>{{ $contratParent->employe->nom_complet }}</span>
        <span style="color:var(--text-secondary);">{{ $contratParent->type }} — {{ number_format($contratParent->salaire_base, 0, ',', ' ') }} DH/mois</span>
        <span style="color:var(--text-secondary);">depuis le {{ $contratParent->date_debut->format('d/m/Y') }}</span>
    </div>
</div>

<form method="POST" action="{{ route('contrats.store-renouvellement', $contratParent) }}"
      style="display:flex;flex-direction:column;gap:16px;">
@csrf

<x-rh.form-section title="Nouveau contrat">
    <x-rh.form-field label="Type de contrat" name="type" :required="true">
        <select name="type" class="form-control @error('type') is-invalid @enderror" id="sel-type" onchange="toggleDateFin()">
            <option value="">— Choisir —</option>
            <option value="CDI" {{ old('type', $contratParent->type) === 'CDI' ? 'selected' : '' }}>CDI</option>
            <option value="CDD" {{ old('type', $contratParent->type) === 'CDD' ? 'selected' : '' }}>CDD</option>
        </select>
    </x-rh.form-field>

    <x-rh.form-field label="Fiche de poste" name="fiche_poste_id" :full="true">
        @php $fichesByCat = $fiches->groupBy('categorie_id'); @endphp
        <select name="fiche_poste_id" class="form-control @error('fiche_poste_id') is-invalid @enderror">
            <option value="">— Sélectionner dans le référentiel des emplois —</option>
            @foreach($categories as $cat)
                @if($fichesByCat->has($cat->id))
                <optgroup label="{{ $cat->nom }}">
                    @foreach($fichesByCat[$cat->id] as $fiche)
                    <option value="{{ $fiche->id }}"
                        {{ old('fiche_poste_id', $contratParent->fiche_poste_id) == $fiche->id ? 'selected' : '' }}>
                        {{ $fiche->fonction->nom }} › {{ $fiche->poste->nom }}
                    </option>
                    @endforeach
                </optgroup>
                @endif
            @endforeach
        </select>
    </x-rh.form-field>
</x-rh.form-section>

<x-rh.form-section title="Dates & Rémunération">
    <x-rh.form-field label="Date de début" name="date_debut" :required="true">
        <input type="date" name="date_debut" class="form-control @error('date_debut') is-invalid @enderror"
               value="{{ old('date_debut', now()->format('Y-m-d')) }}">
    </x-rh.form-field>

    <x-rh.form-field label="Date de fin" name="date_fin" id="grp-date-fin">
        <input type="date" name="date_fin" class="form-control @error('date_fin') is-invalid @enderror"
               value="{{ old('date_fin') }}">
    </x-rh.form-field>

    <x-rh.form-field label="Salaire de base (DH)" name="salaire_base" :required="true">
        <input type="number" name="salaire_base" class="form-control @error('salaire_base') is-invalid @enderror"
               value="{{ old('salaire_base', $contratParent->salaire_base) }}" min="0" step="0.01">
    </x-rh.form-field>

    <x-rh.form-field label="Durée (mois)" name="duree_mois">
        <input type="number" name="duree_mois" class="form-control @error('duree_mois') is-invalid @enderror"
               value="{{ old('duree_mois') }}" min="1" placeholder="Ex: 12">
    </x-rh.form-field>

    <x-rh.form-field label="Calendrier congés" name="calendrier_conges" :hint="'Laissez vide pour utiliser le réglage société'">
        <select name="calendrier_conges" class="form-control">
            <option value="">— Par défaut société —</option>
            <option value="ouvrable"   {{ old('calendrier_conges', $contratParent->calendrier_conges) === 'ouvrable'   ? 'selected' : '' }}>Jours ouvrables (Lun–Ven)</option>
            <option value="calendaire" {{ old('calendrier_conges', $contratParent->calendrier_conges) === 'calendaire' ? 'selected' : '' }}>Jours calendaires (tous les jours)</option>
        </select>
    </x-rh.form-field>

    <x-rh.form-field label="Renouvellement automatique" name="renouvellement_auto">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;height:34px;">
            <input type="checkbox" name="renouvellement_auto" value="1"
                   {{ old('renouvellement_auto', $contratParent->renouvellement_auto) ? 'checked' : '' }}
                   style="width:16px;height:16px;accent-color:var(--accent);">
            <span style="font-size:13px;color:var(--text-secondary);">Activer le renouvellement automatique</span>
        </label>
    </x-rh.form-field>
</x-rh.form-section>

<x-rh.form-section title="Observations">
    <x-rh.form-field label="Notes / Observations" name="observations" :full="true">
        <textarea name="observations" class="form-control" rows="3" style="height:auto;padding:8px 10px;">{{ old('observations') }}</textarea>
    </x-rh.form-field>
</x-rh.form-section>

<div style="background:var(--danger-light,#fff5f5);border:1px solid var(--danger,#e53e3e);border-radius:var(--radius-sm);padding:10px 14px;font-size:12px;color:var(--danger,#e53e3e);">
    En validant, le contrat <strong>{{ $contratParent->reference }}</strong> sera clôturé avec le statut « Renouvelé » et un nouveau contrat sera créé.
</div>

<div style="display:flex;justify-content:flex-end;gap:10px;padding-bottom:8px;">
    <a href="{{ route('contrats.show', $contratParent) }}" class="tb-btn">Annuler</a>
    <button type="submit" class="btn-new" style="background:var(--accent);">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Confirmer le renouvellement
    </button>
</div>
</form>
</div>

<style>
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;transition:border-color .15s,box-shadow .15s;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-control.is-invalid{border-color:var(--danger);}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;}
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
