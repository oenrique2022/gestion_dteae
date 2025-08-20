<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">
            <i class="fas fa-file-signature me-2"></i>Gestión de Contratos
        </h2>
        <a href="crear_contrato.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Crear Contrato
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>N° Contrato</th>
                        <th>Nombre del Contrato</th>
                        <th>Proveedor</th>
                        <th>Fecha de Inicio</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaContratosBody">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$pagina_js = 'contratos_listado.js'; // Usaremos un JS dedicado para esta vista
require_once __DIR__ . '/../templates/footer.php'; 
?>