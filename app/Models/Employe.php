<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employe extends Model
{
    use SoftDeletes;

    protected $fillable = [
        "matricule","nom","prenom","cin","date_naissance","lieu_naissance",
        "nationalite","sexe","email","telephone","adresse","ville","photo",
        "diplome","specialite","categorie","poste","date_embauche",
        "rib","banque","numero_cnss","numero_amo","nombre_enfants",
        "situation_familiale","statut","created_by",
    ];

    protected $casts = [
        "date_naissance" => "date",
        "date_embauche"  => "date",
    ];

    // ── Accessors ──────────────────────────────────────────────────
    public function getNomCompletAttribute(): string
    {
        return "{$this->nom} {$this->prenom}";
    }

    public const CATEGORIES = [
        "commercial"   => "Commercial",
        "chauffeur"    => "Chauffeur",
        "magasinier"   => "Magasinier",
        "logisticien"  => "Logisticien",
        "administratif"=> "Administratif",
        "cadre"        => "Cadre",
    ];

    public function getCategorieLibelleAttribute(): string
    {
        return self::CATEGORIES[$this->categorie] ?? $this->categorie;
    }

    public function getStatutBadgeAttribute(): string
    {
        return match($this->statut) {
            "actif"     => "bg",
            "inactif"   => "bgr",
            "suspendu"  => "by",
            default     => "bgr",
        };
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeActifs($query)       { return $query->where("statut", "actif"); }
    public function scopeParCategorie($query, string $cat) { return $query->where("categorie", $cat); }

    // ── Relations ──────────────────────────────────────────────────
    public function contrats(): HasMany         { return $this->hasMany(Contrat::class); }
    public function contratActif()              { return $this->hasOne(Contrat::class)->where("statut","en_cours")->latest(); }
    public function presences(): HasMany        { return $this->hasMany(Presence::class); }
    public function conges(): HasMany           { return $this->hasMany(Conge::class); }
    public function bulletinsPaie(): HasMany    { return $this->hasMany(BulletinPaie::class); }
    public function evaluations(): HasMany      { return $this->hasMany(Evaluation::class); }
    public function formations(): HasMany       { return $this->hasMany(Formation::class); }
    public function documents(): HasMany        { return $this->hasMany(DocumentRh::class); }
    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class, "created_by"); }
}
