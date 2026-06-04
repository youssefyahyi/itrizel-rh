@php $hasErrors = $errors->hasAny(['nom','prenom','cin','categorie','poste','date_embauche']); @endphp

<div id="rh-emp-modal-overlay"
     style="display:{{ $hasErrors ? 'flex' : 'none' }};position:fixed;inset:0;background:rgba(15,25,35,0.50);backdrop-filter:blur(3px);align-items:flex-start;justify-content:center;z-index:1000;padding:80px 20px 20px;"
     onclick="if(event.target===this) rhEmpCloseModal()">

    <div role="dialog" style="width:100%;max-width:560px;background:white;border-radius:12px;box-shadow:0 24px 60px rgba(0,0,0,0.20);overflow:hidden;">

        {{-- HEADER --}}
        <div style="padding:18px 22px 14px;border-bottom:1px solid var(--border-light);display:flex;align-items:flex-start;justify-content:space-between;gap:14px;">
            <div>
                <h2 style="font-size:15px;font-weight:600;color:var(--text-primary);margin:0 0 4px;">Nouvel employé</h2>
                <div style="font-size:12px;color:var(--text-muted);">Saisie rapide — vous pourrez tout compléter ensuite.</div>
            </div>
            <button type="button" onclick="rhEmpCloseModal()"
                    style="width:28px;height:28px;border-radius:6px;border:none;background:transparent;color:var(--text-muted);cursor:pointer;display:flex;align-items:center;justify-content:center;"
                    onmouseover="this.style.background='var(--bg)'" onmouseout="this.style.background='transparent'">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- FORM --}}
        <form method="POST" action="{{ route('personnel.store') }}">
            @csrf
            {{-- Champs requis avec valeurs par défaut pour champs non affichés --}}
            <input type="hidden" name="sexe"               value="M">
            <input type="hidden" name="situation_familiale" value="celibataire">
            <input type="hidden" name="nombre_enfants"      value="0">
            <input type="hidden" name="date_naissance"      value="1990-01-01">

            <div style="padding:18px 22px;display:grid;grid-template-columns:1fr 1fr;gap:14px;">

                <div class="form-group">
                    <label style="font-size:11px;font-weight:500;color:var(--text-secondary);margin-bottom:5px;">Nom <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="nom" value="{{ old('nom') }}"
                           style="height:32px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-family:inherit;width:100%;"
                           placeholder="Nom de famille" autofocus>
                    @error('nom')<div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label style="font-size:11px;font-weight:500;color:var(--text-secondary);margin-bottom:5px;">Prénom <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="prenom" value="{{ old('prenom') }}"
                           style="height:32px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-family:inherit;width:100%;"
                           placeholder="Prénom">
                    @error('prenom')<div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label style="font-size:11px;font-weight:500;color:var(--text-secondary);margin-bottom:5px;">CIN <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="cin" value="{{ old('cin') }}"
                           style="height:32px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-family:monospace;width:100%;"
                           placeholder="Ex: AB123456">
                    @error('cin')<div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label style="font-size:11px;font-weight:500;color:var(--text-secondary);margin-bottom:5px;">Date d'embauche <span style="color:var(--danger);">*</span></label>
                    <input type="date" name="date_embauche" value="{{ old('date_embauche', date('Y-m-d')) }}"
                           style="height:32px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-family:inherit;width:100%;">
                    @error('date_embauche')<div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label style="font-size:11px;font-weight:500;color:var(--text-secondary);margin-bottom:5px;">Catégorie <span style="color:var(--danger);">*</span></label>
                    <select name="categorie" style="height:32px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-family:inherit;width:100%;cursor:pointer;">
                        <option value="">— Choisir —</option>
                        @foreach(\App\Models\Employe::CATEGORIES as $v => $l)
                        <option value="{{ $v }}" {{ old('categorie') === $v ? 'selected' : '' }}>{{ $l }}</option>
                        @endforeach
                    </select>
                    @error('categorie')<div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label style="font-size:11px;font-weight:500;color:var(--text-secondary);margin-bottom:5px;">Poste / Fonction <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="poste" value="{{ old('poste') }}"
                           style="height:32px;padding:0 10px;border:1px solid var(--border);border-radius:var(--radius-sm);font-size:13px;font-family:inherit;width:100%;"
                           placeholder="Ex: Chauffeur livreur">
                    @error('poste')<div style="font-size:11px;color:var(--danger);margin-top:3px;">{{ $message }}</div>@enderror
                </div>

            </div>

            {{-- FOOTER --}}
            <div style="padding:12px 22px;border-top:1px solid var(--border-light);background:var(--surface-soft);display:flex;align-items:center;justify-content:space-between;gap:10px;">
                <div style="font-size:11px;color:var(--text-muted);line-height:1.5;">
                    <strong style="color:var(--text-secondary);">Après création</strong>, la fiche s'ouvre pour compléter les informations.
                </div>
                <div style="display:flex;gap:8px;">
                    <button type="button" onclick="rhEmpCloseModal()"
                            style="height:32px;padding:0 14px;border:1px solid var(--border);border-radius:var(--radius-sm);background:var(--surface);font-size:13px;cursor:pointer;font-family:inherit;color:var(--text-secondary);">
                        Annuler
                    </button>
                    <button type="submit" class="btn-new" style="height:32px;">
                        Créer
                        <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function rhEmpOpenModal() {
    document.getElementById('rh-emp-modal-overlay').style.display = 'flex';
    setTimeout(function(){ var f = document.querySelector('#rh-emp-modal-overlay input[name="nom"]'); if(f){f.focus();} }, 50);
}
function rhEmpCloseModal() {
    document.getElementById('rh-emp-modal-overlay').style.display = 'none';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var ov = document.getElementById('rh-emp-modal-overlay');
        if (ov && ov.style.display !== 'none') rhEmpCloseModal();
    }
});
</script>
