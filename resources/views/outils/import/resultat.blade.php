<x-app-layout>
@php $libelle = $entite === 'employes' ? 'Employés' : 'Contrats'; @endphp
<x-rh.page-header
    :breadcrumbs="['Outils' => route('outils.index'), 'Reprise de données' => route('outils.import.index'), $libelle => null]">
</x-rh.page-header>

<div class="content" style="padding:20px 24px;max-width:560px;">

    @include('outils.import._steps', ['active' => 3])

    <div class="info-card" style="text-align:center;padding:40px 32px;">

        @if($importes > 0 && empty($erreurs))
        <div style="font-size:52px;margin-bottom:16px;">&#127881;</div>
        <div style="font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:8px;">
            Import terminé avec succès
        </div>
        <div style="font-size:14px;color:var(--text-secondary);margin-bottom:28px;">
            <strong style="color:#16A34A;">{{ $importes }}</strong>
            {{ $libelle === 'Employés' ? 'employé(s) importé(s)' : 'contrat(s) importé(s)' }}
        </div>

        @elseif($importes > 0)
        <div style="font-size:52px;margin-bottom:16px;">&#9888;&#65039;</div>
        <div style="font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:8px;">
            Import partiel
        </div>
        <div style="font-size:14px;color:var(--text-secondary);margin-bottom:16px;">
            <strong>{{ $importes }}</strong> importé(s) avec succès, {{ count($erreurs) }} erreur(s) lors de l'insertion.
        </div>
        <div style="text-align:left;margin-bottom:28px;">
            @foreach($erreurs as $err)
            <div style="padding:6px 10px;background:#FEF2F2;border-radius:6px;font-size:12px;color:#B91C1C;margin-bottom:4px;">{{ $err }}</div>
            @endforeach
        </div>

        @else
        <div style="font-size:52px;margin-bottom:16px;">&#128533;</div>
        <div style="font-size:18px;font-weight:700;color:var(--text-primary);margin-bottom:8px;">
            Aucune ligne importée
        </div>
        <div style="font-size:14px;color:var(--text-secondary);margin-bottom:28px;">
            Vérifiez les erreurs et recommencez.
        </div>
        @endif

        <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
            @if($entite === 'employes')
            <a href="{{ route('personnel.index') }}" class="btn btn-primary">Voir les employés</a>
            @else
            <a href="{{ route('contrats.index') }}" class="btn btn-primary">Voir les contrats</a>
            @endif
            <a href="{{ route('outils.import.charger', $entite) }}" class="btn btn-secondary btn-sm">
                Nouvel import {{ $libelle }}
            </a>
            <a href="{{ route('outils.import.index') }}" class="btn btn-secondary btn-sm">
                ← Retour
            </a>
        </div>

    </div>
</div>
</x-app-layout>
