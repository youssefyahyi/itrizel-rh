<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avenant extends Model
{
    protected $fillable = [
        'contrat_id', 'reference', 'date_effet', 'nature',
        'ancienne_valeur', 'nouvelle_valeur', 'motif', 'pdf_path', 'created_by',
    ];

    protected $casts = ['date_effet' => 'date'];

    const NATURES = [
        'salaire'       => 'Révision salariale',
        'poste'         => 'Changement de poste',
        'date_fin'      => 'Prolongation / modification échéance',
        'temps_travail' => 'Modification du temps de travail',
        'autre'         => 'Autre modification',
    ];

    public function getNatureLibelleAttribute(): string
    {
        return self::NATURES[$this->nature] ?? $this->nature;
    }

    public static function generateReference(Contrat $contrat): string
    {
        $count = static::where('contrat_id', $contrat->id)->count() + 1;
        return 'AVN-' . $contrat->reference . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
    }

    public function contrat(): BelongsTo  { return $this->belongsTo(Contrat::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
