<x-app-layout>
@php $libelle = $entite === 'employes' ? 'Employés' : 'Contrats'; @endphp
<x-rh.page-header
    :breadcrumbs="['Outils' => route('outils.index'), 'Reprise de données' => route('outils.import.index'), $libelle => null]">
</x-rh.page-header>

<div class="content" style="padding:20px 24px;max-width:640px;">

    @include('outils.import._steps', ['active' => 1])

    {{-- Étape 1 : télécharger le modèle --}}
    <div class="info-card" style="margin-bottom:20px;">
        <div class="info-card-title" style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
            ① Télécharger le modèle Excel
        </div>
        <div style="padding:16px 20px;display:flex;align-items:center;gap:16px;">
            <div style="font-size:13px;color:var(--text-secondary);flex:1;line-height:1.6;">
                Téléchargez le modèle, remplissez-le à partir de la ligne 4 et enregistrez-le.
                Ne modifiez pas les 3 premières lignes.
            </div>
            <a href="{{ route('outils.import.modele', $entite) }}" class="btn btn-secondary btn-sm" style="white-space:nowrap;">
                &#11015; Modèle {{ $libelle }}
            </a>
        </div>
    </div>

    {{-- Étape 2 : uploader le fichier --}}
    <div class="info-card">
        <div class="info-card-title" style="padding:14px 20px;border-bottom:1px solid var(--border-light);">
            ② Uploader le fichier rempli
        </div>
        <form method="POST" action="{{ route('outils.import.upload', $entite) }}"
              enctype="multipart/form-data" style="padding:20px;">
            @csrf

            @if($errors->any())
            <div style="margin-bottom:16px;padding:10px 14px;background:#FEF2F2;border:1px solid #FECACA;border-radius:8px;font-size:13px;color:#B91C1C;">
                {{ $errors->first() }}
            </div>
            @endif

            <div class="upload-zone" id="upload-zone">
                <input type="file" name="fichier" id="fichier" accept=".xlsx,.xls"
                       style="position:absolute;inset:0;opacity:0;cursor:pointer;"
                       onchange="afficherFichier(this)">
                <div id="upload-label">
                    <div style="font-size:32px;margin-bottom:10px;">&#128193;</div>
                    <div style="font-size:14px;font-weight:500;color:var(--text-primary);margin-bottom:4px;">
                        Glissez votre fichier ici ou cliquez pour parcourir
                    </div>
                    <div style="font-size:12px;color:var(--text-muted);">Excel (.xlsx, .xls) — max 5 Mo</div>
                </div>
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:16px;gap:10px;">
                <a href="{{ route('outils.import.index') }}" class="btn btn-secondary btn-sm">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    Analyser le fichier →
                </button>
            </div>
        </form>
    </div>

</div>

<style>
.upload-zone {
    position:relative; border:2px dashed var(--border); border-radius:10px;
    padding:40px 20px; text-align:center; cursor:pointer;
    transition:border-color .15s, background .15s;
}
.upload-zone:hover { border-color:var(--accent); background:var(--accent-soft,rgba(59,130,246,.04)); }
.upload-zone.has-file { border-color:#22C55E; background:#F0FDF4; }
</style>

<script>
function afficherFichier(input) {
    const zone  = document.getElementById('upload-zone');
    const label = document.getElementById('upload-label');
    if (input.files && input.files[0]) {
        const f = input.files[0];
        zone.classList.add('has-file');
        label.innerHTML = `<div style="font-size:28px;margin-bottom:8px;">&#9989;</div>
            <div style="font-size:14px;font-weight:500;color:#16A34A;">${f.name}</div>
            <div style="font-size:12px;color:#6B7280;margin-top:4px;">${(f.size/1024).toFixed(0)} Ko</div>`;
    }
}
</script>
</x-app-layout>
