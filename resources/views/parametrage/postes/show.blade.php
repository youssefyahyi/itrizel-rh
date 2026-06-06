<x-app-layout>
<x-rh.page-header
    :title="$poste->numero.' — '.$poste->nom"
    :breadcrumbs="['Paramétrage' => route('parametrage.index'), 'Postes' => route('parametrage.postes.index'), $poste->nom => null]">
    <a href="{{ route('parametrage.postes.edit', $poste) }}" class="btn-new">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
        Modifier
    </a>
</x-rh.page-header>

<div class="content" style="padding:20px 24px;display:flex;flex-direction:column;gap:16px;">

@if(session('success'))
    <x-rh.alert type="success" :message="session('success')" />
@endif
@if(session('error'))
    <x-rh.alert type="danger" :message="session('error')" />
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

    {{-- ── Fiche poste ─────────────────────────────────────────── --}}
    <div class="card" style="padding:20px;display:flex;flex-direction:column;gap:12px;">
        <div style="font-size:13px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Fiche poste</div>

        <x-rh.detail-row label="Numéro">
            <span style="font-family:monospace;font-weight:700;color:var(--accent);font-size:14px;">
                {{ $poste->numero }}
            </span>
        </x-rh.detail-row>

        <x-rh.detail-row label="Intitulé">
            <span style="font-weight:600;">{{ $poste->nom }}</span>
        </x-rh.detail-row>

        <x-rh.detail-row label="Statut">
            <span class="badge {{ $poste->actif ? 'bg' : 'br' }}">
                {{ $poste->actif ? 'Actif' : 'Inactif' }}
            </span>
        </x-rh.detail-row>

        <x-rh.detail-row label="Effectif actuel">
            <span style="font-weight:600;">{{ $poste->employes->count() }} employé(s)</span>
        </x-rh.detail-row>

        @if($poste->description)
        <div style="margin-top:8px;padding-top:12px;border-top:1px solid var(--border-light);">
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:8px;">Description</div>
            <p style="font-size:13px;color:var(--text-secondary);line-height:1.6;margin:0;">
                {{ $poste->description }}
            </p>
        </div>
        @endif
    </div>

    {{-- ── Compétences requises ─────────────────────────────────── --}}
    <div class="card" style="padding:20px;">
        <div style="font-size:13px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">
            Compétences requises
        </div>
        @if(!empty($poste->competences))
            <ul style="margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:8px;">
                @foreach($poste->competences as $comp)
                <li style="display:flex;align-items:flex-start;gap:8px;font-size:13px;color:var(--text-secondary);">
                    <svg width="14" height="14" fill="none" stroke="var(--accent)" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ $comp }}
                </li>
                @endforeach
            </ul>
        @else
            <p style="font-size:13px;color:var(--text-muted);margin:0;">Aucune compétence renseignée.</p>
        @endif
    </div>

</div>

{{-- ── Employés occupant ce poste ───────────────────────────────── --}}
<div class="card" style="overflow:hidden;">
    <div style="padding:14px 18px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);font-size:13px;font-weight:600;">
        Employés — {{ $poste->employes->count() }} au total
    </div>
    @if($poste->employes->isNotEmpty())
    <table class="data-table">
        <thead>
            <tr>
                <th>Employé</th>
                <th>Matricule</th>
                <th>Catégorie</th>
                <th>Statut</th>
                <th style="text-align:right;"></th>
            </tr>
        </thead>
        <tbody>
        @foreach($poste->employes as $emp)
            <tr>
                <td style="font-weight:500;color:var(--text-primary);">{{ $emp->nom_complet }}</td>
                <td style="font-family:monospace;font-size:12px;">{{ $emp->matricule }}</td>
                <td>{{ \App\Models\Employe::CATEGORIES[$emp->categorie] ?? $emp->categorie }}</td>
                <td>
                    <span class="badge {{ $emp->statut_badge }}">
                        {{ ucfirst($emp->statut) }}
                    </span>
                </td>
                <td style="text-align:right;">
                    <a href="{{ route('personnel.show', $emp) }}" class="tb-btn tb-btn-sm">Voir</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @else
        <div style="padding:32px;text-align:center;color:var(--text-muted);font-size:13px;">
            Aucun employé affecté à ce poste.
        </div>
    @endif
</div>

{{-- ── Zone danger ──────────────────────────────────────────────── --}}
@if($poste->employes->count() === 0)
<div class="card" style="padding:16px 20px;border:1px solid var(--danger-light,#fecaca);">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:16px;">
        <div>
            <div style="font-size:13px;font-weight:600;color:var(--danger);">Supprimer ce poste</div>
            <div style="font-size:12px;color:var(--text-muted);margin-top:3px;">
                Irréversible. Possible uniquement car aucun employé n'est affecté.
            </div>
        </div>
        <form method="POST" action="{{ route('parametrage.postes.destroy', $poste) }}"
              onsubmit="return confirm('Supprimer définitivement «{{ $poste->nom }}» ?')">
            @csrf @method('DELETE')
            <button type="submit"
                    style="padding:7px 14px;background:var(--danger);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;cursor:pointer;">
                Supprimer
            </button>
        </form>
    </div>
</div>
@endif

</div>

<style>
.badge{display:inline-flex;align-items:center;padding:3px 8px;border-radius:20px;font-size:11px;font-weight:600;}
.bg{background:#dcfce7;color:#16a34a;}.br{background:#fee2e2;color:#dc2626;}.by{background:#fef9c3;color:#ca8a04;}
.data-table{width:100%;border-collapse:collapse;}
.data-table th{padding:10px 14px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);background:var(--surface-soft);border-bottom:1px solid var(--border);}
.data-table td{padding:12px 14px;font-size:13px;color:var(--text-secondary);border-bottom:1px solid var(--border-light);vertical-align:middle;}
.data-table tbody tr:hover{background:var(--surface-soft);}
.tb-btn-sm{padding:4px 8px !important;font-size:11px;}
</style>
</x-app-layout>
