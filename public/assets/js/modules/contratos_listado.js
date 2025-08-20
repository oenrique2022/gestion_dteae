document.addEventListener('DOMContentLoaded', function() {
    const tablaContratosBody = document.getElementById('tablaContratosBody');
    if (!tablaContratosBody) return;

    const cargarContratos = () => {
        // Necesitaremos un endpoint AJAX para 'listar' en 'contratos_ajax.php'
        // Por ahora, asumimos que existirá y funcionará como en los otros módulos.
        fetch('../app/ajax/contratos_ajax.php?action=listar')
            .then(response => response.json())
            .then(data => {
                tablaContratosBody.innerHTML = '';
                if (data.success && data.data.length > 0) {
                    data.data.forEach(c => {
                        tablaContratosBody.innerHTML += `
                            <tr>
                                <td>${c.numero_contrato}</td>
                                <td>${c.nombre_contrato}</td>
                                <td>${c.nombre_proveedor}</td>
                                <td>${c.fecha_inicio}</td>
                                <td class="text-end">
                                    <a href="editar_contrato.php?id=${c.id}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="${c.id}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tablaContratosBody.innerHTML = '<tr><td colspan="5" class="text-center">No hay contratos registrados.</td></tr>';
                }
            });
    };

    // (Aquí iría la lógica para el botón de eliminar, similar a los otros módulos)

    cargarContratos();
});