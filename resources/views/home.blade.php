<x-layouts.app title="Inicio">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Catálogo de productos</h1>
        <p class="mt-1 text-sm text-slate-500">Resumen general del catálogo y sus categorías.</p>
    </div>

    <dl class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <dt class="flex items-center gap-2 text-sm text-slate-500"><x-icono nombre="categoria" class="size-4" />Categorías</dt>
            <dd class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ $indicadores['categorias'] }}</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <dt class="flex items-center gap-2 text-sm text-slate-500"><x-icono nombre="exito" class="size-4 text-emerald-600" />Productos activos</dt>
            <dd class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ $indicadores['activos'] }}</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <dt class="flex items-center gap-2 text-sm text-slate-500"><x-icono nombre="inactivo" class="size-4" />Productos inactivos</dt>
            <dd class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ $indicadores['inactivos'] }}</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <dt class="flex items-center gap-2 text-sm text-slate-500"><x-icono nombre="dinero" class="size-4" />Valor del catálogo activo</dt>
            <dd class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">${{ number_format($indicadores['valor_activo'], 2) }} MXN</dd>
            @if ($valorActivoUsd !== null)
                <dd class="mt-1 text-sm tabular-nums text-slate-500">≈ ${{ number_format($valorActivoUsd, 2) }} USD</dd>
            @endif
        </div>
    </dl>

    @if (session('estado'))
        <div role="status" class="mt-6 rounded-md border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">
            {{ session('estado') }}
        </div>
    @endif

    <section class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-lg border border-slate-200 bg-white p-5">
        <div>
            <h2 class="flex items-center gap-2 text-sm text-slate-500"><x-icono nombre="dolar" class="size-4" />Tipo de cambio USD/MXN</h2>
            @if ($tipoCambio)
                <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">1 USD = ${{ number_format($tipoCambio['tasa'], 4) }} MXN</p>
                <p class="mt-1 text-sm text-slate-500">
                    Fuente: {{ $tipoCambio['fuente'] ?? 'API externa' }} · publicado el {{ \Illuminate\Support\Carbon::parse($tipoCambio['fecha'])->format('d/m/Y') }}
                    · consultado a la API el {{ \Illuminate\Support\Carbon::parse($tipoCambio['consultado'])->format('d/m/Y H:i') }} y guardado en caché
                </p>
            @else
                <p class="mt-1 text-lg font-medium text-slate-900">No disponible</p>
                <p class="mt-1 text-sm text-slate-500">No se pudo consultar la API de tipo de cambio; los precios se muestran solo en MXN.</p>
            @endif
        </div>

        <form method="POST" action="{{ route('tipo-cambio.actualizar') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                <x-icono nombre="actualizar" class="size-4" />
                Actualizar ahora
            </button>
        </form>
    </section>

    <section class="mt-8 rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Últimos productos activos</h2>
            <a href="{{ route('categorias.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Ver categorías
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Producto</th>
                        <th class="px-5 py-3 font-medium">Categoría</th>
                        <th class="px-5 py-3 font-medium">Estatus</th>
                        <th class="px-5 py-3 text-right font-medium">Precio</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recientes as $producto)
                        <tr>
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $producto->titulo }}</td>
                            <td class="px-5 py-3">
                                <a href="{{ route('productos.index', ['categoria' => $producto->categoria_id]) }}" class="text-indigo-600 hover:text-indigo-800">
                                    {{ $producto->categoria->nombre }}
                                </a>
                            </td>
                            <td class="px-5 py-3"><x-estatus-badge :estatus="$producto->estatus" /></td>
                            <td class="px-5 py-3 text-right tabular-nums">${{ number_format($producto->precio, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-slate-500">
                                Aún no hay productos activos. Ejecuta <code class="rounded bg-slate-100 px-1">php artisan db:seed</code> para cargar datos de ejemplo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
