<x-app-layout>
<x-rh.page-header
    :title="$mode === 'create' ? 'Nouvelle formation' : 'Modifier la formation — '.Str::limit($formation->intitule, 40)"
    :breadcrumbs="['Formations' => route('formations.index'), $mode === 'create' ? 'Nouvelle' : 'Modifier' => null]">
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if($errors->any())
<x-rh.alert type="danger" :message="'Veuillez corriger les '.$errors->count().' erreur(s) ci-dessous.'" />
@endif

<form method="POST"
      action="{{ $mode === 'create' ? route('formations.store') : route('formations.update', $formation) }}"
      style="display:flex;flex-direction:column;gap:16px;">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    {{-- Section 1 : Employé --}}
    <x-rh.form-section title="Employé">
        <x-rh.form-field label="Employé concerné" name="employe_id" :required="true">
            @if($mode === 'create')
                <select name="employe_id" id="employe_id"
                        class="form-control @error('employe_id') is-invalid @enderror">
                    <option value="">— Sélectionner un employé —</option>
                    @foreach($employes as $emp)
                        <option value="{{ $emp->id }}"
                            {{ old('employe_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nom_complet }} — {{ $emp->matricule }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="text" class="form-control"
                       value="{{ $formation->employe->nom_complet }} — {{ $formation->employe->matricule }}"
                       disabled>
            @endif
        </x-rh.form-field>
    </x-rh.form-section>

    {{-- Section 2 : Informations de la formation --}}
    <x-rh.form-section title="Informations de la formation">
        <x-rh.form-field label="Intitulé" name="intitule" :required="true" :full="true">
            <input type="text" name="intitule" id="intitule"
                   class="form-control @error('intitule') is-invalid @enderror"
                   value="{{ old('intitule', $formation->intitule) }}"
                   placeholder="ex: Formation Excel avancé, Gestion de projet...">
        </x-rh.form-field>

        <x-rh.form-field label="Type" name="type" :required="true">
            <select name="type" id="type"
                    class="form-control @error('type') is-invalid @enderror">
                @foreach(\App\Models\Formation::TYPES as $v => $l)
                    <option value="{{ $v }}"
                        {{ old('type', $formation->type) === $v ? 'selected' : '' }}>
                        {{ $l }}
                    </option>
                @endforeach
            </select>
        </x-rh.form-field>

        <x-rh.form-field label="Organisme" name="organisme"
                         :hint="'Facultatif — prestataire ou organisme interne'">
            <input type="text" name="organisme" id="organisme"
                   class="form-control @error('organisme') is-invalid @enderror"
                   value="{{ old('organisme', $formation->organisme) }}"
                   placeholder="ex: OFPPT, organisme interne...">
        </x-rh.form-field>
    </x-rh.form-section>

    {{-- Section 3 : Dates & Volume --}}
    <x-rh.form-section title="Dates & Volume horaire">
        <x-rh.form-field label="Date de début" name="date_debut" :required="true">
            <input type="date" name="date_debut" id="date_debut"
                   class="form-control @error('date_debut') is-invalid @enderror"
                   value="{{ old('date_debut', $formation->date_debut?->format('Y-m-d')) }}">
        </x-rh.form-field>

        <x-rh.form-field label="Date de fin" name="date_fin" :required="true">
            <input type="date" name="date_fin" id="date_fin"
                   class="form-control @error('date_fin') is-invalid @enderror"
                   value="{{ old('date_fin', $formation->date_fin?->format('Y-m-d')) }}">
        </x-rh.form-field>

        <x-rh.form-field label="Nombre d'heures" name="nb_heures" :required="true">
            <input type="number" name="nb_heures" id="nb_heures"
                   class="form-control @error('nb_heures') is-invalid @enderror"
                   value="{{ old('nb_heures', $formation->nb_heures) }}"
                   min="0" step="0.5" placeholder="ex: 16">
        </x-rh.form-field>

        <x-rh.form-field label="Coût (DH)" name="cout"
                         :hint="'Facultatif — coût total de la formation'">
            <input type="number" name="cout" id="cout"
                   class="form-control @error('cout') is-invalid @enderror"
                   value="{{ old('cout', $formation->cout > 0 ? $formation->cout : '') }}"
                   min="0" step="0.01" placeholder="ex: 2 500.00">
        </x-rh.form-field>
    </x-rh.form-section>

    {{-- Section 4 : Statut (édition uniquement) --}}
    @if($mode === 'edit')
    <x-rh.form-section title="Statut" :cols="1">
        <x-rh.form-field label="Statut de la formation" name="statut" :required="true">
            <select name="statut" id="statut"
                    class="form-control @error('statut') is-invalid @enderror"
                    style="max-width:300px;">
                @foreach(\App\Models\Formation::STATUTS as $v => $s)
                    <option value="{{ $v }}"
                        {{ old('statut', $formation->statut) === $v ? 'selected' : '' }}>
                        {{ $s['label'] }}
                    </option>
                @endforeach
            </select>
        </x-rh.form-field>
    </x-rh.form-section>
    @endif

    {{-- Section 5 : Observations --}}
    <x-rh.form-section title="Observations" :cols="1">
        <x-rh.form-field label="Observations" name="observations" :full="true">
            <textarea name="observations" id="observations"
                      class="form-control @error('observations') is-invalid @enderror"
                      rows="4" style="height:auto;padding:8px 10px;">{{ old('observations', $formation->observations) }}</textarea>
        </x-rh.form-field>
    </x-rh.form-section>

    <div style="display:flex;justify-content:flex-end;gap:10px;">
        <a href="{{ route('formations.index') }}" class="tb-btn">Annuler</a>
        <button type="submit" class="btn-new">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $mode === 'create' ? 'Créer la formation' : 'Enregistrer les modifications' }}
        </button>
    </div>

</form>
</div>

<style>
.form-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;}
.form-card-header{display:flex;align-items:center;padding:14px 18px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);}
.form-card-title{font-size:13px;font-weight:600;color:var(--text-primary);}
.form-card-body{padding:18px;}
.form-grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;}
.form-grid-1{display:grid;grid-template-columns:1fr;gap:14px;}
.form-group{display:flex;flex-direction:column;gap:5px;}
.form-group.form-grid-full{grid-column:1/-1;}
.form-label{font-size:12px;font-weight:500;color:var(--text-secondary);}
.form-required{color:var(--danger);margin-left:2px;}
.form-hint{font-size:11px;color:var(--text-muted);margin-top:2px;}
.form-error{font-size:11px;color:var(--danger);margin-top:2px;}
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;transition:border-color .15s,box-shadow .15s;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-control.is-invalid{border-color:var(--danger);}
select.form-control{cursor:pointer;}
textarea.form-control{height:auto;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;transition:opacity .15s;}
.btn-new:hover{opacity:.9;}
</style>
</x-app-layout>
