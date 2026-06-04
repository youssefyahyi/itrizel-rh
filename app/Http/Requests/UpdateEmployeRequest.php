<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route("employe")->id ?? $this->route("employe");
        return [
            "nom"                => ["required","string","max:100"],
            "prenom"             => ["required","string","max:100"],
            "cin"                => ["required","string","max:20", Rule::unique("employes","cin")->ignore($id)],
            "date_naissance"     => ["required","date","before:today"],
            "sexe"               => ["required","in:M,F"],
            "categorie"          => ["required","in:commercial,chauffeur,magasinier,logisticien,administratif,cadre"],
            "poste"              => ["required","string","max:150"],
            "date_embauche"      => ["required","date"],
            "situation_familiale"=> ["required","in:celibataire,marie,divorce,veuf"],
            "nombre_enfants"     => ["required","integer","min:0","max:20"],
            "email"              => ["nullable","email", Rule::unique("employes","email")->ignore($id)],
            "telephone"          => ["nullable","string","max:20"],
            "adresse"            => ["nullable","string","max:255"],
            "ville"              => ["nullable","string","max:100"],
            "diplome"            => ["nullable","string","max:150"],
            "specialite"         => ["nullable","string","max:150"],
            "rib"                => ["nullable","string","max:30"],
            "banque"             => ["nullable","string","max:100"],
            "numero_cnss"        => ["nullable","string","max:30"],
            "numero_amo"         => ["nullable","string","max:30"],
        ];
    }
}
