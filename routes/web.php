<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReporteController;
use App\Livewire\GestionProductos;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/productos', GestionProductos::class)->name('productos.index');

Route::controller(ReporteController::class)
    ->prefix('reportes')
    ->name('reportes.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/categorias/{categoria}', 'categoria')->name('categoria');
    });
