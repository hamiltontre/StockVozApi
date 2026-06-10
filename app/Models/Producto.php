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
        'precio', 'stock', 'stock_minimo', 'activo', 'cliente_id',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'integer',
            'stock' => 'integer',
            'stock_minimo' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function negocio(): BelongsTo     { return $this->belongsTo(Negocio::class); }
    public function categoria(): BelongsTo   { return $this->belongsTo(Categoria::class); }
    public function palabrasClave(): HasMany { return $this->hasMany(PalabraClave::class); }
}
