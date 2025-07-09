

// Llama a la función al cargar la página


document.addEventListener("DOMContentLoaded", () => {
    const formCapacitacion = document.getElementById("formCapacitacion");
    const tablaCapacitacionesBody = document.querySelector("#tablaCapacitaciones tbody");
    const equipoSelect = document.getElementById("id_equipo");
    const institucionSelect = document.getElementById("id_institucion");

    // Guardar capacitación
    formCapacitacion.addEventListener('submit', async (e) => {
        e.preventDefault();
    
        const formData = new FormData(formCapacitacion);
        const idCapacitacion = document.getElementById('idCapacitacion').value;
    
        // Determina la acción: 'crear' o 'actualizar'
        const action = idCapacitacion ? 'actualizar' : 'crear';
        formData.append('action', action);
    
        try {
            const response = await fetch(`${BASE_URL}controllers/capacitacionesController.php`, {
                method: 'POST',
                body: formData,
            });
    
            const result = await response.json();
            if (result.success) {
                mostrarNotificacion(action === 'crear' ? 'Capacitación creada con éxito' : 'Capacitación actualizada con éxito');
                cargarCapacitaciones(); // Recarga la lista
                formCapacitacion.reset(); // Limpia el formulario
                document.getElementById('idCapacitacion').value = ''; // Limpia el ID
            } else {
                alert('Error al guardar la capacitación');
            }
        } catch (error) {
            console.error('Error al guardar la capacitación:', error);
        }
    });
    
    async function cargarInstituciones() {
        try {
            const response = await fetch(`${BASE_URL}controllers/capacitacionesController.php?action=listarInstituciones`, {
                method: 'POST'
            });
            const instituciones = await response.json();
            const institucionSelect = document.getElementById('id_institucion');
            institucionSelect.innerHTML = '<option value="">Seleccione una institución</option>';
            instituciones.forEach(institucion => {
                const option = document.createElement('option');
                option.value = institucion.centro_id;
                option.textContent = institucion.nombre_ce;
                institucionSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error al cargar instituciones:', error);
        }
    }
    // Cargar listado de capacitaciones
    async function cargarCapacitaciones() {
        const response = await fetch(`${BASE_URL}controllers/capacitacionesController.php?action=listar`, {
            method: "POST",
        });

        const capacitaciones = await response.json();
        tablaCapacitacionesBody.innerHTML = capacitaciones
            .map(
                (capacitacion) => `
            <tr>
                <th>${capacitacion.codigo_infraestructura}</th>
                <td>${capacitacion.institucion}</td>
                <td>${capacitacion.nombre_capacitacion}</td>
                <td>${capacitacion.equipo}</td>
                <td>${capacitacion.fecha}</td>
                <td>${capacitacion.duracion} horas</td>
                <td>${capacitacion.tipo_capacitacion}</td>
                <td>${capacitacion.docentes_capacitados}</td>
                <td>${capacitacion.estudiantes_capacitados}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="cargarCapacitacion(${capacitacion.id_capacitacion})">Editar</button>
                </td>
            </tr>`
            )
            .join("");
            cargarTabla();
    }

    tablaCapacitacionesBody.addEventListener('click', (event) => {
        if (event.target.classList.contains('btn-editar')) {
            const id = event.target.getAttribute('data-id');
            cargarCapacitacion(id);
        }
    });
    
    // Cargar equipos dinámicamente
    async function cargarEquipos() {
        const response = await fetch(`${BASE_URL}controllers/capacitacionesController.php?action=listarEquipos`, {
            method: "POST",
        });

        const equipos = await response.json();
        equipoSelect.innerHTML = '<option value="">Seleccione un equipo</option>';
        equipos.forEach((equipo) => {
            const option = document.createElement("option");
            option.value = equipo.id_equipo;
            option.textContent = equipo.nombre_equipo;
            equipoSelect.appendChild(option);
        });
    }

    // Editar capacitación
    async function editarCapacitacion(idCapacitacion) {
        const response = await fetch(`${BASE_URL}controllers/capacitacionesController.php?action=obtener&id=${idCapacitacion}`, {
            method: "GET",
        });

        const capacitacion = await response.json();
        if (capacitacion) {
            document.getElementById("idCapacitacion").value = capacitacion.id_capacitacion;
            document.getElementById("nombre_capacitacion").value = capacitacion.nombre_capacitacion;
            document.getElementById("id_equipo").value = capacitacion.id_equipo;
            document.getElementById("fecha").value = capacitacion.fecha;
            document.getElementById("duracion").value = capacitacion.duracion;
            document.getElementById("tipo_capacitacion").value = capacitacion.tipo_capacitacion;
            document.getElementById("docentes_capacitados").value = capacitacion.docentes_capacitados;
            document.getElementById("estudiantes_capacitados").value = capacitacion.estudiantes_capacitados;
            document.getElementById("descripcion").value = capacitacion.descripcion;

            // Cambiar a la pestaña del formulario
            const tabFormulario = document.querySelector('#capacitacionesTab button[data-bs-target="#formulario"]');
            const tab = new bootstrap.Tab(tabFormulario);
            tab.show();
        }
    }

    // Eliminar capacitación
    async function eliminarCapacitacion(idCapacitacion) {
        if (!confirm("¿Estás seguro de eliminar esta capacitación?")) {
            return;
        }

        const response = await fetch(`${BASE_URL}controllers/capacitacionesController.php?action=eliminar`, {
            method: "POST",
            body: new URLSearchParams({ id: idCapacitacion }),
        });

        const result = await response.json();
        if (result.success) {
            alert("Capacitación eliminada con éxito");
            cargarCapacitaciones();
        } else {
            alert("Error al eliminar capacitación");
        }
    }

    // Inicializar carga de datos
    cargarCapacitaciones();
    cargarEquipos();
    cargarInstituciones();
});

async function cargarCapacitacion(id) {
    try {
        const response = await fetch(`${BASE_URL}controllers/capacitacionesController.php`, {
            method: 'POST',
            body: new URLSearchParams({ action: 'obtenerCapacitacion', id_capacitacion: id }),
        });
        const capacitacion = await response.json();

        if (capacitacion) {
            // Llena el formulario con los datos
            document.getElementById('idCapacitacion').value = capacitacion.id_capacitacion;
            document.getElementById('nombre_capacitacion').value = capacitacion.nombre_capacitacion;
            document.getElementById('id_equipo').value = capacitacion.id_equipo;
            document.getElementById('id_institucion').value = capacitacion.id_institucion;
            document.getElementById('fecha').value = capacitacion.fecha;
            document.getElementById('duracion').value = capacitacion.duracion;
            document.getElementById('tipo_capacitacion').value = capacitacion.tipo_capacitacion;
            document.getElementById('docentes_capacitados').value = capacitacion.docentes_capacitados;
            document.getElementById('estudiantes_capacitados').value = capacitacion.estudiantes_capacitados;
            document.getElementById('descripcion').value = capacitacion.descripcion || '';

            // Activa la pestaña de edición
            const tabEditar = document.querySelector('#capacitacionesTab button[data-bs-target="#formulario"]');
            const tab = new bootstrap.Tab(tabEditar);
            tab.show();

            // Lleva el formulario a la vista
            document.getElementById('formulario').scrollIntoView({ behavior: 'smooth' });
        } else {
            alert('No se pudo cargar la capacitación');
        }
    } catch (error) {
        console.error('Error al cargar la capacitación:', error);
    }
}