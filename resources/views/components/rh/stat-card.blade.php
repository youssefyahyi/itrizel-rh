@props(['label', 'value', 'sub' => null, 'color' => 'var(--accent)', 'icon' => null])
<div class="stat-card" style="--st:{{ $color }}">
    @if($icon)
    <div class="stat-icon" style="background:{{ $color }}18;">
        <svg width="16" height="16" fill="none" stroke="{{ $color }}" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
        </svg>
    </div>
    @endif
    <div>
        <div class="stat-label">{{ $label }}</div>
        <div class="stat-value" style="color:{{ $color }}">{{ $value }}</div>
        @if($sub)<div class="stat-sub">{{ $sub }}</div>@endif
    </div>
</div>