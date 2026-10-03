<?php

namespace App\Livewire;

use App\Livewire\Forms\ProductoForm;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\TipoCambioService;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public ProductoForm $form;

    public bool $mostrarFormulario = false;

    // Los filtros llegan desde la URL, así que se descartan los valores que no sean válidos
    public function mount(): void
    {
        if (! in_array($this->estatus, Producto::ESTATUS)) {
            $this->estatus = '';
        }

        if (! ctype_digit($this->categoriaId)) {
            $this->categoriaId = '';
        }
    }

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

    public function crear(): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->mostrarFormulario = true;
    }

    public function editar(int $id): void
    {
        $this->form->resetValidation();
        $this->form->setProducto(Producto::findOrFail($id));
        $this->mostrarFormulario = true;
    }

    public function cancelar(): void
    {
        $this->form->reset();
        $this->form->resetValidation();
        $this->mostrarFormulario = false;
    }

    public function save(): void
    {
        $esEdicion = $this->form->producto !== null;

        $producto = $this->form->save();

        $this->mostrarFormulario = false;

        // Evento de navegador que escucha el aviso hecho con Alpine en la vista
        $this->dispatch('notificar', mensaje: $esEdicion
            ? "Producto «{$producto->titulo}» actualizado."
            : "Producto «{$producto->titulo}» creado.");
    }

    public function delete(int $id): void
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();

        // Si se estaba editando el producto eliminado, se cierra el formulario
        if ($this->form->producto?->is($producto)) {
            $this->cancelar();
        }

        // Si era el último registro de la página, se retrocede a la anterior
        if ($this->getPage() > 1 && $this->productos()->isEmpty()) {
            $this->previousPage();
        }

        $this->dispatch('notificar', mensaje: "Producto «{$producto->titulo}» eliminado.");
    }

    private function productos(): LengthAwarePaginator
    {
        return Producto::with('categoria')
            ->buscar($this->buscar)
            ->estatus($this->estatus)
            ->when($this->categoriaId, fn ($query) => $query->where('categoria_id', $this->categoriaId))
            ->latest('id')
            ->paginate(10);
    }

    public function render(): View
    {
        return view('livewire.gestion-productos', [
            'productos' => $this->productos(),
            'categorias' => Categoria::orderBy('nombre')->get(),
            'tipoCambio' => app(TipoCambioService::class),
        ]);
    }
}
