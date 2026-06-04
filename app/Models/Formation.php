<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Formation extends Model
{
    protected $fillable = [
        'employe_id','intitule','organisme','type','date_debut','date_fin',
        'nb_heures','cout','statut','attestation','observations','created_by',
    ];

    protected $casts = ['date_debut' => 'date', 'date_fin' => 'date', 'nb_heures' => 'decimal:2', 'cout' => 'decimal:2'];

    const TYPES = ['interne' => 'Interne', 'externe' => 'Externe'];

    const STATUTS = [
        'planifie' => ['label' => 'Planifiée', 'badge' => 'bb'],
        'en_cours' => ['label' => 'En cours',  'badge' => 'by'],
        'termine'  => ['label' => 'Terminée',  'badge' => 'bg'],
        'annule'   => ['label' => 'Annulée',   'badge' => 'bgr'],
    ];

    public function getTypeLibelleAttribute(): string   { return self::TYPES[$this->type] ?? $this->type; }
    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut]['label'] ?? $this->statut; }
    public function getStatutBadgeAttribute(): string   { return self::STATUTS[$this->statut]['badge'] ?? 'bgr'; }

    public function scopeEnCours($q)   { return $q->where('statut', 'en_cours'); }
    public function scopeTerminees($q) { return $q->where('statut', 'termine'); }

    public function employe(): BelongsTo   { return $this->belongsTo(Employe::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
