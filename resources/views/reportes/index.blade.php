<x-layouts.app title="Reporte por categoría">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Reporte por categoría</h1>
        <p class="mt-1 text-sm text-slate-500">Productos y valor del inventario activo de cada categoría.</p>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Categoría</th>
                    <th class="px-5 py-3 text-right font-medium">Productos</th>
                    <th class="px-5 py-3 text-right font-medium">Activos</th>
                    <th class="px-5 py-3 text-right font-medium">Valor activo (MXN)</th>
                    <th class="px-5 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($categorias as $categoria)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-900">{{ $categoria->nombre }}</div>
                            <div class="text-slate-500">{{ $categoria->descripcion }}</div>
                        </td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $categoria->productos_count }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $categoria->activos_count }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">${{ number_format($categoria->valor_activo ?? 0, 2) }}</td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('reportes.categoria', $categoria) }}" class="font-medium text-indigo-600 hover:text-indigo-800">
                                Ver detalle
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-500">No hay categorías registradas.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($categorias->isNotEmpty())
                <tfoot class="border-t border-slate-200 bg-slate-50 font-semibold text-slate-900">
                    <tr>
                        <td class="px-5 py-3">Total</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $totales['productos'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $totales['activos'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">${{ number_format($totales['valor_activo'], 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</x-layouts.app>
