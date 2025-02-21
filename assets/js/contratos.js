document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById('formContrato');
    const spinner = document.getElementById('spinner');
    const tableBody = document.getElementById('tablaContratosBody');
    const proveedorSelect = document.getElementById('proveedor_id'); // Asegúrate de que el ID coincide
    const fuenteFinanciamiento = document.getElementById('fuente_financiamiento_id'); // Asegúrate de que el ID coincide

    // Función para cargar los datos en la tabla
    function cargarContratos() {
        fetch(`${BASE_URL}controllers/contratosController.php?accion=listar`)
            .then(response => response.json())
            .then(data => {
                //console.log("Datos recibidos del servidor:", data); // Depuración
                tableBody.innerHTML = ''; // Limpiar la tabla antes de insertar los nuevos datos

                if (data.length > 0) {
                    data.forEach(contrato => {
                        const row = document.createElement('tr'); // Crear una nueva fila

                        row.innerHTML = `
                            <td>${contrato.id}</td>
                            <td>${contrato.nombre_contrato || 'No disponible'}</td>
                            <td>${contrato.proveedor}</td>
                            <td>${contrato.fuente_financiamiento}</td>
                            <td>${contrato.nombre_encargado}</td>
                            <td>${contrato.comentarios}</td>
                            <td>${contrato.fecha_inicio}</td>
                            <td>
                                <button class="btn btn-warning btn-sm editar" data-id="${contrato.id}">Editar</button>
                                <button class="btn btn-danger btn-sm eliminar" data-id="${contrato.id}">Eliminar</button>
                            </td>
                        `;

                        tableBody.appendChild(row); // Insertar la fila en el tbody
                    });
                    cargarTabla();
                } else {
                    tableBody.innerHTML = '<tr><td colspan="8">No hay contratos disponibles.</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error al cargar los contratos:', error);
                tableBody.innerHTML = '<tr><td colspan="8">Hubo un error al cargar los contratos.</td></tr>';
            });
       
    }

    function cargarProveedores() {
        fetch(`${BASE_URL}controllers/contratosController.php?accion=listar_proveedores`)
            .then(response => response.json())
            .then(data => {
                // Acceder a la propiedad 'proveedores' en lugar de 'data' directamente
                const proveedores = data.proveedores;
                proveedorSelect.innerHTML = ''; // Limpiar el select antes de agregar las opciones
                proveedores.forEach(proveedor => {
                    const option = document.createElement('option');
                    option.value = proveedor.id_proveedor; // Usar el ID del proveedor como valor
                    option.textContent = proveedor.nombre_proveedor; // Mostrar el nombre del proveedor
                    proveedorSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error al cargar los proveedores:', error);
            });
    }
    

    // Función para cargar las fuentes de financiamiento en el select
    function cargarFuentes() {
        fetch(`${BASE_URL}controllers/contratosController.php?accion=listar_fuentes`)
            .then(response => response.json())
            .then(data => {
                fuenteFinanciamiento.innerHTML = ''; // Limpiar el select antes de agregar las opciones
                data.forEach(fuente => {
                    const option = document.createElement('option');
                    option.value = fuente.id; // Usar el ID de la fuente como valor
                    option.textContent = fuente.nombre; // Mostrar el nombre de la fuente
                    fuenteFinanciamiento.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error al cargar las fuentes:', error);
            });
    }
    cargarContratos();
    cargarProveedores();
    cargarFuentes();
    function cargarContratoParaEditar(id) {
        fetch(`${BASE_URL}controllers/contratosController.php?accion=obtener`, {
            method: 'POST',
            body: new URLSearchParams({ id })
        })
            .then(response => response.json())
            .then(data => {
                document.getElementById('idContrato').value = data.id;
                document.getElementById('contrato_id_equipo').value = data.id;
                document.getElementById('nombre_contrato').value = data.nombre_contrato;
                document.getElementById('numero_contrato').value = data.numero_contrato;
                document.getElementById('proveedor_id').value = data.proveedor_id;
                document.getElementById('fuente_financiamiento_id').value = data.fuente_financiamiento_id;
                document.getElementById('nombre_encargado').value = data.nombre_encargado;
                document.getElementById('comentarios').value = data.comentarios;
                document.getElementById('numero_contrato').value = data.numero_contrato;
                document.getElementById('fecha_contrato').value = data.fecha_inicio;
                document.getElementById('fecha_cierre_contrato').value = data.fecha_fin || ''; // Manejo de nulo
                cargarEquiposAsociados(data.id);
                // Cambiar la pestaña al formulario
                const registrarTab = document.getElementById('registrar-tab');
                registrarTab.click();
            })
            .catch(error => console.error('Error al cargar el contrato para editar:', error));
    }
    
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('editar')) {
            const contratoId = event.target.dataset.id;
            cargarContratoParaEditar(contratoId);
        }
    });

    
    // Envío del formulario
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        const formData = new FormData(form);
        if(Number(document.getElementById("idContrato").value)>0)
        formData.append("accion","actualizar");
        else
        formData.append("accion","crear");
        spinner.style.display = 'flex';

        fetch(`${BASE_URL}controllers/contratosController.php`, {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                spinner.style.display = 'none';
                if (data.success) {
                    Swal.fire({
                        title: 'Éxito!',
                        text: 'El contrato ha sido guardado correctamente.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                    }).then(() => {
                        form.reset();
                        cargarContratos(); // Recargar la tabla
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message || 'Hubo un problema al guardar el contrato.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                    });
                }
            })
            .catch(error => {
                spinner.style.display = 'none';
                Swal.fire({
                    title: 'Error!',
                    text: 'Hubo un problema al procesar la solicitud.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            });
    });
    //
    const formAgregarEquipo = document.getElementById("formAsociarEquipo");
    const tablaEquiposAsociados = document.getElementById("tablaEquiposAsociados").querySelector("tbody");

    // Agregar equipo al contrato
    formAgregarEquipo.addEventListener("submit", (e) => {
        e.preventDefault();
        const formData = new FormData(formAgregarEquipo);

        fetch(`${BASE_URL}controllers/contratosController.php?accion=agregarEquipo`, {
            method: "POST",
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire("¡Éxito!", "Equipo agregado al contrato.", "success");
                    formAgregarEquipo.reset();
                    cargarEquiposAsociados(document.getElementById("contrato_id_equipo").value);
                } else {
                    Swal.fire("Error", "No se pudo agregar el equipo.", "error");
                }
            })
            .catch(error => console.error("Error al agregar equipo:", error));
    });

    // Listar equipos asociados
    function cargarEquiposAsociados(contratoId) {
        fetch(`${BASE_URL}controllers/contratosController.php?accion=listarEquiposAsociados&contrato_id=${contratoId}`)
            .then(response => response.json())
            .then(equipos => {
                console.table(equipos);
                tablaEquiposAsociados.innerHTML = "";
                equipos.forEach(equipo => {
                    const fila = `
                        <tr>
                            <td>${equipo.nombre_equipo}</td>
                            <td>${equipo.cantidad}</td>
                            <td>${equipo.marca || "N/A"}</td>
                            <td>${Number(equipo.precio).toFixed(2)}</td>
                            <td>${(Number(equipo.total)).toFixed(2)}</td>
                            <td>
                                <button class="btn btn-danger btn-sm eliminar" data-id="${equipo.id}">Eliminar</button>
                            </td>
                        </tr>
                    `;
                    tablaEquiposAsociados.innerHTML += fila;
                });
            })
            .catch(error => console.error("Error al cargar equipos asociados:", error));
    }

    // Cargar equipos al cambiar contrato seleccionado
    document.getElementById("contrato_id_equipo").addEventListener("change", (e) => {
        cargarEquiposAsociados(e.target.value);
    });
});
