<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Catálogo</h1>
        <p class="mt-1 text-sm text-slate-500">Elige tus productos y agrégalos al carrito; el total se calcula al instante.</p>
    </div>

    {{-- Filtros: los resuelve Livewire en el servidor --}}
    <div class="mb-6 grid gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-[1fr_auto_auto]">
        <div class="relative">
            <label for="portal-buscar" class="sr-only">Buscar</label>
            <x-icono nombre="buscar" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
            <input id="portal-buscar" type="search" wire:model.live.debounce.300ms="buscar" placeholder="Buscar productos…"
                class="w-full rounded-md border border-slate-300 py-2 pl-9 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
        </div>

        <div>
            <label for="portal-categoria" class="sr-only">Categoría</label>
            <select id="portal-categoria" wire:model.live="categoriaId"
                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Todas las categorías</option>
                @foreach ($categorias as $categoria)
                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                @endforeach
            </select>
        </div>

        <button type="button" wire:click="limpiarFiltros"
            class="inline-flex items-center justify-center gap-2 rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
            <x-icono nombre="cerrar" class="size-4 text-slate-500" />
            Limpiar
        </button>
    </div>

    {{-- Tarjetas: las pinta Livewire; el botón solo llama al store del carrito (resources/js/carrito.js) --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" wire:loading.class="opacity-60">
        @forelse ($productos as $producto)
            <article wire:key="portal-{{ $producto->id }}" class="flex flex-col rounded-lg border border-slate-200 bg-white p-5">
                <p class="text-xs font-medium uppercase tracking-wide text-indigo-600">{{ $producto->categoria->nombre }}</p>
                <h2 class="mt-1 font-semibold text-slate-900">{{ $producto->titulo }}</h2>
                <p class="mt-1 line-clamp-2 flex-1 text-sm text-slate-500">{{ $producto->descripcion }}</p>

                <div class="mt-4 flex items-end justify-between gap-3">
                    <div>
                        <x-precio :valor="$producto->precio" class="text-lg font-semibold tabular-nums text-slate-900" />
                        @if (($precioUsd = $tipoCambio->convertirAUsd($producto->precio)) !== null)
                            <div class="text-xs tabular-nums text-slate-500">≈ <x-precio :valor="$precioUsd" moneda="USD" /></div>
                        @endif
                    </div>

                    <button type="button" x-on:click="$store.carrito.agregar(@js($producto->datosCarrito()))"
                        x-bind:class="$store.carrito.cantidadDe({{ $producto->id }}) > 0 ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                        class="inline-flex shrink-0 items-center gap-2 rounded-md px-3 py-2 text-sm font-medium text-white">
                        <x-icono nombre="carrito" class="size-4" />
                        <span x-text="$store.carrito.etiquetaBoton({{ $producto->id }})">Agregar al carrito</span>
                    </button>
                </div>
            </article>
        @empty
            <p class="col-span-full rounded-lg border border-slate-200 bg-white px-5 py-10 text-center text-slate-500">
                No encontramos productos con esa búsqueda.
            </p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $productos->links() }}
    </div>

    {{-- Aviso flotante al agregar un artículo --}}
    <div x-data="avisoCarrito" wire:ignore x-on:carrito-agregado.window="mostrar($event)"
        x-show="visible" x-transition x-cloak role="status"
        class="fixed bottom-4 left-1/2 z-50 flex -translate-x-1/2 items-center gap-2 rounded-md bg-slate-900 px-4 py-3 text-sm text-white shadow-lg">
        <x-icono nombre="exito" class="text-emerald-400" />
        <span x-text="texto"></span>
    </div>

    {{--
        Panel del carrito. Su lógica está en resources/js/carrito.js (Alpine.store y Alpine.data).
        wire:ignore evita que Livewire lo reemplace al filtrar o paginar el catálogo.
    --}}
    <div x-data="carritoPanel" wire:ignore
        x-on:abrir-carrito.window="abrir()"
        x-on:keydown.escape.window="abierto && cerrar()">
        <div x-show="abierto" x-cloak class="fixed inset-0 z-40" role="dialog" aria-modal="true" aria-labelledby="titulo-carrito">
            <div x-show="abierto" x-transition.opacity class="fixed inset-0 bg-slate-900/50" x-on:click="cerrar()"></div>

            <aside x-show="abierto" x-trap.noscroll="abierto"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                class="fixed inset-y-0 right-0 flex w-full max-w-md flex-col bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 id="titulo-carrito" class="flex items-center gap-2 text-lg font-semibold text-slate-900">
                        <x-icono nombre="carrito" class="text-indigo-600" />
                        Tu carrito
                    </h2>
                    <button type="button" x-on:click="cerrar()" class="rounded-md p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700" aria-label="Cerrar carrito">
                        <x-icono nombre="cerrar" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-5 py-4">
                    {{-- Dentro del panel se usa x-bind:hidden en lugar de x-show: es más confiable al reabrirlo --}}
                    <p x-bind:hidden="!cargando" class="py-8 text-center text-sm text-slate-500">Actualizando precios…</p>
                    <p x-bind:hidden="!error" x-text="error" role="alert" class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"></p>
                    <p x-bind:hidden="!aviso" x-text="aviso" role="status" class="mb-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"></p>

                    <p x-bind:hidden="cargando || carrito.renglones.length > 0" class="py-12 text-center text-sm text-slate-500">
                        Tu carrito está vacío.
                    </p>

                    <ul x-bind:hidden="cargando" class="divide-y divide-slate-100">
                        <template x-for="renglon in carrito.renglones" x-bind:key="renglon.id">
                            <li class="flex gap-3 py-4">
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-slate-900" x-text="renglon.titulo"></p>
                                    <p class="text-xs text-slate-500">
                                        <span x-text="renglon.categoria"></span> · <span x-text="carrito.pesos(renglon.precio_centavos)"></span> c/u
                                    </p>

                                    <div class="mt-2 flex items-center gap-1">
                                        <button type="button" x-on:click="carrito.cambiarCantidad(renglon, -1)" x-bind:disabled="carrito.cantidadValida(renglon) <= 1"
                                            class="rounded-md border border-slate-300 p-1 text-slate-600 hover:bg-slate-100 disabled:opacity-40"
                                            x-bind:aria-label="`Quitar una pieza de ${renglon.titulo}`">
                                            <x-icono nombre="menos" class="size-4" />
                                        </button>
                                        <input type="number" min="1" x-bind:max="carrito.cantidadMaxima" step="1" inputmode="numeric"
                                            x-model.number="renglon.cantidad" x-on:blur="carrito.corregirCantidad(renglon)"
                                            x-bind:aria-label="`Cantidad de ${renglon.titulo}`"
                                            class="w-14 rounded-md border border-slate-300 px-2 py-1 text-center text-sm tabular-nums focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                        <button type="button" x-on:click="carrito.cambiarCantidad(renglon, 1)" x-bind:disabled="carrito.cantidadValida(renglon) >= carrito.cantidadMaxima"
                                            class="rounded-md border border-slate-300 p-1 text-slate-600 hover:bg-slate-100 disabled:opacity-40"
                                            x-bind:aria-label="`Agregar una pieza de ${renglon.titulo}`">
                                            <x-icono nombre="agregar" class="size-4" />
                                        </button>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end justify-between">
                                    <p class="font-semibold tabular-nums text-slate-900" x-text="carrito.pesos(carrito.importe(renglon))"></p>
                                    <button type="button" x-on:click="carrito.quitar(renglon.id)"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-800">
                                        <x-icono nombre="eliminar" class="size-4" />
                                        Quitar
                                    </button>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Totales: se recalculan solos con cada cambio --}}
                <div class="border-t border-slate-200 px-5 py-4">
                    <dl class="space-y-1 text-sm">
                        <div class="flex justify-between text-slate-600">
                            <dt>Artículos</dt>
                            <dd class="tabular-nums" x-text="carrito.resumenPiezas"></dd>
                        </div>
                        <div class="flex justify-between text-base font-semibold text-slate-900">
                            <dt>Total</dt>
                            <dd class="tabular-nums"><span x-text="carrito.pesos(carrito.total)"></span> MXN</dd>
                        </div>
                        <div x-bind:hidden="carrito.totalUsd === null" class="flex justify-between text-slate-500">
                            <dt>Equivalente</dt>
                            <dd class="tabular-nums">≈ <span x-text="carrito.pesos(carrito.totalUsd)"></span> USD</dd>
                        </div>
                    </dl>

                    <button type="button" disabled
                        class="mt-4 w-full cursor-not-allowed rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white opacity-50">
                        Finalizar compra
                    </button>
                    <p class="mt-2 text-center text-xs text-slate-500">El pago se habilitará al integrar el broker bancario.</p>

                    <button type="button" x-on:click="carrito.vaciar()" x-bind:disabled="carrito.renglones.length === 0"
                        class="mt-3 w-full rounded-md px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">
                        Vaciar carrito
                    </button>
                </div>
            </aside>
        </div>
    </div>
</div>
