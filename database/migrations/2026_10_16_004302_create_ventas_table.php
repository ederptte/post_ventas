<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            // Identificación y relaciones
            $table->string('numero_venta')->nullable()->unique();
            $table->foreignId('cliente_id')
                ->constrained('clientes')
                ->restrictOnDelete();

            // Estados
            $table->enum('estado', ['pendiente', 'realizado'])->default('pendiente');
            $table->enum('tipo_pago', ['contado', 'credito'])->default('contado');
            $table->enum('estado_pago', ['pagado', 'pendiente', 'abono_parcial'])->default('pagado');

            // Montos
            $table->decimal('total_venta', 10, 2)->default(0);
            $table->decimal('monto_abonado', 10, 2)->default(0);
            $table->decimal('saldo_pendiente', 10, 2)->default(0);

            // Fechas
            $table->timestamp('fecha_entrega')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};