<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_barra')->unique();
            $table->string('nombre_producto');
            $table->string('presentacion', 100);
            $table->enum('unidad_medida', ['pieza', 'kilogramo'])->default('pieza');
            $table->decimal('precio_costo', 10,2);
            $table->decimal('precio_venta', 10,2);
            $table->integer('alerta_bajo_stock')->default(5);
            $table->decimal('stock', 10, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
