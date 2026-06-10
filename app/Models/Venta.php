<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'negocio_id', 'usuario_id', 'total', 'descuento',
        'metodo_pago', 'estado', 'notas', 'cliente_id', 'vendido_en',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'descuento' => 'integer',
            'vendido_en' => 'datetime',
        ];
    }

    public function negocio(): BelongsTo  { return $this->belongsTo(Negocio::class); }
    public function usuario(): BelongsTo  { return $this->belongsTo(Usuario::class); }
    public function detalle(): HasMany    { return $this->hasMany(DetalleVenta::class); }
}
