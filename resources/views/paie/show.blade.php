<x-app-layout>
<div style="display:flex;align-items:center;gap:8px;padding:12px 24px;border-bottom:1px solid var(--border-light);background:var(--surface);">
    <a href="{{ route('paie.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">Paie</a>
    <span style="color:var(--text-muted);">›</span>
    <span style="font-size:13px;color:var(--text-primary);font-weight:500;">{{ $bulletin->employe->nom_complet }} — {{ $bulletin->periode_libelle }}</span>
</div>

@if(session('success'))<div class="alert alert-success" style="margin:16px 24px 0;">{{ session('success') }}</div>@endif

<div class="fiche-header" style="margin:16px 24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <div>
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;">
                <span class="badge {{ $bulletin->statut_badge }}"><span class="dot"></span>{{ $bulletin->statut_libelle }}</span>
            </div>
            <h1 style="font-size:18px;font-weight:600;color:var(--text-primary);margin-bottom:3px;">
                <a href="{{ route('personnel.show',$bulletin->employe) }}" style="color:inherit;text-decoration:none;">{{ $bulletin->employe->nom_complet }}</a>
                — {{ $bulletin->periode_libelle }}
            </h1>
            <div style="font-size:22px;font-weight:700;color:var(--accent);margin-top:6px;">{{ number_format($bulletin->net_a_payer, 2, ',', ' ') }} DH</div>
            <div style="font-size:12px;color:var(--text-muted);">Net à payer</div>
        </div>
        <div style="display:flex;gap:8px;">
            <a href="{{ route('paie.edit',$bulletin) }}" class="btn btn-outline btn-sm">Modifier</a>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;padding:0 24px 24px;align-items:start;">
    <div>
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('detail',this)">Détail du bulletin</button>
            <button class="tab-btn" onclick="switchTab('historique',this)">Historique</button>
        </div>
        <div class="tab-pane active" id="tab-detail">
            <div class="card">
                <div class="card-header"><span class="card-title">Éléments de rémunération</span></div>
                <div style="padding:0 20px;">
                    <div style="padding:12px 0;border-bottom:1px solid var(--border-light);">
                        <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Gains</div>
                        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;"><span style="color:var(--text-secondary);">Salaire de base</span><span class="amount">{{ number_format($bulletin->salaire_base,2,',',' ') }} DH</span></div>
                        @if($bulletin->total_primes > 0)<div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;"><span style="color:var(--text-secondary);">Primes</span><span class="amount">{{ number_format($bulletin->total_primes,2,',',' ') }} DH</span></div>@endif
                        <div style="display:flex;justify-content:space-between;padding:8px 0 5px;font-size:13px;font-weight:600;border-top:1px solid var(--border-light);margin-top:4px;"><span>Brut</span><span style="color:var(--accent);">{{ number_format($bulletin->salaire_base + $bulletin->total_primes,2,',',' ') }} DH</span></div>
                    </div>
                    <div style="padding:12px 0;border-bottom:1px solid var(--border-light);">
                        <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Cotisations salariales</div>
                        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;"><span style="color:var(--text-secondary);">IR mensuel</span><span class="amount" style="color:var(--danger);">- {{ number_format($bulletin->ir_mensuel,2,',',' ') }} DH</span></div>
                        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;"><span style="color:var(--text-secondary);">AMO salarié</span><span class="amount" style="color:var(--danger);">- {{ number_format($bulletin->amo_salarie,2,',',' ') }} DH</span></div>
                        <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;"><span style="color:var(--text-secondary);">CNSS salarié</span><span class="amount" style="color:var(--danger);">- {{ number_format($bulletin->cnss_salarie,2,',',' ') }} DH</span></div>
                        @if($bulletin->cimr_salarie > 0)<div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;"><span style="color:var(--text-secondary);">CIMR salarié</span><span class="amount" style="color:var(--danger);">- {{ number_format($bulletin->cimr_salarie,2,',',' ') }} DH</span></div>@endif
                        @if($bulletin->avances_deduites > 0)<div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px;"><span style="color:var(--text-secondary);">Avances déduites</span><span class="amount" style="color:var(--danger);">- {{ number_format($bulletin->avances_deduites,2,',',' ') }} DH</span></div>@endif
                    </div>
                    <div style="padding:12px 0;">
                        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;"><span style="color:var(--text-primary);">NET À PAYER</span><span style="color:var(--accent);">{{ number_format($bulletin->net_a_payer,2,',',' ') }} DH</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="tab-historique">
            <div class="card"><div class="empty-state">Aucun historique.</div></div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card" style="padding:16px 20px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Résumé</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--text-muted);">Période</span><span style="font-weight:500;">{{ $bulletin->periode_libelle }}</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--text-muted);">Salaire base</span><span>{{ number_format($bulletin->salaire_base,0,',',' ') }} DH</span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--text-muted);">Cotisations</span><span style="color:var(--danger);">{{ number_format($bulletin->total_cotisations,0,',',' ') }} DH</span></div>
                <div style="border-top:1px solid var(--border-light);padding-top:8px;display:flex;justify-content:space-between;font-size:14px;font-weight:700;"><span style="color:var(--text-primary);">Net</span><span style="color:var(--accent);">{{ number_format($bulletin->net_a_payer,0,',',' ') }} DH</span></div>
                @if($bulletin->date_paiement)<div style="display:flex;justify-content:space-between;font-size:12px;"><span style="color:var(--text-muted);">Payé le</span><span class="muted">{{ $bulletin->date_paiement->format('d/m/Y') }}</span></div>@endif
            </div>
        </div>
        <div class="card" style="padding:16px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Actions</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <a href="{{ route('paie.edit',$bulletin) }}" class="tb-btn" style="justify-content:flex-start;font-size:12px;">Modifier</a>
                <form method="POST" action="{{ route('paie.destroy',$bulletin) }}" onsubmit="return confirm('Supprimer ?')">@csrf @method('DELETE')
                    <button type="submit" class="tb-btn" style="width:100%;justify-content:flex-start;font-size:12px;color:var(--danger);">Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>
<style>
.tabs{display:flex;border-bottom:1px solid var(--border);margin-bottom:16px;}
.tab-btn{padding:10px 16px;border:none;background:none;font-size:13px;color:var(--text-muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;font-family:var(--font);}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent);font-weight:500;}
.tab-pane{display:none;}.tab-pane.active{display:block;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;}
.card-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);}
.card-title{font-size:13px;font-weight:600;color:var(--text-primary);}
.fiche-header{background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow-sm);padding:20px 24px;}
.empty-state{padding:24px;text-align:center;color:var(--text-muted);font-size:13px;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:var(--font);border:none;}
.btn-sm{padding:5px 10px;font-size:12px;}
.btn-outline{background:var(--surface);color:var(--text-secondary);border:1px solid var(--border);}
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;display:flex;align-items:center;gap:8px;}
.alert-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
</style>
</x-app-layout>
