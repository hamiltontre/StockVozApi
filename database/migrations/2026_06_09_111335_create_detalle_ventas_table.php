<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            // Producto referenciado — nullable porque puede ser borrado
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            // Snapshot para preservar el histórico aunque el producto cambie/desaparezca
            $table->string('nombre_producto', 100);
            $table->unsignedInteger('cantidad');
            $table->unsignedBigInteger('precio_unitario')->comment('Precio al momento de la venta, en centavos');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
