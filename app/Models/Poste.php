<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poste extends Model
{
    protected $table    = 'postes';
    protected $fillable = ['nom', 'actif'];
    protected $casts    = ['actif' => 'boolean'];

    public function fiches(): HasMany
    {
        return $this->hasMany(FichePoste::class, 'poste_id');
    }

    public static function actives(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('actif', true)->orderBy('nom')->get();
    }
}
