<?php

namespace App\Livewire\Forms;

use App\Models\Producto;
use Livewire\Attributes\Validate;
use Livewire\Form;

class ProductoForm extends Form
{
    public ?Producto $producto = null;

    // #[Validate] sin reglas activa la validación en tiempo real usando las de rules()
    #[Validate]
    public string $titulo = '';

    #[Validate]
    public string $descripcion = '';

    #[Validate]
    public string $categoria_id = '';

    #[Validate]
    public string $estatus = Producto::ESTATUS_ACTIVO;

    #[Validate]
    public string $precio = '';

    public function rules(): array
    {
        return Producto::reglasValidacion();
    }

    public function messages(): array
    {
        return Producto::mensajesValidacion();
    }

    public function setProducto(Producto $producto): void
    {
        $this->producto = $producto;
        $this->titulo = $producto->titulo;
        $this->descripcion = (string) $producto->descripcion;
        $this->categoria_id = (string) $producto->categoria_id;
        $this->estatus = $producto->estatus;
        $this->precio = (string) $producto->precio;
    }

    public function save(): Producto
    {
        $datos = $this->validate();
        $datos['descripcion'] = $datos['descripcion'] ?: null;

        if ($this->producto) {
            $this->producto->update($datos);
            $producto = $this->producto;
        } else {
            $producto = Producto::create($datos);
        }

        $this->reset();

        return $producto;
    }
}
