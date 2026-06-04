@props(['message' => 'Aucun résultat', 'icon' => null, 'action' => null, 'actionLabel' => null, 'actionRoute' => null])
<div class="empty-state" style="padding:48px 24px;">
    @if($icon)
    <svg width="40" height="40" fill="none" stroke="var(--border)" viewBox="0 0 24 24" style="margin:0 auto 16px;display:block;">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="{{ $icon }}"/>
    </svg>
    @endif
    <div style="font-size:14px;font-weight:500;color:var(--text-secondary);margin-bottom:6px;">{{ $message }}</div>
    @if($actionRoute)
    <a href="{{ $actionRoute }}" class="btn btn-sm btn-primary" style="margin-top:12px;display:inline-flex;">
        {{ $actionLabel ?? 'Ajouter' }}
    </a>
    @endif
    {{ $slot }}
</div>