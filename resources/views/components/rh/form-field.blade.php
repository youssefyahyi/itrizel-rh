@props(['label', 'name', 'required' => false, 'hint' => null, 'full' => false, 'error' => null])
<div @class(['form-group', 'form-grid-full' => $full])>
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if($required)<span class="form-required">*</span>@endif
    </label>
    {{ $slot }}
    @if($hint)<div class="form-hint">{{ $hint }}</div>@endif
    @if($error)<div class="form-error">{{ $error }}</div>@endif
    @error($name)<div class="form-error">{{ $message }}</div>@enderror
</div>