<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

    {{-- carrito.js registra el store y los componentes de Alpine antes de que Livewire lo arranque --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/carrito.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    {{-- Layout público: sin el menú de administración --}}
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <a href="{{ route('portal.index') }}" class="flex items-center gap-2 text-lg font-semibold text-slate-900">
                <x-icono nombre="tienda" class="text-indigo-600" />
                Tienda
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('home') }}" class="hidden rounded-md px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 sm:inline-flex">
                    Administración
                </a>

                {{-- El contador sale del store global del carrito; el panel escucha el evento abrir-carrito --}}
                <button type="button" x-data x-on:click="$dispatch('abrir-carrito')"
                    class="relative inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                    <x-icono nombre="carrito" />
                    Carrito
                    <span x-cloak x-bind:hidden="$store.carrito.piezas === 0" x-text="$store.carrito.piezas"
                        class="ml-1 rounded-full bg-white px-2 py-0.5 text-xs font-semibold tabular-nums text-indigo-700"></span>
                    <span class="sr-only">artículos en el carrito</span>
                </button>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
