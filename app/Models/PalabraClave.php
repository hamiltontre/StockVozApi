<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PalabraClave extends Model
{
    use HasFactory;

    protected $table = 'palabras_clave';

    protected $fillable = ['producto_id', 'palabra'];

    public function producto(): BelongsTo { return $this->belongsTo(Producto::class); }
}
