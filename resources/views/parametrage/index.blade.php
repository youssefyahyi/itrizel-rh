<x-app-layout>
<div class="page-header">
    <div class="page-title">Paramétrage</div>
</div>

<div class="content" style="padding:20px 24px;">
    <div class="param-grid">

        <a href="{{ route('parametrage.organisation.index') }}" class="param-card">
            <div class="param-card-icon">&#127962;</div>
            <div>
                <div class="param-card-title">Organisation</div>
                <div class="param-card-desc">Organigramme, unités et hiérarchie</div>
            </div>
        </a>

        <a href="{{ route('parametrage.emplois.index') }}" class="param-card">
            <div class="param-card-icon">&#128193;</div>
            <div>
                <div class="param-card-title">Référentiel des emplois</div>
                <div class="param-card-desc">Catégories, fonctions et fiches de poste</div>
            </div>
        </a>

        <a href="{{ route('parametrage.rh') }}" class="param-card">
            <div class="param-card-icon">&#9881;&#65039;</div>
            <div>
                <div class="param-card-title">Paramètres RH</div>
                <div class="param-card-desc">Congés, quotités de travail, durée légale</div>
            </div>
        </a>

        <a href="{{ route('parametrage.paie') }}" class="param-card">
            <div class="param-card-icon">&#128176;</div>
            <div>
                <div class="param-card-title">Paramètres paie</div>
                <div class="param-card-desc">Société, taux CNSS/AMO/CIMR, barème IR</div>
            </div>
        </a>

    </div>
</div>

<style>
.param-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 16px;
}
.param-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    text-decoration: none;
    color: var(--text-primary);
    transition: border-color .15s, box-shadow .15s;
}
.param-card:hover {
    border-color: var(--accent);
    box-shadow: var(--shadow);
}
.param-card-disabled {
    opacity: .5;
    cursor: not-allowed;
    pointer-events: none;
}
.param-card-icon {
    font-size: 28px;
    flex-shrink: 0;
}
.param-card-title {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 4px;
}
.param-card-desc {
    font-size: 12px;
    color: var(--text-muted);
}
</style>
</x-app-layout>
