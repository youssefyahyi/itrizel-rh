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
        "manager_id","unite_id",
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
    public function absences(): HasMany         { return $this->hasMany(Absence::class); }
    public function conges(): HasMany           { return $this->hasMany(Conge::class); }
    public function bulletinsPaie(): HasMany    { return $this->hasMany(BulletinPaie::class); }
    public function evaluations(): HasMany      { return $this->hasMany(Evaluation::class); }
    public function formations(): HasMany       { return $this->hasMany(Formation::class); }
    public function documents(): HasMany        { return $this->hasMany(DocumentRh::class); }
    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class, "created_by"); }

    // ── Relations hiérarchie & organisation ────────────────────────
    public function unite(): BelongsTo
    {
        return $this->belongsTo(UniteOrganisationnelle::class, 'unite_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(self::class, 'manager_id');
    }

    public function subordonnes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'manager_id');
    }

    /** Retourne tous les subordonnés récursivement */
    public function tousSubordonnes(): \Illuminate\Database\Eloquent\Collection
    {
        $result = new \Illuminate\Database\Eloquent\Collection();
        foreach ($this->subordonnes as $sub) {
            $result->push($sub);
            $result = $result->merge($sub->tousSubordonnes());
        }
        return $result;
    }

    // ── Compte utilisateur lié ──────────────────────────────────────
    public function userCompte(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(User::class, 'employe_id');
    }

    public function hasCompte(): bool
    {
        return $this->userCompte()->exists();
    }
}
