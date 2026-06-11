@props(['title', 'breadcrumbs' => [], 'back' => null])
<div class="rh-page-header">
    <div style="min-width:0;flex:1;">
        @if(count($breadcrumbs))
        <div class="breadcrumb" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            @foreach($breadcrumbs as $label => $url)
                @if($url)<a href="{{ $url }}">{{ $label }}</a><span class="sep">›</span>
                @else<span class="current">{{ $label }}</span>
                @endif
            @endforeach
        </div>
        @else
        <div class="rh-page-header-title">{{ $title }}</div>
        @endif
    </div>
    @if($slot->isNotEmpty())
    <div class="rh-page-header-actions">
        {{ $slot }}
    </div>
    @endif
</div>

<style>
.rh-page-header{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 24px;background:var(--surface);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:40;}
.rh-page-header-title{font-size:15px;font-weight:600;color:var(--text-primary);}
.rh-page-header-actions{display:flex;align-items:center;gap:6px;flex-shrink:0;}
.breadcrumb{display:flex;align-items:center;gap:4px;font-size:13px;}
.breadcrumb a{color:var(--text-secondary);text-decoration:none;font-weight:500;}
.breadcrumb a:hover{color:var(--accent);}
.breadcrumb .sep{color:var(--text-muted);font-size:11px;}
.breadcrumb .current{color:var(--text-primary);font-weight:600;}
</style>
