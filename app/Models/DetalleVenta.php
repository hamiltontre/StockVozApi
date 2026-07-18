<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas';

    protected $fillable = [
        'venta_id', 'producto_id', 'nombre_producto',
        'cantidad', 'precio_unitario', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            // float: cantidades fraccionarias (media libra = 0.5)
            'cantidad' => 'float',
            'precio_unitario' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function venta(): BelongsTo    { return $this->belongsTo(Venta::class); }
    public function producto(): BelongsTo { return $this->belongsTo(Producto::class); }
}
