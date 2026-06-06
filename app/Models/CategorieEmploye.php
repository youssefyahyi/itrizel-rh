<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategorieEmploye extends Model
{
    protected $table    = 'categories_employe';
    protected $fillable = ['nom', 'ordre', 'actif'];
    protected $casts    = ['actif' => 'boolean', 'ordre' => 'integer'];

    public function employes(): HasMany { return $this->hasMany(Employe::class, 'categorie_id'); }
    public function contrats(): HasMany { return $this->hasMany(Contrat::class,  'categorie_id'); }

    /** Retourne toutes les catégories actives triées par ordre. */
    public static function actives()
    {
        return static::where('actif', true)->orderBy('ordre')->get();
    }
}
