<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h5 mb-0">
            <i class="fas fa-boxes me-2"></i>Informe — Productos y centros educativos
        </h2>
        <div class="d-flex flex-wrap gap-2">
            <a href="reportes_dashboard.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-chart-line me-1"></i> Dashboard
            </a>
            <a href="reportes.php" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-school me-1"></i> Entregas por centro
            </a>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Cada fila es un producto o tecnología entregada en una entrega concreta y el centro educativo que la recibió. Use el buscador para filtrar.
        </p>
        <div class="row mb-3 g-2 align-items-end">
            <div class="col-md-6 col-lg-5">
                <label for="busquedaReportesProductos" class="form-label small text-muted mb-1">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" class="form-control" id="busquedaReportesProductos" placeholder="Producto, tipo, centro, contrato, código…" autocomplete="off">
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Producto / tecnología</th>
                        <th>Tipo</th>
                        <th>Cód. producto</th>
                        <th class="text-end">Cantidad</th>
                        <th>Centro educativo</th>
                        <th>Cód. infra.</th>
                        <th>N° Contrato</th>
                        <th>Fecha entrega</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaReportesProductosBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleProductoEntrega" tabindex="-1" aria-labelledby="modalDetalleProductoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetalleProductoTitulo">
                    <i class="fas fa-info-circle me-2"></i>Detalle de la entrega
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body small">
                <h6 class="text-primary">Producto</h6>
                <dl class="row mb-3">
                    <dt class="col-sm-4">Nombre</dt>
                    <dd class="col-sm-8" id="modal-prod-nombre">—</dd>
                    <dt class="col-sm-4">Tipo</dt>
                    <dd class="col-sm-8" id="modal-prod-tipo">—</dd>
                    <dt class="col-sm-4">Código</dt>
                    <dd class="col-sm-8" id="modal-prod-codigo">—</dd>
                    <dt class="col-sm-4">Cantidad</dt>
                    <dd class="col-sm-8" id="modal-prod-cantidad">—</dd>
                </dl>
                <h6 class="text-primary">Centro que recibió</h6>
                <dl class="row mb-3">
                    <dt class="col-sm-4">Nombre</dt>
                    <dd class="col-sm-8" id="modal-prod-ce">—</dd>
                    <dt class="col-sm-4">Cód. infraestructura</dt>
                    <dd class="col-sm-8" id="modal-prod-infra">—</dd>
                </dl>
                <h6 class="text-primary">Contrato</h6>
                <dl class="row mb-0">
                    <dt class="col-sm-4">N°</dt>
                    <dd class="col-sm-8" id="modal-prod-contrato-num">—</dd>
                    <dt class="col-sm-4">Nombre</dt>
                    <dd class="col-sm-8" id="modal-prod-contrato-nom">—</dd>
                    <dt class="col-sm-4">Entrega</dt>
                    <dd class="col-sm-8" id="modal-prod-fecha">—</dd>
                    <dt class="col-sm-4">Estado</dt>
                    <dd class="col-sm-8" id="modal-prod-estado">—</dd>
                </dl>
                <p class="text-muted mt-3 mb-0 d-none" id="modal-prod-comentario-wrap">
                    <span class="fw-semibold d-block">Comentario de línea</span>
                    <span id="modal-prod-comentario" style="white-space: pre-wrap;"></span>
                </p>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" class="btn btn-primary" id="modal-prod-link-contrato">
                    <i class="fas fa-edit me-1"></i>Abrir contrato completo
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$pagina_js = 'reportes_productos.js';
require_once __DIR__ . '/../templates/footer.php';
?>
