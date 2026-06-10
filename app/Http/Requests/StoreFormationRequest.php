<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employe_id'  => ['required', 'exists:employes,id'],
            'intitule'    => ['required', 'string', 'max:255'],
            'organisme'   => ['nullable', 'string', 'max:255'],
            'type'        => ['required', 'in:interne,externe'],
            'date_debut'  => ['required', 'date'],
            'date_fin'    => ['required', 'date', 'after_or_equal:date_debut'],
            'nb_heures'   => ['required', 'numeric', 'min:0'],
            'cout'        => ['nullable', 'numeric', 'min:0'],
            'observations'=> ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_fin.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',
        ];
    }
}
