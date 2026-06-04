<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    protected $fillable = [
        'employe_id','evaluateur_id','type','periode_debut','periode_fin',
        'note_globale','statut','observations','decision','created_by',
    ];

    protected $casts = ['periode_debut' => 'date', 'periode_fin' => 'date', 'note_globale' => 'decimal:2'];

    const TYPES = [
        'semestriel'    => 'Semestrielle',
        'annuel'        => 'Annuelle',
        'periode_essai' => "Période d'essai",
    ];

    const DECISIONS = [
        'renouvellement'     => ['label' => 'Renouvellement',     'badge' => 'bg'],
        'non_renouvellement' => ['label' => 'Non renouvellement', 'badge' => 'br'],
        'amelioration'       => ['label' => 'Amélioration',       'badge' => 'by'],
        'en_attente'         => ['label' => 'En attente',         'badge' => 'bgr'],
    ];

    public function getTypeLibelleAttribute(): string     { return self::TYPES[$this->type] ?? $this->type; }
    public function getDecisionLibelleAttribute(): string { return self::DECISIONS[$this->decision]['label'] ?? $this->decision; }
    public function getDecisionBadgeAttribute(): string   { return self::DECISIONS[$this->decision]['badge'] ?? 'bgr'; }
    public function getStatutBadgeAttribute(): string     { return $this->statut === 'finalise' ? 'bg' : 'bgr'; }

    public function employe(): BelongsTo    { return $this->belongsTo(Employe::class); }
    public function evaluateur(): BelongsTo { return $this->belongsTo(User::class, 'evaluateur_id'); }
    public function createdBy(): BelongsTo  { return $this->belongsTo(User::class, 'created_by'); }
}
