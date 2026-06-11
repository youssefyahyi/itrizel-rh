<x-app-layout>
<x-rh.page-header
    :title="$mode === 'create' ? 'Nouvelle absence' : 'Modifier l\'absence'"
    :breadcrumbs="['Absences' => route('absences.index'), $mode === 'create' ? 'Nouvelle' : 'Modifier' => null]">
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
@if($errors->any())<x-rh.alert type="danger" :message="'Veuillez corriger les '.$errors->count().' erreur(s).'" />@endif

<form method="POST" action="{{ $mode === 'create' ? route('absences.store') : route('absences.update',$absence) }}" style="display:flex;flex-direction:column;gap:16px;">
@csrf
@if($mode === 'edit') @method('PUT') @endif

<x-rh.form-section title="Employé & Date">
    @if($mode === 'create')
    <x-rh.form-field label="Employé" name="employe_id" :required="true">
        <select name="employe_id" class="form-control @error('employe_id') is-invalid @enderror">
            <option value="">— Sélectionner —</option>
            @foreach($employes as $emp)
            <option value="{{ $emp->id }}" {{ old('employe_id') == $emp->id ? 'selected' : '' }}>{{ $emp->nom_complet }} — {{ $emp->matricule }}</option>
            @endforeach
        </select>
    </x-rh.form-field>
    <x-rh.form-field label="Date" name="date" :required="true">
        <input type="date" name="date" class="form-control @error('date') is-invalid @enderror"
               max="{{ date('Y-m-d') }}" value="{{ old('date', date('Y-m-d')) }}">
    </x-rh.form-field>
    @else
    <x-rh.form-field label="Employé" name="employe_id">
        <input type="text" class="form-control" value="{{ $absence->employe->nom_complet }}" disabled>
    </x-rh.form-field>
    <x-rh.form-field label="Date" name="date">
        <input type="text" class="form-control" value="{{ $absence->date->format('d/m/Y') }}" disabled>
    </x-rh.form-field>
    @endif
</x-rh.form-section>

<x-rh.form-section title="Horaires & Statut">
    <x-rh.form-field label="Statut" name="statut" :required="true">
        <select name="statut" class="form-control @error('statut') is-invalid @enderror">
            @foreach(\App\Models\Absence::STATUTS as $v => $s)
            <option value="{{ $v }}" {{ old('statut', $absence->statut) === $v ? 'selected' : '' }}>{{ $s['label'] }}</option>
            @endforeach
        </select>
    </x-rh.form-field>
    <x-rh.form-field label="Motif d'absence" name="motif_absence">
        <input type="text" name="motif_absence" class="form-control" value="{{ old('motif_absence', $absence->motif_absence) }}" placeholder="Si absent...">
    </x-rh.form-field>
    <x-rh.form-field label="Heure d'arrivée" name="heure_arrivee">
        <input type="time" name="heure_arrivee" class="form-control" value="{{ old('heure_arrivee', $absence->heure_arrivee ? \Carbon\Carbon::parse($absence->heure_arrivee)->format('H:i') : '') }}">
    </x-rh.form-field>
    <x-rh.form-field label="Heure de départ" name="heure_depart">
        <input type="time" name="heure_depart" class="form-control" value="{{ old('heure_depart', $absence->heure_depart ? \Carbon\Carbon::parse($absence->heure_depart)->format('H:i') : '') }}">
    </x-rh.form-field>
    <x-rh.form-field label="Heures prévues" name="heures_prevues">
        <input type="number" name="heures_prevues" class="form-control" value="{{ old('heures_prevues', $absence->heures_prevues ?? 8) }}" min="0" max="24" step="0.5">
    </x-rh.form-field>
    <x-rh.form-field label="Heures réalisées" name="heures_realisees">
        <input type="number" name="heures_realisees" class="form-control" value="{{ old('heures_realisees', $absence->heures_realisees) }}" min="0" max="24" step="0.5">
    </x-rh.form-field>
</x-rh.form-section>

<x-rh.form-section title="Remarque">
    <x-rh.form-field label="Remarque" name="remarque" :full="true">
        <textarea name="remarque" class="form-control" rows="3" style="height:auto;padding:8px 10px;">{{ old('remarque', $absence->remarque) }}</textarea>
    </x-rh.form-field>
</x-rh.form-section>

<div style="display:flex;justify-content:flex-end;gap:10px;">
    <a href="{{ route('absences.index') }}" class="tb-btn">Annuler</a>
    <button type="submit" class="btn-new">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $mode === 'create' ? 'Enregistrer l\'absence' : 'Mettre à jour' }}
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
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;transition:border-color .15s,box-shadow .15s;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-control.is-invalid{border-color:var(--danger);}
.form-error{font-size:11px;color:var(--danger);}
select.form-control{cursor:pointer;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;}
</style>
</x-app-layout>
