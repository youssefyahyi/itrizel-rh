<x-app-layout>
<div class="page-header">
    <div class="page-title">Outils</div>
</div>

<div class="content" style="padding:20px 24px;">
    <div class="param-grid">

        <a href="{{ route('outils.import.index') }}" class="param-card">
            <div class="param-card-icon">&#128229;</div>
            <div>
                <div class="param-card-title">Reprise de données</div>
                <div class="param-card-desc">Import employés et contrats depuis Excel</div>
            </div>
        </a>

    </div>
</div>

<style>
.param-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
.param-card { display:flex; align-items:center; gap:16px; padding:20px; background:var(--surface); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow-sm); text-decoration:none; color:var(--text-primary); transition:border-color .15s,box-shadow .15s; }
.param-card:hover { border-color:var(--accent); box-shadow:var(--shadow); }
.param-card-icon { font-size:28px; flex-shrink:0; }
.param-card-title { font-size:14px; font-weight:600; margin-bottom:4px; }
.param-card-desc { font-size:12px; color:var(--text-muted); }
</style>
</x-app-layout>
