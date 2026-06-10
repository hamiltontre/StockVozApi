<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Negocio extends Model
{
    use HasFactory;

    protected $table = 'negocios';

    protected $fillable = [
        'nombre',
        'ruc',
        'telefono',
        'direccion',
        'email',
        'moneda',
        'plan',
        'plan_expira_en',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'plan_expira_en' => 'datetime',
            'activo' => 'boolean',
        ];
    }

    public function usuarios(): HasMany       { return $this->hasMany(Usuario::class); }
    public function productos(): HasMany      { return $this->hasMany(Producto::class); }
    public function categorias(): HasMany     { return $this->hasMany(Categoria::class); }
    public function ventas(): HasMany         { return $this->hasMany(Venta::class); }
    public function syncLogs(): HasMany       { return $this->hasMany(SyncLog::class); }
}
