// Formato de importes compartido por los scripts del proyecto, igual al de las vistas Blade (<x-precio>)
const formato = new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

export function formatoPrecio(pesos) {
    return '$' + formato.format(Number(pesos));
}
