document.addEventListener('DOMContentLoaded', function () {
    if (!document.getElementById('tablaCentrosBody')) {
        return;
    }

    const card = document.getElementById('centrosCatalogCard');
    const permisos = window.APP_PERMISOS || {};
    const puedeEscribir = !!permisos.puedeEscribir;
    const puedeEliminar = !!permisos.puedeEliminar;
    const baseAjax =
        card && card.dataset.ajaxCentros
            ? card.dataset.ajaxCentros
            : '../app/ajax/centros_educativos_ajax.php';

    const inputBuscar = document.getElementById('buscarCentro');
    const pagInfo = document.getElementById('centrosPaginationInfo');
    const pagNav = document.getElementById('centrosPaginationNav');

    let paginaActual = 1;
    let textoBusqueda = '';
    const porPagina = 50;
    let debounceTimer = null;
    const centroModal = new bootstrap.Modal(document.getElementById('centroModal'));
    const centroForm = document.getElementById('centroForm');
    const modalTitle = document.getElementById('centroModalTitle');
    const tablaBody = document.getElementById('tablaCentrosBody');
    const selDepto = document.getElementById('departamento_id');
    const selMuni = document.getElementById('municipio_id');
    const hiddenDeptoNombre = document.getElementById('departamento');
    const hiddenMuniNombre = document.getElementById('municipio');

    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    };

    const cargarDepartamentosEnSelect = () => {
        return fetch(`${baseAjax}?action=listar_departamentos`)
            .then((r) => r.json())
            .then((data) => {
                const prev = selDepto.value;
                selDepto.innerHTML = '<option value="">— Seleccione —</option>';
                if (data.success && Array.isArray(data.data)) {
                    data.data.forEach((d) => {
                        if (d.departamento_id == null) {
                            return;
                        }
                        const opt = document.createElement('option');
                        opt.value = String(d.departamento_id);
                        opt.textContent = d.departamento || ('ID ' + d.departamento_id);
                        opt.dataset.nombre = d.departamento || '';
                        selDepto.appendChild(opt);
                    });
                }
                if (prev) {
                    selDepto.value = prev;
                }
            });
    };

    const cargarMunicipios = (departamentoId, municipioIdPreselect) => {
        selMuni.innerHTML = '';
        hiddenMuniNombre.value = '';
        if (!departamentoId) {
            selMuni.disabled = true;
            selMuni.innerHTML = '<option value="">— Primero elija departamento —</option>';
            return Promise.resolve();
        }
        selMuni.disabled = true;
        selMuni.innerHTML = '<option value="">Cargando…</option>';
        return fetch(
            `${baseAjax}?action=listar_municipios&departamento_id=${encodeURIComponent(departamentoId)}`
        )
            .then((r) => r.json())
            .then((data) => {
                selMuni.innerHTML = '<option value="">— Seleccione municipio —</option>';
                if (data.success && Array.isArray(data.data)) {
                    data.data.forEach((m) => {
                        if (m.municipio_id == null) {
                            return;
                        }
                        const opt = document.createElement('option');
                        opt.value = String(m.municipio_id);
                        opt.textContent = m.municipio || ('ID ' + m.municipio_id);
                        opt.dataset.nombre = m.municipio || '';
                        selMuni.appendChild(opt);
                    });
                }
                selMuni.disabled = false;
                if (municipioIdPreselect) {
                    selMuni.value = String(municipioIdPreselect);
                    const opt = selMuni.selectedOptions[0];
                    if (opt && opt.dataset.nombre !== undefined) {
                        hiddenMuniNombre.value = opt.dataset.nombre;
                    }
                }
            })
            .catch(() => {
                selMuni.innerHTML = '<option value="">Error al cargar</option>';
                selMuni.disabled = true;
            });
    };

    const syncDeptoNombre = () => {
        const opt = selDepto.selectedOptions[0];
        hiddenDeptoNombre.value = opt && opt.dataset.nombre !== undefined ? opt.dataset.nombre : '';
    };

    const syncMuniNombre = () => {
        const opt = selMuni.selectedOptions[0];
        hiddenMuniNombre.value = opt && opt.dataset.nombre !== undefined ? opt.dataset.nombre : '';
    };

    /** Si el valor guardado no aparece en el DISTINCT (datos viejos o importación), añade la opción. */
    const asegurarOpcion = (select, idVal, etiqueta, nombreDataset) => {
        if (idVal === null || idVal === undefined || idVal === '') {
            return;
        }
        const s = String(idVal);
        const existe = Array.from(select.options).some((o) => o.value === s);
        if (!existe) {
            const opt = document.createElement('option');
            opt.value = s;
            opt.textContent = etiqueta || 'ID ' + s;
            opt.dataset.nombre = nombreDataset != null && nombreDataset !== '' ? nombreDataset : etiqueta || '';
            select.appendChild(opt);
        }
        select.value = s;
    };

    selDepto.addEventListener('change', () => {
        syncDeptoNombre();
        cargarMunicipios(selDepto.value, null);
    });

    selMuni.addEventListener('change', () => {
        syncMuniNombre();
    });

    const renderPaginacion = (pag) => {
        if (!pagInfo || !pagNav) {
            return;
        }
        if (!pag || typeof pag.total !== 'number') {
            pagInfo.textContent = '';
            pagNav.innerHTML = '';
            return;
        }
        const total = pag.total;
        const page = pag.page;
        const per = pag.per_page;
        const totalPages = pag.total_pages;
        if (total === 0) {
            pagInfo.textContent = '0 registros';
            pagNav.innerHTML = '';
            return;
        }
        const desde = (page - 1) * per + 1;
        const hasta = Math.min(page * per, total);
        pagInfo.textContent = `Mostrando ${desde.toLocaleString('es-SV')}–${hasta.toLocaleString('es-SV')} de ${total.toLocaleString('es-SV')}`;
        pagNav.innerHTML = '';
        const mkBtn = (label, disabled, pageTarget, icon) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'btn btn-outline-secondary btn-sm';
            b.disabled = disabled;
            b.dataset.page = String(pageTarget);
            b.innerHTML = icon ? `<i class="fas ${icon}"></i> ${label}` : label;
            b.addEventListener('click', () => {
                paginaActual = pageTarget;
                cargarTabla();
            });
            return b;
        };
        pagNav.appendChild(mkBtn('Anterior', page <= 1, page - 1, 'fa-chevron-left'));
        const span = document.createElement('span');
        span.className = 'align-self-center px-2 small';
        span.textContent = `Página ${page} / ${totalPages || 1}`;
        pagNav.appendChild(span);
        pagNav.appendChild(mkBtn('Siguiente', page >= totalPages, page + 1, 'fa-chevron-right'));
    };

    const cargarTabla = () => {
        const params = new URLSearchParams();
        params.set('action', 'listar');
        params.set('page', String(paginaActual));
        params.set('per_page', String(porPagina));
        if (textoBusqueda) {
            params.set('q', textoBusqueda);
        }
        tablaBody.innerHTML =
            '<tr><td colspan="6" class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm me-2"></span>Cargando…</td></tr>';

        fetch(`${baseAjax}?${params.toString()}`)
            .then((r) => {
                if (!r.ok) {
                    throw new Error('Respuesta HTTP ' + r.status);
                }
                return r.text().then((text) => {
                    try {
                        return text ? JSON.parse(text) : {};
                    } catch (e) {
                        throw new Error('La respuesta no es JSON válido. Revise la consola o los logs del servidor.');
                    }
                });
            })
            .then((data) => {
                tablaBody.innerHTML = '';
                const rows = data.success && Array.isArray(data.data) ? data.data : [];
                renderPaginacion(data.pagination);
                if (rows.length > 0) {
                    const html = rows
                        .map((row) => {
                            const codigo = row.codigo_infraestructura != null ? row.codigo_infraestructura : '';
                            return `
                            <tr>
                                <td>${esc(row.nombre_ce)}</td>
                                <td>${esc(row.departamento)}</td>
                                <td>${esc(row.municipio)}</td>
                                <td>${esc(codigo)}</td>
                                <td>${esc(row.director_actual)}</td>
                                <td class="text-end text-nowrap">
                                    ${puedeEscribir ? `<button type="button" class="btn btn-warning btn-sm btn-editar" data-id="${esc(row.centro_id)}">
                                        <i class="fas fa-edit"></i>
                                    </button>` : ''}
                                    ${puedeEliminar ? `<button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="${esc(row.centro_id)}">
                                        <i class="fas fa-trash"></i>
                                    </button>` : ''}
                                </td>
                            </tr>`;
                        })
                        .join('');
                    tablaBody.innerHTML = html;
                } else {
                    tablaBody.innerHTML =
                        '<tr><td colspan="6" class="text-center text-muted">No hay centros que coincidan con la búsqueda o el catálogo está vacío.</td></tr>';
                }
            })
            .catch((err) => {
                tablaBody.innerHTML = '';
                if (pagInfo) {
                    pagInfo.textContent = '';
                }
                if (pagNav) {
                    pagNav.innerHTML = '';
                }
                tablaBody.innerHTML = `<tr><td colspan="6" class="text-danger">No se pudieron cargar los datos. ${esc(
                    err.message || String(err)
                )}</td></tr>`;
                console.error(err);
            });
    };

    if (inputBuscar) {
        inputBuscar.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                textoBusqueda = inputBuscar.value.trim();
                paginaActual = 1;
                cargarTabla();
            }, 350);
        });
    }

    const btnNuevoCentro = document.getElementById('btnNuevoCentro');
    if (btnNuevoCentro) {
        btnNuevoCentro.style.display = puedeEscribir ? '' : 'none';
        btnNuevoCentro.addEventListener('click', () => {
            if (!puedeEscribir) return;
            centroForm.reset();
            document.getElementById('centro_id').value = '';
            modalTitle.textContent = 'Nuevo centro educativo';
            hiddenDeptoNombre.value = '';
            hiddenMuniNombre.value = '';
            cargarDepartamentosEnSelect().then(() => {
                selDepto.value = '';
                selMuni.innerHTML = '<option value="">— Primero elija departamento —</option>';
                selMuni.disabled = true;
            });
            centroModal.show();
        });
    }

    centroForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!puedeEscribir) {
            Swal.fire('Sin permiso', 'Solo puede consultar esta sección.', 'warning');
            return;
        }
        syncDeptoNombre();
        syncMuniNombre();
        const formData = new FormData(this);

        fetch(`${baseAjax}?action=guardar`, {
            method: 'POST',
            body: formData
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.success) {
                    centroModal.hide();
                    Swal.fire('Listo', data.message, 'success');
                    paginaActual = 1;
                    cargarTabla();
                    cargarDepartamentosEnSelect();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
    });

    tablaBody.addEventListener('click', function (e) {
        const target = e.target.closest('button');
        if (!target) {
            return;
        }
        const id = target.getAttribute('data-id');

        if (target.classList.contains('btn-editar')) {
            if (!puedeEscribir) return;
            fetch(`${baseAjax}?action=obtener&id=${encodeURIComponent(id)}`)
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success) {
                        Swal.fire('Error', data.message || 'No se pudo cargar', 'error');
                        return;
                    }
                    const c = data.data;
                    document.getElementById('centro_id').value = c.centro_id;
                    document.getElementById('nombre_ce').value = c.nombre_ce || '';
                    document.getElementById('codigo_infraestructura').value =
                        c.codigo_infraestructura != null ? String(c.codigo_infraestructura) : '';
                    document.getElementById('director_actual').value = c.director_actual || '';
                    modalTitle.textContent = 'Editar centro educativo';

                    cargarDepartamentosEnSelect().then(() => {
                        const depId = c.departamento_id != null ? String(c.departamento_id) : '';
                        if (depId) {
                            asegurarOpcion(selDepto, c.departamento_id, c.departamento, c.departamento);
                        } else {
                            selDepto.value = '';
                        }
                        syncDeptoNombre();
                        const munId = c.municipio_id != null ? c.municipio_id : '';
                        return cargarMunicipios(depId, munId).then(() => {
                            if (munId !== '' && munId !== null) {
                                asegurarOpcion(selMuni, c.municipio_id, c.municipio, c.municipio);
                            }
                            syncMuniNombre();
                        });
                    }).then(() => {
                        centroModal.show();
                    });
                });
        }

        if (target.classList.contains('btn-eliminar')) {
            if (!puedeEliminar) return;
            Swal.fire({
                title: '¿Eliminar este centro?',
                text: 'Solo debe hacerlo si no está referenciado en contratos u otros registros.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }
                const formData = new FormData();
                formData.append('id', id);
                fetch(`${baseAjax}?action=eliminar`, {
                    method: 'POST',
                    body: formData
                })
                    .then((r) => r.json())
                    .then((data) => {
                        if (data.success) {
                            Swal.fire('Eliminado', data.message, 'success');
                            cargarTabla();
                            cargarDepartamentosEnSelect();
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
            });
        }
    });

    cargarTabla();
});
