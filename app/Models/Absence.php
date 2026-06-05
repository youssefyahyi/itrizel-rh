<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absence extends Model
{
    protected $table = 'absences';

    protected $fillable = [
        'employe_id','date','heure_arrivee','heure_depart',
        'heures_prevues','heures_realisees','statut',
        'motif_absence','justificatif','remarque','created_by',
    ];

    protected $casts = [
        'date'          => 'date',
        'heure_arrivee' => 'datetime:H:i',
        'heure_depart'  => 'datetime:H:i',
    ];

    const STATUTS = [
        'present' => ['label' => 'Présent',  'badge' => 'bg'],
        'absent'  => ['label' => 'Absent',   'badge' => 'br'],
        'conge'   => ['label' => 'Congé',    'badge' => 'by'],
        'ferie'   => ['label' => 'Férié',    'badge' => 'bgr'],
        'mission' => ['label' => 'Mission',  'badge' => 'bb'],
    ];

    // ── Accessors ──────────────────────────────────────────────────
    public function getStatutLibelleAttribute(): string
    {
        return self::STATUTS[$this->statut]['label'] ?? $this->statut;
    }

    public function getStatutBadgeAttribute(): string
    {
        return self::STATUTS[$this->statut]['badge'] ?? 'bgr';
    }

    public function getDureeAttribute(): ?float
    {
        if ($this->heures_realisees !== null) {
            return (float) $this->heures_realisees;
        }
        if ($this->heure_arrivee && $this->heure_depart) {
            $arr = \Carbon\Carbon::parse($this->heure_arrivee);
            $dep = \Carbon\Carbon::parse($this->heure_depart);
            return round($dep->diffInMinutes($arr) / 60, 2);
        }
        return null;
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeParDate($query, $date) { return $query->whereDate('date', $date); }
    public function scopeParStatut($query, $statut) { return $query->where('statut', $statut); }

    // ── Relations ──────────────────────────────────────────────────
    public function employe(): BelongsTo   { return $this->belongsTo(Employe::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
