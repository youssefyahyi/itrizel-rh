<x-app-layout>

{{-- PAGE HEADER --}}
<div class="page-header">
    <div class="page-title">
        Paie
        <span class="badge-count">{{ $bulletins->total() }}</span>
    </div>
    <a href="{{ route('paie.create') }}" class="btn-new">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Nouveau bulletin
    </a>
</div>

{{-- STATS --}}
<div class="stats-row">
    <div class="stat-card" style="--st:var(--text-muted);">
        <div class="stat-icon" style="background:var(--border-light);"><svg width="16" height="16" fill="none" stroke="var(--text-secondary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
        <div><div class="stat-label">Brouillons</div><div class="stat-value" style="color:var(--text-secondary);">{{ $stats['brouillons'] }}</div></div>
    </div>
    <div class="stat-card" style="--st:var(--accent);">
        <div class="stat-icon" style="background:var(--accent-light);"><svg width="16" height="16" fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div><div class="stat-label">Validés</div><div class="stat-value" style="color:var(--accent);">{{ $stats['valides'] }}</div></div>
    </div>
    <div class="stat-card" style="--st:var(--success);">
        <div class="stat-icon" style="background:var(--success-light);"><svg width="16" height="16" fill="none" stroke="var(--success)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></div>
        <div><div class="stat-label">Payés</div><div class="stat-value" style="color:var(--success);">{{ $stats['payes'] }}</div></div>
    </div>
    <div class="stat-card" style="--st:var(--purple,#7c3aed);">
        <div class="stat-icon" style="background:#f5f3ff;"><svg width="16" height="16" fill="none" stroke="#7c3aed" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
        <div><div class="stat-label">Masse salariale (mois)</div><div class="stat-value" style="color:#7c3aed;font-size:15px;">{{ number_format($stats['masse'],0,',',' ') }} DH</div></div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin:16px 24px 0;">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- LIST CARD --}}
<div class="list-card">
    <form method="GET" action="{{ route('paie.index') }}" id="f-paie">
        <input type="hidden" name="statut" id="f-statut" value="{{ request('statut') }}">
        <input type="hidden" name="mois"   id="f-mois"   value="{{ request('mois') }}">
        <input type="hidden" name="annee"  id="f-annee"  value="{{ request('annee') }}">

        <div class="list-toolbar">
            {{-- Recherche --}}
            <div class="search-box">
                <svg width="13" height="13" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un employé..." onchange="this.form.submit()">
            </div>

            <div style="width:1px;height:18px;background:var(--border);flex-shrink:0;"></div>

            {{-- Filtres --}}
            @php $ac = collect(['q','statut','mois','annee'])->filter(fn($k)=>request($k))->count(); @endphp
            <div style="position:relative;">
                <button type="button" class="tb-btn {{ $ac ? 'active' : '' }}" onclick="gcmToggle('dd-paie-f', event)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Filtres @if($ac)<span class="tb-count">{{ $ac }}</span>@endif
                </button>
                <div id="dd-paie-f" style="display:none;position:absolute;top:calc(100% + 4px);left:0;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-lg);min-width:200px;z-index:999;overflow:hidden;">
                    <div style="padding:8px 12px 4px;font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Statut</div>
                    @foreach(['' => 'Tous', 'brouillon' => 'Brouillon', 'valide' => 'Validé', 'paye' => 'Payé'] as $v => $l)
                    <div onclick="gcmSetFilter('statut','{{ $v }}')" style="padding:8px 14px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:8px;{{ request('statut')===$v ? 'background:var(--accent-light);color:var(--accent);font-weight:600;' : 'color:var(--text-secondary);' }}">
                        @if(request('statut')===$v)<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:12px;height:12px;flex-shrink:0;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>@else<span style="width:12px;display:inline-block;"></span>@endif {{ $l }}
                    </div>
                    @endforeach
                    <div style="height:1px;background:var(--border-light);margin:3px 0;"></div>
                    <div style="padding:8px 12px 4px;font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Période</div>
                    <div style="padding:6px 12px 10px;display:flex;gap:6px;">
                        @php $moisNoms = ['1'=>'Jan','2'=>'Fév','3'=>'Mar','4'=>'Avr','5'=>'Mai','6'=>'Jun','7'=>'Jul','8'=>'Aoû','9'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Déc']; @endphp
                        <select onchange="gcmSetFilter('mois',this.value)" style="flex:1;height:28px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:12px;padding:0 4px;background:var(--surface);cursor:pointer;">
                            <option value="">Mois</option>
                            @foreach($moisNoms as $m => $n)<option value="{{ $m }}" {{ request('mois')==$m ? 'selected' : '' }}>{{ $n }}</option>@endforeach
                        </select>
                        <select onchange="gcmSetFilter('annee',this.value)" style="flex:1;height:28px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:12px;padding:0 4px;background:var(--surface);cursor:pointer;">
                            <option value="">Année</option>
                            @foreach(range(date('Y'), 2024) as $y)<option value="{{ $y }}" {{ request('annee')==$y ? 'selected' : '' }}>{{ $y }}</option>@endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Chips filtres actifs --}}
            @if(request('q'))<span class="chip">"{{ request('q') }}" <button type="button" onclick="gcmRemoveFilter('q')">×</button></span>@endif
            @if(request('statut'))<span class="chip">{{ ucfirst(request('statut')) }} <button type="button" onclick="gcmRemoveFilter('statut')">×</button></span>@endif
            @if(request('mois') || request('annee'))<span class="chip">{{ $moisNoms[request('mois')] ?? '' }} {{ request('annee') }} <button type="button" onclick="gcmRemoveFilter('mois');gcmRemoveFilter('annee')">×</button></span>@endif

            <div class="tb-spacer"></div>
        </div>
    </form>

    <div class="table-wrap">
    <table>
        <thead><tr>
            <th style="width:36px;"><input type="checkbox" onchange="document.querySelectorAll('.row-check').forEach(c=>c.checked=this.checked)"></th>
            <th><span class="th-in"><span class="dh">*</span>Employé</span></th>
            <th><span class="th-in"><span class="dh">*</span>Période</span></th>
            <th class="tr"><span class="th-in"><span class="dh">*</span>Salaire base</span></th>
            <th class="tr"><span class="th-in"><span class="dh">*</span>Cotisations</span></th>
            <th class="tr"><span class="th-in"><span class="dh">*</span>Net à payer</span></th>
            <th class="tc"><span class="th-in">Statut</span></th>
            <th class="tc"></th>
        </tr></thead>
        <tbody>
        @forelse($bulletins as $b)
        <tr>
            <td><input type="checkbox" class="row-check"></td>
            <td>
                <a href="{{ route('paie.show', $b) }}" class="link" style="font-weight:500;">{{ $b->employe->nom_complet }}</a>
                <div class="muted" style="font-size:11px;">{{ $b->employe->fichePoste?->poste->nom ?? '—' }}</div>
            </td>
            <td style="color:var(--text-primary);font-weight:500;">{{ $b->periode_libelle }}</td>
            <td class="tr"><span class="amount">{{ number_format($b->salaire_base, 0, ',', ' ') }} DH</span></td>
            <td class="tr muted">{{ number_format($b->total_cotisations, 0, ',', ' ') }} DH</td>
            <td class="tr"><span class="amount" style="color:var(--accent);font-weight:600;">{{ number_format($b->net_a_payer, 0, ',', ' ') }} DH</span></td>
            <td class="tc"><span class="badge {{ $b->statut_badge }}"><span class="dot"></span>{{ $b->statut_libelle }}</span></td>
            <td class="tc" style="white-space:nowrap;">
                <a href="{{ route('paie.show', $b) }}" class="tb-btn" title="Voir">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <a href="{{ route('paie.edit', $b) }}" class="tb-btn" title="Modifier">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                <a href="{{ route('paie.print', $b) }}" target="_blank" class="tb-btn" title="Imprimer">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                </a>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:48px 20px;">Aucun bulletin trouvé.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
    <x-pagination :paginator="$bulletins" singular="bulletin" plural="bulletins" />
</div>

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
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;display:flex;align-items:center;gap:8px;}
.alert-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;text-decoration:none;transition:background .15s;}
.btn-new:hover{opacity:.9;}
</style>

</x-app-layout>
