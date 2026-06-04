@props(['title', 'icon' => null, 'cols' => 2])
<div class="form-card">
    <div class="form-card-header">
        @if($icon)
        <div class="form-card-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        </div>
        @endif
        <div class="form-card-title">{{ $title }}</div>
    </div>
    <div class="form-card-body">
        <div class="form-grid-{{ $cols }}">
            {{ $slot }}
        </div>
    </div>
</div>