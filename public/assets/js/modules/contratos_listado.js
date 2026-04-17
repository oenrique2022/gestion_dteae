document.addEventListener('DOMContentLoaded', function() {
    const tablaContratosBody = document.getElementById('tablaContratosBody');
    if (!tablaContratosBody) return;
    const permisos = window.APP_PERMISOS || {};
    const puedeEscribir = !!permisos.puedeEscribir;
    const puedeEliminar = !!permisos.puedeEliminar;
    const urlAjax = typeof window.URL_AJAX_CONTRATOS === 'string' ? window.URL_AJAX_CONTRATOS : '../app/ajax/contratos_ajax.php';
    const basePublic = typeof window.APP_PUBLIC_BASE === 'string' ? window.APP_PUBLIC_BASE.replace(/\/$/, '') : '';
    const modalRutasEl = document.getElementById('modalRutasEntrega');
    const modalDocsRutaEl = document.getElementById('modalDocsRutaEntrega');

    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    };
    const fmt = (s) => (s == null || String(s).trim() === '' ? '—' : esc(String(s)));

    const cargarContratos = () => {
        fetch(`${urlAjax}?action=listar`)
            .then(response => response.json())
            .then(data => {
                tablaContratosBody.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(c => {
                        tablaContratosBody.innerHTML += `
                            <tr>
                                <td>${c.numero_contrato}</td>
                                <td>${c.nombre_contrato}</td>
                                <td>${c.nombre_proveedor}</td>
                                <td>${c.fecha_inicio}</td>
                                <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-1 flex-nowrap">
                                    <button class="btn btn-outline-primary btn-sm btn-rutas-entrega text-nowrap" data-id="${c.id}" data-numero="${esc(c.numero_contrato)}" data-nombre="${esc(c.nombre_contrato)}" title="Gestionar ruta de entrega">
                                        <i class="fas fa-route me-1"></i>Ruta
                                    </button>
                                    ${puedeEscribir ? `<a href="editar_contrato.php?id=${c.id}" class="btn btn-warning btn-sm" title="Editar contrato">
                                        <i class="fas fa-edit"></i>
                                    </a>` : ''}
                                    ${puedeEliminar ? `<button class="btn btn-danger btn-sm btn-eliminar" data-id="${c.id}" title="Eliminar contrato">
                                        <i class="fas fa-trash"></i>
                                    </button>` : ''}
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tablaContratosBody.innerHTML = '<tr><td colspan="5" class="text-center">No hay contratos registrados.</td></tr>';
                }
            });
    };

    const renderFilaRuta = (idContrato, row) => {
        const idInst = Number(row.id_institucion || 0);
        const centro = row.centro || `ID ${idInst}`;
        const ruta = row.ruta || {};
        const idRuta = ruta.id ? Number(ruta.id) : 0;
        const soloLectura = !puedeEscribir;
        return `<tr data-id-institucion="${idInst}" data-id-ruta="${idRuta}">
            <td class="small">${esc(centro)}</td>
            <td><input type="text" class="form-control form-control-sm ruta-responsable" value="${esc(ruta.responsable_entrega || '')}" ${soloLectura ? 'disabled' : ''}></td>
            <td><input type="text" class="form-control form-control-sm ruta-motorista" value="${esc(ruta.motorista || '')}" ${soloLectura ? 'disabled' : ''}></td>
            <td><input type="text" class="form-control form-control-sm ruta-vehiculo" value="${esc(ruta.vehiculo || '')}" ${soloLectura ? 'disabled' : ''}></td>
            <td><input type="text" class="form-control form-control-sm ruta-placas" value="${esc(ruta.placas || '')}" ${soloLectura ? 'disabled' : ''}></td>
            <td>
                <select class="form-select form-select-sm ruta-estado" ${soloLectura ? 'disabled' : ''}>
                    <option value="Programada" ${(ruta.estado || 'Programada') === 'Programada' ? 'selected' : ''}>Programada</option>
                    <option value="En ruta" ${(ruta.estado || '') === 'En ruta' ? 'selected' : ''}>En ruta</option>
                    <option value="Entregado" ${(ruta.estado || '') === 'Entregado' ? 'selected' : ''}>Entregado</option>
                </select>
            </td>
            <td class="small">
                <input type="date" class="form-control form-control-sm ruta-fecha-programada" value="${esc((ruta.fecha_programada || '').toString().substring(0,10))}" ${soloLectura ? 'disabled' : ''}>
            </td>
            <td><textarea class="form-control form-control-sm ruta-comentarios" rows="3" placeholder="Bitácora / observaciones" ${soloLectura ? 'disabled' : ''}>${esc(ruta.comentarios || '')}</textarea></td>
            <td class="text-nowrap">
                ${puedeEscribir ? `<button class="btn btn-success btn-sm btn-guardar-ruta mb-1" data-id-contrato="${idContrato}" data-id-institucion="${idInst}">
                    <i class="fas fa-save"></i>
                </button>` : ''}
                <button class="btn btn-outline-primary btn-sm btn-docs-ruta" data-id-ruta="${idRuta}" data-centro="${esc(centro)}" ${idRuta > 0 ? '' : 'disabled title="Guarde la ruta primero"'}>
                    <i class="fas fa-file-pdf"></i>
                </button>
            </td>
        </tr>`;
    };

    const abrirModalRutas = (idContrato, numeroContrato, nombreContrato) => {
        const info = document.getElementById('rutasContratoInfo');
        const body = document.getElementById('tablaRutasEntregaBody');
        const empty = document.getElementById('rutasEntregaVacia');
        const hidden = document.getElementById('rutas_id_contrato_actual');
        hidden.value = String(idContrato);
        info.textContent = `Contrato ${numeroContrato} - ${nombreContrato}`;
        body.innerHTML = '<tr><td colspan="9" class="text-center text-muted">Cargando...</td></tr>';
        empty.classList.add('d-none');
        if (modalRutasEl && window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalRutasEl).show();
        }
        fetch(`${urlAjax}?action=listar_rutas_entrega&id_contrato=${encodeURIComponent(String(idContrato))}`)
            .then((r) => r.json())
            .then((data) => {
                body.innerHTML = '';
                if (!data.success) {
                    body.innerHTML = `<tr><td colspan="9" class="text-danger text-center">${esc(data.message || 'Error al cargar rutas.')}</td></tr>`;
                    return;
                }
                const rows = Array.isArray(data.data) ? data.data : [];
                if (!rows.length) {
                    empty.classList.remove('d-none');
                    return;
                }
                rows.forEach((row) => { body.insertAdjacentHTML('beforeend', renderFilaRuta(idContrato, row)); });
            })
            .catch(() => {
                body.innerHTML = '<tr><td colspan="9" class="text-danger text-center">Error de conexión.</td></tr>';
            });
    };

    const abrirDocsRuta = (idRuta, centroTxt) => {
        if (!idRuta || Number(idRuta) <= 0) return;
        document.getElementById('docsRutaIdActual').value = String(idRuta);
        document.getElementById('docsRutaCentroInfo').textContent = centroTxt || '';
        document.getElementById('listaDocsRutaEntrega').innerHTML = '';
        document.getElementById('docsRutaVacio').classList.add('d-none');
        if (modalDocsRutaEl && window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(modalDocsRutaEl).show();
        }
        fetch(`${urlAjax}?action=listar_documentos_ruta&id_ruta=${encodeURIComponent(String(idRuta))}`)
            .then((r) => r.json())
            .then((data) => {
                const box = document.getElementById('listaDocsRutaEntrega');
                if (!data.success) {
                    box.innerHTML = `<div class="alert alert-danger py-2 mb-0">${esc(data.message || 'No se pudo cargar')}</div>`;
                    return;
                }
                const docs = Array.isArray(data.data) ? data.data : [];
                if (!docs.length) {
                    document.getElementById('docsRutaVacio').classList.remove('d-none');
                    return;
                }
                docs.forEach((d) => {
                    box.insertAdjacentHTML('beforeend', `<div class="list-group-item">
                        <a href="${esc(basePublic + (d.ruta_archivo || ''))}" target="_blank" rel="noopener"><i class="fas fa-file-pdf text-danger me-1"></i>${esc(d.nombre_archivo || 'archivo.pdf')}</a>
                        <div class="small text-muted">${fmt(d.fecha_subida)}</div>
                        <div class="small fst-italic text-secondary">"${esc(d.comentario || '')}"</div>
                    </div>`);
                });
            })
            .catch(() => {
                document.getElementById('listaDocsRutaEntrega').innerHTML = '<div class="alert alert-danger py-2 mb-0">Error de conexión.</div>';
            });
    };

    tablaContratosBody.addEventListener('click', (e) => {
        const btnRuta = e.target.closest('.btn-rutas-entrega');
        if (btnRuta) {
            abrirModalRutas(btnRuta.dataset.id, btnRuta.dataset.numero || '', btnRuta.dataset.nombre || '');
            return;
        }
    });

    document.addEventListener('click', (e) => {
        const btnSave = e.target.closest('.btn-guardar-ruta');
        if (btnSave) {
            if (!puedeEscribir) {
                Swal.fire('Sin permiso', 'Su perfil es de solo consulta.', 'warning');
                return;
            }
            const tr = btnSave.closest('tr');
            const fd = new FormData();
            fd.append('action', 'guardar_ruta_entrega');
            fd.append('id_contrato', String(btnSave.dataset.idContrato || '0'));
            fd.append('id_institucion', String(btnSave.dataset.idInstitucion || '0'));
            fd.append('responsable_entrega', tr.querySelector('.ruta-responsable')?.value || '');
            fd.append('motorista', tr.querySelector('.ruta-motorista')?.value || '');
            fd.append('vehiculo', tr.querySelector('.ruta-vehiculo')?.value || '');
            fd.append('placas', tr.querySelector('.ruta-placas')?.value || '');
            fd.append('estado', tr.querySelector('.ruta-estado')?.value || 'Programada');
            fd.append('fecha_programada', tr.querySelector('.ruta-fecha-programada')?.value || '');
            fd.append('fecha_en_ruta', '');
            fd.append('fecha_entregado', '');
            fd.append('comentarios', tr.querySelector('.ruta-comentarios')?.value || '');
            fetch(urlAjax, { method: 'POST', body: fd })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success) {
                        Swal.fire('Error', data.message || 'No se pudo guardar', 'error');
                        return;
                    }
                    const idRuta = Number(data.id_ruta || 0);
                    tr.setAttribute('data-id-ruta', String(idRuta));
                    const btnDocs = tr.querySelector('.btn-docs-ruta');
                    if (btnDocs && idRuta > 0) {
                        btnDocs.dataset.idRuta = String(idRuta);
                        btnDocs.disabled = false;
                        btnDocs.removeAttribute('title');
                    }
                    Swal.fire('Listo', data.message || 'Ruta guardada.', 'success');
                })
                .catch(() => Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error'));
            return;
        }

        const btnDocs = e.target.closest('.btn-docs-ruta');
        if (btnDocs) {
            abrirDocsRuta(Number(btnDocs.dataset.idRuta || '0'), btnDocs.dataset.centro || '');
        }
    });

    const formDocRuta = document.getElementById('formSubirDocRuta');
    if (formDocRuta) {
        if (!puedeEscribir) {
            formDocRuta.style.display = 'none';
        }
        formDocRuta.addEventListener('submit', function(ev) {
            ev.preventDefault();
            if (!puedeEscribir) {
                Swal.fire('Sin permiso', 'Su perfil es de solo consulta.', 'warning');
                return;
            }
            const idRuta = Number(document.getElementById('docsRutaIdActual').value || '0');
            const fileInput = document.getElementById('docRutaArchivo');
            if (idRuta <= 0) {
                Swal.fire('Atención', 'Guarde la ruta primero.', 'warning');
                return;
            }
            if (!fileInput || !fileInput.files || !fileInput.files[0]) {
                Swal.fire('Atención', 'Seleccione un PDF.', 'warning');
                return;
            }
            const fd = new FormData();
            fd.append('action', 'subir_documento_ruta');
            fd.append('id_ruta', String(idRuta));
            fd.append('comentario_archivo', document.getElementById('docRutaComentario').value || '');
            fd.append('archivo_pdf', fileInput.files[0]);
            fetch(urlAjax, { method: 'POST', body: fd })
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success) {
                        Swal.fire('Error', data.message || 'No se pudo subir el archivo.', 'error');
                        return;
                    }
                    Swal.fire('Listo', data.message || 'Archivo cargado.', 'success');
                    fileInput.value = '';
                    document.getElementById('docRutaComentario').value = '';
                    abrirDocsRuta(idRuta, document.getElementById('docsRutaCentroInfo').textContent || '');
                })
                .catch(() => Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error'));
        });
    }

    cargarContratos();
});