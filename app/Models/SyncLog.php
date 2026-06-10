<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncLog extends Model
{
    use HasFactory;

    protected $table = 'sync_logs';

    protected $fillable = [
        'negocio_id', 'usuario_id', 'tabla',
        'operacion', 'resultado', 'payload', 'error_mensaje',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function negocio(): BelongsTo { return $this->belongsTo(Negocio::class); }
    public function usuario(): BelongsTo { return $this->belongsTo(Usuario::class); }
}
