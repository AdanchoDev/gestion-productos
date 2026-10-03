<?php

namespace App\Http\Controllers;

use App\Services\TipoCambioService;
use Illuminate\Http\RedirectResponse;

class TipoCambioController extends Controller
{
    // Descarta el valor en caché y vuelve a consultar la API
    public function actualizar(TipoCambioService $tipoCambio): RedirectResponse
    {
        $tipoCambio->olvidar();

        return back()->with(
            'estado',
            $tipoCambio->obtener()
                ? 'Tipo de cambio actualizado desde la API.'
                : 'No se pudo consultar el tipo de cambio. Intenta más tarde.'
        );
    }
}
