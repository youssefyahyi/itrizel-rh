<x-app-layout>

{{-- BREADCRUMB --}}
<div style="display:flex;align-items:center;gap:8px;padding:12px 24px;border-bottom:1px solid var(--border-light);background:var(--surface);">
    <a href="{{ route('paie.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">Paie</a>
    <span style="color:var(--text-muted);">›</span>
    <a href="{{ route('personnel.show', $bulletin->employe) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">{{ $bulletin->employe->nom_complet }}</a>
    <span style="color:var(--text-muted);">›</span>
    <span style="font-size:13px;color:var(--text-primary);font-weight:500;">{{ $bulletin->periode_libelle }}</span>
</div>

@if(session('success'))
<div class="alert alert-success" style="margin:16px 24px 0;">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- FICHE HEADER --}}
<div style="margin:16px 24px;background:var(--surface);border-radius:var(--radius);border:1px solid var(--border);box-shadow:var(--shadow-sm);padding:20px 24px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;">
        <div style="display:flex;align-items:flex-start;gap:16px;flex:1;">
            {{-- Avatar initiales --}}
            @php $emp = $bulletin->employe; @endphp
            <div style="width:52px;height:52px;border-radius:50%;background:var(--accent);color:#fff;font-size:17px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                {{ strtoupper(substr($emp->prenom,0,1).substr($emp->nom,0,1)) }}
            </div>
            <div style="flex:1;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap;">
                    <span class="badge {{ $bulletin->statut_badge }}"><span class="dot"></span>{{ $bulletin->statut_libelle }}</span>
                    @if($emp->fichePoste)<span class="badge bb">{{ $emp->fichePoste->categorie->nom ?? '—' }}</span>@endif
                </div>
                <h1 style="font-size:20px;font-weight:600;color:var(--text-primary);margin-bottom:3px;">
                    <a href="{{ route('personnel.show', $emp) }}" style="color:inherit;text-decoration:none;">{{ $emp->nom_complet }}</a>
                </h1>
                <div style="font-size:13px;color:var(--text-muted);">
                    {{ $emp->fichePoste?->poste->nom ?? '—' }}
                    &nbsp;·&nbsp;
                    <span class="mono" style="font-size:12px;">{{ $emp->matricule }}</span>
                    &nbsp;·&nbsp;
                    Bulletin <strong style="color:var(--text-primary);">{{ $bulletin->periode_libelle }}</strong>
                </div>
                <div style="font-size:22px;font-weight:700;color:var(--accent);margin-top:8px;">
                    {{ number_format($bulletin->net_a_payer, 2, ',', ' ') }} DH
                    <span style="font-size:13px;font-weight:400;color:var(--text-muted);">net à payer</span>
                </div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <a href="{{ route('paie.print', $bulletin) }}" target="_blank" class="btn btn-outline btn-sm" style="display:inline-flex;align-items:center;gap:5px;">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimer
            </a>
            <a href="{{ route('paie.edit', $bulletin) }}" class="btn btn-primary btn-sm">Modifier</a>
        </div>
    </div>
</div>

{{-- LAYOUT 2 COLONNES --}}
<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;padding:0 24px 24px;align-items:start;">

    {{-- COLONNE PRINCIPALE --}}
    <div>
        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('detail',this)">
                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 11h.01M12 11h.01M15 11h.01M12 7h.01M3 5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5z"/></svg>
                Détail du bulletin
            </button>
            <button class="tab-btn" onclick="switchTab('historique',this)">
                Historique paie
                <span class="tab-count">{{ $emp->bulletinsPaie->count() }}</span>
            </button>
        </div>

        {{-- ONGLET DÉTAIL --}}
        <div class="tab-pane active" id="tab-detail">
            <div class="card">
                <div class="card-header"><span class="card-title">Éléments de rémunération</span></div>
                <div style="padding:0 20px 16px;">

                    {{-- GAINS --}}
                    <div style="padding:14px 0 10px;">
                        <div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">Gains</div>
                        <div class="remun-row">
                            <span class="remun-label">Salaire de base</span>
                            <span class="remun-amount">{{ number_format($bulletin->salaire_base, 2, ',', ' ') }} DH</span>
                        </div>
                        @if($bulletin->total_primes > 0)
                        <div class="remun-row">
                            <span class="remun-label">Primes et indemnités</span>
                            <span class="remun-amount">{{ number_format($bulletin->total_primes, 2, ',', ' ') }} DH</span>
                        </div>
                        @endif
                        <div class="remun-row remun-subtotal">
                            <span>Salaire brut</span>
                            <span>{{ number_format((float)$bulletin->salaire_base + (float)$bulletin->total_primes, 2, ',', ' ') }} DH</span>
                        </div>
                    </div>

                    <div style="height:1px;background:var(--border-light);"></div>

                    {{-- RETENUES --}}
                    <div style="padding:14px 0 10px;">
                        <div style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px;">Cotisations salariales</div>
                        <div class="remun-row">
                            <span class="remun-label">CNSS — Part salariale</span>
                            <span class="remun-amount" style="color:var(--danger);">− {{ number_format($bulletin->cnss_salarie, 2, ',', ' ') }} DH</span>
                        </div>
                        <div class="remun-row">
                            <span class="remun-label">AMO — Part salariale</span>
                            <span class="remun-amount" style="color:var(--danger);">− {{ number_format($bulletin->amo_salarie, 2, ',', ' ') }} DH</span>
                        </div>
                        @if($bulletin->cimr_salarie > 0)
                        <div class="remun-row">
                            <span class="remun-label">CIMR — Part salariale</span>
                            <span class="remun-amount" style="color:var(--danger);">− {{ number_format($bulletin->cimr_salarie, 2, ',', ' ') }} DH</span>
                        </div>
                        @endif
                        <div class="remun-row">
                            <span class="remun-label">Impôt sur le Revenu (IR)</span>
                            <span class="remun-amount" style="color:var(--danger);">− {{ number_format($bulletin->ir_mensuel, 2, ',', ' ') }} DH</span>
                        </div>
                        @if($bulletin->avances_deduites > 0)
                        <div class="remun-row">
                            <span class="remun-label">Avances déduites</span>
                            <span class="remun-amount" style="color:var(--danger);">− {{ number_format($bulletin->avances_deduites, 2, ',', ' ') }} DH</span>
                        </div>
                        @endif
                        <div class="remun-row remun-subtotal" style="color:var(--danger);">
                            <span style="color:var(--text-primary);">Total retenues</span>
                            <span>{{ number_format($bulletin->total_retenues, 2, ',', ' ') }} DH</span>
                        </div>
                    </div>

                    <div style="height:1px;background:var(--border);"></div>

                    {{-- NET --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:14px 0 4px;">
                        <span style="font-size:15px;font-weight:700;color:var(--text-primary);">NET À PAYER</span>
                        <span style="font-size:20px;font-weight:700;color:var(--accent);">{{ number_format($bulletin->net_a_payer, 2, ',', ' ') }} DH</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ONGLET HISTORIQUE --}}
        <div class="tab-pane" id="tab-historique">
            <div class="card">
                <div class="card-header"><span class="card-title">Historique paie — {{ $emp->nom_complet }}</span></div>
                @if($emp->bulletinsPaie->count())
                <div class="table-wrap" style="margin:0;border:none;box-shadow:none;">
                <table>
                    <thead><tr>
                        <th><span class="th-in">Période</span></th>
                        <th class="tr"><span class="th-in">Brut</span></th>
                        <th class="tr"><span class="th-in">Cotisations</span></th>
                        <th class="tr"><span class="th-in">Net</span></th>
                        <th class="tc"><span class="th-in">Statut</span></th>
                        <th class="tc"></th>
                    </tr></thead>
                    <tbody>
                    @foreach($emp->bulletinsPaie->sortByDesc(fn($b) => $b->periode_annee * 100 + $b->periode_mois) as $bh)
                    <tr style="{{ $bh->id === $bulletin->id ? 'background:var(--accent-light);' : '' }}">
                        <td style="font-weight:{{ $bh->id === $bulletin->id ? '600' : '400' }};">{{ $bh->periode_libelle }}</td>
                        <td class="tr muted">{{ number_format((float)$bh->salaire_base + (float)$bh->total_primes, 0, ',', ' ') }} DH</td>
                        <td class="tr muted">{{ number_format($bh->total_cotisations, 0, ',', ' ') }} DH</td>
                        <td class="tr"><span class="amount" style="color:var(--accent);">{{ number_format($bh->net_a_payer, 0, ',', ' ') }} DH</span></td>
                        <td class="tc"><span class="badge {{ $bh->statut_badge }}"><span class="dot"></span>{{ $bh->statut_libelle }}</span></td>
                        <td class="tc">
                            <a href="{{ route('paie.show', $bh) }}" class="tb-btn" title="Voir">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                </div>
                @else
                <div class="empty-state">Aucun bulletin de paie.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- COLONNE DROITE --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Résumé --}}
        <div class="card" style="padding:16px 20px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">Résumé</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div class="side-row"><span class="side-label">Période</span><span class="side-val">{{ $bulletin->periode_libelle }}</span></div>
                <div class="side-row"><span class="side-label">Salaire base</span><span class="side-val">{{ number_format($bulletin->salaire_base, 0, ',', ' ') }} DH</span></div>
                @if($bulletin->total_primes > 0)
                <div class="side-row"><span class="side-label">Primes</span><span class="side-val">{{ number_format($bulletin->total_primes, 0, ',', ' ') }} DH</span></div>
                @endif
                <div class="side-row"><span class="side-label">Cotisations</span><span class="side-val" style="color:var(--danger);">{{ number_format($bulletin->total_cotisations, 0, ',', ' ') }} DH</span></div>
                <div style="height:1px;background:var(--border-light);"></div>
                <div class="side-row">
                    <span style="font-weight:700;color:var(--text-primary);font-size:13px;">Net à payer</span>
                    <span style="font-weight:700;color:var(--accent);font-size:14px;">{{ number_format($bulletin->net_a_payer, 0, ',', ' ') }} DH</span>
                </div>
                @if($bulletin->date_paiement)
                <div class="side-row"><span class="side-label">Payé le</span><span class="side-val">{{ $bulletin->date_paiement->format('d/m/Y') }}</span></div>
                @endif
            </div>
        </div>

        {{-- Employé --}}
        <div class="card" style="padding:16px 20px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">Employé</div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <div class="side-row"><span class="side-label">Nom</span><a href="{{ route('personnel.show', $emp) }}" class="link" style="font-size:13px;font-weight:500;">{{ $emp->nom_complet }}</a></div>
                <div class="side-row"><span class="side-label">Matricule</span><span class="side-val mono">{{ $emp->matricule }}</span></div>
                <div class="side-row"><span class="side-label">Poste</span><span class="side-val">{{ $emp->fichePoste?->poste->nom ?? '—' }}</span></div>
                <div class="side-row"><span class="side-label">N° CNSS</span><span class="side-val mono">{{ $emp->numero_cnss ?? '—' }}</span></div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card" style="padding:16px 20px;">
            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px;">Actions</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <a href="{{ route('paie.print', $bulletin) }}" target="_blank" class="action-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Imprimer le bulletin
                </a>
                <a href="{{ route('paie.edit', $bulletin) }}" class="action-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Modifier
                </a>
                <form method="POST" action="{{ route('paie.destroy', $bulletin) }}" onsubmit="return confirm('Supprimer ce bulletin ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn" style="width:100%;color:var(--danger);border-color:transparent;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<style>
.alert{padding:10px 14px;border-radius:var(--radius-sm);font-size:13px;display:flex;align-items:center;gap:8px;}
.alert-success{background:var(--success-light);color:var(--success);border:1px solid #A7F3D0;}
.btn{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:var(--radius-sm);font-size:13px;font-weight:500;cursor:pointer;font-family:var(--font);border:1px solid transparent;text-decoration:none;}
.btn-sm{padding:5px 10px;font-size:12px;}
.btn-primary{background:var(--accent);color:#fff;border-color:var(--accent);}
.btn-outline{background:var(--surface);color:var(--text-secondary);border-color:var(--border);}
.tabs{display:flex;border-bottom:1px solid var(--border);margin-bottom:16px;}
.tab-btn{padding:10px 16px;border:none;background:none;font-size:13px;color:var(--text-muted);cursor:pointer;border-bottom:2px solid transparent;margin-bottom:-1px;font-family:var(--font);display:flex;align-items:center;gap:6px;}
.tab-btn.active{color:var(--accent);border-bottom-color:var(--accent);font-weight:500;}
.tab-count{background:var(--border-light);color:var(--text-muted);font-size:10px;padding:1px 6px;border-radius:10px;font-weight:600;}
.tab-pane{display:none;}.tab-pane.active{display:block;}
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow-sm);overflow:hidden;}
.card-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-bottom:1px solid var(--border-light);background:var(--surface-soft);}
.card-title{font-size:13px;font-weight:600;color:var(--text-primary);}
.empty-state{padding:32px;text-align:center;color:var(--text-muted);font-size:13px;}
.remun-row{display:flex;justify-content:space-between;align-items:center;padding:5px 0;font-size:13px;}
.remun-label{color:var(--text-secondary);}
.remun-amount{font-weight:500;color:var(--text-primary);}
.remun-subtotal{font-weight:700;font-size:13px;border-top:1px solid var(--border-light);margin-top:4px;padding-top:8px;}
.side-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;}
.side-label{color:var(--text-muted);font-size:12px;}
.side-val{color:var(--text-primary);font-weight:500;font-size:13px;}
.action-btn{display:flex;align-items:center;gap:8px;padding:7px 10px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);color:var(--text-secondary);font-size:12px;font-weight:500;cursor:pointer;font-family:var(--font);text-decoration:none;transition:background .12s;}
.action-btn:hover{background:var(--surface-soft);}
</style>

</x-app-layout>
