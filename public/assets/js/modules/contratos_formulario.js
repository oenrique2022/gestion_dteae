/**
 * Formulario de contrato: equipos, entregas (tabla + modal), guardado AJAX.
 * Las entregas viven en `entregasContrato` y se envían con campos ocultos (#entregasHiddenFields).
 */
$(document).ready(function () {
    const form = document.getElementById('formContrato');
    if (!form) return;

    /** Siempre el mismo array que el JSON del servidor (evita que `let` del inline no sea visible aquí). */
    const listaEntregas = () => {
        if (!Array.isArray(window.entregasContrato)) {
            window.entregasContrato = [];
        }
        if (window.entregasContrato.length === 0) {
            const el = document.getElementById('entregas-contrato-data');
            if (el && el.textContent && el.textContent.trim()) {
                try {
                    const p = JSON.parse(el.textContent);
                    if (Array.isArray(p) && p.length) {
                        window.entregasContrato = p;
                    }
                } catch (e) {
                    /* ignore */
                }
            }
        }
        return window.entregasContrato;
    };
    listaEntregas();

    const detalleEquiposBody = $('#detalleEquiposBody');
    const btnAgregarEquipo = $('#btnAgregarEquipo');
    const entregasHiddenFields = $('#entregasHiddenFields');
    const inputArchivos = $('#documentos');
    const listaArchivosNuevos = $('#listaArchivosNuevos');
    const listaArchivosExistentes = $('#listaArchivosExistentes');

    const tabEntregasBtn = document.querySelector('#entregas-tab');
    const tabDetallesBtn = document.querySelector('#detalles-tab');

    const getBootstrapModalCtor = () => {
        const g = typeof globalThis !== 'undefined' ? globalThis : window;
        if (g.bootstrap && g.bootstrap.Modal) {
            return g.bootstrap.Modal;
        }
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            return bootstrap.Modal;
        }
        return null;
    };

    const mostrarModalBootstrap = (modalEl) => {
        if (!modalEl) return;
        const Ctor = getBootstrapModalCtor();
        if (Ctor) {
            try {
                Ctor.getOrCreateInstance(modalEl).show();
                return;
            } catch (e) {
                /* fallback abajo */
            }
        }
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
        modalEl.setAttribute('aria-modal', 'true');
        document.body.classList.add('modal-open');
        document.body.style.overflow = 'hidden';
        let b = document.getElementById('backdrop-contrato-modal-fallback');
        if (!b) {
            b = document.createElement('div');
            b.id = 'backdrop-contrato-modal-fallback';
            b.className = 'modal-backdrop fade show';
            document.body.appendChild(b);
        }
    };

    const ocultarModalBootstrap = (modalEl) => {
        if (!modalEl) return;
        const Ctor = getBootstrapModalCtor();
        if (Ctor) {
            try {
                Ctor.getOrCreateInstance(modalEl).hide();
                return;
            } catch (e) {
                /* fallback */
            }
        }
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        modalEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        const b = document.getElementById('backdrop-contrato-modal-fallback');
        if (b) b.remove();
    };

    const escHtml = (s) => {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    };

    const textoCentroPorId = (idInst) => {
        if (idInst == null || idInst === '') return '—';
        const val = String(idInst);
        const opt = $('#centros_educativos_hidden option').filter(function () {
            return String($(this).val()) === val;
        }).first();
        return opt.length ? opt.text().trim() : '—';
    };

    const idEntregaDeObj = (entrega) =>
        entrega == null ? null : entrega.id_entrega ?? entrega.ID_ENTREGA ?? entrega.id;

    /** Entregas nuevas sin guardar usan `Date.now()` (~1.7e12); los id de BD son inferiores a este umbral. */
    const LIMITE_ID_ENTREGA_TEMPORAL = 1e12;
    const idEntregaEsPersistida = (raw) => {
        const n = Number(raw);
        return Number.isFinite(n) && n > 0 && n < LIMITE_ID_ENTREGA_TEMPORAL;
    };

    const actualizarBotonActaPdf = (entregaIdRaw) => {
        const el = document.getElementById('btnActaRecepcionPdf');
        if (!el) return;
        if (!idEntregaEsPersistida(entregaIdRaw)) {
            el.classList.add('d-none');
            el.setAttribute('href', '#');
            return;
        }
        el.setAttribute(
            'href',
            `acta_entrega_pdf.php?id_entrega=${encodeURIComponent(String(Math.trunc(Number(entregaIdRaw))))}`
        );
        el.classList.remove('d-none');
    };

    const cantidadDetalleParaEquipo = (entrega, equipoId) => {
        if (!entrega || !entrega.detalle || !equipoId) return 0;
        const d = entrega.detalle.find((x) => String(x.id_equipo ?? x.equipo_id) === String(equipoId));
        return d ? parseInt(d.cantidad, 10) || 0 : 0;
    };

    /** Rellena la tabla solo desde la lista en memoria (misma referencia que `window.entregasContrato`). */
    const refrescarTablaEntregas = () => {
        const tb = $('#tablaResumenEntregasBody');
        const vacia = $('#tablaEntregasVacia');
        const badge = $('#badgeNumEntregas');
        if (!tb.length) return;
        tb.empty();
        let n = 0;
        const ec = listaEntregas();
        if (ec && ec.length > 0) {
            ec.forEach((entrega) => {
                const rawId = idEntregaDeObj(entrega);
                if (rawId == null || rawId === '') return;
                const sid = String(rawId);
                n += 1;
                const centro = textoCentroPorId(entrega.id_institucion ?? entrega.ID_INSTITUCION);
                const fecha =
                    entrega.fecha_entrega != null && String(entrega.fecha_entrega).trim() !== ''
                        ? String(entrega.fecha_entrega).substring(0, 10)
                        : '—';
                const estado = (entrega.estado && String(entrega.estado).trim()) || '—';
                const pdfBtn = idEntregaEsPersistida(rawId)
                    ? `<a class="btn btn-sm btn-outline-secondary" href="acta_entrega_pdf.php?id_entrega=${encodeURIComponent(
                          String(Math.trunc(Number(rawId)))
                      )}" target="_blank" rel="noopener" title="Acta de recepción (PDF)"><i class="fas fa-file-pdf"></i></a>`
                    : '';
                tb.append(
                    `<tr>
                    <td>${n}</td>
                    <td>${escHtml(centro)}</td>
                    <td>${escHtml(fecha)}</td>
                    <td><span class="badge ${estado === 'Entregado' ? 'bg-success' : 'bg-info text-dark'}">${escHtml(estado)}</span></td>
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-detalle-entrega" data-entrega-id="${escHtml(sid)}"><i class="fas fa-edit me-1"></i>Detalles</button>
                        ${pdfBtn}
                        <button type="button" class="btn btn-sm btn-outline-danger btn-quitar-entrega-resumen" data-entrega-id="${escHtml(sid)}" title="Quitar esta entrega del borrador"><i class="fas fa-times"></i></button>
                    </td>
                </tr>`
                );
            });
        }
        if (badge.length) badge.text(String(n));
        if (vacia.length) vacia.toggleClass('d-none', n > 0);
    };

    /** Genera inputs ocultos entregas[EID][...] para que PHP reciba el mismo POST que antes. */
    const sincronizarCamposOcultosEntregas = () => {
        entregasHiddenFields.empty();
        const ec = listaEntregas();
        if (!ec.length) return;

        ec.forEach((entrega) => {
            const eid = idEntregaDeObj(entrega);
            if (eid == null || eid === '') return;
            const prefix = `entregas[${eid}]`;
            const append = (suffix, val) => {
                $('<input>', {
                    type: 'hidden',
                    name: `${prefix}${suffix}`,
                    value: val == null ? '' : String(val)
                }).appendTo(entregasHiddenFields);
            };
            append('[id_institucion]', entrega.id_institucion ?? entrega.ID_INSTITUCION ?? '');
            append('[fecha_entrega]', entrega.fecha_entrega != null ? String(entrega.fecha_entrega).substring(0, 10) : '');
            append('[firma_responsable]', entrega.firma_responsable ?? '');
            append('[estado]', entrega.estado ?? 'En proceso');
            append('[comentarios]', entrega.comentarios ?? '');

            const det = entrega.detalle || [];
            let idx = 0;
            det.forEach((d) => {
                const eqId = d.id_equipo ?? d.equipo_id;
                const cant = parseInt(d.cantidad, 10) || 0;
                if (eqId == null || eqId === '') return;
                append(`[items][${idx}][equipo_id]`, eqId);
                append(`[items][${idx}][cantidad]`, cant);
                idx += 1;
            });
        });
    };

    const abrirPestanaEntregas = () => {
        if (tabEntregasBtn) tabEntregasBtn.click();
    };

    const abrirPestanaDetalles = () => {
        if (tabDetallesBtn) tabDetallesBtn.click();
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

    btnAgregarEquipo.on('click', () => crearFilaEquipo());

    const urlAjaxEquipos =
        typeof window.URL_AJAX_EQUIPOS === 'string' ? window.URL_AJAX_EQUIPOS : '../app/ajax/equipos_ajax.php';

    const registrarEquipoEnCatalogoLocal = (id, nombre) => {
        if (typeof equiposDisponibles === 'undefined' || !Array.isArray(equiposDisponibles)) return;
        const idStr = String(id);
        if (equiposDisponibles.some((eq) => String(eq.id_equipo) === idStr)) return;
        equiposDisponibles.push({ id_equipo: id, nombre_equipo: nombre });
        equiposDisponibles.sort((a, b) =>
            String(a.nombre_equipo || '').localeCompare(String(b.nombre_equipo || ''), 'es', { sensitivity: 'base' })
        );
    };

    const anadirOpcionEquipoASelect = ($select, id, nombre) => {
        const idStr = String(id);
        if (
            $select
                .find('option')
                .filter(function () {
                    return String($(this).val()) === idStr;
                }).length
        ) {
            return;
        }
        const label = $('<div>').text(nombre).html();
        $select.append(`<option value="${idStr.replace(/"/g, '&quot;')}">${label}</option>`);
    };

    const aplicarNuevoEquipoATodasLasFilas = (id, nombre) => {
        $('#detalleEquiposBody .fila-equipo-item select[name*="[id]"]').each(function () {
            anadirOpcionEquipoASelect($(this), id, nombre);
        });
    };

    const modalCatalogoEl = document.getElementById('modalCatalogoEquipo');
    if (modalCatalogoEl) {
        modalCatalogoEl.addEventListener('show.bs.modal', function () {
            $('#catalogo_equipo_nombre').val('').prop('disabled', false);
            $('#catalogo_equipo_codigo').val('');
            const $tipo = $('#catalogo_equipo_tipo');
            if ($tipo.find('option').length > 1) {
                $tipo.prop('selectedIndex', 0);
            }
            setTimeout(() => {
                const n = document.getElementById('catalogo_equipo_nombre');
                if (n) n.focus();
            }, 200);
        });
    }

    $('#formCatalogoEquipo').on('submit', function (e) {
        e.preventDefault();
        const nombre = ($('#catalogo_equipo_nombre').val() || '').trim();
        const codigo = ($('#catalogo_equipo_codigo').val() || '').trim();
        const idTipo = $('#catalogo_equipo_tipo').val();
        if (!nombre || nombre.length < 2) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Nombre requerido', text: 'Escriba al menos 2 caracteres.' });
            } else {
                window.alert('Indique el nombre del equipo.');
            }
            return;
        }
        if (!idTipo) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Tipo requerido', text: 'Seleccione un tipo de equipo.' });
            } else {
                window.alert('Seleccione un tipo de equipo.');
            }
            return;
        }

        const fd = new FormData();
        fd.append('action', 'crear_rapido');
        fd.append('nombre_equipo', nombre);
        fd.append('codigo_equipo', codigo);
        fd.append('id_tipo_equipo', idTipo);

        const $btn = $('#btnGuardarCatalogoEquipo').prop('disabled', true);

        fetch(urlAjaxEquipos, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then((r) => r.json())
            .then((data) => {
                if (!data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'No se guardó', text: data.message || 'Error desconocido' });
                    } else {
                        window.alert(data.message || 'Error');
                    }
                    return;
                }
                const row = data.data || {};
                const nuevoId = row.id_equipo;
                const nom = row.nombre_equipo || nombre;
                registrarEquipoEnCatalogoLocal(nuevoId, nom);
                aplicarNuevoEquipoATodasLasFilas(nuevoId, nom);

                const $ultimaFila = $('#detalleEquiposBody tr.fila-equipo-item').last();
                if ($ultimaFila.length) {
                    $ultimaFila.find('select[name*="[id]"]').val(String(nuevoId));
                }

                if (modalCatalogoEl) {
                    ocultarModalBootstrap(modalCatalogoEl);
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Equipo añadido',
                        text: 'Ya puede usarlo en el desplegable Equipo.',
                        timer: 2200,
                        showConfirmButton: true
                    });
                }
            })
            .catch(() => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo contactar al servidor.' });
                } else {
                    window.alert('Error de red');
                }
            })
            .finally(() => $btn.prop('disabled', false));
    });

    const modalNuevaEntregaEl = document.getElementById('modalNuevaEntrega');

    const aplicarCambiosModalAEntrega = function (entregaIdRaw) {
        const entregaId = String(entregaIdRaw);
        const idInst = $('#modal_entrega_institucion').val();
        if (!idInst) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'warning', title: 'Seleccione un centro educativo' });
            } else {
                window.alert('Seleccione un centro educativo.');
            }
            return;
        }
        const ec = listaEntregas();
        const ix = ec.findIndex((x) => String(idEntregaDeObj(x)) === entregaId);
        if (ix === -1) return;

        const detalle = [];
        $('#modalNuevaEntregaEquipos tr').each(function () {
            const equipoId = $(this).data('equipo-id');
            const cant = parseInt($(this).find('.input-modal-cant-entrega').val(), 10) || 0;
            if (equipoId != null && equipoId !== '') {
                detalle.push({ id_equipo: equipoId, cantidad: cant });
            }
        });

        ec[ix] = {
            ...ec[ix],
            id_institucion: idInst,
            fecha_entrega: $('#modal_entrega_fecha').val() || null,
            firma_responsable: $('#modal_entrega_firma').val() || '',
            estado: $('#modal_entrega_estado').val() || 'En proceso',
            comentarios: $('#modal_entrega_comentarios').val() || '',
            detalle
        };

        const $inst = $('#modal_entrega_institucion');
        if ($inst.hasClass('select2-hidden-accessible')) {
            $inst.select2('destroy');
        }
        ocultarModalBootstrap(modalNuevaEntregaEl);
        refrescarTablaEntregas();
    };

    const abrirModalEditarEntrega = function (entregaIdRaw) {
        const entregaId = String(entregaIdRaw);
        const entrega = listaEntregas().find((x) => String(idEntregaDeObj(x)) === entregaId);
        if (!entrega) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'No se encontró la entrega', text: 'Recargue la página e intente de nuevo.' });
            } else {
                window.alert('No se encontró la entrega. Recargue la página.');
            }
            return;
        }

        $('#modal_entrega_modo').val('editar');
        $('#modal_entrega_editando_id').val(entregaId);
        actualizarBotonActaPdf(entregaId);
        $('#modalNuevaEntregaTitulo').html('<i class="fas fa-edit me-2"></i>Editar entrega');
        $('#btnModalConfirmarEntregaTexto').text('Guardar cambios');
        $('#modalEntregaIntro').text('Modifique datos o cantidades y guarde. El resumen se actualiza solo.');

        $('#modal_entrega_fecha').val(
            entrega.fecha_entrega != null && String(entrega.fecha_entrega).trim() !== ''
                ? String(entrega.fecha_entrega).substring(0, 10)
                : ''
        );
        $('#modal_entrega_firma').val(entrega.firma_responsable || '');
        $('#modal_entrega_estado').val(entrega.estado || 'En proceso');
        $('#modal_entrega_comentarios').val(entrega.comentarios || '');

        const tb = $('#modalNuevaEntregaEquipos');
        tb.empty();
        let tieneLineas = false;

        $('.fila-equipo-item').each(function () {
            const fila = $(this);
            const selectEquipo = fila.find('select');
            const equipoId = selectEquipo.val();
            if (!equipoId) return;
            tieneLineas = true;
            const equipoTexto = $('<div>').text(selectEquipo.find('option:selected').text()).html();
            const cantidadTotal = fila.find('input[name*="[cantidad]"]').val();
            const maxNum = parseInt(cantidadTotal, 10) || 999999;
            const cantVal = cantidadDetalleParaEquipo(entrega, equipoId);
            const cantValSafe = String(cantVal).replace(/"/g, '&quot;');
            tb.append(
                `<tr data-equipo-id="${equipoId}">
                    <td>${equipoTexto}</td>
                    <td><span class="badge bg-secondary">${escHtml(String(cantidadTotal))}</span></td>
                    <td style="max-width:120px"><input type="number" class="form-control form-control-sm input-modal-cant-entrega" min="0" max="${maxNum}" value="${cantValSafe}"></td>
                </tr>`
            );
        });

        $('#modalNuevaEntregaSinEquipos').toggleClass('d-none', tieneLineas);
        $('#btnModalConfirmarEntrega').prop('disabled', !tieneLineas);

        const $inst = $('#modal_entrega_institucion');
        if ($inst.hasClass('select2-hidden-accessible')) {
            $inst.select2('destroy');
        }
        $inst.val(String(entrega.id_institucion ?? entrega.ID_INSTITUCION ?? ''));

        mostrarModalBootstrap(modalNuevaEntregaEl);
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

    const abrirModalNuevaEntrega = function () {
        actualizarBotonActaPdf(null);
        $('#modal_entrega_modo').val('nueva');
        $('#modal_entrega_editando_id').val('');
        $('#modalNuevaEntregaTitulo').html('<i class="fas fa-truck me-2"></i>Nueva entrega por centro');
        $('#btnModalConfirmarEntregaTexto').text('Agregar a la lista');
        $('#modalEntregaIntro').text('Complete los datos y las cantidades; confirme para añadir la entrega al resumen. Guarde el contrato al final.');

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

        mostrarModalBootstrap(modalNuevaEntregaEl);

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
            actualizarBotonActaPdf(null);
            const $inst = $('#modal_entrega_institucion');
            if ($inst.hasClass('select2-hidden-accessible')) {
                $inst.select2('destroy');
            }
            $('#modal_entrega_modo').val('nueva');
            $('#modal_entrega_editando_id').val('');
        });
    }

    $('#btnModalConfirmarEntrega').on('click', function () {
        const modo = $('#modal_entrega_modo').val();
        if (modo === 'editar') {
            const eid = $('#modal_entrega_editando_id').val();
            if (eid) aplicarCambiosModalAEntrega(eid);
            return;
        }

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
            if (equipoId != null && equipoId !== '') {
                detalle.push({ id_equipo: equipoId, cantidad: cant });
            }
        });

        const entregaId = Date.now();
        const nueva = {
            id_entrega: entregaId,
            id_institucion: idInst,
            fecha_entrega: $('#modal_entrega_fecha').val() || null,
            firma_responsable: $('#modal_entrega_firma').val() || '',
            estado: $('#modal_entrega_estado').val() || 'En proceso',
            comentarios: $('#modal_entrega_comentarios').val() || '',
            detalle
        };

        listaEntregas().push(nueva);

        const $inst = $('#modal_entrega_institucion');
        if ($inst.hasClass('select2-hidden-accessible')) {
            $inst.select2('destroy');
        }
        ocultarModalBootstrap(modalNuevaEntregaEl);

        abrirPestanaEntregas();
        refrescarTablaEntregas();
        const card = document.getElementById('cardTablaEntregas');
        if (card) card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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

    $(document).on('click', '#btnAgregarEntrega', function (e) {
        e.preventDefault();
        clickAsignarEntrega();
    });

    $(document).on('click', '.btn-detalle-entrega', function (e) {
        e.preventDefault();
        const id = String($(this).attr('data-entrega-id') || '');
        if (!id) return;
        listaEntregas();
        abrirPestanaEntregas();
        setTimeout(() => abrirModalEditarEntrega(id), 200);
    });

    $(document).on('click', '.btn-quitar-entrega-resumen', function (e) {
        e.preventDefault();
        const id = $(this).attr('data-entrega-id');
        if (id == null || id === '') return;
        const quitar = () => {
            const ec = listaEntregas();
            const ix = ec.findIndex((x) => String(idEntregaDeObj(x)) === String(id));
            if (ix !== -1) ec.splice(ix, 1);
            refrescarTablaEntregas();
        };
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: '¿Quitar entrega?',
                text: 'Se eliminará esta asignación del borrador (antes de guardar el contrato).',
                showCancelButton: true,
                confirmButtonText: 'Sí, quitar',
                cancelButtonText: 'Cancelar'
            }).then((r) => {
                if (r.isConfirmed) quitar();
            });
        } else if (window.confirm('¿Quitar esta entrega del listado?')) {
            quitar();
        }
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
        sincronizarCamposOcultosEntregas();
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
        } else if (modoFormulario === 'crear') {
            crearFilaEquipo();
        }
        // La tabla de entregas se pinta en PHP; no vaciar al cargar. Solo sincronizar badge si hace falta.
        if ($('#tablaResumenEntregasBody tr').length === 0 && listaEntregas().length > 0) {
            refrescarTablaEntregas();
        }
    };

    inicializarFormulario();
});
