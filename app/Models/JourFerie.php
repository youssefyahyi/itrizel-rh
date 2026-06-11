<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JourFerie extends Model
{
    protected $table = 'jours_feries';

    protected $fillable = ['date', 'libelle', 'annee', 'type'];

    protected $casts = ['date' => 'date'];

    public static function toutesDates(): array
    {
        return static::pluck('date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();
    }

    public static function parAnnee(): \Illuminate\Support\Collection
    {
        return static::orderBy('date')->get()->groupBy('annee');
    }
}
