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
            'salaire_base'        => ['required', 'numeric', 'min:1'],
            'date_debut'          => ['required', 'date'],
            'date_fin'            => ['nullable', 'date', 'after_or_equal:date_debut', $this->regleDateFinCDD()],
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
            'salaire_base.min'          => 'Le salaire de base doit être supérieur à 0.',
            'date_fin.after_or_equal'   => 'La date de fin ne peut pas être antérieure à la date de début.',
        ];
    }

    private function regleDateFinCDD(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $type = $this->route('contrat')?->type ?? $this->input('type');
            if (in_array($type, ['CDD', 'interim', 'vacataire']) && empty($value)) {
                $fail("Un contrat {$type} doit avoir une date de fin.");
            }
        };
    }
}
