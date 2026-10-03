<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class TipoCambioService
{
    public const CLAVE_CACHE = 'tipo_cambio:usd_mxn';

    // La página consulta el servicio varias veces por petición; con esto solo se va a Redis una vez
    private ?array $enMemoria = null;

    private bool $consultado = false;

    /**
     * Devuelve ['tasa' => pesos por dólar, 'fecha' => ..., 'fuente' => ..., 'consultado' => ...] o null si no hay dato disponible.
     */
    public function obtener(): ?array
    {
        if ($this->consultado) {
            return $this->enMemoria;
        }

        $this->consultado = true;

        return $this->enMemoria = $this->desdeCache();
    }

    public function convertirAUsd(float|string|null $pesos): ?float
    {
        $tipoCambio = $this->obtener();

        if (! $tipoCambio || $pesos === null) {
            return null;
        }

        return round((float) $pesos / $tipoCambio['tasa'], 2);
    }

    // Borra el valor guardado para que la siguiente consulta vuelva a llamar a la API
    public function olvidar(): void
    {
        $this->consultado = false;
        $this->enMemoria = null;

        try {
            Cache::store(config('services.tipo_cambio.cache_store'))->forget(self::CLAVE_CACHE);
        } catch (Throwable $e) {
            Log::warning('No se pudo limpiar la caché del tipo de cambio.', ['error' => $e->getMessage()]);
        }
    }

    private function desdeCache(): ?array
    {
        try {
            $cache = Cache::store(config('services.tipo_cambio.cache_store'));

            if ($guardado = $cache->get(self::CLAVE_CACHE)) {
                return $guardado;
            }

            $tipoCambio = $this->consultarApi();

            // Solo se guardan respuestas válidas: un fallo de la API no debe quedar en caché
            if ($tipoCambio) {
                $cache->put(self::CLAVE_CACHE, $tipoCambio, config('services.tipo_cambio.cache_ttl'));
            }

            return $tipoCambio;
        } catch (Throwable $e) {
            // Si Redis no responde, la aplicación sigue funcionando consultando la API directamente
            Log::warning('Caché del tipo de cambio no disponible.', ['error' => $e->getMessage()]);

            return $this->consultarApi();
        }
    }

    // Banxico es la fuente principal; si no hay token o falla, se usa la API de respaldo
    private function consultarApi(): ?array
    {
        if (config('services.banxico.token')) {
            return $this->consultarBanxico() ?? $this->consultarRespaldo();
        }

        return $this->consultarRespaldo();
    }

    private function consultarBanxico(): ?array
    {
        try {
            $url = config('services.banxico.url').'/series/'.config('services.banxico.serie').'/datos/oportuno';

            $respuesta = Http::timeout(config('services.tipo_cambio.timeout'))
                ->acceptJson()
                ->withHeaders(['Bmx-Token' => config('services.banxico.token')])
                ->get($url);

            // "oportuno" devuelve el último dato publicado: {"bmx":{"series":[{"datos":[{"fecha":"02/10/2026","dato":"18.3350"}]}]}}
            $dato = $respuesta->json('bmx.series.0.datos.0');
            $tasa = $dato['dato'] ?? null;

            if ($respuesta->failed() || ! is_numeric($tasa) || $tasa <= 0) {
                Log::warning('Respuesta inesperada de la API de Banxico.', [
                    'status' => $respuesta->status(),
                    'mensaje' => $respuesta->json('error.mensaje'),
                ]);

                return null;
            }

            return [
                'tasa' => (float) $tasa,
                'fecha' => Carbon::createFromFormat('d/m/Y', $dato['fecha'])->toDateString(),
                'fuente' => 'Banxico (FIX)',
                'consultado' => now()->toDateTimeString(),
            ];
        } catch (Throwable $e) {
            Log::warning('No se pudo consultar la API de Banxico.', ['error' => $e->getMessage()]);

            return null;
        }
    }

    private function consultarRespaldo(): ?array
    {
        try {
            $respuesta = Http::timeout(config('services.tipo_cambio.timeout'))
                ->acceptJson()
                ->get(config('services.tipo_cambio.url'), ['base' => 'USD', 'symbols' => 'MXN']);

            $tasa = $respuesta->json('rates.MXN');

            if ($respuesta->failed() || ! is_numeric($tasa) || $tasa <= 0) {
                Log::warning('Respuesta inesperada de la API de tipo de cambio.', ['status' => $respuesta->status()]);

                return null;
            }

            return [
                'tasa' => (float) $tasa,
                'fecha' => $respuesta->json('date'),
                'fuente' => 'Frankfurter',
                'consultado' => now()->toDateTimeString(),
            ];
        } catch (Throwable $e) {
            Log::warning('No se pudo consultar la API de tipo de cambio.', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
