<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    use HasFactory;

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
