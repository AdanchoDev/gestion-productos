{{-- Importe con formato único en todas las vistas. Uso: <x-precio :valor="$producto->precio" moneda="MXN" /> --}}
@props(['valor', 'moneda' => null])

<span {{ $attributes }}>${{ number_format((float) $valor, 2) }}@if ($moneda) {{ $moneda }}@endif</span>
