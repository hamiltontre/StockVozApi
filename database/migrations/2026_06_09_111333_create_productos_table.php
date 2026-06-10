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
            $table->unsignedBigInteger('precio')->comment('Precio en centavos');
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('stock_minimo')->default(1);
            $table->boolean('activo')->default(true);
            // ID del producto en la app local — para resolver conflictos de sync
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->timestamps();

            $table->index(['negocio_id', 'activo']);
            $table->index('codigo_barras');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
