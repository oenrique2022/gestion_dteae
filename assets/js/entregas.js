const formEntrega = document.getElementById("formEntrega");
const formDetalle = document.getElementById("formDetalleEntrega");
const detalleBody = document.getElementById("detalleEntregaBody");
const contratoSelect = document.getElementById("contrato_id");
const institucionSelect = document.getElementById("institucion_id");
const equipoSelect = document.getElementById("equipo_id");
const tablaEntregasBody = document.querySelector("#tablaEntregas tbody");
document.addEventListener("DOMContentLoaded", () => {


    // Guardar encabezado de entrega
    formEntrega.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(formEntrega);
        formData.append("action", "crear");
        const response = await fetch(`${BASE_URL}controllers/entregasController.php`, {
            method: "POST",
            body: formData,
        });
    
        const result = await response.json();
        if (result.success) {
            alert("Entrega registrada con éxito");
           // formEntrega.reset();
            document.getElementById("id_entrega").value = result.id_entrega; // Asignar el ID de entrega
        } else {
            alert("Error al registrar entrega");
        }
    });
    

    // Guardar detalle de entrega
    formDetalle.addEventListener("submit", async (e) => {
        e.preventDefault();
    
        const entregaId = document.getElementById("id_entrega").value;
        if (!entregaId) {
            alert("Primero debe registrar una entrega.");
            return;
        }
    
        const formData = new FormData(formDetalle);
        formData.append("action", "agregarDetalle");
        formData.append("entrega_id", entregaId); // Asegurarse de enviar el ID de entrega
    
        const response = await fetch(`${BASE_URL}controllers/entregasController.php`, {
            method: "POST",
            body: formData,
        });
    
        const result = await response.json();
        if (result.success) {
            mostrarNotificacion('Equipo agregado con éxito');
          
            cargarDetalles(entregaId);
            formDetalle.reset();
        } else {
            alert("Error al agregar equipo");
        }
    });
    



    // Función para cargar contratos
    async function cargarContratos() {
        const response = await fetch(`${BASE_URL}controllers/entregasController.php?action=listarContratos`,{method:'POST'});
        const contratos = await response.json();
        contratoSelect.innerHTML = '<option value="">Seleccione un contrato</option>';
        contratos.forEach((contrato) => {
            const option = document.createElement("option");
            option.value = contrato.id;
            option.textContent = contrato.nombre_contrato;
            contratoSelect.appendChild(option);
        });
    }

    // Función para cargar instituciones
    async function cargarInstituciones() {
        const response = await fetch(`${BASE_URL}controllers/entregasController.php?action=listarInstituciones`,{method:'POST'});
        const instituciones = await response.json();
        institucionSelect.innerHTML = '<option value="">Seleccione una institución</option>';
        instituciones.forEach((institucion) => {
            //console.log(institucion);
            const option = document.createElement("option");
            option.value = institucion.centro_id;
            option.textContent = institucion.nombre_ce;
            institucionSelect.appendChild(option);
        });
    }

    // Función para cargar equipos
    async function cargarEquipos() {
        const response = await fetch(`${BASE_URL}controllers/entregasController.php?action=listarEquipos`,{method:'POST'});
        const equipos = await response.json();
        equipoSelect.innerHTML = '<option value="">Seleccione un equipo</option>';
        equipos.forEach((equipo) => {
            const option = document.createElement("option");
            option.value = equipo.id_equipo;
            option.textContent = equipo.nombre_equipo;
            equipoSelect.appendChild(option);
        });
    }

  
    async function cargarEntregas() {
        const response = await fetch(`${BASE_URL}controllers/entregasController.php?action=listarEntregas`, {
            method: "POST",
            body: new URLSearchParams({ action: "listarEntregas" })
        });
    
        const entregas = await response.json();
        tablaEntregasBody.innerHTML = "";
    
        entregas.forEach((entrega) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${entrega.codigo_infraestructura}</td>
                <td>${entrega.nombre_ce}</td>
                <td>${entrega.nombre_contrato}</td>
                <td>${entrega.fecha_entrega}</td>
                <td>${entrega.comentarios || ""}</td>
                <td>${entrega.estado}</td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="cargarEntrega(${entrega.id_entrega})">Detalles</button>
                    <a target='_blank' class="btn btn-warning btn-sm" href="../reports/acta_entrega.php?idEntrega=${entrega.id_entrega}">Generar acta</a>
                </td>
            `;
            tablaEntregasBody.appendChild(row);
        });
        cargarTabla();
    }

     // Inicializar
     cargarContratos();
     cargarInstituciones();
     cargarEquipos();
     cargarEntregas();
});


async function cargarEntrega(idEntrega) {
    const response = await fetch(`${BASE_URL}controllers/entregasController.php`, {
        method: "POST",
        body: new URLSearchParams({ action: "obtenerEntrega", id_entrega: idEntrega }),
    });

    const entrega = await response.json();
    if (entrega) {
        document.getElementById("idEntrega").value = entrega.id_entrega;
        document.getElementById("contrato_id").value = entrega.id_contrato;
        document.getElementById("institucion_id").value = entrega.id_institucion;
        document.getElementById("fecha_entrega").value = entrega.fecha_entrega;
        document.getElementById("estado").value = entrega.estado;
        document.getElementById("comentarios").value = entrega.comentarios || "";
        document.getElementById("firma_responsable").value = entrega.firma_responsable || "";

        document.getElementById("formEntrega").scrollIntoView({ behavior: "smooth" });
                // Activar la pestaña de registro
                const tabRegistrar = document.querySelector('#entregasTab button[data-bs-target="#registrar"]');
                const tab = new bootstrap.Tab(tabRegistrar); // Crear una instancia del Tab
                tab.show(); // Activar la pestaña
                document.getElementById("id_entrega").value =idEntrega;
                cargarDetalles(idEntrega);
    }
}



    // Cargar detalles de una entrega
    async function cargarDetalles(entregaId) {
        const response = await fetch(`${BASE_URL}controllers/entregasController.php?action=listarDetalles&id_entrega=${entregaId}`,{method:'POST'});
        const detalles = await response.json();
        detalleBody.innerHTML = detalles
            .map(
                (detalle) => `
            <tr>
                <td>${detalle.nombre_equipo}</td>
                <td>${detalle.cantidad}</td>
                <td>${detalle.precio}</td>
                <td>${detalle.comentario}</td>
                <td>
                    <button class="btn btn-danger btn-sm eliminar-detalle" data-id="${detalle.id_detalle}">Eliminar</button>
                </td>
            </tr>`
            )
            .join("");
    }