document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tablaTiposEquiposBody');
    if (!tbody) return;

    const permisos = window.APP_PERMISOS || {};
    const puedeEscribir = !!permisos.puedeEscribir;
    const puedeEliminar = !!permisos.puedeEliminar;

    const modalEl = document.getElementById('tipoEquipoModal');
    const modal = modalEl && window.bootstrap ? new bootstrap.Modal(modalEl) : null;
    const form = document.getElementById('tipoEquipoForm');
    const titleEl = document.getElementById('tipoEquipoModalTitle');

    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    };

    const fmtDt = (v) => {
        if (v == null || String(v).trim() === '') return '—';
        const s = String(v);
        return s.length >= 16 ? s.substring(0, 16).replace('T', ' ') : s;
    };

    const cargar = () => {
        fetch('../app/ajax/tipos_equipos_ajax.php?action=listar')
            .then((r) => r.json())
            .then((data) => {
                tbody.innerHTML = '';
                if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="5" class="text-center text-muted">No hay categorías registradas.</td></tr>';
                    return;
                }
                data.data.forEach((row) => {
                    const id = row.id_tipo_equipo;
                    const nom = esc(row.nombre_tipo_equipo);
                    const desc = row.descripcion != null && String(row.descripcion).trim() !== '' ? esc(row.descripcion) : '—';
                    tbody.innerHTML += `<tr>
                        <td>${nom}</td>
                        <td class="small">${desc}</td>
                        <td class="small text-muted">${fmtDt(row.fecha_creacion)}</td>
                        <td class="small text-muted">${fmtDt(row.fecha_modificacion)}</td>
                        <td class="text-end text-nowrap">
                            ${puedeEscribir ? `<button type="button" class="btn btn-warning btn-sm btn-editar" data-id="${id}"><i class="fas fa-edit"></i></button>` : ''}
                            ${puedeEliminar ? `<button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="${id}"><i class="fas fa-trash"></i></button>` : ''}
                        </td>
                    </tr>`;
                });
            })
            .catch(() => {
                tbody.innerHTML =
                    '<tr><td colspan="5" class="text-center text-danger">Error al cargar los datos.</td></tr>';
            });
    };

    const btnNuevo = document.getElementById('btnNuevoTipoEquipo');
    if (btnNuevo) {
        btnNuevo.style.display = puedeEscribir ? '' : 'none';
        btnNuevo.addEventListener('click', () => {
            if (!puedeEscribir || !form || !modal) return;
            form.reset();
            document.getElementById('id_tipo_equipo').value = '';
            if (titleEl) titleEl.textContent = 'Nueva categoría';
            modal.show();
        });
    }

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!puedeEscribir) {
                Swal.fire('Sin permiso', 'Solo puede consultar esta sección.', 'warning');
                return;
            }
            const fd = new FormData(form);
            fetch('../app/ajax/tipos_equipos_ajax.php?action=guardar', { method: 'POST', body: fd })
                .then((r) => r.json())
                .then((data) => {
                    if (data.success) {
                        if (modal) modal.hide();
                        Swal.fire('Listo', data.message, 'success');
                        cargar();
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo guardar.', 'error');
                    }
                })
                .catch(() => Swal.fire('Error', 'Error de red.', 'error'));
        });
    }

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;
        const id = btn.getAttribute('data-id');

        if (btn.classList.contains('btn-editar')) {
            if (!puedeEscribir) return;
            fetch(`../app/ajax/tipos_equipos_ajax.php?action=obtener&id=${encodeURIComponent(id)}`)
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success || !data.data) {
                        Swal.fire('Error', data.message || 'No se pudo cargar.', 'error');
                        return;
                    }
                    const t = data.data;
                    document.getElementById('id_tipo_equipo').value = t.id_tipo_equipo;
                    document.getElementById('nombre_tipo_equipo').value = t.nombre_tipo_equipo || '';
                    document.getElementById('descripcion_tipo').value = t.descripcion != null ? t.descripcion : '';
                    if (titleEl) titleEl.textContent = 'Editar categoría';
                    if (modal) modal.show();
                });
            return;
        }

        if (btn.classList.contains('btn-eliminar')) {
            if (!puedeEliminar) return;
            Swal.fire({
                title: '¿Eliminar esta categoría?',
                text: 'No debe haber equipos asignados a este tipo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
            }).then((result) => {
                if (!result.isConfirmed) return;
                const fd = new FormData();
                fd.append('id', id);
                fetch('../app/ajax/tipos_equipos_ajax.php?action=eliminar', { method: 'POST', body: fd })
                    .then((r) => r.json())
                    .then((data) => {
                        if (data.success) {
                            Swal.fire('Eliminado', data.message, 'success');
                            cargar();
                        } else {
                            Swal.fire('No permitido', data.message, 'warning');
                        }
                    });
            });
        }
    });

    cargar();
});
