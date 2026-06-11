<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            "nom"                => ["required","string","max:100"],
            "prenom"             => ["required","string","max:100"],
            "cin"                => ["required","string","max:20","unique:employes,cin"],
            "date_naissance"     => ["required","date","before:today"],
            "lieu_naissance"     => ["nullable","string","max:100"],
            "nationalite"        => ["nullable","string","max:100"],
            "sexe"               => ["required","in:M,F"],
            "date_embauche"      => ["required","date","before_or_equal:today"],
            "situation_familiale"=> ["required","in:celibataire,marie,divorce,veuf"],
            "nombre_enfants"     => ["required","integer","min:0","max:20"],
            "quotite_travail"    => ["required","numeric","in:100,80,75,50,25"],
            "heures_semaine"     => ["required","numeric","min:1","max:60"],
            "email"              => ["nullable","email","unique:employes,email"],
            "telephone"          => ["nullable","string","max:20"],
            "adresse"            => ["nullable","string","max:255"],
            "ville"              => ["nullable","string","max:100"],
            "diplome"            => ["nullable","string","max:150"],
            "specialite"         => ["nullable","string","max:150"],
            "rib"                => ["nullable","string","max:30"],
            "banque"             => ["nullable","string","max:100"],
            "numero_cnss"        => ["nullable","string","max:30"],
            "numero_amo"         => ["nullable","string","max:30"],
            "photo"              => ["nullable","image","max:2048"],
            "fiche_poste_id"     => ["nullable","exists:fiches_poste,id"],
        ];
    }

    public function messages(): array
    {
        return [
            "cin.unique"                    => "Ce numéro de CIN est déjà enregistré.",
            "email.unique"                  => "Cette adresse email est déjà utilisée.",
            "date_naissance.before"         => "La date de naissance doit être dans le passé.",
            "date_embauche.before_or_equal" => "La date d'embauche ne peut pas être dans le futur.",
        ];
    }
}
