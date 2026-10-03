<?php

namespace App\Livewire;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Productos')]
class GestionProductos extends Component
{
    use WithPagination;

    // #[Url] sincroniza el filtro con la barra de direcciones para poder compartir o recargar la búsqueda
    #[Url(except: '')]
    public string $buscar = '';

    #[Url(except: '')]
    public string $estatus = '';

    #[Url(as: 'categoria', except: '')]
    public string $categoriaId = '';

    // Al cambiar cualquier filtro se regresa a la página 1; si no, podría quedar en una página que ya no existe
    public function updated(string $propiedad): void
    {
        if (in_array($propiedad, ['buscar', 'estatus', 'categoriaId'])) {
            $this->resetPage();
        }
    }

    public function limpiarFiltros(): void
    {
        $this->reset('buscar', 'estatus', 'categoriaId');
        $this->resetPage();
    }

    public function render(): View
    {
        $productos = Producto::with('categoria')
            ->buscar($this->buscar)
            ->estatus($this->estatus)
            ->when($this->categoriaId, fn ($query) => $query->where('categoria_id', $this->categoriaId))
            ->latest('id')
            ->paginate(10);

        return view('livewire.gestion-productos', [
            'productos' => $productos,
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }
}
