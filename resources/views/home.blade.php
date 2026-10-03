<x-layouts.app title="Inicio">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Catálogo de productos</h1>
        <p class="mt-1 text-sm text-slate-500">Resumen general del catálogo y sus categorías.</p>
    </div>

    <dl class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <dt class="text-sm text-slate-500">Categorías</dt>
            <dd class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ $indicadores['categorias'] }}</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <dt class="text-sm text-slate-500">Productos activos</dt>
            <dd class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ $indicadores['activos'] }}</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <dt class="text-sm text-slate-500">Productos inactivos</dt>
            <dd class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ $indicadores['inactivos'] }}</dd>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <dt class="text-sm text-slate-500">Valor del catálogo activo</dt>
            <dd class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">${{ number_format($indicadores['valor_activo'], 2) }} MXN</dd>
        </div>
    </dl>

    <section class="mt-8 rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Últimos productos activos</h2>
            <a href="{{ route('reportes.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                Ver reporte por categoría
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
                                <a href="{{ route('reportes.categoria', $producto->categoria) }}" class="text-indigo-600 hover:text-indigo-800">
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
