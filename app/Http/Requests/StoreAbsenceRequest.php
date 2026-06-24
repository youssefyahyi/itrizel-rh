<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbsenceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employe_id'       => 'required|exists:employes,id',
            'date'             => 'required|date|before_or_equal:today',
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
            'employe_id.required' => 'L\'employé est obligatoire.',
            'date.required'       => 'La date est obligatoire.',
            'date.before_or_equal'=> 'La date ne peut pas être dans le futur.',
            'statut.required'     => 'Le statut est obligatoire.',
        ];
    }
}
