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
        "diplome","specialite","fiche_poste_id","date_embauche",
        "rib","banque","numero_cnss","numero_amo","nombre_enfants",
        "situation_familiale","quotite_travail","heures_semaine","statut","created_by",
        "manager_id","unite_id",
    ];

    protected $casts = [
        "date_naissance"  => "date",
        "date_embauche"   => "date",
        "quotite_travail" => "decimal:2",
        "heures_semaine"  => "decimal:2",
    ];

    // ── Accessors ──────────────────────────────────────────────────
    public function getNomCompletAttribute(): string
    {
        return "{$this->nom} {$this->prenom}";
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

    // ── Congés — compteurs ─────────────────────────────────────────
    public function getSoldeCongesAcquisAttribute(): float
    {
        if (!$this->date_embauche) return 0.0;
        $joursParMois   = (float) ParametrageRh::get('rh.conges_jours_par_mois', '1.5');
        $debutExercice  = now()->startOfYear();
        $debutCompte    = $this->date_embauche->gt($debutExercice) ? $this->date_embauche : $debutExercice;
        $mois           = (int) $debutCompte->diffInMonths(now());
        return round($mois * $joursParMois * ((float)$this->quotite_travail / 100), 1);
    }

    public function getSoldeCongesPrisAttribute(): float
    {
        return (float) $this->conges()
            ->where('type_conge', 'annuel')
            ->where('statut', 'approuve')
            ->whereYear('date_debut', now()->year)
            ->sum('nb_jours');
    }

    public function getSoldeCongesRestantAttribute(): float
    {
        return max(0.0, $this->solde_conges_acquis - $this->solde_conges_pris);
    }

    // ── Scopes ─────────────────────────────────────────────────────
    public function scopeActifs($query)   { return $query->where('statut', 'actif'); }
    public function scopeInactifs($query) { return $query->whereIn('statut', ['inactif', 'suspendu']); }

    // ── Génération du matricule ─────────────────────────────────────
    /**
     * Génère le prochain matricule unique au format EMP-AAAA-XXXX.
     * Méthode statique : logique métier de numérotation dans le modèle.
     */
    public static function generateMatricule(): string
    {
        return app(\App\Services\CodificationService::class)->generer('matricule');
    }

    // ── Relations ──────────────────────────────────────────────────
    public function contrats(): HasMany  { return $this->hasMany(Contrat::class); }

    /**
     * Contrat actif courant — utilise latestOfMany() pour garantir
     * qu'un seul enregistrement est retourné même si plusieurs contrats
     * sont en cours (cas de renouvellement sans clôture de l'ancien).
     */
    public function contratActif(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Contrat::class)
                    ->where('statut', 'en_cours')
                    ->latestOfMany();
    }
    public function absences(): HasMany         { return $this->hasMany(Absence::class); }
    public function conges(): HasMany           { return $this->hasMany(Conge::class); }
    public function bulletinsPaie(): HasMany    { return $this->hasMany(BulletinPaie::class); }
    public function evaluations(): HasMany      { return $this->hasMany(Evaluation::class); }
    public function formations(): HasMany       { return $this->hasMany(Formation::class); }
    public function documents(): HasMany        { return $this->hasMany(DocumentRh::class); }
    public function createdBy(): BelongsTo      { return $this->belongsTo(User::class, "created_by"); }
    public function fichePoste(): BelongsTo     { return $this->belongsTo(FichePoste::class, 'fiche_poste_id'); }

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

    /**
     * Retourne tous les subordonnés récursivement.
     *
     * @param  int  $profondeurMax  Protection contre les cycles ou les orgs très profondes.
     * @param  array $vus           IDs déjà parcourus (détection de cycle).
     */
    public function tousSubordonnes(int $profondeurMax = 10, array $vus = []): \Illuminate\Database\Eloquent\Collection
    {
        $result = new \Illuminate\Database\Eloquent\Collection();

        if ($profondeurMax <= 0 || in_array($this->id, $vus, true)) {
            return $result;
        }

        $vus[] = $this->id;

        foreach ($this->subordonnes as $sub) {
            $result->push($sub);
            $result = $result->merge($sub->tousSubordonnes($profondeurMax - 1, $vus));
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
