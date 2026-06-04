@props(['label', 'value' => null, 'muted' => false])
@if($value !== null && $value !== '')
<div style="display:flex;align-items:flex-start;gap:8px;padding:7px 0;border-bottom:1px solid var(--border-light);">
    <span style="min-width:140px;font-size:12px;color:var(--text-muted);flex-shrink:0;">{{ $label }}</span>
    <span style="font-size:13px;color:{{ $muted ? 'var(--text-muted)' : 'var(--text-primary)' }};font-weight:450;">
        {{ $slot->isEmpty() ? $value : $slot }}
    </span>
</div>
@endif
