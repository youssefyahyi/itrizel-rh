<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $table = "audit_logs";
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        "user_id","module","action","description",
        "model_type","model_id","donnees_avant","donnees_apres","ip_address",
    ];

    protected $casts = [
        "donnees_avant" => "array",
        "donnees_apres" => "array",
    ];

    public static function log(string $module, string $action, string $description, ?Model $model = null): void
    {
        static::create([
            "user_id"     => auth()->id(),
            "module"      => $module,
            "action"      => $action,
            "description" => $description,
            "model_type"  => $model ? get_class($model) : null,
            "model_id"    => $model?->id,
            "ip_address"  => request()->ip(),
        ]);
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
