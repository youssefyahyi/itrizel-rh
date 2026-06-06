<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fonction extends Model
{
    protected $table    = 'fonctions';
    protected $fillable = ['nom', 'ordre', 'actif'];
    protected $casts    = ['actif' => 'boolean', 'ordre' => 'integer'];

    public function fiches(): HasMany
    {
        return $this->hasMany(FichePoste::class, 'fonction_id');
    }

    public static function actives(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('actif', true)->orderBy('ordre')->orderBy('nom')->get();
    }
}
