<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\FichePoste;

class Contrat extends Model
{
    protected $fillable = [
        "employe_id","contrat_parent_id","reference","type","fiche_poste_id","salaire_base",
        "date_debut","date_fin","duree_mois","renouvellement_auto","calendrier_conges",
        "statut","motif_resiliation","observations","pdf_path","created_by",
    ];

    protected $casts = [
        "date_debut"          => "date",
        "date_fin"            => "date",
        "renouvellement_auto" => "boolean",
        "salaire_base"        => "decimal:2",
    ];

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeActifs($query)     { return $query->where("statut", "en_cours"); }
    public function scopeExpirants($query, int $jours = 30)
    {
        return $query->where("statut", "en_cours")
                     ->whereNotNull("date_fin")
                     ->whereBetween("date_fin", [now(), now()->addDays($jours)]);
    }

    public const TYPES = ['CDD' => 'CDD', 'CDI' => 'CDI', 'interim' => 'Intérim', 'vacataire' => 'Vacataire'];

    public const STATUTS = [
        'en_cours'  => ['label' => 'En cours',  'badge' => 'bg'],
        'expire'    => ['label' => 'Expiré',    'badge' => 'br'],
        'renouvele' => ['label' => 'Renouvelé', 'badge' => 'bb'],
        'resilie'   => ['label' => 'Résilié',   'badge' => 'br'],
        'cloture'   => ['label' => 'Clôturé',   'badge' => 'bgr'],
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

    public function getJoursRestantsAttribute(): ?int
    {
        if (!$this->date_fin || $this->statut !== 'en_cours') return null;
        return (int) now()->startOfDay()->diffInDays($this->date_fin, false);
    }

    public function getAlertExpirationAttribute(): ?string
    {
        $j = $this->jours_restants;
        if ($j === null) return null;
        if ($j < 0)  return 'expire';
        if ($j <= 7)  return 'critique';
        if ($j <= 15) return 'urgent';
        if ($j <= 30) return 'alerte';
        return null;
    }

    public static function generateReference(): string
    {
        $annee = date('Y');
        $last  = static::where('reference', 'like', "CTR-{$annee}-%")->max('reference');
        $seq   = $last ? (int) substr($last, -4) + 1 : 1;
        return "CTR-{$annee}-" . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── Relations ──────────────────────────────────────────────────
    public function employe(): BelongsTo       { return $this->belongsTo(Employe::class); }
    public function createdBy(): BelongsTo     { return $this->belongsTo(User::class, "created_by"); }
    public function fichePoste(): BelongsTo    { return $this->belongsTo(FichePoste::class, 'fiche_poste_id'); }
    public function parent(): BelongsTo        { return $this->belongsTo(Contrat::class, 'contrat_parent_id'); }
    public function renouvellements()          { return $this->hasMany(Contrat::class, 'contrat_parent_id'); }
    public function avenants()                 { return $this->hasMany(Avenant::class)->orderBy('date_effet'); }

    public static function aDejaContratActif(int $employeId, ?int $exceptContratId = null): bool
    {
        return static::where('employe_id', $employeId)
            ->where('statut', 'en_cours')
            ->when($exceptContratId, fn($q) => $q->where('id', '!=', $exceptContratId))
            ->exists();
    }
}
