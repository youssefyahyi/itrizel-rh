<x-app-layout>
<x-rh.page-header
    :title="$avenant->reference"
    :breadcrumbs="['Contrats' => route('contrats.index'), $avenant->contrat->reference => route('contrats.show', $avenant->contrat), $avenant->reference => null]">
    @if(!$avenant->pdf_path)
    <form method="POST" action="{{ route('avenants.pdf', $avenant) }}" style="display:inline;">
        @csrf
        @foreach($clauses as $cl)
        <input type="hidden" name="clauses[]" value="{{ $cl->id }}">
        @endforeach
        <button type="submit" class="tb-btn">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Générer PDF
        </button>
    </form>
    @else
    <form method="POST" action="{{ route('avenants.pdf', $avenant) }}" style="display:inline;">
        @csrf
        <button type="submit" class="tb-btn">
            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Télécharger PDF
        </button>
    </form>
    @endif
    <a href="{{ route('contrats.show', $avenant->contrat) }}" class="tb-btn">← Contrat</a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if(session('success'))<x-rh.alert type="success" :message="session('success')" />@endif

<div class="emp-hero">
    <div style="flex:1;">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:6px;">
            <span style="font-size:18px;font-weight:700;color:var(--text-primary);font-family:monospace;">{{ $avenant->reference }}</span>
            <span class="badge bb">{{ App\Models\Avenant::NATURES[$avenant->nature] ?? $avenant->nature }}</span>
            @if($avenant->pdf_path)<span class="badge bg">PDF disponible</span>@endif
        </div>
        <div style="font-size:13px;color:var(--text-secondary);">
            Contrat <a href="{{ route('contrats.show', $avenant->contrat) }}" class="link mono">{{ $avenant->contrat->reference }}</a>
            — {{ $avenant->contrat->employe->nom_complet }}
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
    <div class="info-card">
        <div class="info-card-title">Détails de l'avenant</div>
        <x-rh.detail-row label="Référence"      :value="$avenant->reference" />
        <x-rh.detail-row label="Nature"         :value="App\Models\Avenant::NATURES[$avenant->nature] ?? $avenant->nature" />
        <x-rh.detail-row label="Date d'effet"   :value="$avenant->date_effet->format('d/m/Y')" />
        @if($avenant->ancienne_valeur)
        <x-rh.detail-row label="Avant"          :value="$avenant->ancienne_valeur" />
        @endif
        @if($avenant->nouvelle_valeur)
        <x-rh.detail-row label="Après"          :value="$avenant->nouvelle_valeur" />
        @endif
    </div>
    <div class="info-card">
        <div class="info-card-title">Contrat associé</div>
        <x-rh.detail-row label="Référence"   :value="$avenant->contrat->reference" />
        <x-rh.detail-row label="Employé"     :value="$avenant->contrat->employe->nom_complet" />
        <x-rh.detail-row label="Type"        :value="$avenant->contrat->type" />
        <x-rh.detail-row label="Poste"       :value="$avenant->contrat->fichePoste?->poste->nom ?? '—'" />
    </div>
</div>

@if($avenant->motif)
<div class="info-card">
    <div class="info-card-title">Motif</div>
    <p style="font-size:13px;color:var(--text-secondary);line-height:1.6;padding-top:4px;">{{ $avenant->motif }}</p>
</div>
@endif

</div>

<style>
.emp-hero{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px 24px;display:flex;align-items:flex-start;gap:18px;box-shadow:var(--shadow-sm);}
.info-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px;box-shadow:var(--shadow-sm);}
.info-card-title{font-size:12px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border-light);}
</style>
</x-app-layout>
