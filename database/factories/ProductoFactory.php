<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Producto>
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
        return [
            'categoria_id' => Categoria::factory(),
            'titulo' => ucfirst(fake()->words(3, true)),
            'descripcion' => fake()->sentence(12),
            'estatus' => fake()->boolean(75) ? Producto::ESTATUS_ACTIVO : Producto::ESTATUS_INACTIVO,
            'precio' => fake()->randomFloat(2, 50, 25000),
        ];
    }

    public function activo(): static
    {
        return $this->state(['estatus' => Producto::ESTATUS_ACTIVO]);
    }

    public function inactivo(): static
    {
        return $this->state(['estatus' => Producto::ESTATUS_INACTIVO]);
    }
}
