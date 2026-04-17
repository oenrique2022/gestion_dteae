document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('tablaFuentesBody');
    if (!tbody) return;

    const permisos = window.APP_PERMISOS || {};
    const puedeEscribir = !!permisos.puedeEscribir;
    const puedeEliminar = !!permisos.puedeEliminar;

    const modalEl = document.getElementById('fuenteModal');
    const modal = modalEl && window.bootstrap ? new bootstrap.Modal(modalEl) : null;
    const form = document.getElementById('fuenteForm');
    const titleEl = document.getElementById('fuenteModalTitle');
    const chkActivo = document.getElementById('activo_fuente');

    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    };

    const cargar = () => {
        fetch('../app/ajax/fuentes_financiamiento_ajax.php?action=listar')
            .then((r) => r.json())
            .then((data) => {
                tbody.innerHTML = '';
                if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="4" class="text-center text-muted">No hay fuentes registradas.</td></tr>';
                    return;
                }
                data.data.forEach((row) => {
                    const id = row.id;
                    const nom = esc(row.nombre);
                    const desc =
                        row.descripcion != null && String(row.descripcion).trim() !== ''
                            ? esc(row.descripcion)
                            : '—';
                    const activo = parseInt(row.activo, 10) === 1 ? 'Sí' : 'No';
                    tbody.innerHTML += `<tr>
                        <td>${nom}</td>
                        <td class="small">${desc}</td>
                        <td><span class="badge ${parseInt(row.activo, 10) === 1 ? 'bg-success' : 'bg-secondary'}">${activo}</span></td>
                        <td class="text-end text-nowrap">
                            ${puedeEscribir ? `<button type="button" class="btn btn-warning btn-sm btn-editar" data-id="${id}"><i class="fas fa-edit"></i></button>` : ''}
                            ${puedeEliminar ? `<button type="button" class="btn btn-danger btn-sm btn-eliminar" data-id="${id}"><i class="fas fa-trash"></i></button>` : ''}
                        </td>
                    </tr>`;
                });
            })
            .catch(() => {
                tbody.innerHTML =
                    '<tr><td colspan="4" class="text-center text-danger">Error al cargar los datos.</td></tr>';
            });
    };

    const btnNuevo = document.getElementById('btnNuevaFuente');
    if (btnNuevo) {
        btnNuevo.style.display = puedeEscribir ? '' : 'none';
        btnNuevo.addEventListener('click', () => {
            if (!puedeEscribir || !form || !modal) return;
            form.reset();
            document.getElementById('id_fuente').value = '';
            if (chkActivo) chkActivo.checked = true;
            if (titleEl) titleEl.textContent = 'Nueva fuente de financiamiento';
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
            if (chkActivo && !chkActivo.checked) {
                fd.set('activo', '0');
            } else {
                fd.set('activo', '1');
            }
            fetch('../app/ajax/fuentes_financiamiento_ajax.php?action=guardar', { method: 'POST', body: fd })
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
            fetch(`../app/ajax/fuentes_financiamiento_ajax.php?action=obtener&id=${encodeURIComponent(id)}`)
                .then((r) => r.json())
                .then((data) => {
                    if (!data.success || !data.data) {
                        Swal.fire('Error', data.message || 'No se pudo cargar.', 'error');
                        return;
                    }
                    const t = data.data;
                    document.getElementById('id_fuente').value = t.id;
                    document.getElementById('nombre_fuente').value = t.nombre || '';
                    document.getElementById('descripcion_fuente').value = t.descripcion != null ? t.descripcion : '';
                    if (chkActivo) chkActivo.checked = parseInt(t.activo, 10) === 1;
                    if (titleEl) titleEl.textContent = 'Editar fuente de financiamiento';
                    if (modal) modal.show();
                });
            return;
        }

        if (btn.classList.contains('btn-eliminar')) {
            if (!puedeEliminar) return;
            Swal.fire({
                title: '¿Eliminar esta fuente?',
                text: 'No debe haber contratos asociados.',
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
                fetch('../app/ajax/fuentes_financiamiento_ajax.php?action=eliminar', { method: 'POST', body: fd })
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
