<?php

namespace App\Livewire\Forms;

use App\Models\Producto;
use Illuminate\Validation\Rule;
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
        return [
            'titulo' => ['required', 'string', 'min:3', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'categoria_id' => ['required', Rule::exists('categorias', 'id')],
            'estatus' => ['required', Rule::in(Producto::ESTATUS)],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título es obligatorio.',
            'titulo.min' => 'El título debe tener al menos 3 caracteres.',
            'titulo.max' => 'El título no puede superar los 150 caracteres.',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',
            'categoria_id.required' => 'Selecciona una categoría.',
            'categoria_id.exists' => 'La categoría seleccionada no existe.',
            'estatus.required' => 'Selecciona un estatus.',
            'estatus.in' => 'El estatus seleccionado no es válido.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un número.',
            'precio.min' => 'El precio no puede ser negativo.',
            'precio.max' => 'El precio es demasiado alto.',
        ];
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
