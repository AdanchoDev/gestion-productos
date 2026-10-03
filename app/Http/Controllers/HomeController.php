<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Services\TipoCambioService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(TipoCambioService $servicio): View
    {
        $indicadores = [
            'categorias' => Categoria::count(),
            'activos' => Producto::activos()->count(),
            'inactivos' => Producto::estatus(Producto::ESTATUS_INACTIVO)->count(),
            'valor_activo' => Producto::activos()->sum('precio'),
        ];

        // with() trae las categorías en una sola consulta extra y evita el problema N+1
        $recientes = Producto::with('categoria')
            ->activos()
            ->latest('id')
            ->limit(5)
            ->get();

        return view('home', [
            'indicadores' => $indicadores,
            'recientes' => $recientes,
            'tipoCambio' => $servicio->obtener(),
            'valorActivoUsd' => $servicio->convertirAUsd($indicadores['valor_activo']),
        ]);
    }
}
