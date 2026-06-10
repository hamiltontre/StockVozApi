<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Usuario del sistema StockVoz.
 * Puede acceder a la app móvil (PIN) y/o al dashboard web (email + password).
 */
class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'negocio_id',
        'nombre',
        'email',
        'rol',
        'password',
        'pin_hash',
        'salt',
        'activo',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password',
        'pin_hash',
        'salt',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
            'ultimo_acceso' => 'datetime',
        ];
    }

    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }
}
