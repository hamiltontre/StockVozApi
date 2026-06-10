<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoría de sincronización — cada item que la app móvil envía queda
 * registrado aquí con su resultado. Útil para diagnosticar problemas
 * sin tocar las tablas de negocio.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('sync_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->string('tabla', 40);
            $table->enum('operacion', ['INSERT', 'UPDATE', 'DELETE']);
            $table->enum('resultado', ['exito', 'error']);
            $table->json('payload');
            $table->text('error_mensaje')->nullable();
            $table->timestamps();

            $table->index(['negocio_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_logs');
    }
};
