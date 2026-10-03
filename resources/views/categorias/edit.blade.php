<x-layouts.app title="Editar categoría">
    <a href="{{ route('categorias.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-800">
        <x-icono nombre="anterior" class="size-4" />
        Volver a categorías
    </a>

    <h1 class="mb-6 mt-3 text-2xl font-semibold text-slate-900">Editar categoría</h1>

    <form method="POST" action="{{ route('categorias.update', $categoria) }}" novalidate class="max-w-xl rounded-lg border border-slate-200 bg-white p-6">
        @csrf
        @method('PUT')

        @include('categorias._form')
    </form>
</x-layouts.app>
