<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaieRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employe_id'      => ['required', 'exists:employes,id'],
            'periode_mois'    => ['required', 'integer', 'min:1', 'max:12'],
            'periode_annee'   => ['required', 'integer', 'min:2020', 'max:2050'],
            'salaire_base'    => ['required', 'numeric', 'min:0'],
            'total_primes'    => ['nullable', 'numeric', 'min:0'],
            'avances_deduites'=> ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'employe_id.required' => 'Veuillez sélectionner un employé.',
            'salaire_base.min'    => 'Le salaire de base ne peut pas être négatif.',
        ];
    }
}
