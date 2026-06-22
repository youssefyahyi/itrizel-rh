<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectionScenario extends Model
{
    protected $fillable = ['nom', 'description', 'horizon', 'created_by', 'archived_at'];

    protected $casts = [
        'horizon'     => 'integer',
        'archived_at' => 'datetime',
    ];

    public function lignes(): HasMany
    {
        return $this->hasMany(ProjectionScenarioLigne::class, 'scenario_id')->orderBy('mois_effet');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActifs($query)
    {
        return $query->whereNull('archived_at');
    }

    public function getIsArchivedAttribute(): bool
    {
        return $this->archived_at !== null;
    }
}
