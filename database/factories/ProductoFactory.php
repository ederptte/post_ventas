<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $costo = fake()->numberBetween(5, 200) * 100; // entre 500 y 20,000

        return [
            'codigo_barra' => fake()->unique()->ean13(),
            'nombre_producto' => fake()->randomElement([
                'Arroz Diana', 'Aceite de Motor 1L', 'Azúcar Manuelita',
                'Leche Alquería', 'Café Sello Rojo', 'Atún Van Camp',
                'Detergente Fab', 'Jabón Rey', 'Harina PAN', 'Sal Refisal',
            ]),
            'presentacion' => fake()->randomElement([
                'Bolsa x 250g', 'Botella plástica de 1 Litro',
                'Caja x 12 unidades', 'Paquete x 500g', 'Lata x 170g',
            ]),
            'unidad_medida' => fake()->randomElement(['pieza', 'kilogramo']),
            'precio_costo' => $costo,
            'precio_venta' => round($costo * fake()->randomFloat(2, 1.15, 1.6), 2),
            'alerta_bajo_stock' => fake()->numberBetween(3, 10),
            'stock' => fake()->numberBetween(0, 80),
            'activo' => fake()->boolean(85), // ~85% activos
        ];
    }
}
