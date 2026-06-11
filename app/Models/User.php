<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'actif',
        'last_login_at',
        'telephone',
        'employe_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'password'          => 'hashed',
            'actif'             => 'boolean',
        ];
    }

    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isLecteur(): bool    { return $this->role === 'lecteur'; }
    public function isSalarie(): bool    { return $this->role === 'salarie'; }
    public function isGestionnaire(): bool { return $this->role === 'gestionnaire'; }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Employe::class, 'employe_id');
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin'        => 'Administrateur',
            'gestionnaire' => 'Gestionnaire',
            'lecteur'      => 'Lecteur',
            'salarie'      => 'Salarié',
            default        => $this->role,
        };
    }
}
