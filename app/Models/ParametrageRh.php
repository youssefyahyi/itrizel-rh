<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Modèle clé-valeur pour les paramètres RH (société, paie, IR…).
 *
 * Toutes les lectures passent par un cache Laravel (TTL 1h) pour
 * éviter des requêtes SQL répétées — notamment lors du calcul des
 * bulletins de paie qui charge ~15 paramètres.
 *
 * Appeler ParametrageRh::clearCache() après toute modification.
 */
class ParametrageRh extends Model
{
    protected $table = 'parametrage_rh';

    protected $fillable = ['cle', 'valeur', 'groupe', 'libelle', 'description'];

    // Durée du cache en secondes (1 heure)
    private const CACHE_TTL = 3600;
    private const CACHE_KEY = 'parametrage_rh_all';

    // ── Cache ──────────────────────────────────────────────────────

    /**
     * Charge tous les paramètres depuis le cache ou la base.
     * Retourne un tableau [ cle => valeur ].
     */
    private static function all_cached(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return static::pluck('valeur', 'cle')->toArray();
        });
    }

    /** Invalide le cache. À appeler après toute écriture. */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    // ── Helpers de lecture ─────────────────────────────────────────

    /** Valeur d'une clé (ou $default si absente). */
    public static function get(string $cle, mixed $default = null): mixed
    {
        return self::all_cached()[$cle] ?? $default;
    }

    /** Toutes les clés d'un groupe sous forme [cle => valeur]. */
    public static function getGroupe(string $groupe): array
    {
        // On requête par groupe (colonne dédiée) — pas de cache partiel car
        // le cache global est sur toutes les clés, pas indexé par groupe.
        return static::where('groupe', $groupe)->pluck('valeur', 'cle')->toArray();
    }

    /** Valeur JSON décodée en tableau associatif. */
    public static function getJson(string $cle, mixed $default = []): mixed
    {
        $val = self::get($cle);
        if (!$val) return $default;
        return json_decode($val, true) ?? $default;
    }

    /** Valeur castée en float. */
    public static function getFloat(string $cle, float $default = 0.0): float
    {
        return (float)(self::get($cle) ?? $default);
    }

    // ── Écriture ───────────────────────────────────────────────────

    /** Met à jour ou crée une clé, puis invalide le cache. */
    public static function set(string $cle, mixed $valeur): void
    {
        static::updateOrCreate(['cle' => $cle], ['valeur' => $valeur]);
        self::clearCache();
    }
}
