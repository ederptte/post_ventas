<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Producto::factory()->count(40)->create();

        // Un caso extremo: nombre largo, sin stock e inactivo
        Producto::factory()->create([
            'nombre_producto' => 'Producto con un nombre excesivamente largo para probar cómo se comporta la tabla en pantallas pequeñas',
            'stock' => 0,
            'activo' => false,
        ]);
    }
}
