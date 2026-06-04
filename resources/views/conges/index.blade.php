<x-app-layout>
<div class="page-header">
    <div class="page-title">Conges <span class="badge-count">{{ $stats['en_attente'] }}</span></div>
    <a href="{{ route('conges.create') }}" class="btn-new">+ Nouvelle demande</a>
</div>
<div class="stats-row">
    <div class="stat-card" style="--st:var(--warning);"><div class="stat-icon" style="background:var(--warning-light);"><svg width="16" height="16" fill="none" stroke="var(--warning)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div><div class="stat-label">En attente</div><div class="stat-value" style="color:var(--warning);">{{ $stats['en_attente'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--success);"><div class="stat-icon" style="background:var(--success-light);"><svg width="16" height="16" fill="none" stroke="var(--success)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div><div class="stat-label">Approuves ce mois</div><div class="stat-value" style="color:var(--success);">{{ $stats['approuves'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--danger);"><div class="stat-icon" style="background:var(--danger-light);"><svg width="16" height="16" fill="none" stroke="var(--danger)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div><div class="stat-label">Rejetes ce mois</div><div class="stat-value" style="color:var(--danger);">{{ $stats['rejetes'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--accent);"><div class="stat-icon" style="background:var(--accent-light);"><svg width="16" height="16" fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div><div><div class="stat-label">Jours pris ce mois</div><div class="stat-value" style="color:var(--accent);">{{ $stats['jours_mois'] }}</div></div></div>
</div>
@if(session('success'))<div class="alert alert-success" style="margin:16px 24px 0;">{{ session('success') }}</div>@endif
<div class="list-card">
    <form method="GET" action="{{ route('conges.index') }}" id="f-conges">
        <input type="hidden" name="statut" id="f-statut" value="{{ request('statut') }}">
        <input type="hidden" name="type_conge" id="f-type_conge" value="{{ request('type_conge') }}">
        <div class="list-toolbar">
            <div class="search-box"><svg width="13" height="13" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un employe..." onchange="this.form.submit()"></div>
            @php $ac = collect(['q','statut','type_conge'])->filter(fn($k)=>request($k))->count(); @endphp
            <div style="position:relative;">
                <button type="button" class="tb-btn {{ $ac ? 'active' : '' }}" onclick="gcmToggle('dd-cng-f',event)">Filtres @if($ac)<span class="tb-count">{{ $ac }}</span>@endif</button>
                <div id="dd-cng-f" style="display:none;position:absolute;top:calc(100% + 4px);left:0;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-lg);min-width:190px;z-index:999;overflow:hidden;">
                    <div style="padding:6px 12px 3px;font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Statut</div>
                    @foreach(\App\Models\Conge::STATUTS as $v => $s)<div onclick="gcmSetFilter('statut','{{ $v }}')" style="padding:8px 14px;font-size:13px;cursor:pointer;color:{{ request('statut')===$v ? 'var(--accent)' : 'var(--text-secondary)' }};">{{ $s['label'] }}</div>@endforeach
                    <div style="height:1px;background:var(--border-light);margin:3px 0;"></div>
                    <div style="padding:6px 12px 3px;font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Type</div>
                    @foreach(\App\Models\Conge::TYPES as $v => $l)<div onclick="gcmSetFilter('type_conge','{{ $v }}')" style="padding:8px 14px;font-size:13px;cursor:pointer;color:{{ request('type_conge')===$v ? 'var(--accent)' : 'var(--text-secondary)' }};">{{ $l }}</div>@endforeach
                </div>
            </div>
            @if(request('statut'))<span class="chip">{{ \App\Models\Conge::STATUTS[request('statut')]['label'] ?? '' }} <button type="button" onclick="gcmRemoveFilter('statut')">x</button></span>@endif
            @if(request('type_conge'))<span class="chip">{{ \App\Models\Conge::TYPES[request('type_conge')] ?? '' }} <button type="button" onclick="gcmRemoveFilter('type_conge')">x</button></span>@endif
            <div class="tb-spacer"></div>
        </div>
    </form>
    <div class="table-wrap"><table>
        <thead><tr>
            <th><span class="th-in">Employe</span></th><th><span class="th-in">Type</span></th>
            <th><span class="th-in">Du</span></th><th><span class="th-in">Au</span></th>
            <th class="tc"><span class="th-in">Jours</span></th><th class="tc"><span class="th-in">Statut</span></th><th class="tc"></th>
        </tr></thead>
        <tbody>
        @forelse($conges as $c)
        <tr>
            <td><a href="{{ route('personnel.show',$c->employe) }}" class="link" style="font-weight:500;">{{ $c->employe->nom_complet }}</a></td>
            <td><span class="badge bb">{{ $c->type_conge_libelle }}</span></td>
            <td class="muted">{{ $c->date_debut->format('d/m/Y') }}</td>
            <td class="muted">{{ $c->date_fin->format('d/m/Y') }}</td>
            <td class="tc amount">{{ $c->nb_jours }}j</td>
            <td class="tc"><span class="badge {{ $c->statut_badge }}"><span class="dot"></span>{{ $c->statut_libelle }}</span></td>
            <td class="tc" style="white-space:nowrap;">
                <a href="{{ route('conges.show',$c) }}" class="tb-btn" title="Voir"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                @if(in_array($c->statut,['soumis','en_validation']))<form method="POST" action="{{ route('conges.approuver',$c) }}" style="display:inline;">@csrf @method('PATCH')<button type="submit" class="tb-btn" style="color:var(--success);" title="Approuver">✓</button></form>@endif
            </td>
        </tr>
        @empty<tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:40px;">Aucun conge.</td></tr>@endforelse
        </tbody>
    </table></div>
    <x-pagination :paginator="$conges" singular="conge" plural="conges" />
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