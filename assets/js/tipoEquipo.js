document.addEventListener('DOMContentLoaded', () => {
    const formTipoEquipo = document.getElementById('formTipoEquipo');
    const formularioTipoEquipo = document.getElementById('formularioTipoEquipo');
    const cancelarFormulario = document.getElementById('cancelarFormulario');

    // Función para cargar los tipos de equipos en la tabla
    const cargarTiposEquipos = async () => {
        const response = await fetch(`${BASE_URL}controllers/tipoEquipoController.php`, {
            method: 'POST',
            body: new URLSearchParams({ action: 'listar' })
        });
        const tiposEquipos = await response.json();
        const tbody = document.querySelector('#tablaTiposEquipos tbody');
        tbody.innerHTML = '';

        tiposEquipos.forEach(tipo => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${tipo.nombre_tipo_equipo}</td>
                <td>${tipo.descripcion}</td>
                <td>
                    <button class="btn btn-warning" onclick="cargarTipoEquipoParaEditar(${tipo.id_tipo_equipo})">Editar</button>
                    <button class="btn btn-danger" onclick="eliminarTipoEquipo(${tipo.id_tipo_equipo})">Eliminar</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    };

    // Función para cargar los datos de un tipo de equipo para editar
    window.cargarTipoEquipoParaEditar = async (id) => {
        const response = await fetch(`${BASE_URL}controllers/tipoEquipoController.php`, {
            method: 'POST',
            body: new URLSearchParams({ action: 'obtener', id })
        });
        const tipo = await response.json();

        if (tipo) {
            document.getElementById('id_tipo_equipo').value = tipo.id_tipo_equipo;
            document.getElementById('nombre_tipo_equipo').value = tipo.nombre_tipo_equipo;
            document.getElementById('descripcion').value = tipo.descripcion;
            document.getElementById('tituloFormulario').textContent = 'Editar Tipo de Equipo';
            formularioTipoEquipo.style.display = 'block';
        }
    };

    // Función para guardar un nuevo tipo de equipo o editar uno existente
    formTipoEquipo.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(formTipoEquipo);
        const action = document.getElementById('id_tipo_equipo').value ? 'editar' : 'crear';
        
        const response = await fetch(`${BASE_URL}controllers/tipoEquipoController.php`, {
            method: 'POST',
            body: new URLSearchParams({
                action,
                ...Object.fromEntries(formData)
            })
        });

        const result = await response.json();

        if (result.success) {
            Swal.fire('Éxito', action === 'crear' ? 'Tipo de equipo creado con éxito' : 'Tipo de equipo actualizado con éxito', 'success');
            cargarTiposEquipos();
            formularioTipoEquipo.style.display = 'none';
        } else {
            Swal.fire('Error', 'Hubo un problema al guardar el tipo de equipo', 'error');
        }
    });

    // Función para eliminar un tipo de equipo
    window.eliminarTipoEquipo = async (id) => {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: 'Este tipo de equipo será eliminado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            const response = await fetch(`${BASE_URL}controllers/tipoEquipoController.php`, {
                method: 'POST',
                body: new URLSearchParams({ action: 'eliminar', id })
            });

            const data = await response.json();
            if (data.success) {
                Swal.fire('Eliminado', 'Tipo de equipo eliminado con éxito', 'success');
                cargarTiposEquipos();
            } else {
                Swal.fire('Error', 'Hubo un problema al eliminar el tipo de equipo', 'error');
            }
        }
    };

    // Cancelar formulario
    cancelarFormulario.addEventListener('click', () => {
        formularioTipoEquipo.style.display = 'none';
    });

    // Cargar los tipos de equipos al inicio
    cargarTiposEquipos();
});
