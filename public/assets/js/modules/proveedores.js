// Espera a que todo el HTML esté cargado para ejecutar el script
document.addEventListener('DOMContentLoaded', function() {

    // Revisa si estamos en la página de gestión de proveedores
    if (document.getElementById('tablaProveedoresBody')) {
        const permisos = window.APP_PERMISOS || {};
        const puedeEscribir = !!permisos.puedeEscribir;
        const puedeEliminar = !!permisos.puedeEliminar;
        const proveedorModal = new bootstrap.Modal(document.getElementById('proveedorModal'));
        const proveedorForm = document.getElementById('proveedorForm');
        const modalTitle = document.getElementById('modalTitle');
        const tablaBody = document.getElementById('tablaProveedoresBody');

        // Función para cargar y mostrar los proveedores en la tabla
        const cargarProveedores = () => {
            fetch('../app/ajax/proveedores_ajax.php?action=listar')
                .then(response => response.json())
                .then(data => {
                    tablaBody.innerHTML = ''; // Limpiar la tabla
                    if (data.success && data.data.length > 0) {
                        data.data.forEach(p => {
                            tablaBody.innerHTML += `
                                <tr>
                                    <td>${p.nombre_proveedor}</td>
                                    <td>${p.contacto}</td>
                                    <td>${p.telefono_contacto}</td>
                                    <td>${p.email_contacto}</td>
                                    <td class="text-end">
                                        ${puedeEscribir ? `<button class="btn btn-warning btn-sm btn-editar" data-id="${p.id_proveedor}">
                                            <i class="fas fa-edit"></i>
                                        </button>` : ''}
                                        ${puedeEliminar ? `<button class="btn btn-danger btn-sm btn-eliminar" data-id="${p.id_proveedor}">
                                            <i class="fas fa-trash"></i>
                                        </button>` : ''}
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        tablaBody.innerHTML = '<tr><td colspan="5" class="text-center">No hay proveedores registrados.</td></tr>';
                    }
                });
        };

        // Abrir modal para un nuevo proveedor
        const btnNuevo = document.getElementById('btnNuevoProveedor');
        if (btnNuevo) {
            btnNuevo.style.display = puedeEscribir ? '' : 'none';
            btnNuevo.addEventListener('click', () => {
                if (!puedeEscribir) return;
                proveedorForm.reset();
                document.getElementById('id_proveedor').value = '';
                modalTitle.textContent = 'Nuevo Proveedor';
                proveedorModal.show();
            });
        }

        // Guardar (Crear o Actualizar) proveedor
        proveedorForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (!puedeEscribir) {
                Swal.fire('Sin permiso', 'Solo puede consultar esta sección.', 'warning');
                return;
            }
            const formData = new FormData(this);

            fetch('../app/ajax/proveedores_ajax.php?action=guardar', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    proveedorModal.hide();
                    Swal.fire('¡Éxito!', data.message, 'success');
                    cargarProveedores();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        });
        
        // Clicks en botones de la tabla (Editar y Eliminar)
        tablaBody.addEventListener('click', function(e) {
            const target = e.target.closest('button');
            if (!target) return;

            const id = target.getAttribute('data-id');

            // Botón Editar
            if (target.classList.contains('btn-editar')) {
                if (!puedeEscribir) return;
                fetch(`../app/ajax/proveedores_ajax.php?action=obtener&id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const p = data.data;
                        document.getElementById('id_proveedor').value = p.id_proveedor;
                        document.getElementById('nombre_proveedor').value = p.nombre_proveedor;
                        document.getElementById('contacto').value = p.contacto;
                        document.getElementById('telefono_contacto').value = p.telefono_contacto;
                        document.getElementById('email_contacto').value = p.email_contacto;
                        document.getElementById('descripcion').value = p.descripcion;
                        modalTitle.textContent = 'Editar Proveedor';
                        proveedorModal.show();
                    }
                });
            }

            // Botón Eliminar
            if (target.classList.contains('btn-eliminar')) {
                if (!puedeEliminar) return;
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esta acción!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, ¡eliminar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('id', id);
                        
                        fetch('../app/ajax/proveedores_ajax.php?action=eliminar', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('¡Eliminado!', data.message, 'success');
                                cargarProveedores();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        });
                    }
                });
            }
        });

        // Cargar los proveedores al iniciar la página
        cargarProveedores();
    }
});