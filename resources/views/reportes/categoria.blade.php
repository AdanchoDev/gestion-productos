<x-layouts.app :title="$categoria->nombre">
    <a href="{{ route('reportes.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
        &larr; Volver al reporte
    </a>

    <div class="mb-6 mt-3 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ $categoria->nombre }}</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $categoria->descripcion }}</p>
        </div>

        <form method="GET" action="{{ route('reportes.categoria', $categoria) }}" class="flex items-center gap-2 text-sm">
            <label for="estatus" class="text-slate-600">Estatus</label>
            <select id="estatus" name="estatus" onchange="this.form.submit()"
                class="rounded-md border border-slate-300 bg-white px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <option value="">Todos</option>
                @foreach (\App\Models\Producto::ESTATUS as $opcion)
                    <option value="{{ $opcion }}" @selected($estatus === $opcion)>{{ ucfirst($opcion) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-5 py-3 font-medium">Producto</th>
                    <th class="px-5 py-3 font-medium">Estatus</th>
                    <th class="px-5 py-3 text-right font-medium">Precio (MXN)</th>
                    <th class="px-5 py-3 font-medium">Actualizado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($productos as $producto)
                    <tr>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-900">{{ $producto->titulo }}</div>
                            <div class="text-slate-500">{{ $producto->descripcion }}</div>
                        </td>
                        <td class="px-5 py-3"><x-estatus-badge :estatus="$producto->estatus" /></td>
                        <td class="px-5 py-3 text-right tabular-nums">${{ number_format($producto->precio, 2) }}</td>
                        <td class="px-5 py-3 text-slate-500">{{ $producto->updated_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-slate-500">
                            No hay productos con el filtro seleccionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $productos->links() }}
    </div>
</x-layouts.app>
