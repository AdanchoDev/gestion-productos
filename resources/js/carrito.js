import { formatoPrecio } from './formato';

// Carrito del portal público. Toda la lógica vive aquí; las vistas solo enlazan con directivas.
//
// - Los importes se manejan en centavos enteros: en JavaScript 0.1 + 0.2 da 0.30000000000000004,
//   mientras que 10 + 20 centavos siempre da 30.
// - El carrito se guarda en el localStorage del navegador ($persist); no se envía al servidor.
// - El IVA y el pago se agregarán cuando se integre un broker bancario.

const CANTIDAD_MAXIMA = 99;

// Livewire arranca Alpine al terminar de cargar la página; este evento se dispara justo antes
document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    // Estado global: lo comparten las tarjetas, el botón del encabezado y el panel del carrito
    Alpine.store('carrito', {
        renglones: Alpine.$persist([]).as('portal_carrito'), // [{ id, titulo, categoria, precio_centavos, cantidad }]
        tipoCambio: null, // pesos por dólar; lo llena el panel al consultar los precios vigentes

        agregar(producto) {
            const renglon = this.renglones.find((r) => r.id === producto.id);

            if (renglon) {
                renglon.cantidad = Math.min(CANTIDAD_MAXIMA, this.cantidadValida(renglon) + 1);
            } else {
                const { id, titulo, categoria, precio_centavos } = producto;
                this.renglones.push({ id, titulo, categoria, precio_centavos, cantidad: 1 });
            }

            window.dispatchEvent(new CustomEvent('carrito-agregado', { detail: { titulo: producto.titulo } }));
        },

        quitar(id) {
            this.renglones = this.renglones.filter((renglon) => renglon.id !== id);
        },

        vaciar() {
            this.renglones = [];
        },

        cantidadDe(id) {
            const renglon = this.renglones.find((r) => r.id === id);
            return renglon ? this.cantidadValida(renglon) : 0;
        },

        etiquetaBoton(id) {
            const cantidad = this.cantidadDe(id);
            return cantidad > 0 ? `En el carrito (${cantidad})` : 'Agregar al carrito';
        },

        // Mientras se escribe, una cantidad inválida o vacía cuenta como 0
        cantidadValida(renglon) {
            const cantidad = Math.trunc(Number(renglon.cantidad));
            if (!Number.isFinite(cantidad) || cantidad < 0) return 0;
            return Math.min(cantidad, CANTIDAD_MAXIMA);
        },

        // Al salir del campo se corrige a un entero entre 1 y el máximo
        corregirCantidad(renglon) {
            renglon.cantidad = Math.max(1, this.cantidadValida(renglon));
        },

        cambiarCantidad(renglon, diferencia) {
            renglon.cantidad = Math.min(CANTIDAD_MAXIMA, Math.max(1, this.cantidadValida(renglon) + diferencia));
        },

        importe(renglon) {
            return renglon.precio_centavos * this.cantidadValida(renglon);
        },

        get piezas() {
            return this.renglones.reduce((suma, renglon) => suma + this.cantidadValida(renglon), 0);
        },

        get resumenPiezas() {
            return this.piezas === 1 ? '1 pieza' : `${this.piezas} piezas`;
        },

        get total() {
            return this.renglones.reduce((suma, renglon) => suma + this.importe(renglon), 0);
        },

        // Total en centavos de dólar, o null si no hay tipo de cambio
        get totalUsd() {
            return this.tipoCambio ? Math.round(this.total / this.tipoCambio) : null;
        },

        // Actualiza precios con los vigentes del servidor y quita lo que ya no está disponible.
        // Devuelve cuántos renglones se quitaron.
        sincronizar(vigentes) {
            const antes = this.renglones.length;

            this.renglones = this.renglones
                .map((renglon) => {
                    const producto = vigentes.find((p) => p.id === renglon.id);
                    return producto ? { ...renglon, ...producto, cantidad: renglon.cantidad } : null;
                })
                .filter(Boolean);

            return antes - this.renglones.length;
        },

        pesos(centavos) {
            return formatoPrecio(centavos / 100);
        },

        get cantidadMaxima() {
            return CANTIDAD_MAXIMA;
        },
    });

    // Panel lateral del carrito. Al abrirse pide a Livewire los precios vigentes,
    // porque lo guardado en el navegador puede haber cambiado desde la última visita.
    Alpine.data('carritoPanel', () => ({
        abierto: false,
        cargando: false,
        aviso: '',
        error: '',

        get carrito() {
            return Alpine.store('carrito');
        },

        async abrir() {
            this.abierto = true;
            this.aviso = '';
            this.error = '';

            if (this.carrito.renglones.length === 0) return;

            this.cargando = true;

            try {
                const datos = await this.$wire.preciosVigentes(this.carrito.renglones.map((r) => r.id));
                this.carrito.tipoCambio = datos.tipo_cambio;

                const quitados = this.carrito.sincronizar(datos.productos);
                if (quitados > 0) {
                    this.aviso = quitados === 1
                        ? 'Se quitó 1 artículo que ya no está disponible.'
                        : `Se quitaron ${quitados} artículos que ya no están disponibles.`;
                }
            } catch (e) {
                this.error = 'No se pudieron actualizar los precios. Cierra el carrito e intenta de nuevo.';
            } finally {
                this.cargando = false;
            }
        },

        cerrar() {
            this.abierto = false;
        },
    }));

    // Aviso flotante que confirma cada artículo agregado
    Alpine.data('avisoCarrito', () => ({
        visible: false,
        texto: '',
        temporizador: null,

        mostrar(evento) {
            this.texto = `«${evento.detail.titulo}» se agregó al carrito.`;
            this.visible = true;
            clearTimeout(this.temporizador);
            this.temporizador = setTimeout(() => (this.visible = false), 2500);
        },
    }));
});
