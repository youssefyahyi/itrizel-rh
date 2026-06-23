<x-app-layout>

<x-rh.page-header
    :breadcrumbs="['Paramétrage' => route('parametrage.index'), 'Codification' => null]">
</x-rh.page-header>

<div style="padding:20px 24px;display:flex;flex-direction:column;gap:20px;max-width:760px;">

@if(session('success'))
<x-rh.alert type="success" message="{{ session('success') }}" />
@endif

<p style="font-size:13px;color:var(--text-secondary);margin:0;">
    Configurez le format des identifiants générés automatiquement par l'application.
    Les modifications s'appliquent aux <strong>prochains</strong> codes générés uniquement.
</p>

@foreach($configs as $config)
<div class="info-card" style="padding:0;overflow:hidden;">

    {{-- En-tête --}}
    <div style="padding:14px 20px;border-bottom:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;">
        <div>
            <div style="font-size:13px;font-weight:600;color:var(--text-primary);">{{ $config->libelle }}</div>
            <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
                Prochain : <code style="background:var(--surface-soft);padding:1px 6px;border-radius:4px;font-size:12px;">{{ $config->apercu() }}</code>
                &nbsp;·&nbsp; Compteur actuel : {{ $config->compteur_courant }}
            </div>
        </div>
        <form method="POST" action="{{ route('parametrage.codification.reinitialiser', $config) }}"
              onsubmit="return confirm('Remettre le compteur à 0 ? Les prochains codes repartiront de 1.')">
            @csrf @method('PATCH')
            <button type="submit" class="tb-btn" style="font-size:11px;color:var(--text-muted);">
                Remettre à zéro
            </button>
        </form>
    </div>

    {{-- Formulaire --}}
    <form method="POST" action="{{ route('parametrage.codification.update', $config) }}"
          id="form-cod-{{ $config->entite }}"
          onchange="apercuCod('{{ $config->entite }}')">
        @csrf @method('PUT')

        <div style="padding:16px 20px;display:grid;grid-template-columns:repeat(3,1fr) auto;gap:12px;align-items:end;">

            <div>
                <label class="cod-label">Préfixe</label>
                <input type="text" name="prefixe" value="{{ $config->prefixe }}"
                       class="cod-input" placeholder="Ex : EMP" maxlength="20">
            </div>

            <div>
                <label class="cod-label">Séparateur</label>
                <select name="separateur" class="cod-input">
                    @foreach(['-' => 'Tiret  (  -  )', '/' => 'Barre  (  /  )', '.' => 'Point  (  .  )', '' => 'Aucun'] as $v => $l)
                    <option value="{{ $v }}" {{ $config->separateur === $v ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="cod-label">Chiffres séquence</label>
                <select name="nb_chiffres" class="cod-input">
                    @foreach([2,3,4,5,6] as $n)
                    <option value="{{ $n }}" {{ $config->nb_chiffres === $n ? 'selected' : '' }}>
                        {{ $n }} chiffres ({{ str_pad('1', $n, '0', STR_PAD_LEFT) }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;gap:8px;align-items:center;padding-bottom:2px;">
                <button type="submit" class="btn btn-primary btn-sm">Enregistrer</button>
            </div>
        </div>

        <div style="padding:0 20px 16px;display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">

            <div style="display:flex;align-items:center;gap:8px;">
                <label class="cod-label" style="margin:0;">Inclure l'année</label>
                <input type="hidden" name="inclure_annee" value="0">
                <input type="checkbox" name="inclure_annee" value="1" {{ $config->inclure_annee ? 'checked' : '' }}
                       style="width:16px;height:16px;cursor:pointer;">
            </div>

            <div>
                <label class="cod-label">Format année</label>
                <select name="format_annee" class="cod-input" {{ !$config->inclure_annee ? 'disabled' : '' }}>
                    <option value="YYYY" {{ $config->format_annee === 'YYYY' ? 'selected' : '' }}>4 chiffres (2026)</option>
                    <option value="YY"   {{ $config->format_annee === 'YY'   ? 'selected' : '' }}>2 chiffres (26)</option>
                </select>
            </div>

            <div style="display:flex;align-items:center;gap:8px;">
                <label class="cod-label" style="margin:0;">Remise à zéro annuelle</label>
                <input type="hidden" name="reset_annuel" value="0">
                <input type="checkbox" name="reset_annuel" value="1" {{ $config->reset_annuel ? 'checked' : '' }}
                       style="width:16px;height:16px;cursor:pointer;">
            </div>
        </div>

        {{-- Aperçu live --}}
        <div style="padding:10px 20px;background:var(--surface-soft);border-top:1px solid var(--border-light);display:flex;align-items:center;gap:10px;">
            <span style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Aperçu</span>
            <code id="apercu-{{ $config->entite }}" style="font-size:13px;font-weight:600;color:var(--accent);background:var(--accent-light);padding:3px 10px;border-radius:6px;">
                {{ $config->apercu() }}
            </code>
        </div>

    </form>
</div>
@endforeach

</div>

<script>
function apercuCod(entite) {
    const form = document.getElementById('form-cod-' + entite);
    const data = new FormData(form);
    data.append('entite', entite);

    fetch('{{ route('parametrage.codification.apercu') }}?' + new URLSearchParams({
        entite:        data.get('prefixe') !== null ? entite : entite,
        prefixe:       data.get('prefixe') || '',
        separateur:    data.get('separateur') || '-',
        inclure_annee: data.getAll('inclure_annee').includes('1') ? '1' : '0',
        format_annee:  data.get('format_annee') || 'YYYY',
        nb_chiffres:   data.get('nb_chiffres') || '4',
    }))
    .then(r => r.json())
    .then(d => {
        const el = document.getElementById('apercu-' + entite);
        if (el) el.textContent = d.apercu;
    });
}
</script>

<style>
.cod-label { display:block; font-size:11px; font-weight:500; color:var(--text-secondary); margin-bottom:5px; }
.cod-input { width:100%; padding:8px 10px; border:1px solid var(--border); border-radius:8px; font-family:inherit; font-size:13px; color:var(--text-primary); background:#fff; outline:none; }
.cod-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-soft); }
.cod-input:disabled { background:var(--surface-soft); color:var(--text-muted); }
</style>

</x-app-layout>
