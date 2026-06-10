<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEvaluationRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'evaluateur_id' => ['required', 'exists:users,id'],
            'type'          => ['required', 'in:semestriel,annuel,periode_essai'],
            'periode_debut' => ['required', 'date'],
            'periode_fin'   => ['required', 'date', 'after_or_equal:periode_debut'],
            'note_globale'  => ['nullable', 'numeric', 'min:0', 'max:20'],
            'statut'        => ['required', 'in:brouillon,finalise'],
            'decision'      => ['required', 'in:renouvellement,non_renouvellement,amelioration,en_attente'],
            'observations'  => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'periode_fin.after_or_equal' => 'La fin de période doit être égale ou postérieure au début.',
            'note_globale.max'           => 'La note ne peut pas dépasser 20.',
        ];
    }
}
