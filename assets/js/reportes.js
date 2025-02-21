document.addEventListener("DOMContentLoaded", () => {
    const yearFilter = document.getElementById("anioFiltro");
    const institucionesCard = document.getElementById("institucionesCard");
    const tecnologiasChart = document.getElementById("tecnologiasChart").getContext("2d");
    const capacitacionesChart = document.getElementById("capacitacionesChart").getContext("2d");
    let tecnologiasChartInstance = null; // Asegúrate de declarar esta variable globalmente
    let institucionesChartInstance;
    let modalidadesChartInstance;

    
    //let tecnologiasChartInstance;
    let capacitacionesChartInstance;

    // Función para cargar instituciones con tecnología


    async function cargarInstitucionesConTecnologia() {
        const anio = yearFilter.value || null;
    
        try {
            const response = await fetch(`${BASE_URL}controllers/reportesController.php`, {
                method: "POST",
                body: new URLSearchParams({ action: "institucionesConTecnologia", anio }),
            });
    
            const result = await response.json();
    
            if (result.success) {
                // Actualizar el valor de instituciones en la tarjeta
                institucionesCard.textContent = result.data.total_instituciones || 0;
    
                // Preparar datos para el gráfico
                const labels = result.data.detalle.map(item => item.institucion);
                const values = result.data.detalle.map(item => item.total);
    
                // Destruir gráfico anterior si existe
                if (institucionesChartInstance) {
                    institucionesChartInstance.destroy();
                }
    
                // Crear gráfico
                const canvas = document.getElementById('institucionesChart');
                institucionesChartInstance = new Chart(canvas, {
                    type: "doughnut", // Puede ser bar, pie, line, etc.
                    data: {
                        labels,
                        datasets: [{
                            label: "Instituciones con Tecnología",
                            data: values,
                            backgroundColor: [
                                "rgba(255, 99, 132, 0.6)",
                                "rgba(54, 162, 235, 0.6)",
                                "rgba(255, 206, 86, 0.6)",
                                "rgba(75, 192, 192, 0.6)",
                                "rgba(153, 102, 255, 0.6)",
                            ],
                            borderColor: [
                                "rgba(255, 99, 132, 1)",
                                "rgba(54, 162, 235, 1)",
                                "rgba(255, 206, 86, 1)",
                                "rgba(75, 192, 192, 1)",
                                "rgba(153, 102, 255, 1)",
                            ],
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: "top",
                            },
                        },
                    },
                });
            } else {
                console.error("No se pudieron cargar los datos de instituciones con tecnología.");
            }
        } catch (error) {
            console.error("Error al cargar el gráfico de instituciones:", error);
        }
    }
    

    // Función para cargar tecnologías entregadas

    async function cargarTecnologiasEntregadas() {
        if (tecnologiasChartInstance !== null) {
            tecnologiasChartInstance.destroy();
        }

        
        const anio = yearFilter.value || null;
        try {
            const response = await fetch(`${BASE_URL}controllers/reportesController.php`, {
                method: "POST",
                body: new URLSearchParams({ action: "tecnologiasEntregadas", anio }),
            });
    
            const result = await response.json();
    
            if (result.success) {
                const labels = result.data.map(item => item.tecnologia);
                const values = result.data.map(item => item.total);
    
                if (tecnologiasChartInstance) {
                    tecnologiasChartInstance.destroy();
                }
                tecnologiasChartInstance = new Chart(tecnologiasChart, {
                    type: "bar",
                    data: {
                        labels,
                        datasets: [{
                            label: "Tecnologías Entregadas",
                            data: values,
                            backgroundColor: "rgba(75, 192, 192, 0.6)",
                            borderColor: "rgba(75, 192, 192, 1)",
                            borderWidth: 1,
                            barThickness: 50, // Ajusta el grosor de las barras
                        }]
                        
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: {
                            padding: {
                                top: 10,
                                left: 15,
                                right: 15,
                                bottom: 10,
                            },
                        },
                        scales: {
                            x: {
                                ticks: {
                                    maxRotation: 45, // Gira las etiquetas si son largas
                                    minRotation: 0,
                                },
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 5, // Ajusta el intervalo de la escala Y
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: "top",
                            },
                        },
                    },
                });
            } else {
                console.error("No se pudieron cargar los datos del gráfico.");
            }
        } catch (error) {
            console.error("Error al cargar el gráfico:", error);
        }
    }
    

    async function cargarModalidadesCapacitacion() {
        const anio = yearFilter.value || null;
    
        try {
            const response = await fetch(`${BASE_URL}controllers/reportesController.php`, {
                method: "POST",
                body: new URLSearchParams({ action: "modalidadesCapacitacion", anio }),
            });
    
            const result = await response.json();
    
            if (result.success) {
                // Preparar datos para el gráfico
                const labels = result.data.map(item => item.modalidad); // Ejemplo: ["Presencial", "Autogestionado"]
                const docentes = result.data.map(item => item.total_docentes); // Totales de docentes capacitados
                const estudiantes = result.data.map(item => item.total_estudiantes); // Totales de estudiantes capacitados
    
                // Destruir gráfico anterior si existe
                if (modalidadesChartInstance) {
                    modalidadesChartInstance.destroy();
                }
    
                // Crear gráfico de barras agrupadas
                const canvas = document.getElementById('capacitacionesEstadoChart');
                modalidadesChartInstance = new Chart(canvas, {
                    type: "bar",
                    data: {
                        labels, // Modalidades
                        datasets: [
                            {
                                label: "Docentes Capacitados",
                                data: docentes,
                                backgroundColor: "rgba(54, 162, 235, 0.6)",
                                borderColor: "rgba(54, 162, 235, 1)",
                                borderWidth: 1,
                            },
                            {
                                label: "Estudiantes Capacitados",
                                data: estudiantes,
                                backgroundColor: "rgba(255, 99, 132, 0.6)",
                                borderColor: "rgba(255, 99, 132, 1)",
                                borderWidth: 1,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: false, // Barras agrupadas
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 0,
                                },
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 10, // Ajustar según los datos
                                },
                            },
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: "top",
                            },
                        },
                    },
                });
            } else {
                console.error("No se pudieron cargar los datos del gráfico de modalidades de capacitación.");
            }
        } catch (error) {
            console.error("Error al cargar el gráfico de modalidades de capacitación:", error);
        }
    }
    
    // Función para cargar capacitaciones por tipo
    async function cargarCapacitacionesPorTipo() {
        const anio = yearFilter.value || 2024;
        const response = await fetch(`${BASE_URL}controllers/reportesController.php`, {
            method: "POST",
            body: new URLSearchParams({ action: "capacitacionesPorTipo", anio }),
        });
        const result = await response.json();
        console.log(result);
        if (result.success) {
           
            const labels = result.data.map(item => item.tipo_capacitacion);
            const values = result.data.map(item => item.total);
            
            if (capacitacionesChartInstance) {
                capacitacionesChartInstance.destroy();
            }
            
            capacitacionesChartInstance = new Chart(capacitacionesChart, {
                type: "pie",
                data: {
                    labels,
                    datasets: [{
                        data: values,
                        backgroundColor: [
                            "rgba(255, 99, 132, 0.6)",
                            "rgba(54, 162, 235, 0.6)",
                            "rgba(255, 206, 86, 0.6)",
                        ],
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                },
            });
        }
    }

    async function cargarAnios() {
        const response = await fetch(`${BASE_URL}controllers/reportesController.php`, {
            method: "POST",
            body: new URLSearchParams({ action: "listarAnios" }),
        });
    
        const result = await response.json();
        const yearFilter = document.getElementById("anioFiltro");
    
        if (result.success) {
            yearFilter.innerHTML = '<option value="">Todos los años</option>';
            result.data.forEach((anio) => {
                const option = document.createElement("option");
                option.value = anio;
                option.textContent = anio;
                yearFilter.appendChild(option);
            });
        }
    }

    
    // Cargar todos los reportes iniciales
    async function cargarReportes() {
        await cargarInstitucionesConTecnologia();
         await cargarTecnologiasEntregadas();
         await cargarCapacitacionesPorTipo();
         await cargarModalidadesCapacitacion();
    }

    // Actualizar reportes al cambiar el filtro de año
    yearFilter.addEventListener("change", cargarReportes);

    // Inicializar
    cargarReportes();
    cargarAnios();

});
