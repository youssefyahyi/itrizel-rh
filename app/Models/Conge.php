<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conge extends Model
{
    protected $fillable = [
        "employe_id","type_conge","date_debut","date_fin","nb_jours",
        "motif","statut","etape_actuelle","document_justificatif","created_by",
    ];

    protected $casts = [
        "date_debut" => "date",
        "date_fin"   => "date",
    ];

    const TYPES = [
        'annuel'       => 'Congé annuel',   'maladie'      => 'Maladie',
        'maternite'    => 'Maternité',      'paternite'    => 'Paternité',
        'sans_solde'   => 'Sans solde',     'exceptionnel' => 'Exceptionnel',
        'recuperation' => 'Récupération',
    ];

    const STATUTS = [
        'soumis'        => ['label' => 'Soumis',        'badge' => 'by'],
        'en_validation' => ['label' => 'En validation', 'badge' => 'bb'],
        'approuve'      => ['label' => 'Approuvé',      'badge' => 'bg'],
        'rejete'        => ['label' => 'Rejeté',        'badge' => 'br'],
        'annule'        => ['label' => 'Annulé',        'badge' => 'bgr'],
    ];

    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut]['label'] ?? $this->statut; }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeEnAttente($query)  { return $query->whereIn("statut", ["soumis","en_validation"]); }
    public function scopeApprouves($query)  { return $query->where("statut", "approuve"); }

    // ── Accessors ──────────────────────────────────────────────────
    public function getStatutBadgeAttribute(): string
    {
        return match($this->statut) {
            "soumis","en_validation" => "by",
            "approuve"               => "bg",
            "rejete","annule"        => "br",
            default                  => "bgr",
        };
    }

    public function getTypeCongeLibelleAttribute(): string
    {
        return self::TYPES[$this->type_conge] ?? $this->type_conge;
    }

    // ── Relations ──────────────────────────────────────────────────
    public function employe(): BelongsTo        { return $this->belongsTo(Employe::class); }
    public function validations(): HasMany       { return $this->hasMany(CongeValidation::class); }
    public function createdBy(): BelongsTo       { return $this->belongsTo(User::class, "created_by"); }
}
