<x-app-layout>
<div class="page-header">
    <div class="page-title">Formations <span class="badge-count">{{ $stats['terminees'] }}</span></div>
    <a href="{{ route('formations.create') }}" class="btn-new">+ Nouvelle formation</a>
</div>
<div class="stats-row">
    <div class="stat-card" style="--st:var(--accent);"><div class="stat-icon" style="background:var(--accent-light);"><svg width="16" height="16" fill="none" stroke="var(--accent)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div><div><div class="stat-label">Planifiees</div><div class="stat-value" style="color:var(--accent);">{{ $stats['planifiees'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--warning);"><div class="stat-icon" style="background:var(--warning-light);"><svg width="16" height="16" fill="none" stroke="var(--warning)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div><div class="stat-label">En cours</div><div class="stat-value" style="color:var(--warning);">{{ $stats['en_cours'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--success);"><div class="stat-icon" style="background:var(--success-light);"><svg width="16" height="16" fill="none" stroke="var(--success)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div><div><div class="stat-label">Terminees</div><div class="stat-value" style="color:var(--success);">{{ $stats['terminees'] }}</div></div></div>
    <div class="stat-card" style="--st:var(--purple);"><div class="stat-icon" style="background:var(--purple-light);"><svg width="16" height="16" fill="none" stroke="var(--purple)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13"/></svg></div><div><div class="stat-label">Heures cette annee</div><div class="stat-value" style="color:var(--purple);font-size:18px;">{{ number_format($stats['total_heures'],0) }}h</div></div></div>
</div>
@if(session('success'))<div class="alert alert-success" style="margin:16px 24px 0;">{{ session('success') }}</div>@endif
<div class="list-card">
    <form method="GET" action="{{ route('formations.index') }}" id="f-form">
        <input type="hidden" name="type"   id="f-type"   value="{{ request('type') }}">
        <input type="hidden" name="statut" id="f-statut" value="{{ request('statut') }}">
        <div class="list-toolbar">
            <div class="search-box"><svg width="13" height="13" fill="none" stroke="var(--text-muted)" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input type="text" name="q" value="{{ request('q') }}" placeholder="Formation, employe..." onchange="this.form.submit()"></div>
            <div style="position:relative;">
                <button type="button" class="tb-btn" onclick="gcmToggle('dd-form-f',event)">Filtres</button>
                <div id="dd-form-f" style="display:none;position:absolute;top:calc(100% + 4px);left:0;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-lg);min-width:170px;z-index:999;overflow:hidden;">
                    <div style="padding:6px 12px 3px;font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Type</div>
                    @foreach(\App\Models\Formation::TYPES as $v => $l)<div onclick="gcmSetFilter('type','{{ $v }}')" style="padding:8px 14px;font-size:13px;cursor:pointer;color:{{ request('type')===$v ? 'var(--accent)' : 'var(--text-secondary)' }};">{{ $l }}</div>@endforeach
                    <div style="height:1px;background:var(--border-light);margin:3px 0;"></div>
                    <div style="padding:6px 12px 3px;font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;">Statut</div>
                    @foreach(\App\Models\Formation::STATUTS as $v => $s)<div onclick="gcmSetFilter('statut','{{ $v }}')" style="padding:8px 14px;font-size:13px;cursor:pointer;color:{{ request('statut')===$v ? 'var(--accent)' : 'var(--text-secondary)' }};">{{ $s['label'] }}</div>@endforeach
                </div>
            </div>
            <div class="tb-spacer"></div>
        </div>
    </form>
    <div class="table-wrap"><table>
        <thead><tr>
            <th><span class="th-in">Employe</span></th><th><span class="th-in">Intitule</span></th>
            <th><span class="th-in">Organisme</span></th><th><span class="th-in">Type</span></th>
            <th><span class="th-in">Dates</span></th><th class="tc"><span class="th-in">Heures</span></th>
            <th class="tc"><span class="th-in">Statut</span></th><th class="tc"></th>
        </tr></thead>
        <tbody>
        @forelse($formations as $f)
        <tr>
            <td><a href="{{ route('personnel.show',$f->employe) }}" class="link" style="font-weight:500;">{{ $f->employe->nom_complet }}</a></td>
            <td style="color:var(--text-primary);font-weight:500;">{{ Str::limit($f->intitule,40) }}</td>
            <td class="muted">{{ $f->organisme ?? '—' }}</td>
            <td><span class="badge {{ $f->type === 'externe' ? 'bp' : 'bb' }}">{{ $f->type_libelle }}</span></td>
            <td class="muted">{{ $f->date_debut->format('d/m') }} → {{ $f->date_fin->format('d/m/Y') }}</td>
            <td class="tc amount">{{ number_format($f->nb_heures,1) }}h</td>
            <td class="tc"><span class="badge {{ $f->statut_badge }}">{{ $f->statut_libelle }}</span></td>
            <td class="tc" style="white-space:nowrap;">
                <a href="{{ route('formations.show',$f) }}" class="tb-btn"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg></a>
                <a href="{{ route('formations.edit',$f) }}" class="tb-btn"><svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></a>
            </td>
        </tr>
        @empty<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:40px;">Aucune formation.</td></tr>@endforelse
        </tbody>
    </table></div>
    <x-pagination :paginator="$formations" singular="formation" plural="formations" />
</div>
</x-app-layout>