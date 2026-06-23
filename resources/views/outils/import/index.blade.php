<x-app-layout>
<x-rh.page-header
    :breadcrumbs="['Outils' => route('outils.index'), 'Reprise de données' => null]">
</x-rh.page-header>

<div class="content" style="padding:20px 24px;max-width:640px;">

    <p style="font-size:13px;color:var(--text-secondary);margin:0 0 24px;">
        Importez vos données existantes via un fichier Excel. Le wizard vérifie chaque ligne
        avant l'import et vous permet de corriger les erreurs éventuelles.
    </p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">

        <a href="{{ route('outils.import.charger', 'employes') }}" class="import-choice-card">
            <div style="font-size:36px;margin-bottom:12px;">&#128101;</div>
            <div style="font-size:15px;font-weight:600;margin-bottom:6px;">Employés</div>
            <div style="font-size:12px;color:var(--text-muted);line-height:1.5;">
                Nom, prénom, CIN, date d'embauche, unité organisationnelle…
            </div>
            <div style="margin-top:16px;" class="btn btn-primary btn-sm">Commencer</div>
        </a>

        <a href="{{ route('outils.import.charger', 'contrats') }}" class="import-choice-card">
            <div style="font-size:36px;margin-bottom:12px;">&#128196;</div>
            <div style="font-size:15px;font-weight:600;margin-bottom:6px;">Contrats</div>
            <div style="font-size:12px;color:var(--text-muted);line-height:1.5;">
                Type, dates, salaire de base. Les employés doivent être importés au préalable.
            </div>
            <div style="margin-top:16px;" class="btn btn-primary btn-sm">Commencer</div>
        </a>

    </div>

</div>

<style>
.import-choice-card {
    display:flex; flex-direction:column; align-items:center; text-align:center;
    padding:28px 20px; background:var(--surface); border:2px solid var(--border);
    border-radius:var(--radius); text-decoration:none; color:var(--text-primary);
    transition:border-color .15s, box-shadow .15s;
}
.import-choice-card:hover { border-color:var(--accent); box-shadow:var(--shadow); }
</style>
</x-app-layout>
