<x-layouts.app title="Categorías">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Categorías</h1>
            <p class="mt-1 text-sm text-slate-500">CRUD clásico con controlador y vistas Blade.</p>
        </div>

        <a href="{{ route('categorias.create') }}"
            class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            <x-icono nombre="agregar" />
            Nueva categoría
        </a>
    </div>

    @if (session('exito'))
        <div role="status" class="mb-4 flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <x-icono nombre="exito" />
            {{ session('exito') }}
        </div>
    @endif

    @if (session('error'))
        <div role="alert" class="mb-4 flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <x-icono nombre="error" />
            {{ session('error') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-3 font-medium sm:px-5">Categoría</th>
                    <th class="px-3 py-3 text-right font-medium sm:px-5">Productos</th>
                    <th class="px-3 py-3 text-right font-medium sm:px-5"><span class="sr-only sm:not-sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($categorias as $categoria)
                    <tr>
                        <td class="px-3 py-3 sm:px-5">
                            <div class="font-medium text-slate-900">{{ $categoria->nombre }}</div>
                            <div class="text-slate-500">{{ $categoria->descripcion }}</div>
                        </td>
                        <td class="px-3 py-3 text-right tabular-nums sm:px-5">
                            {{-- El listado de Livewire lee el filtro de categoría desde la URL --}}
                            <a href="{{ route('productos.index', ['categoria' => $categoria->id]) }}"
                                class="font-medium text-indigo-600 hover:text-indigo-800"
                                title="Ver los productos de {{ $categoria->nombre }}">{{ $categoria->productos_count }}</a>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-right sm:px-5">
                            <a href="{{ route('categorias.edit', $categoria) }}" title="Editar"
                                class="inline-flex rounded-md p-1.5 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800 sm:p-2">
                                <x-icono nombre="editar" />
                                <span class="sr-only">Editar {{ $categoria->nombre }}</span>
                            </a>
                            <button type="button" title="Eliminar" data-eliminar
                                data-url="{{ route('categorias.destroy', $categoria) }}"
                                data-nombre="{{ $categoria->nombre }}"
                                class="inline-flex rounded-md p-1.5 text-red-600 hover:bg-red-50 hover:text-red-800 sm:p-2">
                                <x-icono nombre="eliminar" />
                                <span class="sr-only">Eliminar {{ $categoria->nombre }}</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-3 py-8 text-center text-slate-500 sm:px-5">
                            Aún no hay categorías registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $categorias->links() }}
    </div>

    {{-- Confirmación con el elemento nativo <dialog>: un solo formulario cuya acción se asigna al abrirlo --}}
    <dialog id="dialogo-eliminar" aria-labelledby="dialogo-eliminar-titulo"
        class="m-auto w-full max-w-md rounded-lg bg-white p-6 shadow-xl backdrop:bg-slate-900/50">
        <div class="flex gap-4">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                <x-icono nombre="advertencia" />
            </span>
            <div>
                <h2 id="dialogo-eliminar-titulo" class="text-lg font-semibold text-slate-900">Eliminar categoría</h2>
                <p class="mt-1 text-sm text-slate-600">
                    ¿Eliminar <strong id="dialogo-eliminar-nombre" class="text-slate-900"></strong>? Esta acción no se puede deshacer.
                </p>
            </div>
        </div>

        <form id="form-eliminar" method="POST" class="mt-6 flex justify-end gap-2">
            @csrf
            @method('DELETE')
            <button type="button" id="dialogo-eliminar-cancelar"
                class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                Cancelar
            </button>
            <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                Sí, eliminar
            </button>
        </form>
    </dialog>

    @push('scripts')
        <script>
            const dialogo = document.getElementById('dialogo-eliminar');

            document.querySelectorAll('[data-eliminar]').forEach((boton) => {
                boton.addEventListener('click', () => {
                    document.getElementById('form-eliminar').action = boton.dataset.url;
                    document.getElementById('dialogo-eliminar-nombre').textContent = boton.dataset.nombre;
                    dialogo.showModal();
                });
            });

            document.getElementById('dialogo-eliminar-cancelar').addEventListener('click', () => dialogo.close());
        </script>
    @endpush
</x-layouts.app>
