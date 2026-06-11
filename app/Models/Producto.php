<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'negocio_id', 'categoria_id', 'nombre', 'codigo_barras',
        'precio', 'precio_costo', 'unidad', 'fecha_vencimiento',
        'stock', 'stock_minimo', 'activo', 'cliente_id',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'integer',
            'precio_costo' => 'integer',
            'stock' => 'integer',
            'stock_minimo' => 'integer',
            'activo' => 'boolean',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function negocio(): BelongsTo     { return $this->belongsTo(Negocio::class); }
    public function categoria(): BelongsTo   { return $this->belongsTo(Categoria::class); }
    public function palabrasClave(): HasMany { return $this->hasMany(PalabraClave::class); }
}
