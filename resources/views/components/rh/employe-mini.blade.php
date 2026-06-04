@props(['employe', 'link' => true])
@php $initials = strtoupper(substr($employe->nom,0,1).substr($employe->prenom,0,1)); @endphp
<div style="display:flex;align-items:center;gap:10px;">
    <div class="employe-avatar sm">{{ $initials }}</div>
    <div>
        @if($link)
        <a href="{{ route(''personnel.show'', $employe) }}" class="link" style="font-weight:500;">{{ $employe->nom_complet }}</a>
        @else
        <div style="font-weight:500;color:var(--text-primary);">{{ $employe->nom_complet }}</div>
        @endif
        <div class="muted">{{ $employe->matricule }}</div>
    </div>
</div>