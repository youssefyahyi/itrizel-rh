{{--
    Composant date-input — saisie de date cohérente dans tout l'app.

    Paramètres :
      @param string  $name          Nom du champ (required)
      @param string  $label         Label affiché (required)
      @param bool    $required      Champ obligatoire (défaut false)
      @param string  $value         Valeur initiale Y-m-d (défaut '')
      @param string|null $max       Date max au format Y-m-d (défaut null = sans limite)
      @param string|null $min       Date min au format Y-m-d (défaut null = sans limite)
      @param bool    $pastOnly      Raccourci : max = today (défaut false)
      @param bool    $todayOrPast   Raccourci : max = today, alias de pastOnly

    Usage :
      {{-- Date dans le passé (naissance) --}}
      <x-rh.date-input name="date_naissance" label="Date de naissance" :required="true" :past-only="true"
          :value="old('date_naissance', $employe->date_naissance?->format('Y-m-d'))" />

      {{-- Date quelconque (début contrat) --}}
      <x-rh.date-input name="date_debut" label="Début du contrat" :required="true"
          :value="old('date_debut', $contrat->date_debut?->format('Y-m-d'))" />

      {{-- Plage : fin >= début --}}
      <x-rh.date-input name="date_fin" label="Fin du contrat"
          min="..." :value="old('date_fin')" />
--}}
@props([
    'name'        => '',
    'label'       => '',
    'required'    => false,
    'value'       => '',
    'max'         => null,
    'min'         => null,
    'pastOnly'    => false,
    'todayOrPast' => false,
])

@php
    $maxAttr = ($pastOnly || $todayOrPast) ? date('Y-m-d') : $max;
@endphp

<x-rh.form-field :label="$label" :name="$name" :required="$required">
    <input
        type="date"
        name="{{ $name }}"
        id="{{ $name }}"
        class="form-control @error($name) is-invalid @enderror"
        @if($maxAttr) max="{{ $maxAttr }}" @endif
        @if($min)     min="{{ $min }}"     @endif
        value="{{ $value }}"
        {{ $required ? 'required' : '' }}
    >
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</x-rh.form-field>
