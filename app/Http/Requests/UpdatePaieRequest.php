<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaieRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'salaire_base'    => ['required', 'numeric', 'min:0'],
            'total_primes'    => ['nullable', 'numeric', 'min:0'],
            'avances_deduites'=> ['nullable', 'numeric', 'min:0'],
            'statut'          => ['required', 'in:brouillon,valide,paye'],
            'date_paiement'   => ['nullable', 'date'],
        ];
    }
}
