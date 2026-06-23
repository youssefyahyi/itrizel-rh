@php
$steps = [
    1 => 'Chargement',
    2 => 'Vérification',
    3 => 'Résultat',
];
@endphp
<div class="wizard-steps">
    @foreach($steps as $n => $label)
    <div class="wizard-step {{ $active === $n ? 'active' : ($active > $n ? 'done' : '') }}">
        <div class="wizard-step-num">{{ $active > $n ? '✓' : $n }}</div>
        <div class="wizard-step-label">{{ $label }}</div>
    </div>
    @if(!$loop->last)
    <div class="wizard-step-sep"></div>
    @endif
    @endforeach
</div>

<style>
.wizard-steps { display:flex; align-items:center; margin-bottom:24px; }
.wizard-step { display:flex; align-items:center; gap:8px; }
.wizard-step-num {
    width:28px; height:28px; border-radius:50%; display:flex; align-items:center;
    justify-content:center; font-size:12px; font-weight:700;
    background:var(--surface-soft); color:var(--text-muted); border:2px solid var(--border);
}
.wizard-step.active .wizard-step-num { background:var(--accent); color:#fff; border-color:var(--accent); }
.wizard-step.done   .wizard-step-num { background:#22C55E; color:#fff; border-color:#22C55E; }
.wizard-step-label { font-size:12px; font-weight:500; color:var(--text-muted); }
.wizard-step.active .wizard-step-label { color:var(--text-primary); font-weight:600; }
.wizard-step.done   .wizard-step-label { color:#16A34A; }
.wizard-step-sep { flex:1; height:2px; background:var(--border); margin:0 12px; min-width:40px; }
</style>
