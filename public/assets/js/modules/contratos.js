document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCrearContrato');
    if (!form) return;

    // Lógica para añadir filas de equipos dinámicamente (simplificada)
    // En un caso real, la variable 'equiposDisponibles' se llenaría con un fetch al cargar la página
    document.getElementById('btnAgregarEquipo').addEventListener('click', () => {
        const tbody = document.getElementById('detalleEquiposBody');
        const newRow = `
            <tr>
                <td><select name="equipo_id[]" class="form-control">...</select></td>
                <td><input type="text" name="marca[]" class="form-control"></td>
                <td><input type="number" name="cantidad[]" class="form-control" value="1"></td>
                <td><input type="number" step="0.01" name="precio[]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove()">X</button></td>
            </tr>
        `;
        tbody.insertAdjacentHTML('beforeend', newRow);
    });

    // Lógica para previsualizar archivos y añadir campo de descripción
    const inputArchivos = document.getElementById('documentos');
    const listaArchivos = document.getElementById('listaArchivos');
    inputArchivos.addEventListener('change', () => {
        listaArchivos.innerHTML = '';
        Array.from(inputArchivos.files).forEach((file, index) => {
            listaArchivos.innerHTML += `
                <div class="d-flex align-items-center mb-2">
                    <span class="me-2"><i class="fas fa-file"></i> ${file.name}</span>
                    <input type="text" name="descripcion_archivo[${index}]" class="form-control form-control-sm" placeholder="Descripción del archivo">
                </div>
            `;
        });
    });

    // Envío del formulario
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'guardar');

        Swal.fire({
            title: 'Guardando Contrato',
            text: 'Por favor, espere...',
            didOpen: () => { Swal.showLoading() }
        });

        fetch('../app/ajax/contratos_ajax.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                    window.location.href = 'gestion_contratos.php'; // Redirigir a la lista de contratos
                });
            } else {
                Swal.fire('Error', data.message, 'error');
            }
        })
        .catch(error => {
            Swal.fire('Error de Conexión', 'No se pudo comunicar con el servidor.', 'error');
        });
    });
    // ... dentro del addEventListener('DOMContentLoaded', ...) ...

const listaArchivosExistentes = document.getElementById('listaArchivosExistentes');

if (listaArchivosExistentes) {
    listaArchivosExistentes.addEventListener('click', function(e) {
        const target = e.target.closest('.btn-eliminar-documento');
        if (!target) return;

        const id_documento = target.getAttribute('data-id');

        Swal.fire({
            title: '¿Seguro que quieres eliminar este archivo?',
            text: "Esta acción es irreversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'eliminar_documento');
                formData.append('id_documento', id_documento);

                fetch('../app/ajax/contratos_ajax.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`doc-${id_documento}`).remove();
                        Swal.fire('¡Eliminado!', data.message, 'success');
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }
        });
    });
}
});