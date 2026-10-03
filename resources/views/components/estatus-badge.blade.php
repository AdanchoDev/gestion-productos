@props(['estatus'])

<span @class([
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
    'bg-emerald-100 text-emerald-800' => $estatus === \App\Models\Producto::ESTATUS_ACTIVO,
    'bg-slate-200 text-slate-700' => $estatus !== \App\Models\Producto::ESTATUS_ACTIVO,
])>
    {{ ucfirst($estatus) }}
</span>
