<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            'Electrónica' => 'Dispositivos, gadgets y accesorios electrónicos.',
            'Hogar' => 'Muebles, decoración y artículos para el hogar.',
            'Oficina' => 'Papelería, mobiliario y equipo de oficina.',
            'Deportes' => 'Equipo y ropa para actividades deportivas.',
            'Juguetes' => 'Juguetes y juegos de mesa para todas las edades.',
        ];

        foreach ($categorias as $nombre => $descripcion) {
            // firstOrCreate permite volver a ejecutar el seeder sin chocar con el índice único
            $categoria = Categoria::firstOrCreate(['nombre' => $nombre], ['descripcion' => $descripcion]);

            Producto::factory()
                ->count(8)
                ->for($categoria)
                ->create();
        }
    }
}
