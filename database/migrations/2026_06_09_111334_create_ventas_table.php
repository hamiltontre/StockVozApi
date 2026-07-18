<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->unsignedBigInteger('total')->comment('Total final en centavos');
            $table->unsignedBigInteger('descuento')->default(0);
            $table->enum('metodo_pago', ['efectivo', 'tarjeta', 'transferencia'])->default('efectivo');
            $table->enum('estado', ['completada', 'anulada'])->default('completada');
            $table->text('notas')->nullable();
            // Fiado (venta al crédito): deuda pendiente hasta que fiado_pagado_en tenga fecha
            $table->boolean('es_fiado')->default(false);
            $table->string('fiador_nombre', 100)->nullable();
            $table->timestamp('fiado_pagado_en')->nullable();
            // ID del cliente local para evitar duplicados al sincronizar
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->timestamp('vendido_en')->nullable()
                  ->comment('Fecha real de la venta en el dispositivo (puede diferir de created_at por sync diferida)');
            $table->timestamps();

            $table->index(['negocio_id', 'vendido_en']);
            $table->index(['negocio_id', 'estado']);
            $table->unique(['negocio_id', 'cliente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
