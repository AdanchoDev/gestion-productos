<div>
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Productos</h1>
            <p class="mt-1 text-sm text-slate-500">Gestión de productos con Livewire: alta, edición, baja y filtros en tiempo real.</p>
        </div>

        <button type="button" wire:click="crear"
            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            Nuevo producto
        </button>
    </div>

    @if (session('mensaje'))
        <div role="status" class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('mensaje') }}
        </div>
    @endif

    @if ($mostrarFormulario)
        <form wire:submit="save" novalidate class="mb-6 rounded-lg border border-slate-200 bg-white p-5">
            <h2 class="mb-4 font-semibold text-slate-900">
                {{ $form->producto ? 'Editar producto' : 'Nuevo producto' }}
            </h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="titulo" class="mb-1 block text-sm font-medium text-slate-700">Título</label>
                    <input id="titulo" type="text" wire:model.blur="form.titulo"
                        @class([
                            'w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                            'border-red-400 focus:ring-red-200' => $errors->has('form.titulo'),
                            'border-slate-300 focus:border-indigo-500 focus:ring-indigo-200' => ! $errors->has('form.titulo'),
                        ])>
                    @error('form.titulo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="descripcion" class="mb-1 block text-sm font-medium text-slate-700">Descripción <span class="font-normal text-slate-400">(opcional)</span></label>
                    <textarea id="descripcion" rows="2" wire:model.blur="form.descripcion"
                        @class([
                            'w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                            'border-red-400 focus:ring-red-200' => $errors->has('form.descripcion'),
                            'border-slate-300 focus:border-indigo-500 focus:ring-indigo-200' => ! $errors->has('form.descripcion'),
                        ])></textarea>
                    @error('form.descripcion') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="form-categoria" class="mb-1 block text-sm font-medium text-slate-700">Categoría</label>
                    <select id="form-categoria" wire:model.blur="form.categoria_id"
                        @class([
                            'w-full rounded-md border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2',
                            'border-red-400 focus:ring-red-200' => $errors->has('form.categoria_id'),
                            'border-slate-300 focus:border-indigo-500 focus:ring-indigo-200' => ! $errors->has('form.categoria_id'),
                        ])>
                        <option value="">Selecciona…</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    @error('form.categoria_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="form-estatus" class="mb-1 block text-sm font-medium text-slate-700">Estatus</label>
                    <select id="form-estatus" wire:model.blur="form.estatus"
                        class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                        @foreach (\App\Models\Producto::ESTATUS as $opcion)
                            <option value="{{ $opcion }}">{{ ucfirst($opcion) }}</option>
                        @endforeach
                    </select>
                    @error('form.estatus') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="precio" class="mb-1 block text-sm font-medium text-slate-700">Precio (MXN)</label>
                    <input id="precio" type="number" step="0.01" min="0" inputmode="decimal" wire:model.blur="form.precio"
                        @class([
                            'w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2',
                            'border-red-400 focus:ring-red-200' => $errors->has('form.precio'),
                            'border-slate-300 focus:border-indigo-500 focus:ring-indigo-200' => ! $errors->has('form.precio'),
                        ])>
                    @error('form.precio') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-5 flex items-center gap-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">Guardar</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
                <button type="button" wire:click="cancelar"
                    class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    Cancelar
                </button>
            </div>
        </form>
    @endif

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

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Producto</th>
                    <th class="px-5 py-3 font-medium">Categoría</th>
                    <th class="px-5 py-3 font-medium">Estatus</th>
                    <th class="px-5 py-3 text-right font-medium">Precio (MXN)</th>
                    <th class="px-5 py-3 text-right font-medium">Acciones</th>
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
                        <td class="whitespace-nowrap px-5 py-3 text-right">
                            <button type="button" wire:click="editar({{ $producto->id }})"
                                class="font-medium text-indigo-600 hover:text-indigo-800">Editar</button>
                            <button type="button" wire:click="delete({{ $producto->id }})"
                                wire:confirm="¿Eliminar el producto «{{ $producto->titulo }}»? Esta acción no se puede deshacer."
                                class="ml-3 font-medium text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-500">
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
