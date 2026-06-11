<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContratRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employe_id'          => ['required', 'exists:employes,id'],
            'type'                => ['required', 'in:CDD,CDI,interim,vacataire'],
            'fiche_poste_id'      => ['nullable', 'exists:fiches_poste,id'],
            'salaire_base'        => ['required', 'numeric', 'min:0'],
            'date_debut'          => ['required', 'date', 'after_or_equal:today'],
            'date_fin'            => ['nullable', 'date', 'after:date_debut'],
            'duree_mois'          => ['nullable', 'integer', 'min:1'],
            'renouvellement_auto' => ['boolean'],
            'calendrier_conges'   => ['nullable', 'in:ouvrable,calendaire'],
            'observations'        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_debut.after_or_equal' => 'La date de début du contrat ne peut pas être dans le passé.',
            'date_fin.after'            => 'La date de fin doit être postérieure à la date de début.',
        ];
    }
}
