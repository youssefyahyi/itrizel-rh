@props(['statut', 'map' => []])
@php
$defaults = [
    'actif'         => ['bg', 'Actif'],
    'inactif'       => ['bgr', 'Inactif'],
    'suspendu'      => ['by', 'Suspendu'],
    'en_cours'      => ['bg', 'En cours'],
    'expire'        => ['br', 'Expiré'],
    'renouvele'     => ['bb', 'Renouvelé'],
    'resilie'       => ['br', 'Résilié'],
    'cloture'       => ['bgr', 'Clôturé'],
    'soumis'        => ['by', 'Soumis'],
    'en_validation' => ['bb', 'En validation'],
    'approuve'      => ['bg', 'Approuvé'],
    'rejete'        => ['br', 'Rejeté'],
    'annule'        => ['bgr', 'Annulé'],
    'brouillon'     => ['bgr', 'Brouillon'],
    'valide'        => ['bg', 'Validé'],
    'paye'          => ['bb', 'Payé'],
    'present'       => ['bg', 'Présent'],
    'absent'        => ['br', 'Absent'],
    'conge'         => ['by', 'Congé'],
    'ferie'         => ['bgr', 'Férié'],
    'mission'       => ['bb', 'Mission'],
];
$conf = array_merge($defaults, $map)[$statut] ?? ['bgr', ucfirst($statut)];
@endphp
<span class="badge {{ $conf[0] }}"><span class="dot"></span>{{ $conf[1] }}</span>