<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClauseContrat extends Model
{
    protected $table = 'clause_contrats';

    protected $fillable = ['type_doc', 'titre', 'contenu', 'ordre', 'obligatoire', 'actif'];

    protected $casts = ['obligatoire' => 'boolean', 'actif' => 'boolean'];

    const TYPES = ['CDI' => 'CDI', 'CDD' => 'CDD', 'avenant' => 'Avenant'];

    public function scopeActives($query)                    { return $query->where('actif', true); }
    public function scopePourType($query, string $type)     { return $query->where('type_doc', $type)->orderBy('ordre'); }
}
