//document.addEventListener("DOMContentLoaded", cargarTabla());
//document.addEventListener("load", cargarTabla);
cargarTabla=()=>{
        // Inicializar todas las tablas con la clase .datatable
        const tables = document.querySelectorAll(".datatable");
        tables.forEach(table => {
            new DataTable(table, {
                responsive: true,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                paging: true,
                searching: true,
                ordering: true
            });
        });
}

// Función para mostrar la notificación
mostrarNotificacion=(mensaje, color = '#4CAF50', duracion = 3000) =>{
    var notificacion = document.getElementById('notificacion');
    
    // Configura el mensaje y el color
    notificacion.textContent = mensaje;
    notificacion.style.backgroundColor = color;
    
    // Muestra la notificación con animación
    notificacion.style.display = 'block';
    notificacion.style.animation = 'fadein 0.5s';
    
    // Oculta la notificación después de un tiempo
    setTimeout(function() {
        notificacion.style.animation = 'fadeout 0.5s';
        setTimeout(function() {
            notificacion.style.display = 'none';
        }, 500); // Espera a que termine la animación de fadeout
    }, duracion); // Duración en milisegundos
}
