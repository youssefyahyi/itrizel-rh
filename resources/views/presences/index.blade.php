<x-app-layout>
<div class="page-header">
    <div class="page-title">Presences <span class="badge-count">{{ $stats['ce_mois'] }}</span></div>
    <a href="{{ route('presences.create') }}" class="btn-new">
        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Nouvelle presence
    </a>
</div>
<div class="stats-row">
    <div class="stat-card" style="--st:var(--success);"><div class="stat-icon" style="background:var(--success-light);"><svg width="16" height="16" fill="none" stroke="var(--success)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div><div class="stat-label">Presents aujourd hui</div><div class="stat-value" style="color:var(--success);">{{ $stats['presents_today'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--danger);"><div class="stat-icon" style="background:var(--danger-light);"><svg width="16" height="16" fill="none" stroke="var(--danger)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div><div><div class="stat-label">Absents aujourd hui</div><div class="stat-value" style="color:var(--danger);">{{ $stats['absents_today'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--accent);"><div class="stat-icon" style="background:var(--accent-light);"><svg width="16" height="16" fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div><div><div class="stat-label">Cette semaine</div><div class="stat-value" style="color:var(--accent);">{{ $stats['cette_semaine'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--text-muted);"><div class="stat-icon" style="background:var(--border-light);"><svg width="16" height="16" fill="none" stroke="var(--text-secondary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/></svg></div><div><div class="stat-label">Ce mois</div><div class="stat-value" style="color:var(--text-secondary);">{{ $stats['ce_mois'] }}</div></div></div>
</div>
@if(session('success'))<div class="alert alert-success" style="margin:16px 24px 0;">{{ session('success') }}</div>@endif
<div class="list-card">
    <form method="GET" action="{{ route('presences.index') }}" id="f-pres">
        <input type="hidden" name="statut" id="f-statut" value="{{ request('statut') }}">
        <div class="list-toolbar">
            <div class="search-box"><svg width="13" height="13" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input type="text" name="q" value="{{ request('q') }}" placeholder="Employe..." onchange="this.form.submit()"></div>
            <div style="position:relative;">
                <button type="button" class="tb-btn" onclick="gcmToggle('dd-pres-f',event)">Filtres</button>
                <div id="dd-pres-f" style="display:none;position:absolute;top:calc(100% + 4px);left:0;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-lg);min-width:170px;z-index:999;overflow:hidden;">
                    @foreach(\App\Models\Presence::STATUTS as $v => $s)<div onclick="gcmSetFilter('statut','{{ $v }}')" style="padding:8px 14px;font-size:13px;cursor:pointer;color:{{ request('statut')===$v ? 'var(--accent)' : 'var(--text-secondary)' }};">{{ $s['label'] }}</div>@endforeach
                </div>
            </div>
            @if(request('statut'))<span class="chip">{{ \App\Models\Presence::STATUTS[request('statut')]['label'] ?? '' }} <button type="button" onclick="gcmRemoveFilter('statut')">x</button></span>@endif
            <div class="tb-spacer"></div>
        </div>
    </form>
    <div class="table-wrap"><table>
        <thead><tr>
            <th><span class="th-in">Employe</span></th>
            <th><span class="th-in">Date</span></th>
            <th><span class="th-in">Arrivee</span></th>
            <th><span class="th-in">Depart</span></th>
            <th class="tc"><span class="th-in">Heures</span></th>
            <th class="tc"><span class="th-in">Statut</span></th>
            <th class="tc"></th>
        </tr></thead>
        <tbody>
        @forelse($presences as $p)
        <tr>
            <td><a href="{{ route('personnel.show', $p->employe) }}" class="link" style="font-weight:500;">{{ $p->employe->nom_complet }}</a></td>
            <td class="muted">{{ $p->date->format('d/m/Y') }}</td>
            <td class="mono">{{ $p->heure_arrivee ? \Carbon\Carbon::parse($p->heure_arrivee)->format('H:i') : '—' }}</td>
            <td class="mono">{{ $p->heure_depart  ? \Carbon\Carbon::parse($p->heure_depart)->format('H:i') : '—' }}</td>
            <td class="tc">{{ $p->duree !== null ? number_format($p->duree,1).'h' : '—' }}</td>
            <td class="tc"><span class="badge {{ $p->statut_badge }}">{{ $p->statut_libelle }}</span></td>
            <td class="tc" style="white-space:nowrap;">
                <a href="{{ route('presences.show',$p) }}" class="tb-btn" title="Voir"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                <a href="{{ route('presences.edit',$p) }}" class="tb-btn" title="Modifier"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
            </td>
        </tr>
        @empty<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px;">Aucune presence.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <x-pagination :paginator="$presences" singular="presence" plural="presences" />
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
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;}
.alert-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;}
.btn-new:hover{background:var(--accent-hover);}
</style>
</x-app-layout>