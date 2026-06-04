<x-app-layout>

{{-- PAGE HEADER --}}
<div class="page-header">
    <div class="page-title">
        Personnel
        <span class="badge-count">{{ $stats['total'] }}</span>
    </div>
    <button type="button" class="btn-new" onclick="rhEmpOpenModal()">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Nouvel employe
    </button>
</div>

{{-- STATS --}}
<div class="stats-row">
    <div class="stat-card" style="--st:var(--accent);">
        <div class="stat-icon" style="background:var(--accent-light);"><svg width="16" height="16" fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
        <div><div class="stat-label">Total</div><div class="stat-value" style="color:var(--accent);">{{ $stats['total'] }}</div></div>
    </div>
    <div class="stat-card" style="--st:var(--success);">
        <div class="stat-icon" style="background:var(--success-light);"><svg width="16" height="16" fill="none" stroke="var(--success)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div><div class="stat-label">Actifs</div><div class="stat-value" style="color:var(--success);">{{ $stats['actifs'] }}</div></div>
    </div>
    <div class="stat-card" style="--st:var(--text-muted);">
        <div class="stat-icon" style="background:var(--border-light);"><svg width="16" height="16" fill="none" stroke="var(--text-secondary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
        <div><div class="stat-label">Inactifs</div><div class="stat-value" style="color:var(--text-secondary);">{{ $stats['inactifs'] }}</div></div>
    </div>
    <div class="stat-card" style="--st:var(--warning);">
        <div class="stat-icon" style="background:var(--warning-light);"><svg width="16" height="16" fill="none" stroke="var(--warning)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div><div class="stat-label">Contrats expirant</div><div class="stat-value" style="color:var(--warning);">{{ $stats['expires'] }}</div></div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin:16px 24px 0;">{{ session('success') }}</div>
@endif

{{-- LIST CARD --}}
<div class="list-card">
    <form method="GET" action="{{ route('personnel.index') }}" id="rh-emp-form">
        <input type="hidden" name="statut"    id="f-statut"    value="{{ request('statut') }}">
        <input type="hidden" name="categorie" id="f-categorie" value="{{ request('categorie') }}">
        <div class="list-toolbar">
            <div class="search-box">
                <svg width="13" height="13" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, prenom, matricule, CIN..." onchange="this.form.submit()">
            </div>
            <div style="width:1px;height:18px;background:var(--border);flex-shrink:0;"></div>
            @php $ac = collect(['q','statut','categorie'])->filter(fn($k)=>request($k))->count(); @endphp
            <div style="position:relative;">
                <button type="button" class="tb-btn {{ $ac ? 'active' : '' }}" onclick="gcmToggle('dd-emp-f', event)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Filtres @if($ac)<span class="tb-count">{{ $ac }}</span>@endif
                </button>
                <div id="dd-emp-f" style="display:none;position:absolute;top:calc(100% + 4px);left:0;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-lg);min-width:200px;z-index:999;overflow:hidden;">
                    <div style="padding:8px 12px 4px;font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Statut</div>
                    @foreach(['' => 'Tous', 'actif' => 'Actifs', 'inactif' => 'Inactifs', 'suspendu' => 'Suspendus'] as $v => $l)
                    <div onclick="gcmSetFilter('statut','{{ $v }}')" style="padding:8px 14px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:8px;{{ request('statut')===$v ? 'background:var(--accent-light);color:var(--accent);font-weight:600;' : 'color:var(--text-secondary);' }}">
                        @if(request('statut')===$v)<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:12px;height:12px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>@else<span style="width:12px;"></span>@endif {{ $l }}
                    </div>
                    @endforeach
                    <div style="height:1px;background:var(--border-light);margin:4px 0;"></div>
                    <div style="padding:4px 12px;font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Categorie</div>
                    @foreach(\App\Models\Employe::CATEGORIES as $v => $l)
                    <div onclick="gcmSetFilter('categorie','{{ $v }}')" style="padding:8px 14px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:8px;{{ request('categorie')===$v ? 'background:var(--accent-light);color:var(--accent);font-weight:600;' : 'color:var(--text-secondary);' }}">
                        @if(request('categorie')===$v)<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:12px;height:12px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>@else<span style="width:12px;"></span>@endif {{ $l }}
                    </div>
                    @endforeach
                </div>
            </div>
            @if(request('q'))<span class="chip">"{{ request('q') }}" <button type="button" onclick="gcmRemoveFilter('q')">x</button></span>@endif
            @if(request('statut'))<span class="chip">{{ ucfirst(request('statut')) }} <button type="button" onclick="gcmRemoveFilter('statut')">x</button></span>@endif
            @if(request('categorie'))<span class="chip">{{ \App\Models\Employe::CATEGORIES[request('categorie')] ?? '' }} <button type="button" onclick="gcmRemoveFilter('categorie')">x</button></span>@endif
            <div class="tb-spacer"></div>
            <button type="button" class="tb-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exporter
            </button>
        </div>
    </form>

    <div class="table-wrap">
    <table id="rh-emp-table">
        <thead><tr>
            <th style="width:36px;"><input type="checkbox" onchange="document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked)"></th>
            <th><span class="th-in"><span class="dh">*</span>Employe</span></th>
            <th><span class="th-in"><span class="dh">*</span>Matricule</span></th>
            <th><span class="th-in"><span class="dh">*</span>Categorie</span></th>
            <th><span class="th-in"><span class="dh">*</span>Poste</span></th>
            <th><span class="th-in"><span class="dh">*</span>Embauche</span></th>
            <th><span class="th-in"><span class="dh">*</span>Contrat</span></th>
            <th class="tc"><span class="th-in">Statut</span></th>
            <th class="tc"></th>
        </tr></thead>
        <tbody>
        @forelse($employes as $emp)
        <tr>
            <td><input type="checkbox" class="row-check"></td>
            <td>
                <a href="{{ route('personnel.show', $emp) }}" class="link" style="font-weight:500;">{{ $emp->nom_complet }}</a>
                @if($emp->email)<div class="muted">{{ $emp->email }}</div>@endif
            </td>
            <td><span class="mono">{{ $emp->matricule }}</span></td>
            <td><span class="badge bb">{{ $emp->categorie_libelle }}</span></td>
            <td style="color:var(--text-primary);">{{ $emp->poste }}</td>
            <td class="muted">{{ $emp->date_embauche->format('d/m/Y') }}</td>
            <td>
                @if($emp->contratActif)
                    <span class="badge bg">{{ strtoupper($emp->contratActif->type) }}</span>
                    @if($emp->contratActif->date_fin)<div class="muted" style="font-size:11px;margin-top:2px;">-> {{ $emp->contratActif->date_fin->format('d/m/Y') }}</div>@endif
                @else<span class="muted">-</span>@endif
            </td>
            <td class="tc"><span class="badge {{ $emp->statut_badge }}"><span class="dot"></span>{{ ucfirst($emp->statut) }}</span></td>
            <td class="tc" style="white-space:nowrap;">
                <a href="{{ route('personnel.show', $emp) }}" class="tb-btn" title="Voir"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                <a href="{{ route('personnel.edit', $emp) }}" class="tb-btn" title="Modifier"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
            </td>
        </tr>
        @empty
        <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:48px 20px;">Aucun employe trouve.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <x-pagination :paginator="$employes" singular="employe" plural="employes" />
</div>

@include('personnel._create_quick_modal')

<style>
.page-header{display:flex;align-items:center;justify-content:space-between;padding:16px 24px;background:var(--surface);border-bottom:1px solid var(--border);}
.page-title{font-size:16px;font-weight:700;color:var(--text-primary);display:flex;align-items:center;gap:8px;}
.badge-count{background:var(--accent-light);color:var(--accent);font-size:12px;font-weight:600;padding:2px 8px;border-radius:20px;}
.stats-row{display:flex;gap:12px;padding:16px 24px 0;}
.stat-card{flex:1;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:14px 16px;display:flex;align-items:center;gap:12px;box-shadow:var(--shadow-sm);border-top:3px solid var(--st,var(--border));}
.stat-icon{width:36px;height:36px;border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.stat-label{font-size:11px;color:var(--text-muted);font-weight:500;}
.stat-value{font-size:22px;font-weight:700;line-height:1.1;margin-top:2px;}
.list-card{margin:16px 24px 24px;}
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;}
.alert-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;transition:background .15s;}
.btn-new:hover{background:var(--accent-hover);}
</style>

</x-app-layout>