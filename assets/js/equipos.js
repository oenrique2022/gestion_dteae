document.addEventListener('DOMContentLoaded', () => {
    const formEquipo = document.getElementById('formEquipo');
    const tablaEquipos = document.getElementById('tablaEquipos').querySelector('tbody');
    let equipoEditar = null;

    const cargarEquipos = async () => {
        const response = await fetch(`${BASE_URL}controllers/equipoController.php`, {
            method: 'POST',
            body: new URLSearchParams({ action: 'listar' }),
        });
        const equipos = await response.json();

        tablaEquipos.innerHTML = equipos
            .map(
                (e) => `
                <tr>
                    <td>${e.codigo_equipo}</td>
                    <td>${e.nombre_equipo}</td>
                    <td>${e.nombre_tipo_equipo}</td>
                    <td>${e.estado}</td>
                    <td>${e.fecha_adquisicion || 'N/A'}</td>
                    <td>${e.descripcion || ''}</td>
                    <td>
                        <button class="btn btn-warning btn-sm editar" data-id="${e.id_equipo}">Editar</button>
                        <button class="btn btn-danger btn-sm eliminar" data-id="${e.id_equipo}">Eliminar</button>
                    </td>
                </tr>
            `
            )
            .join('');
            cargarTabla();
        // Agregar eventos a los botones
        document.querySelectorAll('.editar').forEach((btn) =>
            btn.addEventListener('click', (e) => cargarEquipoParaEditar(e.target.dataset.id))
        );
        document.querySelectorAll('.eliminar').forEach((btn) =>
            btn.addEventListener('click', (e) => eliminarEquipo(e.target.dataset.id))
        );
    };

    const cargarEquipoParaEditar = async (id) => {
        const response = await fetch(`${BASE_URL}controllers/equipoController.php`, {
            method: 'POST',
            body: new URLSearchParams({ action: 'obtener', id }),
        });
    
        const responseData = await response.json(); // Esta es la respuesta JSON
    
        // Verificamos si la respuesta es exitosa
        if (responseData.success) {
            const equipo = responseData.data;  // Accedemos a los datos del equipo dentro de 'data'
    
            // Asignamos los valores al formulario
            formEquipo.codigo_equipo.value = equipo.codigo_equipo;
            formEquipo.nombre_equipo.value = equipo.nombre_equipo;
            formEquipo.id_tipo_equipo.value = equipo.id_tipo_equipo;
            formEquipo.estado.value = equipo.estado;
            formEquipo.fecha_adquisicion.value = equipo.fecha_adquisicion || '';  // Manejo de valores vacíos
            formEquipo.descripcion.value = equipo.descripcion || ''; // Manejo de valores vacíos
        } else {
            console.error('Error al cargar el equipo:', responseData.message);
            // Aquí podrías agregar un mensaje de error a la interfaz si es necesario
        }
    };
    

    const eliminarEquipo = async (id) => {
        const confirm = await Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        });

        if (confirm.isConfirmed) {
            const response = await fetch(`${BASE_URL}controllers/equipoController.php`, {
                method: 'POST',
                body: new URLSearchParams({ action: 'eliminar', id }),
            });
            const result = await response.json();

            if (result.success) {
                Swal.fire('Eliminado', result.message, 'success');
                cargarEquipos();
            } else {
                Swal.fire('Error', result.message || 'No se pudo eliminar el equipo', 'error');
            }
        }
    };

    formEquipo.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(formEquipo);
        formData.append('action', equipoEditar ? 'editar' : 'crear');
        if (equipoEditar) formData.append('id', equipoEditar);

        const response = await fetch(`${BASE_URL}controllers/equipoController.php`, {
            method: 'POST',
            body: formData,
        });

        const result = await response.json();
        if (result.success) {
            Swal.fire('Éxito', result.message, 'success');
            formEquipo.reset();
            equipoEditar = null;
            cargarEquipos();
        } else {
            Swal.fire('Error', result.message || 'Hubo un error al procesar la solicitud', 'error');
        }
    });

    cargarEquipos();
});
