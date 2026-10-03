<x-layouts.app title="Productos (jQuery)">
    {{-- Las URL viajan en atributos data-* para no escribir rutas fijas dentro del JavaScript --}}
    <div id="crud-productos"
        data-url-datos="{{ route('jquery.productos.datos') }}"
        data-url-base="{{ route('jquery.productos.index') }}">

        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Productos</h1>
                <p class="mt-1 text-sm text-slate-500">El mismo CRUD en una vista Blade con jQuery y AJAX, sin recargar la página.</p>
            </div>

            <button type="button" id="btn-nuevo"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <x-icono nombre="agregar" />
                Nuevo producto
            </button>
        </div>

        <div id="aviso" role="status" hidden
            class="mb-4 flex items-center justify-between gap-4 rounded-md border px-4 py-3 text-sm">
            <span class="flex items-center gap-2">
                <x-icono nombre="exito" id="aviso-icono-exito" />
                <x-icono nombre="error" id="aviso-icono-error" />
                <span id="aviso-texto"></span>
            </span>
            <button type="button" id="aviso-cerrar" aria-label="Cerrar aviso">
                <x-icono nombre="cerrar" class="size-4" />
            </button>
        </div>

        <div class="mb-4 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_auto_auto_auto]">
            <div class="relative">
                <label for="filtro-buscar" class="sr-only">Buscar</label>
                <x-icono nombre="buscar" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
                <input id="filtro-buscar" type="search" placeholder="Buscar por título o descripción…"
                    class="w-full rounded-md border border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <div>
                <label for="filtro-categoria" class="sr-only">Categoría</label>
                <select id="filtro-categoria"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="filtro-estatus" class="sr-only">Estatus</label>
                <select id="filtro-estatus"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="">Todos los estatus</option>
                    @foreach (\App\Models\Producto::ESTATUS as $opcion)
                        <option value="{{ $opcion }}">{{ ucfirst($opcion) }}</option>
                    @endforeach
                </select>
            </div>

            <button type="button" id="btn-limpiar"
                class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                <x-icono nombre="cerrar" class="size-4 text-slate-500" />
                Limpiar
            </button>
        </div>

        <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-3 sm:px-5 font-medium">Producto</th>
                        <th class="hidden px-3 py-3 font-medium sm:table-cell sm:px-5">Categoría</th>
                        <th class="hidden px-3 py-3 font-medium sm:table-cell sm:px-5">Estatus</th>
                        <th class="px-3 py-3 text-right font-medium sm:px-5">Precio<span class="hidden sm:inline"> (MXN)</span></th>
                        <th class="px-3 py-3 text-right font-medium sm:px-5"><span class="sr-only sm:not-sr-only">Acciones</span></th>
                    </tr>
                </thead>
                {{-- Las filas las genera jQuery con la respuesta JSON --}}
                <tbody id="tabla-productos" class="divide-y divide-slate-100">
                    <tr>
                        <td colspan="5" class="px-3 py-8 sm:px-5 text-center text-slate-500">Cargando productos…</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center justify-between text-sm text-slate-600">
            <span id="paginacion-resumen"></span>
            <div class="flex gap-2">
                <button type="button" id="btn-anterior" disabled
                    class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white py-1.5 pl-2 pr-3 font-medium hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">
                    <x-icono nombre="anterior" class="size-4" />
                    Anterior
                </button>
                <button type="button" id="btn-siguiente" disabled
                    class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white py-1.5 pl-3 pr-2 font-medium hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">
                    Siguiente
                    <x-icono nombre="siguiente" class="size-4" />
                </button>
            </div>
        </div>

        <div id="modal-formulario" hidden class="fixed inset-0 z-40 flex items-start justify-center overflow-y-auto p-4 sm:items-center"
            role="dialog" aria-modal="true" aria-labelledby="modal-formulario-titulo">
            <div class="fondo-modal fixed inset-0 bg-slate-900/50"></div>

            <form id="form-producto" novalidate class="relative w-full max-w-xl rounded-lg bg-white p-6 shadow-xl">
                <h2 id="modal-formulario-titulo" class="mb-4 text-lg font-semibold text-slate-900">Nuevo producto</h2>

                <input type="hidden" id="producto-id">

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="titulo" class="mb-1 block text-sm font-medium text-slate-700">Título</label>
                        <input id="titulo" name="titulo" type="text" maxlength="150"
                            class="campo w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <p class="error mt-1 text-sm text-red-600" data-error="titulo" hidden></p>
                    </div>

                    <div class="sm:col-span-2">
                        <div class="mb-1 flex items-baseline justify-between">
                            <label for="descripcion" class="block text-sm font-medium text-slate-700">Descripción <span class="font-normal text-slate-400">(opcional)</span></label>
                            <span id="contador-descripcion" class="text-xs tabular-nums text-slate-400">0 / 1000</span>
                        </div>
                        <textarea id="descripcion" name="descripcion" rows="3"
                            class="campo w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                        <p class="error mt-1 text-sm text-red-600" data-error="descripcion" hidden></p>
                    </div>

                    <div>
                        <label for="categoria_id" class="mb-1 block text-sm font-medium text-slate-700">Categoría</label>
                        <select id="categoria_id" name="categoria_id"
                            class="campo w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            <option value="">Selecciona…</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        <p class="error mt-1 text-sm text-red-600" data-error="categoria_id" hidden></p>
                    </div>

                    <div>
                        <label for="estatus" class="mb-1 block text-sm font-medium text-slate-700">Estatus</label>
                        <select id="estatus" name="estatus"
                            class="campo w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200">
                            @foreach (\App\Models\Producto::ESTATUS as $opcion)
                                <option value="{{ $opcion }}">{{ ucfirst($opcion) }}</option>
                            @endforeach
                        </select>
                        <p class="error mt-1 text-sm text-red-600" data-error="estatus" hidden></p>
                    </div>

                    <div>
                        <label for="precio" class="mb-1 block text-sm font-medium text-slate-700">Precio (MXN)</label>
                        <input id="precio" name="precio" type="number" step="0.01" min="0" inputmode="decimal"
                            class="campo w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        <p class="error mt-1 text-sm text-red-600" data-error="precio" hidden></p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-2">
                    <button type="button" class="btn-cancelar rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button type="submit" id="btn-guardar"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                        Guardar
                    </button>
                </div>
            </form>
        </div>

        <div id="modal-eliminar" hidden class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="alertdialog" aria-modal="true" aria-labelledby="modal-eliminar-titulo">
            <div class="fondo-modal fixed inset-0 bg-slate-900/50"></div>

            <div class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <div class="flex gap-4">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                        <x-icono nombre="advertencia" />
                    </span>
                    <div>
                        <h2 id="modal-eliminar-titulo" class="text-lg font-semibold text-slate-900">Eliminar producto</h2>
                        <p class="mt-1 text-sm text-slate-600">
                            ¿Eliminar <strong id="eliminar-nombre" class="text-slate-900"></strong>? Esta acción no se puede deshacer.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" class="btn-cancelar rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </button>
                    <button type="button" id="btn-confirmar-eliminar"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-60">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Las filas las arma jQuery, así que los iconos de las acciones se dejan aquí para que el script los clone --}}
    <template id="plantilla-icono-editar"><x-icono nombre="editar" /></template>
    <template id="plantilla-icono-eliminar"><x-icono nombre="eliminar" /></template>

    @push('scripts')
        @vite('resources/js/productos-jquery.js')
    @endpush
</x-layouts.app>
