<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negocio_id')->constrained('negocios')->cascadeOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('nombre', 100);
            $table->string('codigo_barras', 50)->nullable();
            $table->unsignedBigInteger('precio')->comment('Precio de VENTA en centavos');
            $table->unsignedBigInteger('precio_costo')->default(0)->comment('Precio de compra en centavos');
            $table->unsignedBigInteger('precio_docena')->default(0)
                  ->comment('Precio por docena en centavos; 0 = no se vende por docena');
            $table->string('unidad', 20)->default('unidad')
                  ->comment('unidad|caja|docena|libra|litro|metro|par|paquete');
            $table->date('fecha_vencimiento')->nullable()->comment('Null si el producto no vence');
            // decimal: el stock puede ser fraccionario (9.5 libras, 2.5 litros)
            $table->decimal('stock', 10, 2)->default(0);
            $table->decimal('stock_minimo', 10, 2)->default(1);
            $table->boolean('activo')->default(true);
            // ID del producto en la app local — para resolver conflictos de sync
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->timestamps();

            $table->index(['negocio_id', 'activo']);
            $table->index('codigo_barras');
            $table->index(['negocio_id', 'fecha_vencimiento']); // alertas de vencimiento
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
