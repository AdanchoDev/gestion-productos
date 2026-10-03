<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\TipoCambioController;
use App\Livewire\GestionProductos;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/tipo-cambio/actualizar', [TipoCambioController::class, 'actualizar'])->name('tipo-cambio.actualizar');

Route::get('/productos', GestionProductos::class)->name('productos.index');

// CRUD en Blade + jQuery: una ruta entrega la vista y el resto responde JSON
Route::controller(ProductoController::class)
    ->prefix('productos-jquery')
    ->name('jquery.productos.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/datos', 'datos')->name('datos');
        Route::post('/', 'store')->name('store');
        Route::put('/{producto}', 'update')->name('update');
        Route::delete('/{producto}', 'destroy')->name('destroy');
    });

Route::controller(ReporteController::class)
    ->prefix('reportes')
    ->name('reportes.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/categorias/{categoria}', 'categoria')->name('categoria');
    });
