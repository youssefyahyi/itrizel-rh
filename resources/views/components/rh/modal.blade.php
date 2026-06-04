@props(['id', 'title', 'size' => 'md'])
@php $widths = ['sm' => '380px', 'md' => '480px', 'lg' => '640px', 'xl' => '800px']; @endphp
<div id="{{ $id }}" class="modal-overlay">
    <div class="modal" style="width:{{ $widths[$size] ?? '480px' }}">
        <div class="modal-header">
            <div class="modal-title">{{ $title }}</div>
            <button type="button" class="modal-close" onclick="closeModal('{{ $id }}')">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">{{ $slot }}</div>
        @isset($footer)
        <div class="modal-footer">{{ $footer }}</div>
        @endisset
    </div>
</div>