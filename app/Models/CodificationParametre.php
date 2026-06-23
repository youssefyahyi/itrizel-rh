<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodificationParametre extends Model
{
    protected $fillable = [
        'entite', 'libelle', 'prefixe', 'separateur',
        'inclure_annee', 'format_annee', 'nb_chiffres',
        'reset_annuel', 'compteur_courant', 'annee_courante',
    ];

    protected $casts = [
        'inclure_annee' => 'boolean',
        'reset_annuel'  => 'boolean',
        'nb_chiffres'   => 'integer',
        'compteur_courant' => 'integer',
        'annee_courante'   => 'integer',
    ];

    public function apercu(int $numero = null): string
    {
        $n = $numero ?? ($this->compteur_courant + 1);
        $parties = [];

        if ($this->prefixe !== '') $parties[] = $this->prefixe;
        if ($this->inclure_annee) {
            $parties[] = $this->format_annee === 'YY' ? date('y') : date('Y');
        }
        $parties[] = str_pad($n, $this->nb_chiffres, '0', STR_PAD_LEFT);

        return implode($this->separateur, $parties);
    }
}
