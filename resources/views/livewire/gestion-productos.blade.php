<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Productos</h1>
        <p class="mt-1 text-sm text-slate-500">Listado con búsqueda y filtros en tiempo real (Livewire).</p>
    </div>

    <div class="mb-4 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_auto_auto_auto]">
        <div>
            <label for="buscar" class="sr-only">Buscar</label>
            <input id="buscar" type="search" wire:model.live.debounce.300ms="buscar"
                placeholder="Buscar por título o descripción…"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>

        <div>
            <label for="categoria" class="sr-only">Categoría</label>
            <select id="categoria" wire:model.live="categoriaId"
                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Todas las categorías</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="estatus" class="sr-only">Estatus</label>
            <select id="estatus" wire:model.live="estatus"
                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Todos los estatus</option>
                @foreach (\App\Models\Producto::ESTATUS as $opcion)
                    <option value="{{ $opcion }}">{{ ucfirst($opcion) }}</option>
                @endforeach
            </select>
        </div>

        <button type="button" wire:click="limpiarFiltros"
            class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
            Limpiar
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white" wire:loading.class="opacity-60">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Producto</th>
                    <th class="px-5 py-3 font-medium">Categoría</th>
                    <th class="px-5 py-3 font-medium">Estatus</th>
                    <th class="px-5 py-3 text-right font-medium">Precio (MXN)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($productos as $producto)
                    <tr wire:key="producto-{{ $producto->id }}">
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-900">{{ $producto->titulo }}</div>
                            <div class="text-slate-500">{{ $producto->descripcion }}</div>
                        </td>
                        <td class="px-5 py-3">{{ $producto->categoria->nombre }}</td>
                        <td class="px-5 py-3"><x-estatus-badge :estatus="$producto->estatus" /></td>
                        <td class="px-5 py-3 text-right tabular-nums">${{ number_format($producto->precio, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-500">
                            No se encontraron productos con los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $productos->links() }}
    </div>
</div>
