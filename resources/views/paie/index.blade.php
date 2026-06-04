<x-app-layout>
<div class="page-header">
    <div class="page-title">Paie <span class="badge-count">{{ $stats['payes'] }}</span></div>
    <a href="{{ route('paie.create') }}" class="btn-new">+ Nouveau bulletin</a>
</div>
<div class="stats-row">
    <div class="stat-card" style="--st:var(--text-muted);"><div class="stat-icon" style="background:var(--border-light);"><svg width="16" height="16" fill="none" stroke="var(--text-secondary)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div><div><div class="stat-label">Brouillons</div><div class="stat-value" style="color:var(--text-secondary);">{{ $stats['brouillons'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--accent);"><div class="stat-icon" style="background:var(--accent-light);"><svg width="16" height="16" fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div><div class="stat-label">Valides</div><div class="stat-value" style="color:var(--accent);">{{ $stats['valides'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--success);"><div class="stat-icon" style="background:var(--success-light);"><svg width="16" height="16" fill="none" stroke="var(--success)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg></div><div><div class="stat-label">Payes</div><div class="stat-value" style="color:var(--success);">{{ $stats['payes'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--purple);"><div class="stat-icon" style="background:var(--purple-light);"><svg width="16" height="16" fill="none" stroke="var(--purple)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div><div class="stat-label">Masse salariale</div><div class="stat-value" style="color:var(--purple);font-size:16px;">{{ number_format($stats['masse'],0,',',' ') }} DH</div></div></div>
</div>
@if(session('success'))<div class="alert alert-success" style="margin:16px 24px 0;">{{ session('success') }}</div>@endif
<div class="list-card">
    <form method="GET" action="{{ route('paie.index') }}" id="f-paie">
        <input type="hidden" name="statut" id="f-statut" value="{{ request('statut') }}">
        <div class="list-toolbar">
            <div class="search-box"><svg width="13" height="13" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un employe..." onchange="this.form.submit()"></div>
            <div style="position:relative;">
                <button type="button" class="tb-btn" onclick="gcmToggle('dd-paie-f',event)">Filtres</button>
                <div id="dd-paie-f" style="display:none;position:absolute;top:calc(100% + 4px);left:0;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-lg);min-width:160px;z-index:999;overflow:hidden;">
                    @foreach(\App\Models\BulletinPaie::STATUTS as $v => $s)<div onclick="gcmSetFilter('statut','{{ $v }}')" style="padding:8px 14px;font-size:13px;cursor:pointer;color:{{ request('statut')===$v ? 'var(--accent)' : 'var(--text-secondary)' }};">{{ $s['label'] }}</div>@endforeach
                </div>
            </div>
            <div class="tb-spacer"></div>
        </div>
    </form>
    <div class="table-wrap"><table>
        <thead><tr>
            <th><span class="th-in">Employe</span></th><th><span class="th-in">Periode</span></th>
            <th class="tr"><span class="th-in">Salaire base</span></th><th class="tr"><span class="th-in">Cotisations</span></th>
            <th class="tr"><span class="th-in">Net a payer</span></th><th class="tc"><span class="th-in">Statut</span></th><th class="tc"></th>
        </tr></thead>
        <tbody>
        @forelse($bulletins as $b)
        <tr>
            <td><a href="{{ route('personnel.show',$b->employe) }}" class="link" style="font-weight:500;">{{ $b->employe->nom_complet }}</a></td>
            <td class="muted">{{ $b->periode_libelle }}</td>
            <td class="tr amount">{{ number_format($b->salaire_base,0,',',' ') }} DH</td>
            <td class="tr muted">{{ number_format($b->total_cotisations,0,',',' ') }} DH</td>
            <td class="tr amount" style="color:var(--accent);">{{ number_format($b->net_a_payer,0,',',' ') }} DH</td>
            <td class="tc"><span class="badge {{ $b->statut_badge }}"><span class="dot"></span>{{ $b->statut_libelle }}</span></td>
            <td class="tc" style="white-space:nowrap;">
                <a href="{{ route('paie.show',$b) }}" class="tb-btn" title="Voir"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                <a href="{{ route('paie.edit',$b) }}" class="tb-btn" title="Modifier"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
            </td>
        </tr>
        @empty<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px;">Aucun bulletin.</td></tr>@endforelse
        </tbody>
    </table></div>
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
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;}
.alert-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
.btn-new{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:var(--accent);color:#fff;border:none;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:inherit;}
</style>
</x-app-layout>