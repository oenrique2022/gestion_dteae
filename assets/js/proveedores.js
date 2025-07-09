document.addEventListener('DOMContentLoaded', function () {
    const formProveedor = document.getElementById('formProveedor');
    const tablaProveedores = document.getElementById('tablaProveedores').querySelector('tbody');
    const btnGuardar = document.getElementById('btnGuardar');
    const btnCancelar = document.getElementById('btnCancelar');
    const idProveedor = document.getElementById('idProveedor');

    // Cargar proveedores
    const cargarProveedores = async () => {
        const response = await fetch(`${BASE_URL}/controllers/proveedorController.php`, {
            method: 'POST',
            body: new URLSearchParams({ action: 'listar' })
        });
        const data = await response.json();
        tablaProveedores.innerHTML = data.map(proveedor => `
            <tr>
                <td>${proveedor.id_proveedor}</td>
                <td>${proveedor.nombre_proveedor}</td>
                <td>${proveedor.telefono_contacto || ''}</td>
                <td>${proveedor.email_contacto || ''}</td>
                <td>${proveedor.contacto || ''}</td>
                <td>${proveedor.descripcion || ''}</td>
                <td>
                    <button class="btn btn-sm btn-warning btnEditar" data-id="${proveedor.id_proveedor}">Editar</button>
                    <button class="btn btn-sm btn-danger btnEliminar" data-id="${proveedor.id_proveedor}">Eliminar</button>
                </td>
            </tr>
        `).join('');
        cargarTabla();
    };
 
    // Guardar o actualizar proveedor
    formProveedor.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(formProveedor);
        formData.append('action', idProveedor.value ? 'actualizar' : 'crear');
        const response = await fetch(`${BASE_URL}/controllers/proveedorController.php`, {
            method: 'POST',
            body: formData
        });
        const result = await response.json();
        if (result.success) {
            Swal.fire('¡Éxito!', 'Proveedor guardado correctamente.', 'success');
            formProveedor.reset();
            idProveedor.value = '';
            cargarProveedores();
        } else {
            Swal.fire('Error', 'Ocurrió un problema al guardar el proveedor.', 'error');
        }
    });

    // Editar proveedor
    tablaProveedores.addEventListener('click', async function (e) {
        if (e.target.classList.contains('btnEditar')) {
            const id = e.target.dataset.id;
            const response = await fetch(`${BASE_URL}/controllers/proveedorController.php`, {
                method: 'POST',
                body: new URLSearchParams({ action: 'obtener', id })
            });
            const data = await response.json();
            if (data) {
                idProveedor.value = data.id_proveedor;
                document.getElementById('nombre_proveedor').value = data.nombre_proveedor;
                document.getElementById('telefono_contacto').value = data.telefono_contacto;
                document.getElementById('email_contacto').value = data.email_contacto;
                document.getElementById('contacto').value = data.contacto;
                document.getElementById('descripcion').value = data.descripcion;
            }
        }
    });

    // Eliminar proveedor
    tablaProveedores.addEventListener('click', async function (e) {
        if (e.target.classList.contains('btnEliminar')) {
            const id = e.target.dataset.id;
            const confirm = await Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (confirm.isConfirmed) {
                const response = await fetch(`${BASE_URL}/controllers/proveedorController.php`, {
                    method: 'POST',
                    body: new URLSearchParams({ action: 'eliminar', id })
                });
                const result = await response.json();
                if (result.success) {
                    Swal.fire('¡Eliminado!', 'El proveedor ha sido eliminado.', 'success');
                    cargarProveedores();
                } else {
                    Swal.fire('Error', 'No se pudo eliminar el proveedor.', 'error');
                }
            }
        }
    });
    cargarProveedores(); // Inicializar la tabla
    // Cancelar acción
    btnCancelar.addEventListener('click', function () {
        formProveedor.reset();
        idProveedor.value = '';
    });

   
});
