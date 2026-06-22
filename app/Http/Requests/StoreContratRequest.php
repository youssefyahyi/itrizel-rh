<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StoreContratRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employe_id'          => ['required', 'exists:employes,id', $this->regleUnContratActif()],
            'type'                => ['required', 'in:CDD,CDI,interim,vacataire'],
            'fiche_poste_id'      => ['nullable', 'exists:fiches_poste,id'],
            'salaire_base'        => ['required', 'numeric', 'min:1'],
            'date_debut'          => ['required', 'date'],
            'date_fin'            => ['nullable', 'date', 'after:date_debut', $this->regleDateFinCDD()],
            'duree_mois'          => ['nullable', 'integer', 'min:1'],
            'renouvellement_auto' => ['boolean'],
            'calendrier_conges'   => ['nullable', 'in:ouvrable,calendaire'],
            'observations'        => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'salaire_base.min'     => 'Le salaire de base doit être supérieur à 0.',
            'date_fin.after'       => 'La date de fin doit être postérieure à la date de début.',
            'date_fin.required_if' => 'Un contrat CDD, intérim ou vacataire doit avoir une date de fin.',
            'employe_id.*.message' => 'Cet employé a déjà un contrat en cours. Clôturez-le avant d\'en créer un nouveau.',
        ];
    }

    private function regleUnContratActif(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $exists = DB::table('contrats')
                ->where('employe_id', $value)
                ->where('statut', 'en_cours')
                ->exists();

            if ($exists) {
                $fail('Cet employé a déjà un contrat en cours. Clôturez-le avant d\'en créer un nouveau.');
            }
        };
    }

    private function regleDateFinCDD(): \Closure
    {
        return function ($attribute, $value, $fail) {
            $type = $this->input('type');
            if (in_array($type, ['CDD', 'interim', 'vacataire']) && empty($value)) {
                $fail("Un contrat {$type} doit avoir une date de fin.");
            }
        };
    }
}
