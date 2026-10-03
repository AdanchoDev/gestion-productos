<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReporteController extends Controller
{
    public function index(): View
    {
        // Los conteos y la suma se resuelven con subconsultas, sin cargar los productos
        $categorias = Categoria::query()
            ->withCount([
                'productos',
                'productos as activos_count' => fn ($query) => $query->activos(),
            ])
            ->withSum(['productos as valor_activo' => fn ($query) => $query->activos()], 'precio')
            ->orderBy('nombre')
            ->get();

        $totales = [
            'productos' => $categorias->sum('productos_count'),
            'activos' => $categorias->sum('activos_count'),
            'valor_activo' => $categorias->sum('valor_activo'),
        ];

        return view('reportes.index', compact('categorias', 'totales'));
    }

    public function categoria(Request $request, Categoria $categoria): View
    {
        $filtros = $request->validate([
            'estatus' => ['nullable', Rule::in(Producto::ESTATUS)],
        ]);

        $estatus = $filtros['estatus'] ?? null;

        $productos = $categoria->productos()
            ->estatus($estatus)
            ->orderBy('titulo')
            ->paginate(10)
            ->withQueryString();

        return view('reportes.categoria', compact('categoria', 'productos', 'estatus'));
    }
}
