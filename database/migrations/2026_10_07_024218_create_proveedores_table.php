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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_proveedor');
            $table->string('id_tributaria', 30)->unique(); // Cambiado a string para evitar desbordamientos y soportar formatos con guiones/ceros
            $table->boolean('es_mayorista')->default(false);
            $table->json('cuando_viene')->nullable();
            $table->enum('frecuencia_visita', ["semanal", "quincenal", "mensual"])->nullable(); // Opcional por si acaso
            $table->enum('cuando_entrega', ['mismo_dia', 'dia_siguiente', 'otro_dia'])->nullable(); // Opcional por si acaso
            $table->string('nombre_vendedor', 100)->nullable();
            $table->string('telefono_vendedor', 30)->nullable(); // string por los códigos de país (+) o extensiones
            $table->boolean('activo')->default(true)->index(); // Índice para consultas rápidas
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};