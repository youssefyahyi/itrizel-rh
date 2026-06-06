<x-app-layout>
<x-slot name="header">Tableau de bord</x-slot>

{{-- HERO KPI --}}
<div style="background:#0F1923;border-radius:10px;padding:12px 20px;margin-bottom:20px;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;background:radial-gradient(circle,rgba(37,99,235,0.15) 0%,transparent 70%);pointer-events:none;"></div>
    <div style="font-size:8px;font-weight:600;color:#C2410C;letter-spacing:1.5px;text-transform:uppercase;margin-bottom:10px;">Itrizel-RH · Tableau de bord RH opérationnel</div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:0;border-left:1px solid rgba(255,255,255,0.07);">
        @foreach([
            ['label' => 'Personnel actif',     'value' => $stats['personnel']['actifs'],          'sub' => '/ '.$stats['personnel']['total'].' total'],
            ['label' => 'Contrats actifs',      'value' => $stats['contrats']['actifs'],           'sub' => $stats['contrats']['expirants'].' expirants'],
            ['label' => 'Congés en attente',    'value' => $stats['conges']['en_attente'],         'sub' => 'à valider'],
            ['label' => 'Bulletins ce mois',    'value' => $stats['paie']['bulletins_mois'],       'sub' => date('m/Y')],
        ] as $kpi)
        <div style="padding:0 16px;border-right:1px solid rgba(255,255,255,0.07);min-width:0;">
            <div style="font-size:9px;color:#64748B;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $kpi['label'] }}</div>
            <div style="display:flex;align-items:baseline;gap:6px;">
                <span style="font-size:20px;font-weight:700;color:white;line-height:1;">{{ $kpi['value'] }}</span>
                <span style="font-size:11px;color:#64748B;font-weight:400;">{{ $kpi['sub'] }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- GRILLE --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

    {{-- Contrats expirant bientôt --}}
    <div class="card">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:6px;height:6px;border-radius:50%;background:#F59E0B;"></div>
                <span class="card-title">Contrats expirant bientôt</span>
            </div>
            <a href="{{ route('contrats.index') }}" style="font-size:11px;color:var(--accent);text-decoration:none;">Voir tout →</a>
        </div>
        @forelse($contratsExpirants as $c)
        <a href="{{ route('contrats.show', $c) }}" class="related-item">
            <div class="related-item-left">
                <div class="related-icon" style="background:#FEF3C7;color:#D97706;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <div class="related-name">{{ $c->employe->nom_complet ?? '—' }}</div>
                    <div class="related-sub">{{ $c->type }} · {{ $c->fichePoste?->poste->nom ?? '—' }}</div>
                </div>
            </div>
            <span style="font-size:11px;font-weight:600;color:#D97706;white-space:nowrap;">{{ \Carbon\Carbon::parse($c->date_fin)->format('d/m/Y') }}</span>
        </a>
        @empty
        <div class="empty-state" style="padding:20px;">Aucun contrat n'expire dans les 30 jours.</div>
        @endforelse
    </div>

    {{-- Congés en attente de validation --}}
    <div class="card">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:6px;height:6px;border-radius:50%;background:var(--danger);"></div>
                <span class="card-title">Congés en attente de validation</span>
            </div>
            <a href="{{ route('conges.index') }}" style="font-size:11px;color:var(--accent);text-decoration:none;">Voir tout →</a>
        </div>
        @forelse($congesAttente as $cg)
        <a href="{{ route('conges.show', $cg) }}" class="related-item">
            <div class="related-item-left">
                <div class="related-icon" style="background:#FEE2E2;color:#DC2626;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <div class="related-name">{{ $cg->employe->nom_complet ?? '—' }}</div>
                    <div class="related-sub">{{ $cg->type_conge }} · {{ \Carbon\Carbon::parse($cg->date_debut)->format('d/m') }} → {{ \Carbon\Carbon::parse($cg->date_fin)->format('d/m/Y') }}</div>
                </div>
            </div>
            <span class="badge by" style="font-size:10px;">En attente</span>
        </a>
        @empty
        <div class="empty-state" style="padding:20px;">Aucun congé en attente de validation.</div>
        @endforelse
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    {{-- Derniers employés ajoutés --}}
    <div class="card">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:6px;height:6px;border-radius:50%;background:var(--accent);"></div>
                <span class="card-title">Derniers employés ajoutés</span>
            </div>
            <a href="{{ route('personnel.index') }}" style="font-size:11px;color:var(--accent);text-decoration:none;">Voir tout →</a>
        </div>
        @forelse($dernierPersonnel as $emp)
        <a href="{{ route('personnel.show', $emp) }}" class="related-item">
            <div class="related-item-left">
                <div class="related-icon" style="background:var(--accent-light);color:var(--accent);">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <div class="related-name">{{ $emp->nom_complet }}</div>
                    <div class="related-sub">{{ $emp->fichePoste?->poste->nom ?? '—' }} · {{ $emp->fichePoste?->categorie->nom ?? '—' }}</div>
                </div>
            </div>
            <span class="badge bg" style="font-size:10px;">Actif</span>
        </a>
        @empty
        <div class="empty-state" style="padding:20px;">Aucun employé enregistré.</div>
        @endforelse
    </div>

    {{-- Activité récente --}}
    <div class="card">
        <div class="card-header">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:6px;height:6px;border-radius:50%;background:var(--success);"></div>
                <span class="card-title">Activité récente</span>
            </div>
        </div>
        @forelse($recentActivites as $log)
        <div style="display:flex;align-items:flex-start;gap:12px;padding:10px 20px;border-bottom:1px solid var(--border-light);">
            <div style="width:28px;height:28px;border-radius:50%;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;color:var(--text-muted);flex-shrink:0;">
                {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;color:var(--text-primary);font-weight:500;">{{ $log->description }}</div>
                <div style="font-size:11px;color:var(--text-muted);">{{ $log->module }} · {{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</div>
            </div>
        </div>
        @empty
        <div class="empty-state" style="padding:20px;">Aucune activité récente.</div>
        @endforelse
    </div>
</div>

</x-app-layout>