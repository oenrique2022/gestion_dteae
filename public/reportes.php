<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">
            <i class="fas fa-chart-bar me-2"></i>Reportes — Entregas por centro educativo
        </h2>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">
            Listado de entregas registradas: centro, contrato y productos entregados en cada entrega. Use el buscador para filtrar por cualquier texto visible.
        </p>
        <div class="row mb-3 g-2 align-items-end">
            <div class="col-md-6 col-lg-5">
                <label for="busquedaReportes" class="form-label small text-muted mb-1">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" class="form-control" id="busquedaReportes" placeholder="Centro, contrato, código, productos, estado…" autocomplete="off">
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Centro educativo</th>
                        <th>Código infraestructura</th>
                        <th>N° Contrato</th>
                        <th>Nombre del contrato</th>
                        <th>Productos entregados</th>
                        <th>Fecha entrega</th>
                        <th>Estado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaReportesEntregasBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalResumenEntrega" tabindex="-1" aria-labelledby="modalResumenEntregaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalResumenEntregaTitulo">
                    <i class="fas fa-truck me-2"></i>Resumen de esta entrega
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-school me-2"></i>Centro educativo</h6>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4">Nombre</dt>
                        <dd class="col-sm-8" id="modal-ce-nombre">—</dd>
                        <dt class="col-sm-4">Código infraestructura</dt>
                        <dd class="col-sm-8" id="modal-ce-codigo">—</dd>
                    </dl>
                </div>
                <div class="mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-box-open me-2"></i>Esta entrega</h6>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4">Fecha de entrega</dt>
                        <dd class="col-sm-8" id="modal-entrega-fecha">—</dd>
                        <dt class="col-sm-4">Estado</dt>
                        <dd class="col-sm-8" id="modal-entrega-estado">—</dd>
                        <dt class="col-sm-4">Firma responsable</dt>
                        <dd class="col-sm-8" id="modal-entrega-firma">—</dd>
                        <dt class="col-sm-4">Productos</dt>
                        <dd class="col-sm-8 text-break" id="modal-entrega-productos">—</dd>
                    </dl>
                    <p class="small text-muted mt-2 mb-0 d-none" id="modal-entrega-comentarios-wrap">
                        <span class="fw-semibold d-block">Comentarios de la entrega</span>
                        <span id="modal-entrega-comentarios" class="text-body" style="white-space: pre-wrap;"></span>
                    </p>
                </div>
                <div>
                    <h6 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-file-contract me-2"></i>Contrato (resumen)</h6>
                    <dl class="row mb-0 small">
                        <dt class="col-sm-4">N° Contrato</dt>
                        <dd class="col-sm-8" id="modal-contrato-numero">—</dd>
                        <dt class="col-sm-4">Nombre</dt>
                        <dd class="col-sm-8" id="modal-contrato-nombre">—</dd>
                        <dt class="col-sm-4">Vigencia</dt>
                        <dd class="col-sm-8" id="modal-contrato-vigencia">—</dd>
                        <dt class="col-sm-4">Proveedor</dt>
                        <dd class="col-sm-8" id="modal-contrato-proveedor">—</dd>
                        <dt class="col-sm-4">Fuente de financiamiento</dt>
                        <dd class="col-sm-8" id="modal-contrato-fuente">—</dd>
                        <dt class="col-sm-4">Encargado</dt>
                        <dd class="col-sm-8" id="modal-contrato-encargado">—</dd>
                    </dl>
                    <p class="small text-muted mt-2 mb-0 d-none" id="modal-contrato-comentarios-wrap">
                        <span class="fw-semibold d-block">Comentarios del contrato</span>
                        <span id="modal-contrato-comentarios" class="text-body" style="white-space: pre-wrap;"></span>
                    </p>
                </div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a href="#" class="btn btn-primary" id="modal-link-contrato-completo">
                    <i class="fas fa-edit me-1"></i>Abrir contrato completo
                </a>
            </div>
        </div>
    </div>
</div>

<?php
$pagina_js = 'reportes.js';
require_once __DIR__ . '/../templates/footer.php';
?>
