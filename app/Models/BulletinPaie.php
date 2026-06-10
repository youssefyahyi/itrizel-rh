<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BulletinPaie extends Model
{
    protected $table = "bulletins_paie";

    protected $fillable = [
        "employe_id","periode_mois","periode_annee","salaire_base",
        "prime_anciennete","total_primes","total_retenues","net_imposable","ir_mensuel",
        "amo_salarie","cnss_salarie","cimr_salarie","avances_deduites",
        "cnss_patronal","amo_patronal","cimr_patronal","total_patronal",
        "jours_travailles","heures_travailles",
        "net_a_payer","statut","date_paiement","created_by",
    ];

    protected $casts = [
        "date_paiement"    => "date",
        "salaire_base"     => "decimal:2",
        "prime_anciennete" => "decimal:2",
        "total_primes"     => "decimal:2",
        "total_retenues"   => "decimal:2",
        "net_imposable"    => "decimal:2",
        "ir_mensuel"       => "decimal:2",
        "amo_salarie"      => "decimal:2",
        "cnss_salarie"     => "decimal:2",
        "cimr_salarie"     => "decimal:2",
        "avances_deduites" => "decimal:2",
        "cnss_patronal"    => "decimal:2",
        "amo_patronal"     => "decimal:2",
        "cimr_patronal"    => "decimal:2",
        "total_patronal"   => "decimal:2",
        "net_a_payer"      => "decimal:2",
    ];

    const STATUTS = [
        'brouillon' => ['label' => 'Brouillon', 'badge' => 'bgr'],
        'valide'    => ['label' => 'Validé',    'badge' => 'bb'],
        'paye'      => ['label' => 'Payé',      'badge' => 'bg'],
    ];

    public function getStatutLibelleAttribute(): string { return self::STATUTS[$this->statut]['label'] ?? $this->statut; }
    public function getStatutBadgeAttribute(): string   { return self::STATUTS[$this->statut]['badge'] ?? 'bgr'; }
    public function getTotalCotisationsAttribute(): float
    {
        return (float)($this->ir_mensuel + $this->amo_salarie + $this->cnss_salarie + $this->cimr_salarie);
    }

    public function getPeriodeLibelleAttribute(): string
    {
        $mois = ["", "Janvier","Février","Mars","Avril","Mai","Juin",
                 "Juillet","Août","Septembre","Octobre","Novembre","Décembre"];
        return $mois[$this->periode_mois] . " " . $this->periode_annee;
    }

    public function employe(): BelongsTo  { return $this->belongsTo(Employe::class); }
    public function lignes(): HasMany     { return $this->hasMany(BulletinPaieLigne::class, "bulletin_id"); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, "created_by"); }
}
