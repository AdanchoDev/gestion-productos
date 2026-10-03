<?php

namespace App\Providers;

use App\Services\TipoCambioService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Una sola instancia por petición, para que el tipo de cambio se lea de Redis una vez
        $this->app->singleton(TipoCambioService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
