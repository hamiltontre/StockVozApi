<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla central de tenancy — cada fila es un comercio cliente de StockVoz.
 * Las apps móviles sincronizan datos asociados a su negocio_id.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('negocios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ruc', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->string('email', 120)->unique()->nullable();
            $table->enum('moneda', ['NIO', 'USD'])->default('NIO');
            $table->enum('plan', ['basico', 'premium', 'empresarial'])->default('basico');
            $table->timestamp('plan_expira_en')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negocios');
    }
};
