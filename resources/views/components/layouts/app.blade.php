<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        {{-- En pantallas chicas el menú pasa debajo del título y sus enlaces se acomodan en dos renglones --}}
        <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:gap-6 sm:py-4">
            <a href="{{ route('home') }}" class="text-lg font-semibold text-slate-900">
                {{ config('app.name') }}
            </a>

            <nav class="-mx-3 flex flex-wrap items-center gap-1 text-sm font-medium sm:mx-0" aria-label="Principal">
                <a href="{{ route('home') }}" @class([
                    'inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-md px-3 py-2',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('home'),
                    'text-slate-600 hover:bg-slate-100' => ! request()->routeIs('home'),
                ])><x-icono nombre="inicio" class="size-4" />Inicio</a>
                <a href="{{ route('productos.index') }}" @class([
                    'inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-md px-3 py-2',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('productos.*'),
                    'text-slate-600 hover:bg-slate-100' => ! request()->routeIs('productos.*'),
                ])><x-icono nombre="livewire" class="size-4" />Productos (Livewire)</a>
                <a href="{{ route('jquery.productos.index') }}" @class([
                    'inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-md px-3 py-2',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('jquery.productos.*'),
                    'text-slate-600 hover:bg-slate-100' => ! request()->routeIs('jquery.productos.*'),
                ])><x-icono nombre="jquery" class="size-4" />Productos (jQuery)</a>
                <a href="{{ route('categorias.index') }}" @class([
                    'inline-flex shrink-0 items-center gap-2 whitespace-nowrap rounded-md px-3 py-2',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('categorias.*'),
                    'text-slate-600 hover:bg-slate-100' => ! request()->routeIs('categorias.*'),
                ])><x-icono nombre="categoria" class="size-4" />Categorías</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        {{ $slot }}
    </main>

    @stack('scripts')
</body>
</html>
