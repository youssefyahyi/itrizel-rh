<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContratRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'fiche_poste_id'      => ['nullable', 'exists:fiches_poste,id'],
            'salaire_base'        => ['required', 'numeric', 'min:0'],
            'date_debut'          => ['required', 'date'],
            'date_fin'            => ['nullable', 'date', 'after:date_debut'],
            'duree_mois'          => ['nullable', 'integer', 'min:1'],
            'renouvellement_auto' => ['boolean'],
            'statut'              => ['required', 'in:en_cours,expire,renouvele,resilie,cloture'],
            'motif_resiliation'   => ['nullable', 'string', 'max:255'],
            'calendrier_conges'   => ['nullable', 'in:ouvrable,calendaire'],
            'observations'        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
        ];
    }
}
