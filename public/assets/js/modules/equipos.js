document.addEventListener('DOMContentLoaded', function() {
    const tablaEquiposBody = document.getElementById('tablaEquiposBody');
    if (!tablaEquiposBody) return; // Si no estamos en la página de equipos, no hacer nada

    const equipoModal = new bootstrap.Modal(document.getElementById('equipoModal'));
    const equipoForm = document.getElementById('equipoForm');
    const equipoModalTitle = document.getElementById('equipoModalTitle');

    const cargarEquipos = () => {
        fetch('../app/ajax/equipos_ajax.php?action=listar')
            .then(response => response.json())
            .then(data => {
                tablaEquiposBody.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(equipo => {
                        const isChecked = equipo.activo == 1 ? 'checked' : '';
                        tablaEquiposBody.innerHTML += `
                            <tr>
                                <td>${equipo.codigo_equipo}</td>
                                <td>${equipo.nombre_equipo}</td>
                                <td>${equipo.nombre_tipo_equipo}</td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input estado-switch" type="checkbox" role="switch" data-id="${equipo.id_equipo}" ${isChecked}>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-warning btn-sm btn-editar" data-id="${equipo.id_equipo}"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="${equipo.id_equipo}"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tablaEquiposBody.innerHTML = '<tr><td colspan="5" class="text-center">No hay equipos registrados.</td></tr>';
                }
            });
    };

    document.getElementById('btnNuevoEquipo').addEventListener('click', () => {
        equipoForm.reset();
        document.getElementById('id_equipo').value = '';
        equipoModalTitle.textContent = 'Nuevo Equipo';
        equipoModal.show();
    });

    equipoForm.addEventListener('submit', function(e) {
        e.preventDefault();
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
            fetch(`../app/ajax/equipos_ajax.php?action=obtener&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const eq = data.data;
                        document.getElementById('id_equipo').value = eq.id_equipo;
                        document.getElementById('codigo_equipo').value = eq.codigo_equipo;
                        document.getElementById('nombre_equipo').value = eq.nombre_equipo;
                        document.getElementById('id_tipo_equipo').value = eq.id_tipo_equipo;
                        document.getElementById('descripcion').value = eq.descripcion;
                        equipoModalTitle.textContent = 'Editar Equipo';
                        equipoModal.show();
                    }
                });
        }

        if (target.classList.contains('btn-eliminar')) {
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