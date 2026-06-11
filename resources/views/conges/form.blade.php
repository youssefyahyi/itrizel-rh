<x-app-layout>
<x-rh.page-header
    :title="$mode === 'create' ? 'Nouvelle demande de congé' : 'Modifier congé'"
    :breadcrumbs="['Congés' => route('conges.index'), $mode === 'create' ? 'Nouveau' : 'Modifier' => null]">
    <a href="{{ route('conges.index') }}" class="tb-btn">← Retour</a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">
@if($errors->any())<x-rh.alert type="danger" :message="'Veuillez corriger les '.$errors->count().' erreur(s).'" />@endif

<form method="POST" action="{{ $mode === 'create' ? route('conges.store') : route('conges.update',$conge) }}" style="display:flex;flex-direction:column;gap:16px;">
@csrf
@if($mode === 'edit') @method('PUT') @endif

<x-rh.form-section title="Employé & Type">
    <x-rh.form-field label="Employé" name="employe_id" :required="true">
        @if($mode === 'create')
        <select name="employe_id" class="form-control @error('employe_id') is-invalid @enderror">
            <option value="">— Sélectionner —</option>
            @foreach($employes as $emp)
            <option value="{{ $emp->id }}" {{ old('employe_id') == $emp->id ? 'selected' : '' }}>{{ $emp->nom_complet }} — {{ $emp->matricule }}</option>
            @endforeach
        </select>
        @else
        <input type="text" class="form-control" value="{{ $conge->employe->nom_complet }}" disabled>
        @endif
    </x-rh.form-field>
    <x-rh.form-field label="Type de congé" name="type_conge" :required="true">
        <select name="type_conge" class="form-control @error('type_conge') is-invalid @enderror">
            @foreach(\App\Models\Conge::TYPES as $v => $l)
            <option value="{{ $v }}" {{ old('type_conge', $conge->type_conge) === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </x-rh.form-field>
</x-rh.form-section>

<x-rh.form-section title="Dates">
    <x-rh.form-field label="Date de début" name="date_debut" :required="true">
        <input type="date" name="date_debut" class="form-control @error('date_debut') is-invalid @enderror"
               value="{{ old('date_debut', $conge->date_debut?->format('Y-m-d')) }}" id="dt-debut" onchange="calcJours()">
    </x-rh.form-field>
    <x-rh.form-field label="Date de fin" name="date_fin" :required="true">
        <input type="date" name="date_fin" class="form-control @error('date_fin') is-invalid @enderror"
               value="{{ old('date_fin', $conge->date_fin?->format('Y-m-d')) }}" id="dt-fin" onchange="calcJours()">
    </x-rh.form-field>
    <x-rh.form-field label="Nombre de jours" name="nb_jours" :hint="$calendrier === 'ouvrable' ? 'Jours ouvrables, hors fériés' : 'Jours calendaires, hors fériés'">
        <input type="number" name="nb_jours" id="nb-jours" class="form-control" value="{{ old('nb_jours', $conge->nb_jours) }}" min="1" readonly style="background:var(--surface-soft);cursor:default;">
    </x-rh.form-field>
    @if($mode === 'edit')
    <x-rh.form-field label="Statut" name="statut" :required="true">
        <select name="statut" class="form-control">
            @foreach(\App\Models\Conge::STATUTS as $v => $s)
            <option value="{{ $v }}" {{ old('statut', $conge->statut) === $v ? 'selected' : '' }}>{{ $s['label'] }}</option>
            @endforeach
        </select>
    </x-rh.form-field>
    @endif
</x-rh.form-section>

<x-rh.form-section title="Motif">
    <x-rh.form-field label="Motif" name="motif" :full="true">
        <textarea name="motif" class="form-control" rows="3" style="height:auto;padding:8px 10px;">{{ old('motif', $conge->motif) }}</textarea>
    </x-rh.form-field>
</x-rh.form-section>

<div style="display:flex;justify-content:flex-end;gap:10px;">
    <a href="{{ route('conges.index') }}" class="tb-btn">Annuler</a>
    <button type="submit" class="btn-new">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        {{ $mode === 'create' ? 'Soumettre la demande' : 'Enregistrer' }}
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
.form-hint{font-size:11px;color:var(--text-muted);}
.form-control{height:34px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;color:var(--text-primary);background:var(--surface);font-family:inherit;transition:border-color .15s,box-shadow .15s;width:100%;}
.form-control:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-soft);}
.form-control.is-invalid{border-color:var(--danger);}
.form-error{font-size:11px;color:var(--danger);}
select.form-control{cursor:pointer;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;}
</style>
<script>
const CALENDRIER = '{{ $calendrier }}';
const FERIES     = @json($jours_feries);

function calcJours() {
    var debut = document.getElementById('dt-debut').value;
    var fin   = document.getElementById('dt-fin').value;
    if (!debut || !fin) return;

    var start = new Date(debut + 'T00:00:00');
    var end   = new Date(fin   + 'T00:00:00');
    if (end < start) return;

    var count  = 0;
    var cursor = new Date(start);

    while (cursor <= end) {
        var dow     = cursor.getDay(); // 0=dim, 6=sam
        var dateStr = cursor.toISOString().split('T')[0];
        var isWeekend = dow === 0 || dow === 6;
        var isFerie   = FERIES.includes(dateStr);

        if (CALENDRIER === 'ouvrable') {
            if (!isWeekend && !isFerie) count++;
        } else {
            if (!isFerie) count++;
        }
        cursor.setDate(cursor.getDate() + 1);
    }

    if (count > 0) document.getElementById('nb-jours').value = count;
}
</script>
</x-app-layout>
