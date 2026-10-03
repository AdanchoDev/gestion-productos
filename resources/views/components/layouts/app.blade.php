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
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-4 py-4">
            <a href="{{ route('home') }}" class="text-lg font-semibold text-slate-900">
                {{ config('app.name') }}
            </a>

            <nav class="flex items-center gap-1 text-sm font-medium">
                <a href="{{ route('home') }}" @class([
                    'rounded-md px-3 py-2',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('home'),
                    'text-slate-600 hover:bg-slate-100' => ! request()->routeIs('home'),
                ])>Inicio</a>
                <a href="{{ route('productos.index') }}" @class([
                    'rounded-md px-3 py-2',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('productos.*'),
                    'text-slate-600 hover:bg-slate-100' => ! request()->routeIs('productos.*'),
                ])>Productos</a>
                <a href="{{ route('reportes.index') }}" @class([
                    'rounded-md px-3 py-2',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('reportes.*'),
                    'text-slate-600 hover:bg-slate-100' => ! request()->routeIs('reportes.*'),
                ])>Reportes</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-8">
        {{ $slot }}
    </main>
</body>
</html>
