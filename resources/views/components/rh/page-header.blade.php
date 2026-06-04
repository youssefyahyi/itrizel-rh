@props(['title', 'breadcrumbs' => [], 'back' => null])
<div class="rh-page-header">
    <div>
        @if(count($breadcrumbs))
        <div class="breadcrumb" style="margin-bottom:4px;">
            @foreach($breadcrumbs as $label => $url)
                @if($url)<a href="{{ $url }}">{{ $label }}</a><span class="sep">›</span>
                @else<span class="current">{{ $label }}</span>
                @endif
            @endforeach
        </div>
        @endif
        <div class="rh-page-header-title">{{ $title }}</div>
    </div>
    <div class="rh-page-header-actions">
        {{ $slot }}
    </div>
</div>