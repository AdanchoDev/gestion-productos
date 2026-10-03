import $ from 'jquery';

$(function () {
    const $crud = $('#crud-productos');
    const urlDatos = $crud.data('url-datos');
    const urlBase = $crud.data('url-base');

    const $tabla = $('#tabla-productos');
    const $modalFormulario = $('#modal-formulario');
    const $modalEliminar = $('#modal-eliminar');
    const $form = $('#form-producto');

    // Estado de la pantalla: página actual, productos cargados y el producto por eliminar
    let pagina = 1;
    let ultimaPagina = 1;
    let productos = [];
    let idPorEliminar = null;
    let temporizadorBusqueda = null;
    let temporizadorAviso = null;

    // Laravel exige el token CSRF en POST, PUT y DELETE
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            Accept: 'application/json',
        },
    });

    /* ---------- Listado ---------- */

    function cargarProductos() {
        $tabla.addClass('opacity-60');

        $.get(urlDatos, {
            buscar: $('#filtro-buscar').val(),
            categoria: $('#filtro-categoria').val(),
            estatus: $('#filtro-estatus').val(),
            page: pagina,
        })
            .done(function (respuesta) {
                // Si se eliminó el último registro de la página, se retrocede a la anterior
                if (respuesta.productos.length === 0 && pagina > 1) {
                    pagina = respuesta.ultima_pagina;
                    cargarProductos();
                    return;
                }

                productos = respuesta.productos;
                ultimaPagina = respuesta.ultima_pagina;
                pintarTabla();
                pintarPaginacion(respuesta.total);
            })
            .fail(function () {
                mostrarAviso('No se pudo cargar el listado. Intenta de nuevo.', 'error');
            })
            .always(function () {
                $tabla.removeClass('opacity-60');
            });
    }

    function pintarTabla() {
        $tabla.empty();

        if (productos.length === 0) {
            $tabla.append(
                $('<tr>').append(
                    $('<td colspan="5" class="px-5 py-8 text-center text-slate-500">').text(
                        'No se encontraron productos con los filtros seleccionados.'
                    )
                )
            );
            return;
        }

        // Se usa .text() en lugar de concatenar HTML para que el contenido no pueda inyectar etiquetas
        $.each(productos, function (_, producto) {
            const activo = producto.estatus === 'activo';

            const $fila = $('<tr>').attr('data-id', producto.id);

            $('<td class="px-5 py-3">')
                .append($('<div class="font-medium text-slate-900">').text(producto.titulo))
                .append($('<div class="text-slate-500">').text(producto.descripcion ?? ''))
                .appendTo($fila);

            $('<td class="px-5 py-3">').text(producto.categoria).appendTo($fila);

            $('<td class="px-5 py-3">')
                .append(
                    $('<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium">')
                        .addClass(activo ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700')
                        .text(activo ? 'Activo' : 'Inactivo')
                )
                .appendTo($fila);

            $('<td class="px-5 py-3 text-right tabular-nums">').text(formatoPrecio(producto.precio)).appendTo($fila);

            $('<td class="whitespace-nowrap px-5 py-3 text-right">')
                .append($('<button type="button" class="btn-editar font-medium text-indigo-600 hover:text-indigo-800">').text('Editar'))
                .append($('<button type="button" class="btn-eliminar ml-3 font-medium text-red-600 hover:text-red-800">').text('Eliminar'))
                .appendTo($fila);

            $tabla.append($fila);
        });
    }

    function pintarPaginacion(total) {
        $('#paginacion-resumen').text(
            total === 0 ? '' : `Página ${pagina} de ${ultimaPagina} · ${total} productos`
        );
        $('#btn-anterior').prop('disabled', pagina <= 1);
        $('#btn-siguiente').prop('disabled', pagina >= ultimaPagina);
    }

    function formatoPrecio(precio) {
        return '$' + Number(precio).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    /* ---------- Filtros y paginación ---------- */

    // Espera 300 ms tras la última tecla para no lanzar una petición por cada letra
    $('#filtro-buscar').on('input', function () {
        clearTimeout(temporizadorBusqueda);
        temporizadorBusqueda = setTimeout(function () {
            pagina = 1;
            cargarProductos();
        }, 300);
    });

    $('#filtro-categoria, #filtro-estatus').on('change', function () {
        pagina = 1;
        cargarProductos();
    });

    $('#btn-limpiar').on('click', function () {
        $('#filtro-buscar, #filtro-categoria, #filtro-estatus').val('');
        pagina = 1;
        cargarProductos();
    });

    $('#btn-anterior').on('click', function () {
        pagina--;
        cargarProductos();
    });

    $('#btn-siguiente').on('click', function () {
        pagina++;
        cargarProductos();
    });

    /* ---------- Validación en el navegador ---------- */

    // Mismas reglas que el servidor; devuelve el mensaje de error o null si el campo es válido
    const validadores = {
        titulo(valor) {
            if (valor.trim() === '') return 'El título es obligatorio.';
            if (valor.trim().length < 3) return 'El título debe tener al menos 3 caracteres.';
            if (valor.length > 150) return 'El título no puede superar los 150 caracteres.';
            return null;
        },
        descripcion(valor) {
            return valor.length > 1000 ? 'La descripción no puede superar los 1000 caracteres.' : null;
        },
        categoria_id(valor) {
            return valor === '' ? 'Selecciona una categoría.' : null;
        },
        estatus(valor) {
            return valor === '' ? 'Selecciona un estatus.' : null;
        },
        precio(valor) {
            if (valor === '') return 'El precio es obligatorio.';
            if (isNaN(Number(valor))) return 'El precio debe ser un número.';
            if (Number(valor) < 0) return 'El precio no puede ser negativo.';
            return null;
        },
    };

    function mostrarError(campo, mensaje) {
        const $campo = $form.find(`[name="${campo}"]`);
        const $error = $form.find(`[data-error="${campo}"]`);

        $campo.toggleClass('border-red-400', Boolean(mensaje)).toggleClass('border-slate-300', !mensaje);
        $campo.attr('aria-invalid', mensaje ? 'true' : null);
        $error.text(mensaje ?? '').prop('hidden', !mensaje);
    }

    function validarCampo(campo) {
        const mensaje = validadores[campo]($form.find(`[name="${campo}"]`).val());
        mostrarError(campo, mensaje);
        return mensaje === null;
    }

    function validarFormulario() {
        // Se validan todos (sin cortar en el primero) para mostrar todos los errores a la vez
        return Object.keys(validadores)
            .map(validarCampo)
            .every(Boolean);
    }

    function limpiarErrores() {
        Object.keys(validadores).forEach(function (campo) {
            mostrarError(campo, null);
        });
    }

    $form.on('blur', '.campo', function () {
        validarCampo(this.name);
    });

    $('#descripcion').on('input', function () {
        const largo = this.value.length;
        $('#contador-descripcion')
            .text(`${largo} / 1000`)
            .toggleClass('text-red-600', largo > 1000)
            .toggleClass('text-slate-400', largo <= 1000);
    });

    /* ---------- Modales ---------- */

    function abrirModal($modal) {
        $modal.prop('hidden', false);
        $('body').addClass('overflow-hidden');
    }

    function cerrarModales() {
        $modalFormulario.add($modalEliminar).prop('hidden', true);
        $('body').removeClass('overflow-hidden');
        idPorEliminar = null;
    }

    $crud.on('click', '.btn-cancelar, .fondo-modal', cerrarModales);

    $(document).on('keydown', function (evento) {
        if (evento.key === 'Escape') {
            cerrarModales();
        }
    });

    /* ---------- Crear y editar ---------- */

    function abrirFormulario(producto) {
        $form[0].reset();
        limpiarErrores();

        $('#producto-id').val(producto ? producto.id : '');
        $('#modal-formulario-titulo').text(producto ? 'Editar producto' : 'Nuevo producto');

        if (producto) {
            $('#titulo').val(producto.titulo);
            $('#descripcion').val(producto.descripcion ?? '');
            $('#categoria_id').val(producto.categoria_id);
            $('#estatus').val(producto.estatus);
            $('#precio').val(producto.precio);
        }

        $('#descripcion').trigger('input');
        abrirModal($modalFormulario);
        $('#titulo').trigger('focus');
    }

    $('#btn-nuevo').on('click', function () {
        abrirFormulario(null);
    });

    // Eventos delegados: las filas se crean después de cargar la página
    $tabla.on('click', '.btn-editar', function () {
        const id = $(this).closest('tr').data('id');
        abrirFormulario(productos.find((producto) => producto.id === id));
    });

    $form.on('submit', function (evento) {
        evento.preventDefault();

        if (!validarFormulario()) {
            $form.find('[aria-invalid="true"]').first().trigger('focus');
            return;
        }

        const id = $('#producto-id').val();
        const $boton = $('#btn-guardar').prop('disabled', true).text('Guardando…');

        $.ajax({
            url: id ? `${urlBase}/${id}` : urlBase,
            method: id ? 'PUT' : 'POST',
            data: $form.serialize(),
        })
            .done(function (respuesta) {
                cerrarModales();
                mostrarAviso(respuesta.mensaje);

                // Un producto nuevo aparece al inicio del listado, así que se vuelve a la página 1
                if (!id) {
                    pagina = 1;
                }
                cargarProductos();
            })
            .fail(function (xhr) {
                // 422: el servidor rechazó los datos y devuelve los errores por campo
                if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (campo, mensajes) {
                        mostrarError(campo, mensajes[0]);
                    });
                    $form.find('[aria-invalid="true"]').first().trigger('focus');
                    return;
                }

                mostrarAviso('Ocurrió un error al guardar. Intenta de nuevo.', 'error');
            })
            .always(function () {
                $boton.prop('disabled', false).text('Guardar');
            });
    });

    /* ---------- Eliminar ---------- */

    $tabla.on('click', '.btn-eliminar', function () {
        const id = $(this).closest('tr').data('id');
        const producto = productos.find((item) => item.id === id);

        idPorEliminar = id;
        $('#eliminar-nombre').text(producto.titulo);
        abrirModal($modalEliminar);
        $('#btn-confirmar-eliminar').trigger('focus');
    });

    $('#btn-confirmar-eliminar').on('click', function () {
        const $boton = $(this).prop('disabled', true);

        $.ajax({ url: `${urlBase}/${idPorEliminar}`, method: 'DELETE' })
            .done(function (respuesta) {
                mostrarAviso(respuesta.mensaje);
                cargarProductos();
            })
            .fail(function () {
                mostrarAviso('No se pudo eliminar el producto. Intenta de nuevo.', 'error');
            })
            .always(function () {
                $boton.prop('disabled', false);
                cerrarModales();
            });
    });

    /* ---------- Avisos ---------- */

    function mostrarAviso(mensaje, tipo = 'exito') {
        const error = tipo === 'error';

        $('#aviso-texto').text(mensaje);
        $('#aviso')
            .toggleClass('border-emerald-200 bg-emerald-50 text-emerald-800', !error)
            .toggleClass('border-red-200 bg-red-50 text-red-800', error)
            .prop('hidden', false);

        clearTimeout(temporizadorAviso);
        temporizadorAviso = setTimeout(function () {
            $('#aviso').prop('hidden', true);
        }, 4000);
    }

    $('#aviso-cerrar').on('click', function () {
        $('#aviso').prop('hidden', true);
    });

    cargarProductos();
});
