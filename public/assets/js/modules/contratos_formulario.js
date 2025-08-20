$(document).ready(function() {
    const form = document.getElementById('formContrato');
    if (!form) return;

    const detalleEquiposBody = $('#detalleEquiposBody');
    const btnAgregarEquipo = $('#btnAgregarEquipo');
    const contenedorEntregas = $('#contenedorEntregas');
    const btnAgregarEntrega = $('#btnAgregarEntrega');
    const inputArchivos = $('#documentos');
    const listaArchivosNuevos = $('#listaArchivosNuevos');
    const listaArchivosExistentes = $('#listaArchivosExistentes');

    const crearFilaEquipo = (item = null) => {
        const rowId = item ? item.id : Date.now();
        let opciones = '<option value="">Seleccione...</option>';
        if (typeof equiposDisponibles !== 'undefined') {
            equiposDisponibles.forEach(eq => {
                const isSelected = (item && item.equipo_id == eq.id_equipo) ? 'selected' : '';
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
        
        // Obtenemos las líneas de equipo de la Pestaña 1 para construir el detalle
        $('.fila-equipo-item').each(function(index) {
            const fila = $(this);
            const selectEquipo = fila.find('select');
            const equipoId = selectEquipo.val();
            const equipoTexto = selectEquipo.find('option:selected').text();
            const cantidadTotal = fila.find('input[name*="[cantidad]"]').val();
            if (!equipoId) return;
    
            let cantidadEntregada = 0;
            if (entrega && entrega.detalle) {
                const itemDetalle = entrega.detalle.find(d => d.id_equipo == equipoId);
                if (itemDetalle) cantidadEntregada = itemDetalle.cantidad;
            }
    
            lineasEquiposHTML += `
                <tr>
                    <td><input type="hidden" name="entregas[${entregaId}][items][${index}][equipo_id]" value="${equipoId}">${equipoTexto}</td>
                    <td><span class="badge bg-secondary">${cantidadTotal}</span></td>
                    <td><input type="number" name="entregas[${entregaId}][items][${index}][cantidad]" class="form-control form-control-sm" value="${cantidadEntregada}" min="0" max="${cantidadTotal}"></td>
                </tr>`;
        });
        
        // Creamos el HTML para el nuevo bloque de acordeón
        const nuevoBloqueHTML = `
            <div class="accordion-item" id="entrega-${entregaId}">
                <h2 class="accordion-header" id="heading-${entregaId}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-${entregaId}">
                        <span class="fw-bold me-2">Entrega para:</span>
                        <span class="nombre-institucion-header">Cargando...</span>
                        <span class="badge ms-auto ${entrega && entrega.estado === 'Entregado' ? 'bg-success' : 'bg-info text-dark'} estado-header">${entrega ? entrega.estado : 'En proceso'}</span>
                    </button>
                </h2>
                <div id="collapse-${entregaId}" class="accordion-collapse collapse" data-bs-parent="#contenedorEntregas">
                    <div class="accordion-body">
                        <div class="d-flex justify-content-end mb-3"><button type="button" class="btn btn-outline-danger btn-sm btn-remover-entrega" title="Eliminar esta asignación">Eliminar Asignación</button></div>
                        <div class="row"><div class="col-12 mb-3"><label class="form-label">Institución</label><select name="entregas[${entregaId}][id_institucion]" class="form-select select-buscador-entrega" required>${opcionesCentros}</select></div></div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label">Fecha de Entrega</label><input type="date" name="entregas[${entregaId}][fecha_entrega]" class="form-control form-control-sm" value="${entrega && entrega.fecha_entrega ? entrega.fecha_entrega : ''}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Persona que Firma</label><input type="text" name="entregas[${entregaId}][firma_responsable]" class="form-control form-control-sm" value="${entrega ? entrega.firma_responsable : ''}"></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Estado</label><select name="entregas[${entregaId}][estado]" class="form-select form-select-sm estado-select"><option value="En proceso" ${entrega && entrega.estado === 'En proceso' ? 'selected' : ''}>En proceso</option><option value="Entregado" ${entrega && entrega.estado === 'Entregado' ? 'selected' : ''}>Entregado</option></select></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Comentarios</label><textarea name="entregas[${entregaId}][comentarios]" class="form-control form-control-sm" rows="2">${entrega ? entrega.comentarios : ''}</textarea></div>
                        <p class="fw-bold small mb-1">Equipos para esta entrega:</p>
                        <table class="table table-sm table-bordered"><thead class="table-light"><tr><th>Equipo</th><th>Cant. Contrato</th><th>Cant. a Entregar</th></tr></thead><tbody>${lineasEquiposHTML}</tbody></table>
                    </div>
                </div>
            </div>`;
        
        // Añadimos el bloque al contenedor
        contenedorEntregas.append(nuevoBloqueHTML);
        
        // Inicializamos Select2
        const itemAcordeon = $(`#entrega-${entregaId}`);
        const selectCentro = itemAcordeon.find('.select-buscador-entrega');
        selectCentro.select2({ theme: 'bootstrap-5', placeholder: 'Escriba para buscar...', dropdownParent: itemAcordeon });
        
        // --- INICIO DE LA CORRECCIÓN ---
        if (entrega) {
            // 1. Establecemos el valor guardado en el select
            selectCentro.val(entrega.id_institucion).trigger('change.select2'); // Usar .trigger('change.select2') es más específico para el plugin
            
            // 2. Obtenemos el texto de la opción seleccionada
            const textoInstitucion = selectCentro.find('option:selected').text();
            
            // 3. Actualizamos el encabezado del acordeón directamente
            itemAcordeon.find('.nombre-institucion-header').text(textoInstitucion.trim());
        }
        // --- FIN DE LA CORRECCIÓN ---
    
        // Lógica para actualizar los encabezados en tiempo real
        selectCentro.on('change', function() {
            itemAcordeon.find('.nombre-institucion-header').text($(this).find('option:selected').text().trim() || 'Seleccione una institución...');
        });
    
        itemAcordeon.find('.estado-select').on('change', function() {
            const headerBadge = itemAcordeon.find('.estado-header');
            headerBadge.text(this.value);
            headerBadge.removeClass('bg-success bg-info text-dark').addClass(this.value === 'Entregado' ? 'bg-success' : 'bg-info text-dark');
        });
    };

    btnAgregarEquipo.on('click', () => crearFilaEquipo());
    btnAgregarEntrega.on('click', () => crearBloqueEntrega(null));
    contenedorEntregas.on('click', '.btn-remover-entrega', function() { $(this).closest('.accordion-item').remove(); });
    detalleEquiposBody.on('click', '.btn-remover-fila', function() { $(this).closest('tr').remove(); });

    inputArchivos.on('change', function() {
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

    if (listaArchivosExistentes) {
        listaArchivosExistentes.on('click', '.btn-eliminar-documento', function() {
            const id_documento = $(this).data('id');
            // ... (lógica de eliminar documento)
        });
    }

    $(form).on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const action = $('#id_contrato').val() ? 'actualizar' : 'guardar';
        formData.append('action', action);

        Swal.fire({ title: 'Guardando Contrato', text: 'Por favor, espere...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        $.ajax({
            url: '../app/ajax/contratos_ajax.php',
            type: 'POST',
            data: formData,
            processData: false, contentType: false, dataType: 'json',
            success: function(data) {
                if (data.success) {
                    Swal.fire('¡Éxito!', data.message, 'success').then(() => { window.location.href = 'gestion_contratos.php'; });
                } else { Swal.fire('Error', data.message || 'Ocurrió un error.', 'error'); }
            },
            error: function() { Swal.fire('Error de Conexión', 'No se pudo comunicar con el servidor.', 'error'); }
        });
    });

    const inicializarFormulario = () => {
        if (modoFormulario === 'editar') {
            if (equiposContrato && equiposContrato.length > 0) equiposContrato.forEach(item => crearFilaEquipo(item));
            if (entregasContrato && entregasContrato.length > 0) entregasContrato.forEach(entrega => crearBloqueEntrega(entrega));
        } else if (modoFormulario === 'crear') {
            crearFilaEquipo();
        }
    };
    inicializarFormulario();
});