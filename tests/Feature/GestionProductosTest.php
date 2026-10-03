<?php

namespace Tests\Feature;

use App\Livewire\GestionProductos;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GestionProductosTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagina_de_productos_carga_el_componente(): void
    {
        $this->withoutVite();

        $this->get(route('productos.index'))
            ->assertOk()
            ->assertSeeLivewire(GestionProductos::class);
    }

    public function test_el_buscador_filtra_por_titulo(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->for($categoria)->create(['titulo' => 'Teclado mecánico']);
        Producto::factory()->for($categoria)->create(['titulo' => 'Silla ergonómica']);

        Livewire::test(GestionProductos::class)
            ->set('buscar', 'Teclado')
            ->assertSee('Teclado mecánico')
            ->assertDontSee('Silla ergonómica');
    }

    public function test_los_filtros_de_estatus_y_categoria_se_combinan(): void
    {
        $oficina = Categoria::factory()->create();
        $hogar = Categoria::factory()->create();
        Producto::factory()->for($oficina)->activo()->create(['titulo' => 'Escritorio']);
        Producto::factory()->for($oficina)->inactivo()->create(['titulo' => 'Archivero']);
        Producto::factory()->for($hogar)->activo()->create(['titulo' => 'Lámpara']);

        Livewire::test(GestionProductos::class)
            ->set('categoriaId', (string) $oficina->id)
            ->set('estatus', Producto::ESTATUS_ACTIVO)
            ->assertSee('Escritorio')
            ->assertDontSee('Archivero')
            ->assertDontSee('Lámpara');
    }

    public function test_limpiar_filtros_restablece_el_listado(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->for($categoria)->create(['titulo' => 'Teclado mecánico']);
        Producto::factory()->for($categoria)->create(['titulo' => 'Silla ergonómica']);

        Livewire::test(GestionProductos::class)
            ->set('buscar', 'Teclado')
            ->call('limpiarFiltros')
            ->assertSet('buscar', '')
            ->assertSee('Silla ergonómica');
    }
}
