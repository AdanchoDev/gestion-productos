{{-- Estado local de Alpine para la confirmación de borrado: no necesita viajar al servidor --}}
<div x-data="{ porEliminar: { abierto: false, id: null, titulo: '' } }">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Productos</h1>
            <p class="mt-1 text-sm text-slate-500">Gestión de productos con Livewire y Alpine.js.</p>
        </div>

        <button type="button" x-on:click="$wire.crear()"
            class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
            <x-icono nombre="agregar" />
            Nuevo producto
        </button>
    </div>

    {{-- Aviso: escucha el evento "notificar" que despacha el componente y se oculta solo --}}
    <div x-data="{ visible: false, mensaje: '', temporizador: null }"
        x-on:notificar.window="
            mensaje = $event.detail.mensaje;
            visible = true;
            clearTimeout(temporizador);
            temporizador = setTimeout(() => visible = false, 4000);
        "
        x-show="visible" x-transition x-cloak role="status"
        class="mb-4 flex items-center justify-between gap-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <span class="flex items-center gap-2">
            <x-icono nombre="exito" />
            <span x-text="mensaje"></span>
        </span>
        <button type="button" x-on:click="visible = false" class="hover:text-emerald-950" aria-label="Cerrar aviso">
            <x-icono nombre="cerrar" class="size-4" />
        </button>
    </div>

    <div class="mb-4 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_auto_auto_auto]">
        <div class="relative">
            <label for="buscar" class="sr-only">Buscar</label>
            <x-icono nombre="buscar" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input id="buscar" type="search" wire:model.live.debounce.300ms="buscar"
                placeholder="Buscar por título o descripción…"
                class="w-full rounded-md border border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
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

        {{-- Menú desplegable: se abre y cierra en el navegador y usa $wire para aplicar el filtro --}}
        <div class="relative" x-data="{ abierto: false }" x-on:click.outside="abierto = false" x-on:keydown.escape="abierto = false">
            <button type="button" x-on:click="abierto = ! abierto" x-bind:aria-expanded="abierto" aria-haspopup="menu"
                class="flex w-full items-center justify-between gap-2 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                <span class="flex items-center gap-2">
                    <x-icono nombre="filtro" class="size-4 text-slate-500" />
                    Filtros rápidos
                </span>
                <span class="transition-transform" x-bind:class="abierto && 'rotate-180'">
                    <x-icono nombre="chevron-abajo" class="size-4" />
                </span>
            </button>

            <div x-show="abierto" x-transition x-cloak role="menu"
                class="absolute right-0 z-20 mt-1 w-48 rounded-md border border-slate-200 bg-white py-1 text-sm shadow-lg">
                <button type="button" role="menuitem" x-on:click="$wire.set('estatus', 'activo'); abierto = false"
                    class="block w-full px-4 py-2 text-left text-slate-700 hover:bg-slate-100">Solo activos</button>
                <button type="button" role="menuitem" x-on:click="$wire.set('estatus', 'inactivo'); abierto = false"
                    class="block w-full px-4 py-2 text-left text-slate-700 hover:bg-slate-100">Solo inactivos</button>
                <button type="button" role="menuitem" x-on:click="$wire.limpiarFiltros(); abierto = false"
                    class="block w-full border-t border-slate-100 px-4 py-2 text-left text-slate-700 hover:bg-slate-100">Limpiar filtros</button>
            </div>
        </div>
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
            <tbody class="divide-y divide-slate-100">
                @forelse ($productos as $producto)
                    <tr wire:key="producto-{{ $producto->id }}">
                        <td class="px-3 py-3 sm:px-5">
                            <div class="font-medium text-slate-900">{{ $producto->titulo }}</div>
                            <div class="text-slate-500">{{ $producto->descripcion }}</div>
                            {{-- En celular las columnas Categoría y Estatus se ocultan y su contenido se muestra aquí --}}
                            <div class="mt-1.5 flex flex-wrap items-center gap-2 sm:hidden">
                                <x-estatus-badge :estatus="$producto->estatus" />
                                <span class="text-xs text-slate-500">{{ $producto->categoria->nombre }}</span>
                            </div>
                        </td>
                        <td class="hidden px-3 py-3 sm:table-cell sm:px-5">{{ $producto->categoria->nombre }}</td>
                        <td class="hidden px-3 py-3 sm:table-cell sm:px-5"><x-estatus-badge :estatus="$producto->estatus" /></td>
                        <td class="px-3 py-3 sm:px-5 text-right tabular-nums">
                            <div>${{ number_format($producto->precio, 2) }}</div>
                            @if (($precioUsd = $tipoCambio->convertirAUsd($producto->precio)) !== null)
                                <div class="text-xs text-slate-500">≈ ${{ number_format($precioUsd, 2) }} USD</div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 sm:px-5 text-right">
                            {{-- Botones solo con icono: el texto queda oculto para lectores de pantalla y como tooltip --}}
                            <button type="button" x-on:click="$wire.editar({{ $producto->id }})" title="Editar"
                                class="inline-flex rounded-md p-1.5 sm:p-2 text-indigo-600 hover:bg-indigo-50 hover:text-indigo-800">
                                <x-icono nombre="editar" />
                                <span class="sr-only">Editar {{ $producto->titulo }}</span>
                            </button>
                            <button type="button" title="Eliminar"
                                x-on:click="porEliminar = { abierto: true, id: {{ $producto->id }}, titulo: @js($producto->titulo) }"
                                class="inline-flex rounded-md p-1.5 sm:p-2 text-red-600 hover:bg-red-50 hover:text-red-800">
                                <x-icono nombre="eliminar" />
                                <span class="sr-only">Eliminar {{ $producto->titulo }}</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 sm:px-5 text-center text-slate-500">
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

    {{-- Modal del formulario: "abierto" está enlazado con la propiedad $mostrarFormulario del componente --}}
    <div x-data="{ abierto: $wire.entangle('mostrarFormulario') }"
        x-show="abierto" x-cloak
        x-on:keydown.escape.window="abierto && $wire.cancelar()"
        class="fixed inset-0 z-40 flex items-start justify-center overflow-y-auto p-4 sm:items-center"
        role="dialog" aria-modal="true" aria-labelledby="titulo-modal-formulario">
        <div x-show="abierto" x-transition.opacity class="fixed inset-0 bg-slate-900/50" x-on:click="$wire.cancelar()"></div>

        <form wire:submit="save" novalidate x-show="abierto" x-transition x-trap.noscroll="abierto"
            class="relative w-full max-w-xl rounded-lg bg-white p-6 shadow-xl">
            <h2 id="titulo-modal-formulario" class="mb-4 text-lg font-semibold text-slate-900">
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

                {{-- x-model enlaza el texto con Alpine para el contador; entangle lo mantiene sincronizado con Livewire --}}
                <div class="sm:col-span-2" x-data="{ descripcion: $wire.entangle('form.descripcion'), maximo: 1000 }">
                    <div class="mb-1 flex items-baseline justify-between">
                        <label for="descripcion" class="block text-sm font-medium text-slate-700">Descripción <span class="font-normal text-slate-400">(opcional)</span></label>
                        <span class="text-xs tabular-nums" x-bind:class="descripcion.length > maximo ? 'text-red-600' : 'text-slate-400'"
                            x-text="descripcion.length + ' / ' + maximo"></span>
                    </div>
                    <textarea id="descripcion" rows="3" x-model="descripcion" x-on:blur="$wire.$refresh()"
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

            <div class="mt-6 flex items-center justify-end gap-2">
                <button type="button" x-on:click="$wire.cancelar()"
                    class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    Cancelar
                </button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">Guardar</span>
                    <span wire:loading wire:target="save">Guardando…</span>
                </button>
            </div>
        </form>
    </div>

    {{-- Modal de confirmación de borrado: solo llama al servidor cuando se confirma --}}
    <div x-show="porEliminar.abierto" x-cloak
        x-on:keydown.escape.window="porEliminar.abierto = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="alertdialog" aria-modal="true" aria-labelledby="titulo-modal-eliminar">
        <div x-show="porEliminar.abierto" x-transition.opacity class="fixed inset-0 bg-slate-900/50" x-on:click="porEliminar.abierto = false"></div>

        <div x-show="porEliminar.abierto" x-transition x-trap.noscroll="porEliminar.abierto"
            class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <div class="flex gap-4">
                <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <x-icono nombre="advertencia" />
                </span>
                <div>
                    <h2 id="titulo-modal-eliminar" class="text-lg font-semibold text-slate-900">Eliminar producto</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        ¿Eliminar <strong class="text-slate-900" x-text="porEliminar.titulo"></strong>? Esta acción no se puede deshacer.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" x-on:click="porEliminar.abierto = false"
                    class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    Cancelar
                </button>
                <button type="button" x-on:click="$wire.delete(porEliminar.id); porEliminar.abierto = false"
                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">
                    Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>
