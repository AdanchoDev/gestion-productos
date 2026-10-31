<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\Rule;

class Producto extends Model
{
    // Eliminado lógico: delete() llena deleted_at en lugar de borrar la fila,
    // y todas las consultas del modelo omiten esos registros automáticamente
    use SoftDeletes;

    public const ESTATUS_ACTIVO = 'activo';

    public const ESTATUS_INACTIVO = 'inactivo';

    public const ESTATUS = [
        self::ESTATUS_ACTIVO,
        self::ESTATUS_INACTIVO,
    ];

    protected $table = 'productos';

    protected $fillable = [
        'categoria_id',
        'titulo',
        'descripcion',
        'estatus',
        'precio',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
        ];
    }

    /**
     * Datos que el carrito del portal necesita. El precio va en centavos enteros
     * para que el navegador sume sin errores de redondeo. Requiere la categoría cargada.
     */
    public function datosCarrito(): array
    {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'categoria' => $this->categoria->nombre,
            'precio_centavos' => (int) round($this->precio * 100),
        ];
    }

    // Reglas compartidas por el formulario de Livewire y por el CRUD con jQuery
    public static function reglasValidacion(): array
    {
        return [
            'titulo' => ['required', 'string', 'min:3', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            // withoutTrashed: no se puede asignar un producto a una categoría eliminada
            'categoria_id' => ['required', Rule::exists('categorias', 'id')->withoutTrashed()],
            'estatus' => ['required', Rule::in(self::ESTATUS)],
            // decimal:0,2 rechaza más de 2 decimales (MySQL los redondearía sin avisar) y la notación
            // científica como 1e3; gt:0 impide precios en cero; el máximo es el de la columna decimal(10,2)
            'precio' => ['required', 'numeric', 'decimal:0,2', 'gt:0', 'max:99999999.99'],
        ];
    }

    public static function mensajesValidacion(): array
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
            'precio.decimal' => 'Escribe el precio solo con números y máximo 2 decimales, por ejemplo 1234.50.',
            'precio.gt' => 'El precio debe ser mayor a $0.',
            'precio.max' => 'El precio no puede superar $99,999,999.99.',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function scopeActivos(Builder $query): void
    {
        $query->where('estatus', self::ESTATUS_ACTIVO);
    }

    // Filtros opcionales: si el valor llega vacío no se agrega el WHERE
    public function scopeEstatus(Builder $query, ?string $estatus): void
    {
        $query->when($estatus, fn (Builder $q) => $q->where('estatus', $estatus));
    }

    public function scopeBuscar(Builder $query, ?string $termino): void
    {
        $query->when($termino, function (Builder $q) use ($termino) {
            $q->where(function (Builder $q) use ($termino) {
                $q->where('titulo', 'like', "%{$termino}%")
                    ->orWhere('descripcion', 'like', "%{$termino}%");
            });
        });
    }
}
