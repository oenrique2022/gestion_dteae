document.addEventListener('DOMContentLoaded', function() {
    const tablaEquiposBody = document.getElementById('tablaEquiposBody');
    if (!tablaEquiposBody) return; // Si no estamos en la página de equipos, no hacer nada
    const permisos = window.APP_PERMISOS || {};
    const puedeEscribir = !!permisos.puedeEscribir;
    const puedeEliminar = !!permisos.puedeEliminar;

    const equipoModal = new bootstrap.Modal(document.getElementById('equipoModal'));
    const equipoForm = document.getElementById('equipoForm');
    const equipoModalTitle = document.getElementById('equipoModalTitle');

    const fmtFechaTabla = (v) => {
        if (v == null || String(v).trim() === '') return '—';
        const s = String(v);
        return s.length >= 10 ? s.substring(0, 10) : s;
    };

    const cargarEquipos = () => {
        fetch('../app/ajax/equipos_ajax.php?action=listar')
            .then(response => response.json())
            .then(data => {
                tablaEquiposBody.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(equipo => {
                        const isChecked = equipo.activo == 1 ? 'checked' : '';
                        const estadoInv = equipo.estado != null && String(equipo.estado).trim() !== '' ? equipo.estado : '—';
                        tablaEquiposBody.innerHTML += `
                            <tr>
                                <td>${equipo.codigo_equipo}</td>
                                <td>${equipo.nombre_equipo}</td>
                                <td>${equipo.nombre_tipo_equipo}</td>
                                <td>${estadoInv}</td>
                                <td>${fmtFechaTabla(equipo.fecha_adquisicion)}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input estado-switch" type="checkbox" role="switch" data-id="${equipo.id_equipo}" ${isChecked} title="Activo en catálogo">
                                    </div>
                                </td>
                                <td class="text-end">
                                    ${puedeEscribir ? `<button class="btn btn-warning btn-sm btn-editar" data-id="${equipo.id_equipo}"><i class="fas fa-edit"></i></button>` : ''}
                                    ${puedeEliminar ? `<button class="btn btn-danger btn-sm btn-eliminar" data-id="${equipo.id_equipo}"><i class="fas fa-trash"></i></button>` : ''}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tablaEquiposBody.innerHTML = '<tr><td colspan="7" class="text-center">No hay equipos registrados.</td></tr>';
                }
            });
    };

    const btnNuevoEquipo = document.getElementById('btnNuevoEquipo');
    if (btnNuevoEquipo) {
        btnNuevoEquipo.style.display = puedeEscribir ? '' : 'none';
        btnNuevoEquipo.addEventListener('click', () => {
            if (!puedeEscribir) return;
            equipoForm.reset();
            document.getElementById('id_equipo').value = '';
            document.getElementById('estado_inventario').value = 'En inventario';
            document.getElementById('fecha_adquisicion').value = '';
            equipoModalTitle.textContent = 'Nuevo Equipo';
            equipoModal.show();
        });
    }

    equipoForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (!puedeEscribir) {
            Swal.fire('Sin permiso', 'Solo puede consultar esta sección.', 'warning');
            return;
        }
        const formData = new FormData(this);
        fetch('../app/ajax/equipos_ajax.php?action=guardar', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    equipoModal.hide();
                    Swal.fire('¡Éxito!', data.message, 'success');
                    cargarEquipos();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
    });

    tablaEquiposBody.addEventListener('click', function(e) {
        const target = e.target.closest('button');
        if (!target) return;

        const id = target.getAttribute('data-id');

        if (target.classList.contains('btn-editar')) {
            if (!puedeEscribir) return;
            fetch(`../app/ajax/equipos_ajax.php?action=obtener&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const eq = data.data;
                        document.getElementById('id_equipo').value = eq.id_equipo;
                        document.getElementById('codigo_equipo').value = eq.codigo_equipo;
                        document.getElementById('nombre_equipo').value = eq.nombre_equipo;
                        document.getElementById('id_tipo_equipo').value = eq.id_tipo_equipo;
                        document.getElementById('descripcion').value = eq.descripcion || '';
                        const selEst = document.getElementById('estado_inventario');
                        const ev = eq.estado != null ? String(eq.estado).trim() : 'En inventario';
                        if (Array.from(selEst.options).some((o) => o.value === ev)) {
                            selEst.value = ev;
                        } else if (ev) {
                            const opt = document.createElement('option');
                            opt.value = ev;
                            opt.textContent = ev;
                            selEst.appendChild(opt);
                            selEst.value = ev;
                        }
                        const fa = eq.fecha_adquisicion;
                        document.getElementById('fecha_adquisicion').value =
                            fa != null && String(fa).trim() !== '' ? String(fa).substring(0, 10) : '';
                        equipoModalTitle.textContent = 'Editar Equipo';
                        equipoModal.show();
                    }
                });
        }

        if (target.classList.contains('btn-eliminar')) {
            if (!puedeEliminar) return;
            Swal.fire({
                title: '¿Estás seguro?', text: "¡No podrás revertir esto!", icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, ¡eliminar!', cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);
                    fetch('../app/ajax/equipos_ajax.php?action=eliminar', { method: 'POST', body: formData })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('¡Eliminado!', data.message, 'success');
                                cargarEquipos();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                }
            });
        }
    });
    
    tablaEquiposBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('estado-switch')) {
            if (!puedeEscribir) {
                e.target.checked = !e.target.checked;
                Swal.fire('Sin permiso', 'Solo puede consultar esta sección.', 'warning');
                return;
            }
            const id = e.target.getAttribute('data-id');
            const estado = e.target.checked ? 1 : 0;
            
            const formData = new FormData();
            formData.append('id', id);
            formData.append('estado', estado);

            fetch('../app/ajax/equipos_ajax.php?action=cambiar_estado', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: data.message, showConfirmButton: false, timer: 2000 });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                        e.target.checked = !estado; // Revertir el switch si hay un error
                    }
                });
        }
    });

    cargarEquipos();
});