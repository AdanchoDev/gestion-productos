<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Producto;
use App\Services\TipoCambioService;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Portal extends Component
{
    use WithPagination;

    public string $buscar = '';

    public string $categoriaId = '';

    // Filtros que se reflejan en la barra de direcciones, para poder recargar o compartir la búsqueda.
    // except: no se escriben en la URL cuando están vacíos; as: nombre que se ve en la URL.
    protected function queryString(): array
    {
        return [
            'buscar' => ['except' => ''],
            'categoriaId' => ['as' => 'categoria', 'except' => ''],
        ];
    }

    // La categoría llega desde la URL, así que se descarta si no es un número
    public function mount(): void
    {
        if (! ctype_digit($this->categoriaId)) {
            $this->categoriaId = '';
        }
    }

    // Esto lo vimos con Oscar , es para que Livewire escuche los cambios que se hacen en el componente
    // Como en este caso en el Input del Buscador o en el Selector de Categorias xD
    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['buscar', 'categoriaId'])) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset('buscar', 'categoriaId');
        $this->resetPage();
    }

    /**
     * Lo llama el panel del carrito al abrirse. Devuelve los precios vigentes de los productos
     * que el visitante tiene guardados en su navegador; los que ya no están activos no regresan.
     */
    public function preciosVigentes(array $ids): array
    {
        // Solo se necesitan los datos que devuelve; no hace falta volver a pintar el catálogo
        $this->skipRender();

        $ids = array_slice(array_filter($ids, 'is_int'), 0, 100);

        return [
            'productos' => Producto::with('categoria')
                ->activos()
                ->whereIn('id', $ids)
                ->get()
                ->map(fn (Producto $producto) => $producto->datosCarrito()),
            'tipo_cambio' => app(TipoCambioService::class)->obtener()['tasa'] ?? null,
        ];
    }

    public function render(): View
    {
        // Solo productos activos: el portal es la vista pública del catálogo
        $productos = Producto::with('categoria')
            ->activos()
            ->buscar($this->buscar)
            ->when($this->categoriaId, fn ($query) => $query->where('categoria_id', $this->categoriaId))
            ->orderBy('titulo')
            ->paginate(12);

        return view('livewire.portal', [
            'productos' => $productos,
            'categorias' => Categoria::orderBy('nombre')->get(),
            'tipoCambio' => app(TipoCambioService::class),
        ])
            ->layout('components.layouts.portal') // layout público, sin el menú de administración
            ->title('Portal'); // título de la pestaña del navegador
    }
}
