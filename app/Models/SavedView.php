<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedView extends Model
{
    protected $fillable = ['user_id', 'module', 'nom', 'filtres', 'visibilite'];

    protected $casts = ['filtres' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePourModule($query, string $module)
    {
        return $query->where('module', $module);
    }
}
