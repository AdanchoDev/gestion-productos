<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class Producto extends Model
{
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

    // Reglas compartidas por el formulario de Livewire y por el CRUD con jQuery
    public static function reglasValidacion(): array
    {
        return [
            'titulo' => ['required', 'string', 'min:3', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'categoria_id' => ['required', Rule::exists('categorias', 'id')],
            'estatus' => ['required', Rule::in(self::ESTATUS)],
            'precio' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
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
            'precio.min' => 'El precio no puede ser negativo.',
            'precio.max' => 'El precio es demasiado alto.',
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
