@php
$typeColors = [
    'direction'   => '#7C3AED',
    'departement' => '#2563EB',
    'service'     => '#0891B2',
    'equipe'      => '#059669',
    'autre'       => '#6B7280',
];
$typeIcons = [
    'direction'   => '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
    'departement' => '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>',
    'service'     => '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    'equipe'      => '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    'autre'       => '<svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>',
];
$color   = $typeColors[$node->type] ?? '#6B7280';
$icon    = $typeIcons[$node->type]  ?? $typeIcons['autre'];
$isSelected = isset($selected) && $selected && $selected->id === $node->id;
$hasChildren = $node->enfants->count() > 0;
@endphp

<div x-data="{ open: {{ $isSelected || ($hasChildren && $depth < 2) ? 'true' : 'false' }} }" class="org-node">
    <div class="org-node-row {{ $isSelected ? 'selected' : '' }}" style="padding-left: {{ $depth * 18 + 8 }}px;">
        {{-- Toggle --}}
        <button type="button" class="org-toggle" onclick="event.stopPropagation()"
            @if($hasChildren) @click="open = !open" @endif
            style="visibility: {{ $hasChildren ? 'visible' : 'hidden' }}; color:var(--text-muted);">
            <svg x-show="!open" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <svg x-show="open" width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>

        {{-- Icône type --}}
        <span class="org-type-icon" style="color:{{ $color }};">{!! $icon !!}</span>

        {{-- Nom (lien vers show) --}}
        <a href="{{ route('parametrage.organisation.show', $node) }}" class="org-node-label {{ $isSelected ? 'active' : '' }}">
            {{ $node->nom }}
            <span class="org-node-code">{{ $node->code }}</span>
        </a>

        {{-- Badge effectif --}}
        @if($node->employes_count > 0 || true)
        <span class="org-count" title="{{ $node->employes->count() }} direct(s)">
            {{ $node->employes->count() }}
        </span>
        @endif

        {{-- Action + sous-entité --}}
        <a href="{{ route('parametrage.organisation.index', ['action'=>'create','parent_id'=>$node->id]) }}"
           class="org-add-btn" title="Ajouter une sous-entité" onclick="event.stopPropagation()">
            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        </a>
    </div>

    {{-- Enfants récursifs --}}
    @if($hasChildren)
    <div x-show="open" x-transition.duration.100ms>
        @foreach($node->enfants as $child)
            @include('parametrage.organisation._node', ['node' => $child, 'depth' => $depth + 1, 'selected' => $selected ?? null])
        @endforeach
    </div>
    @endif
</div>
