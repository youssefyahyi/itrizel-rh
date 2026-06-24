<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAbsenceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'statut'           => 'required|in:present,absent,conge,ferie,mission',
            'heure_arrivee'    => 'nullable|date_format:H:i',
            'heure_depart'     => 'nullable|date_format:H:i',
            'heures_prevues'   => 'nullable|numeric|min:0|max:24',
            'heures_realisees' => 'nullable|numeric|min:0|max:24',
            'motif_absence'    => 'nullable|string|max:255',
            'remarque'         => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'statut.required' => 'Le statut est obligatoire.',
        ];
    }
}
