<x-app-layout>
<x-rh.page-header
    :title="$mode === 'create' ? 'Nouvelle évaluation' : 'Modifier l\'évaluation — '.$evaluation->type_libelle"
    :breadcrumbs="['Évaluations' => route('evaluations.index'), $mode === 'create' ? 'Nouvelle' : 'Modifier' => null]">
    <a href="{{ route('evaluations.index') }}" class="tb-btn">← Retour</a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if($errors->any())
<x-rh.alert type="danger" :message="'Veuillez corriger les '.$errors->count().' erreur(s) ci-dessous.'" />
@endif

<form method="POST"
      action="{{ $mode === 'create' ? route('evaluations.store') : route('evaluations.update', $evaluation) }}"
      style="display:flex;flex-direction:column;gap:16px;">
    @csrf
    @if($mode === 'edit') @method('PUT') @endif

    {{-- Section 1 : Employé & Évaluateur --}}
    <x-rh.form-section title="Employé & Évaluateur">
        <x-rh.form-field label="Employé" name="employe_id" :required="true">
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
                       value="{{ $evaluation->employe->nom_complet }} — {{ $evaluation->employe->matricule }}"
                       disabled>
            @endif
        </x-rh.form-field>

        <x-rh.form-field label="Évaluateur" name="evaluateur_id" :required="true">
            <select name="evaluateur_id" id="evaluateur_id"
                    class="form-control @error('evaluateur_id') is-invalid @enderror">
                <option value="">— Sélectionner un évaluateur —</option>
                @foreach($evaluateurs as $user)
                    <option value="{{ $user->id }}"
                        {{ old('evaluateur_id', $evaluation->evaluateur_id) == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </x-rh.form-field>
    </x-rh.form-section>

    {{-- Section 2 : Type & Période --}}
    <x-rh.form-section title="Type & Période">
        <x-rh.form-field label="Type d'évaluation" name="type" :required="true">
            <select name="type" id="type"
                    class="form-control @error('type') is-invalid @enderror">
                @foreach(\App\Models\Evaluation::TYPES as $v => $l)
                    <option value="{{ $v }}"
                        {{ old('type', $evaluation->type) === $v ? 'selected' : '' }}>
                        {{ $l }}
                    </option>
                @endforeach
            </select>
        </x-rh.form-field>

        <x-rh.form-field label="Note globale" name="note_globale"
                         :hint="'Sur 20 — laisser vide si non encore attribuée'">
            <input type="number" name="note_globale" id="note_globale"
                   class="form-control @error('note_globale') is-invalid @enderror"
                   value="{{ old('note_globale', $evaluation->note_globale) }}"
                   min="0" max="20" step="0.01" placeholder="ex: 15.50">
        </x-rh.form-field>

        <x-rh.form-field label="Début de période" name="periode_debut" :required="true">
            <input type="date" name="periode_debut" id="periode_debut"
                   class="form-control @error('periode_debut') is-invalid @enderror"
                   value="{{ old('periode_debut', $evaluation->periode_debut?->format('Y-m-d')) }}">
        </x-rh.form-field>

        <x-rh.form-field label="Fin de période" name="periode_fin" :required="true">
            <input type="date" name="periode_fin" id="periode_fin"
                   class="form-control @error('periode_fin') is-invalid @enderror"
                   value="{{ old('periode_fin', $evaluation->periode_fin?->format('Y-m-d')) }}">
        </x-rh.form-field>
    </x-rh.form-section>

    {{-- Section 3 : Résultat --}}
    <x-rh.form-section title="Résultat & Décision">
        <x-rh.form-field label="Décision" name="decision" :required="true">
            <select name="decision" id="decision"
                    class="form-control @error('decision') is-invalid @enderror">
                @foreach(\App\Models\Evaluation::DECISIONS as $v => $d)
                    <option value="{{ $v }}"
                        {{ old('decision', $evaluation->decision) === $v ? 'selected' : '' }}>
                        {{ $d['label'] }}
                    </option>
                @endforeach
            </select>
        </x-rh.form-field>

        @if($mode === 'edit')
        <x-rh.form-field label="Statut" name="statut" :required="true">
            <select name="statut" id="statut"
                    class="form-control @error('statut') is-invalid @enderror">
                <option value="brouillon" {{ old('statut', $evaluation->statut) === 'brouillon' ? 'selected' : '' }}>Brouillon</option>
                <option value="finalise"  {{ old('statut', $evaluation->statut) === 'finalise'  ? 'selected' : '' }}>Finalisée</option>
            </select>
        </x-rh.form-field>
        @endif

        <x-rh.form-field label="Observations" name="observations" :full="true">
            <textarea name="observations" id="observations"
                      class="form-control @error('observations') is-invalid @enderror"
                      rows="4" style="height:auto;padding:8px 10px;">{{ old('observations', $evaluation->observations) }}</textarea>
        </x-rh.form-field>
    </x-rh.form-section>

    <div style="display:flex;justify-content:flex-end;gap:10px;">
        <a href="{{ route('evaluations.index') }}" class="tb-btn">Annuler</a>
        <button type="submit" class="btn-new">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $mode === 'create' ? 'Créer l\'évaluation' : 'Enregistrer les modifications' }}
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
