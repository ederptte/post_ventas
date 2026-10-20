<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venta_abonos', function (Blueprint $table) {
            $table->id();

            // Relación
            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->cascadeOnDelete();

            // Datos del pago
            $table->decimal('monto_abonado', 10, 2);
            $table->date('fecha_pago');
            $table->string('nota_referencia')->nullable(); // Ej: "Transferencia", "Efectivo"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venta_abonos');
    }
};