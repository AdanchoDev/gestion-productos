<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductoRequest;
use App\Models\Categoria;
use App\Models\Producto;
use App\Services\TipoCambioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductoController extends Controller
{
    // Solo entrega la página; el listado lo pide jQuery a datos() por AJAX
    public function index(): View
    {
        return view('productos.jquery', [
            'categorias' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function datos(Request $request): JsonResponse
    {
        $filtros = $request->validate([
            'buscar' => ['nullable', 'string', 'max:100'],
            'estatus' => ['nullable', Rule::in(Producto::ESTATUS)],
            'categoria' => ['nullable', 'integer'],
        ]);

        $productos = Producto::with('categoria')
            ->buscar($filtros['buscar'] ?? null)
            ->estatus($filtros['estatus'] ?? null)
            ->when($filtros['categoria'] ?? null, fn ($query, $id) => $query->where('categoria_id', $id))
            ->latest('id')
            ->paginate(10);

        return response()->json([
            'productos' => $productos->getCollection()->map(fn (Producto $producto) => $this->formato($producto)),
            'pagina' => $productos->currentPage(),
            'ultima_pagina' => $productos->lastPage(),
            'total' => $productos->total(),
        ]);
    }

    public function store(ProductoRequest $request): JsonResponse
    {
        $producto = Producto::create($request->validated());

        return response()->json([
            'mensaje' => "Producto «{$producto->titulo}» creado.",
            'producto' => $this->formato($producto),
        ], 201);
    }

    public function update(ProductoRequest $request, Producto $producto): JsonResponse
    {
        $producto->update($request->validated());

        return response()->json([
            'mensaje' => "Producto «{$producto->titulo}» actualizado.",
            'producto' => $this->formato($producto),
        ]);
    }

    public function destroy(Producto $producto): JsonResponse
    {
        $producto->delete();

        return response()->json([
            'mensaje' => "Producto «{$producto->titulo}» eliminado.",
        ]);
    }

    private function formato(Producto $producto): array
    {
        return [
            'id' => $producto->id,
            'titulo' => $producto->titulo,
            'descripcion' => $producto->descripcion,
            'categoria_id' => $producto->categoria_id,
            'categoria' => $producto->categoria->nombre,
            'estatus' => $producto->estatus,
            'precio' => $producto->precio,
            'precio_usd' => app(TipoCambioService::class)->convertirAUsd($producto->precio),
        ];
    }
}
