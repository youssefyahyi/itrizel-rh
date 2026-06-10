<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCongeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'type_conge' => ['required', 'in:annuel,maladie,maternite,paternite,sans_solde,exceptionnel,recuperation'],
            'date_debut' => ['required', 'date'],
            'date_fin'   => ['required', 'date', 'after_or_equal:date_debut'],
            'nb_jours'   => ['nullable', 'integer', 'min:1'],
            'statut'     => ['required', 'in:soumis,en_validation,approuve,rejete,annule'],
            'motif'      => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_fin.after_or_equal' => 'La date de fin doit être égale ou postérieure à la date de début.',
        ];
    }
}
