/**
 * Formulario de contrato: equipos, entregas (Select2), guardado AJAX.
 * Select2 no debe inicializarse en pestañas ocultas: se usa dropdownParent(body) y se inicializa al mostrar la pestaña Entregas.
 */
$(document).ready(function () {
    const form = document.getElementById('formContrato');
    if (!form) return;

    const detalleEquiposBody = $('#detalleEquiposBody');
    const btnAgregarEquipo = $('#btnAgregarEquipo');
    const contenedorEntregas = $('#contenedorEntregas');
    const inputArchivos = $('#documentos');
    const listaArchivosNuevos = $('#listaArchivosNuevos');
    const listaArchivosExistentes = $('#listaArchivosExistentes');

    const tabEntregasBtn = document.querySelector('#entregas-tab');
    const tabDetallesBtn = document.querySelector('#detalles-tab');

    /** Inicializa Select2 solo en selects de entrega que aún no lo tienen. */
    const initSelect2Entregas = () => {
        $('#contenedorEntregas .select-buscador-entrega').each(function () {
            const $sel = $(this);
            if ($sel.hasClass('select2-hidden-accessible')) return;

            $sel.select2({
                theme: 'bootstrap-5',
                placeholder: 'Escriba para buscar centro...',
                width: '100%',
                dropdownParent: $('body'),
                allowClear: true
            });

            const $item = $sel.closest('.accordion-item');
            const syncHeader = () => {
                const t = $sel.find('option:selected').text().trim();
                $item.find('.nombre-institucion-header').text(t || 'Seleccione un centro educativo…');
            };

            $sel.off('change.entregaHeader select2:select.entregaHeader').on('change.entregaHeader select2:select.entregaHeader', syncHeader);
            syncHeader();
        });
    };

    // Al abrir la pestaña Entregas: inicializar buscadores (por si estaban ocultos al cargar la página)
    if (tabEntregasBtn) {
        tabEntregasBtn.addEventListener('shown.bs.tab', function () {
            initSelect2Entregas();
        });
    }

    /** Cambia a la pestaña Entregas (click nativo: no depende de window.bootstrap). */
    const abrirPestanaEntregas = () => {
        if (tabEntregasBtn) tabEntregasBtn.click();
    };

    const abrirPestanaDetalles = () => {
        if (tabDetallesBtn) tabDetallesBtn.click();
    };

    /**
     * Ejecuta fn cuando la pestaña Entregas está lista para verse (evita init antes del cambio de pestaña).
     */
    const cuandoPestanaEntregasVisible = (fn) => {
        if (!tabEntregasBtn) {
            fn();
            return;
        }
        const pane = document.getElementById('entregas');
        if (pane && pane.classList.contains('show')) {
            fn();
            return;
        }
        let ejecutado = false;
        const ejecutar = () => {
            if (ejecutado) return;
            ejecutado = true;
            clearTimeout(fallbackTimer);
            tabEntregasBtn.removeEventListener('shown.bs.tab', alMostrar);
            fn();
        };
        const alMostrar = () => ejecutar();
        const fallbackTimer = setTimeout(ejecutar, 600);
        tabEntregasBtn.addEventListener('shown.bs.tab', alMostrar);
        abrirPestanaEntregas();
    };

    const crearFilaEquipo = (item = null) => {
        const rowId = item ? item.id : Date.now();
        let opciones = '<option value="">Seleccione...</option>';
        if (typeof equiposDisponibles !== 'undefined') {
            equiposDisponibles.forEach((eq) => {
                const isSelected = item && String(item.equipo_id) === String(eq.id_equipo) ? 'selected' : '';
                opciones += `<option value="${eq.id_equipo}" ${isSelected}>${eq.nombre_equipo}</option>`;
            });
        }
        const nuevaFilaHTML = `
            <tr id="fila-equipo-${rowId}" class="fila-equipo-item">
                <td><select name="equipos[${rowId}][id]" class="form-select form-select-sm" required>${opciones}</select></td>
                <td><input type="text" name="equipos[${rowId}][marca]" class="form-control form-control-sm" value="${item ? (item.marca || '') : ''}" required></td>
                <td><input type="number" name="equipos[${rowId}][cantidad]" class="form-control form-control-sm" value="${item ? (item.cantidad || '1') : '1'}" min="1" required></td>
                <td><input type="number" step="0.01" name="equipos[${rowId}][precio]" class="form-control form-control-sm" value="${item ? (item.precio || '') : ''}" min="0" required></td>
                <td><button type="button" class="btn btn-danger btn-sm py-0 px-2 btn-remover-fila">X</button></td>
            </tr>`;
        detalleEquiposBody.append(nuevaFilaHTML);
    };

    const crearBloqueEntrega = (entrega = null) => {
        const entregaId = entrega ? entrega.id_entrega : Date.now();
        const opcionesCentros = $('#centros_educativos_hidden').html();
        let lineasEquiposHTML = '';

        $('.fila-equipo-item').each(function (index) {
            const fila = $(this);
            const selectEquipo = fila.find('select');
            const equipoId = selectEquipo.val();
            const equipoTexto = selectEquipo.find('option:selected').text();
            const cantidadTotal = fila.find('input[name*="[cantidad]"]').val();
            if (!equipoId) return;

            let cantidadEntregada = 0;
            if (entrega && entrega.detalle) {
                const itemDetalle = entrega.detalle.find((d) => String(d.id_equipo) === String(equipoId));
                if (itemDetalle) cantidadEntregada = itemDetalle.cantidad;
            }

            lineasEquiposHTML += `
                <tr>
                    <td><input type="hidden" name="entregas[${entregaId}][items][${index}][equipo_id]" value="${equipoId}">${equipoTexto}</td>
                    <td><span class="badge bg-secondary">${cantidadTotal}</span></td>
                    <td><input type="number" name="entregas[${entregaId}][items][${index}][cantidad]" class="form-control form-control-sm" value="${cantidadEntregada}" min="0" max="${cantidadTotal}"></td>
                </tr>`;
        });

        const esNueva = !entrega;
        const expandirPanel = esNueva || !!(entrega && entrega.desdeModal);
        const btnCollapsed = expandirPanel ? '' : 'collapsed';
        const collapseClasses = expandirPanel ? 'accordion-collapse collapse show' : 'accordion-collapse collapse';

        const nuevoBloqueHTML = `
            <div class="accordion-item" id="entrega-${entregaId}">
                <h2 class="accordion-header" id="heading-${entregaId}">
                    <button class="accordion-button ${btnCollapsed}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${entregaId}">
                        <span class="fw-bold me-2">Entrega para:</span>
                        <span class="nombre-institucion-header">${esNueva ? 'Seleccione un centro educativo…' : 'Cargando…'}</span>
                        <span class="badge ms-auto ${entrega && entrega.estado === 'Entregado' ? 'bg-success' : 'bg-info text-dark'} estado-header">${entrega ? entrega.estado : 'En proceso'}</span>
                    </button>
                </h2>
                <div id="collapse-${entregaId}" class="${collapseClasses}" data-bs-parent="#contenedorEntregas">
                    <div class="accordion-body">
                        <div class="d-flex justify-content-end mb-3"><button type="button" class="btn btn-outline-danger btn-sm btn-remover-entrega" title="Eliminar esta asignación">Eliminar asignación</button></div>
                        <div class="row"><div class="col-12 mb-3"><label class="form-label">Institución educativa</label><select name="entregas[${entregaId}][id_institucion]" class="form-select select-buscador-entrega" required>${opcionesCentros}</select></div></div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label">Fecha de entrega</label><input type="date" name="entregas[${entregaId}][fecha_entrega]" class="form-control form-control-sm" value="${entrega && entrega.fecha_entrega ? String(entrega.fecha_entrega).substring(0, 10) : ''}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Persona que firma</label><input type="text" name="entregas[${entregaId}][firma_responsable]" class="form-control form-control-sm" value="${entrega ? (entrega.firma_responsable || '') : ''}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Estado</label><select name="entregas[${entregaId}][estado]" class="form-select form-select-sm estado-select"><option value="En proceso" ${entrega && entrega.estado === 'En proceso' ? 'selected' : ''}>En proceso</option><option value="Entregado" ${entrega && entrega.estado === 'Entregado' ? 'selected' : ''}>Entregado</option></select></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Comentarios</label><textarea name="entregas[${entregaId}][comentarios]" class="form-control form-control-sm" rows="2">${entrega ? (entrega.comentarios || '') : ''}</textarea></div>
                        <p class="fw-bold small mb-1">Equipos para esta entrega</p>
                        ${lineasEquiposHTML === '' ? '<p class="text-warning small mb-2"><i class="fas fa-exclamation-triangle me-1"></i>Agregue líneas de equipo en la pestaña <strong>1. Detalles y Líneas</strong> para asignar cantidades.</p>' : ''}
                        <table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Equipo</th><th>Cant. contrato</th><th>Cant. a entregar</th></tr></thead><tbody>${lineasEquiposHTML || '<tr><td colspan="3" class="text-muted small">Sin líneas de equipo</td></tr>'}</tbody></table>
                    </div>
                </div>
            </div>`;

        contenedorEntregas.append(nuevoBloqueHTML);

        const itemAcordeon = $(`#entrega-${entregaId}`);
        const selectCentro = itemAcordeon.find('.select-buscador-entrega');

        if (entrega) {
            selectCentro.val(entrega.id_institucion);
            const textoInstitucion = selectCentro.find('option:selected').text();
            itemAcordeon.find('.nombre-institucion-header').text(textoInstitucion.trim() || '—');
        }

        itemAcordeon.find('.estado-select').on('change', function () {
            const headerBadge = itemAcordeon.find('.estado-header');
            headerBadge.text(this.value);
            headerBadge.removeClass('bg-success bg-info text-dark').addClass(this.value === 'Entregado' ? 'bg-success' : 'bg-info text-dark');
        });

        // Solo inicializar Select2 si la pestaña Entregas está visible (evita fallos en pestaña oculta)
        if ($('#entregas').hasClass('show')) {
            initSelect2Entregas();
        }
    };

    btnAgregarEquipo.on('click', () => crearFilaEquipo());

    const modalNuevaEntregaEl = document.getElementById('modalNuevaEntrega');

    const abrirModalNuevaEntrega = function () {
        const tb = $('#modalNuevaEntregaEquipos');
        tb.empty();
        const sinMsg = $('#modalNuevaEntregaSinEquipos');
        const btnOk = $('#btnModalConfirmarEntrega');
        let tieneLineas = false;

        $('.fila-equipo-item').each(function () {
            const fila = $(this);
            const selectEquipo = fila.find('select');
            const equipoId = selectEquipo.val();
            if (!equipoId) return;
            tieneLineas = true;
            const equipoTexto = $('<div>').text(selectEquipo.find('option:selected').text()).html();
            const cantidadTotal = fila.find('input[name*="[cantidad]"]').val();
            tb.append(
                `<tr data-equipo-id="${equipoId}">
                    <td>${equipoTexto}</td>
                    <td><span class="badge bg-secondary">${cantidadTotal}</span></td>
                    <td style="max-width:120px"><input type="number" class="form-control form-control-sm input-modal-cant-entrega" min="0" max="${cantidadTotal}" value="0"></td>
                </tr>`
            );
        });

        if (!tieneLineas) {
            sinMsg.removeClass('d-none');
            btnOk.prop('disabled', true);
        } else {
            sinMsg.addClass('d-none');
            btnOk.prop('disabled', false);
        }

        $('#modal_entrega_fecha').val('');
        $('#modal_entrega_firma').val('');
        $('#modal_entrega_estado').val('En proceso');
        $('#modal_entrega_comentarios').val('');

        const $inst = $('#modal_entrega_institucion');
        if ($inst.hasClass('select2-hidden-accessible')) {
            $inst.select2('destroy');
        }
        $inst.val('');

        if (modalNuevaEntregaEl && window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalNuevaEntregaEl).show();
        }

        setTimeout(() => {
            if (!$inst.hasClass('select2-hidden-accessible')) {
                $inst.select2({
                    theme: 'bootstrap-5',
                    placeholder: 'Buscar centro educativo…',
                    width: '100%',
                    dropdownParent: $('#modalNuevaEntrega'),
                    allowClear: true
                });
            }
        }, 300);
    };

    if (modalNuevaEntregaEl) {
        modalNuevaEntregaEl.addEventListener('hidden.bs.modal', function () {
            const $inst = $('#modal_entrega_institucion');
            if ($inst.hasClass('select2-hidden-accessible')) {
                $inst.select2('destroy');
            }
        });
    }

    $('#btnModalConfirmarEntrega').on('click', function () {
        const idInst = $('#modal_entrega_institucion').val();
        if (!idInst) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Seleccione un centro educativo', text: 'Debe elegir la institución que recibirá la entrega.' });
            } else {
                window.alert('Seleccione un centro educativo.');
            }
            return;
        }

        const detalle = [];
        $('#modalNuevaEntregaEquipos tr').each(function () {
            const equipoId = $(this).data('equipo-id');
            const cant = parseInt($(this).find('.input-modal-cant-entrega').val(), 10) || 0;
            detalle.push({ id_equipo: equipoId, cantidad: cant });
        });

        const entregaId = Date.now();
        const entrega = {
            id_entrega: entregaId,
            id_institucion: idInst,
            fecha_entrega: $('#modal_entrega_fecha').val() || null,
            firma_responsable: $('#modal_entrega_firma').val() || '',
            estado: $('#modal_entrega_estado').val() || 'En proceso',
            comentarios: $('#modal_entrega_comentarios').val() || '',
            detalle: detalle,
            desdeModal: true
        };

        const $inst = $('#modal_entrega_institucion');
        if ($inst.hasClass('select2-hidden-accessible')) {
            $inst.select2('destroy');
        }
        if (modalNuevaEntregaEl && window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalNuevaEntregaEl).hide();
        }

        cuandoPestanaEntregasVisible(() => {
            crearBloqueEntrega(entrega);
            initSelect2Entregas();
            const agregado = document.getElementById('entrega-' + entregaId);
            if (agregado) {
                agregado.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        });
    });

    const clickAsignarEntrega = function () {
        const filasEq = $('.fila-equipo-item').length;
        if (filasEq === 0) {
            const abrirModal = () => abrirModalNuevaEntrega();
            if (typeof Swal === 'undefined') {
                if (window.confirm('No hay líneas de equipo. ¿Ir a Detalles?')) {
                    abrirPestanaDetalles();
                } else {
                    abrirModal();
                }
                return;
            }
            Swal.fire({
                icon: 'info',
                title: 'Líneas de equipo',
                html: 'Para asignar cantidades por centro, agregue al menos una <strong>línea de equipo</strong> en <strong>Detalles y equipos</strong>.',
                showCancelButton: true,
                confirmButtonText: 'Ir a Detalles',
                cancelButtonText: 'Abrir asistente igual',
                reverseButtons: true
            }).then((r) => {
                if (r.isConfirmed) {
                    abrirPestanaDetalles();
                } else {
                    abrirModal();
                }
            });
            return;
        }

        abrirModalNuevaEntrega();
    };

    // Delegación: el botón está en una pestaña oculta a veces; evita fallos de binding directo
    $(form).on('click', '#btnAgregarEntrega', function (e) {
        e.preventDefault();
        clickAsignarEntrega();
    });

    contenedorEntregas.on('click', '.btn-remover-entrega', function () {
        $(this).closest('.accordion-item').remove();
    });

    detalleEquiposBody.on('click', '.btn-remover-fila', function () {
        $(this).closest('tr').remove();
    });

    inputArchivos.on('change', function () {
        listaArchivosNuevos.html('');
        if (this.files.length > 0) listaArchivosNuevos.html('<h6 class="mt-3">Archivos a subir:</h6>');
        Array.from(this.files).forEach((file, index) => {
            const archivoHTML = `
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text"><i class="fas fa-file-arrow-up me-2"></i><span class="text-truncate" style="max-width: 200px;">${file.name}</span></span>
                    <input type="text" name="descripcion_archivo[${index}]" class="form-control" placeholder="Descripción del archivo..." required>
                </div>`;
            listaArchivosNuevos.append(archivoHTML);
        });
    });

    if (listaArchivosExistentes && listaArchivosExistentes.length) {
        listaArchivosExistentes.on('click', '.btn-eliminar-documento', function () {
            const id_documento = $(this).data('id');
            if (id_documento) {
                Swal.fire({ title: '¿Eliminar archivo?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí, eliminar' }).then((r) => {
                    if (!r.isConfirmed) return;
                    $.post('../app/ajax/contratos_ajax.php', { action: 'eliminar_documento', id_documento }, function (data) {
                        if (data.success) {
                            $(`#doc-${id_documento}`).remove();
                            Swal.fire('Listo', 'Archivo eliminado.', 'success');
                        } else Swal.fire('Error', data.message || 'No se pudo eliminar.', 'error');
                    }, 'json').fail(() => Swal.fire('Error', 'Sin conexión.', 'error'));
                });
            }
        });
    }

    $(form).on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const action = $('#id_contrato').val() ? 'actualizar' : 'guardar';
        formData.append('action', action);

        Swal.fire({ title: 'Guardando contrato', text: 'Por favor espere…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: '../app/ajax/contratos_ajax.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    Swal.fire('¡Listo!', data.message, 'success').then(() => {
                        window.location.href = 'gestion_contratos.php';
                    });
                } else Swal.fire('Error', data.message || 'Ocurrió un error.', 'error');
            },
            error: function () {
                Swal.fire('Error de conexión', 'No se pudo comunicar con el servidor.', 'error');
            }
        });
    });

    const inicializarFormulario = () => {
        if (modoFormulario === 'editar') {
            if (equiposContrato && equiposContrato.length > 0) {
                equiposContrato.forEach((item) => crearFilaEquipo(item));
            }
            if (entregasContrato && entregasContrato.length > 0) {
                entregasContrato.forEach((entrega) => crearBloqueEntrega(entrega));
            }
        } else if (modoFormulario === 'crear') {
            crearFilaEquipo();
        }
    };

    inicializarFormulario();
});
