<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Usuarios de la app (admin/invitado) Y del dashboard web.
 * Tienen ambos credenciales: PIN para la app móvil + password para el dashboard.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->string('nombre', 100);
            $table->string('email', 120)->unique()->nullable()
                  ->comment('Solo admins necesitan email para acceder al dashboard web');
            $table->enum('rol', ['admin', 'invitado'])->default('invitado');

            // Credenciales del dashboard web (Sanctum)
            $table->string('password')->nullable();
            $table->rememberToken();

            // Credenciales de la app móvil (PIN 4 dígitos)
            $table->string('pin_hash', 64)->nullable()
                  ->comment('SHA-256 hex del PIN concatenado con salt');
            $table->string('salt', 32)->nullable()
                  ->comment('Salt aleatorio único por usuario — 128 bits hex');

            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_acceso')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
