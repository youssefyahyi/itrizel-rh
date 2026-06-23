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
    <div class="stat-card" style="--st:var(--danger);">
        <div class="stat-icon" style="background:rgba(239,68,68,.1);">
            <svg width="16" height="16" fill="none" stroke="var(--danger)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        </div>
        <div>
            <div class="stat-label">Sans contrat actif</div>
            <div class="stat-value" style="color:var(--danger);">{{ $stats['sans_contrat'] }}</div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin:16px 24px 0;">{{ session('success') }}</div>
@endif

{{-- LIST CARD --}}
<div class="list-card">
    <form method="GET" action="{{ route('personnel.index') }}" id="rh-emp-form">
        <input type="hidden" name="statut"       id="f-statut"       value="{{ request('statut') }}">
        <input type="hidden" name="unite_id"     id="f-unite_id"     value="{{ request('unite_id') }}">
        <input type="hidden" name="categorie_id" id="f-categorie_id" value="{{ request('categorie_id') }}">
        <input type="hidden" name="type_contrat" id="f-type_contrat" value="{{ request('type_contrat') }}">
        <input type="hidden" name="anciennete"   id="f-anciennete"   value="{{ request('anciennete') }}">
        <input type="hidden" name="sans_contrat" id="f-sans_contrat" value="{{ request('sans_contrat') }}">

        @php
            $filtresActifs = collect(['q','statut','unite_id','categorie_id','type_contrat','anciennete','sans_contrat'])
                ->filter(fn($k) => request($k))->count();

            $labelUnite     = $unites->firstWhere('id', request('unite_id'))?->nom;
            $labelCat       = $filtreCategories->firstWhere('id', request('categorie_id'))?->nom;
            $labelType      = match(request('type_contrat')) { 'CDI'=>'CDI','CDD'=>'CDD','interim'=>'Intérim','vacataire'=>'Vacataire', default=>null };
            $labelAncien    = match(request('anciennete')) { 'lt1'=>'< 1 an','1a5'=>'1 – 5 ans','5a10'=>'5 – 10 ans','gt10'=>'> 10 ans', default=>null };
        @endphp

        <div class="list-toolbar">
            <div class="search-box">
                <svg width="13" height="13" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, prénom, matricule, CIN..." onchange="this.form.submit()">
            </div>
            <div style="width:1px;height:18px;background:var(--border);flex-shrink:0;"></div>

            {{-- Bouton Filtres + dropdown --}}
            <div style="position:relative;">
                <button type="button" class="tb-btn {{ $filtresActifs ? 'active' : '' }}" onclick="gcmToggle('dd-emp-f', event)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Filtres @if($filtresActifs)<span class="tb-count">{{ $filtresActifs }}</span>@endif
                </button>

                <div id="dd-emp-f" style="display:none;position:absolute;top:calc(100% + 4px);left:0;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-lg);width:260px;z-index:999;overflow:hidden;">

                    {{-- Statut --}}
                    <div class="dd-section-label">Statut</div>
                    @foreach(['' => 'Tous', 'actif' => 'Actifs', 'inactif' => 'Inactifs', 'suspendu' => 'Suspendus'] as $v => $l)
                    <div onclick="gcmSetFilter('statut','{{ $v }}')" class="dd-item {{ request('statut')===$v ? 'dd-active' : '' }}">
                        <span class="dd-check">@if(request('statut')===$v)✓@endif</span>{{ $l }}
                    </div>
                    @endforeach

                    {{-- Unité --}}
                    <div class="dd-sep"></div>
                    <div class="dd-section-label">Unité organisationnelle</div>
                    <div onclick="gcmSetFilter('unite_id','')" class="dd-item {{ !request('unite_id') ? 'dd-active' : '' }}">
                        <span class="dd-check">@if(!request('unite_id'))✓@endif</span>Toutes
                    </div>
                    @foreach($unites as $u)
                    <div onclick="gcmSetFilter('unite_id','{{ $u->id }}')" class="dd-item {{ request('unite_id')==$u->id ? 'dd-active' : '' }}">
                        <span class="dd-check">@if(request('unite_id')==$u->id)✓@endif</span>
                        <span style="font-size:10px;opacity:.5;text-transform:uppercase;margin-right:4px;">{{ substr($u->type,0,3) }}</span>{{ $u->nom }}
                    </div>
                    @endforeach

                    {{-- Catégorie --}}
                    <div class="dd-sep"></div>
                    <div class="dd-section-label">Catégorie d'emploi</div>
                    <div onclick="gcmSetFilter('categorie_id','')" class="dd-item {{ !request('categorie_id') ? 'dd-active' : '' }}">
                        <span class="dd-check">@if(!request('categorie_id'))✓@endif</span>Toutes
                    </div>
                    @foreach($filtreCategories as $cat)
                    <div onclick="gcmSetFilter('categorie_id','{{ $cat->id }}')" class="dd-item {{ request('categorie_id')==$cat->id ? 'dd-active' : '' }}">
                        <span class="dd-check">@if(request('categorie_id')==$cat->id)✓@endif</span>{{ $cat->nom }}
                    </div>
                    @endforeach

                    {{-- Type contrat --}}
                    <div class="dd-sep"></div>
                    <div class="dd-section-label">Type de contrat</div>
                    <div onclick="gcmSetFilter('type_contrat','')" class="dd-item {{ !request('type_contrat') ? 'dd-active' : '' }}">
                        <span class="dd-check">@if(!request('type_contrat'))✓@endif</span>Tous
                    </div>
                    @foreach(['CDI'=>'CDI','CDD'=>'CDD','interim'=>'Intérim','vacataire'=>'Vacataire'] as $v => $l)
                    <div onclick="gcmSetFilter('type_contrat','{{ $v }}')" class="dd-item {{ request('type_contrat')===$v ? 'dd-active' : '' }}">
                        <span class="dd-check">@if(request('type_contrat')===$v)✓@endif</span>{{ $l }}
                    </div>
                    @endforeach

                    {{-- Ancienneté --}}
                    <div class="dd-sep"></div>
                    <div class="dd-section-label">Ancienneté</div>
                    <div onclick="gcmSetFilter('anciennete','')" class="dd-item {{ !request('anciennete') ? 'dd-active' : '' }}">
                        <span class="dd-check">@if(!request('anciennete'))✓@endif</span>Toutes
                    </div>
                    @foreach(['lt1'=>'< 1 an','1a5'=>'1 – 5 ans','5a10'=>'5 – 10 ans','gt10'=>'> 10 ans'] as $v => $l)
                    <div onclick="gcmSetFilter('anciennete','{{ $v }}')" class="dd-item {{ request('anciennete')===$v ? 'dd-active' : '' }}">
                        <span class="dd-check">@if(request('anciennete')===$v)✓@endif</span>{{ $l }}
                    </div>
                    @endforeach

                    {{-- Sans contrat --}}
                    <div class="dd-sep"></div>
                    <div onclick="gcmSetFilter('sans_contrat', request('sans_contrat') ? '' : '1')"
                         class="dd-item {{ request('sans_contrat') ? 'dd-active' : '' }}"
                         style="color:var(--danger);">
                        <span class="dd-check" style="color:var(--danger);">@if(request('sans_contrat'))✓@endif</span>
                        Sans contrat actif uniquement
                    </div>

                    {{-- Tout effacer --}}
                    @if($filtresActifs > 0)
                    <div class="dd-sep"></div>
                    <div onclick="empEffacerFiltres()" class="dd-item" style="color:var(--text-muted);font-size:12px;justify-content:center;">
                        Effacer tous les filtres
                    </div>
                    @endif
                </div>
            </div>

            {{-- Chips filtres actifs --}}
            @if(request('q'))<span class="chip">"{{ request('q') }}" <button type="button" onclick="gcmRemoveFilter('q')">×</button></span>@endif
            @if(request('statut'))<span class="chip">{{ ucfirst(request('statut')) }} <button type="button" onclick="gcmRemoveFilter('statut')">×</button></span>@endif
            @if($labelUnite)<span class="chip">{{ $labelUnite }} <button type="button" onclick="gcmRemoveFilter('unite_id')">×</button></span>@endif
            @if($labelCat)<span class="chip">{{ $labelCat }} <button type="button" onclick="gcmRemoveFilter('categorie_id')">×</button></span>@endif
            @if($labelType)<span class="chip">{{ $labelType }} <button type="button" onclick="gcmRemoveFilter('type_contrat')">×</button></span>@endif
            @if($labelAncien)<span class="chip">{{ $labelAncien }} <button type="button" onclick="gcmRemoveFilter('anciennete')">×</button></span>@endif
            @if(request('sans_contrat'))<span class="chip" style="color:var(--danger);border-color:var(--danger);">Sans contrat <button type="button" onclick="gcmRemoveFilter('sans_contrat')" style="color:var(--danger);">×</button></span>@endif

            <div class="tb-spacer"></div>
            <button type="button" class="tb-btn">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exporter
            </button>
        </div>
    </form>

    <script>
    function empEffacerFiltres() {
        ['statut','unite_id','categorie_id','type_contrat','anciennete','sans_contrat','q'].forEach(function(n) {
            var f = document.getElementById('f-' + n) || document.querySelector('[name="' + n + '"]');
            if (f) f.value = '';
        });
        document.getElementById('rh-emp-form').submit();
    }
    </script>

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
            <td><span class="badge bb">{{ $emp->fichePoste?->categorie->nom ?? '—' }}</span></td>
            <td style="color:var(--text-primary);">{{ $emp->fichePoste?->poste->nom ?? '—' }}</td>
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
.dd-section-label{padding:8px 12px 3px;font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;}
.dd-item{padding:7px 14px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;color:var(--text-secondary);transition:background .1s;}
.dd-item:hover{background:var(--surface-soft);}
.dd-item.dd-active{background:var(--accent-light);color:var(--accent);font-weight:600;}
.dd-check{width:14px;font-size:11px;flex-shrink:0;text-align:center;}
.dd-sep{border-top:1px solid var(--border-light);margin:4px 0;}
#dd-emp-f{max-height:420px;overflow-y:auto;}
</style>

</x-app-layout>