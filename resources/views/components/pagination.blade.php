@props(['paginator', 'singular' => 'élément', 'plural' => null])
@php $plural = $plural ?? $singular.'s'; $p = $paginator->withQueryString(); @endphp
<div class="pagination">
    <div class="pag-info">
        {{ $p->firstItem() ?? 0 }} – {{ $p->lastItem() ?? 0 }} sur {{ $p->total() }} {{ $p->total() > 1 ? $plural : $singular }}
    </div>
    <div class="pag-btns">
        @if($p->onFirstPage())
            <button class="pag-btn" disabled>‹</button>
        @else
            <a href="{{ $p->previousPageUrl() }}" class="pag-btn">‹</a>
        @endif

        @foreach($p->getUrlRange(max(1, $p->currentPage()-2), min($p->lastPage(), $p->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="pag-btn {{ $p->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>
        @endforeach

        @if($p->hasMorePages())
            <a href="{{ $p->nextPageUrl() }}" class="pag-btn">›</a>
        @else
            <button class="pag-btn" disabled>›</button>
        @endif
    </div>
</div>
