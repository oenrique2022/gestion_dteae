<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
        <div>
            <h2 class="h5 mb-0">
                <i class="fas fa-chart-line me-2 text-primary"></i>Dashboard gerencial de entregas
            </h2>
            <p class="text-muted small mb-0 mt-1">
                Resumen por rango de fechas (según <strong>fecha de entrega</strong> registrada en cada entrega).
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="reportes.php" class="btn btn-outline-secondary btn-sm">Entregas por centro</a>
            <a href="reportes_productos.php" class="btn btn-outline-secondary btn-sm">Productos y centros</a>
        </div>
    </div>
    <div class="card-body border-bottom bg-light">
        <form class="row g-2 align-items-end" id="formRangoDashboard" onsubmit="return false;">
            <div class="col-6 col-md-auto">
                <label for="fechaDesdeDash" class="form-label small text-muted mb-0">Desde</label>
                <input type="date" class="form-control form-control-sm" id="fechaDesdeDash" required>
            </div>
            <div class="col-6 col-md-auto">
                <label for="fechaHastaDash" class="form-label small text-muted mb-0">Hasta</label>
                <input type="date" class="form-control form-control-sm" id="fechaHastaDash" required>
            </div>
            <div class="col-12 col-md-3 col-lg-2">
                <label for="filtroDepartamentoDash" class="form-label small text-muted mb-0">Departamento</label>
                <select class="form-select form-select-sm" id="filtroDepartamentoDash">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-12 col-md-3 col-lg-2">
                <label for="filtroMunicipioDash" class="form-label small text-muted mb-0">Municipio</label>
                <select class="form-select form-select-sm" id="filtroMunicipioDash">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary btn-sm" id="btnAplicarRango">
                    <i class="fas fa-filter me-1"></i>Aplicar
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="mes" title="Mes actual">Este mes</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="trimestre" title="Trimestre natural actual">Este trimestre</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="anio" title="Año en curso">Este año</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" data-preset="12m" title="Últimos 12 meses">Últimos 12 meses</button>
            </div>
        </form>
        <p class="text-danger small mb-0 mt-2 d-none" id="dashboardError"></p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h3 class="h6 mb-0"><i class="fas fa-route me-2"></i>Calendarización general de rutas de entrega</h3>
        <p class="small text-muted mb-0 mt-1">Rutas programadas en el rango seleccionado (vista consolidada).</p>
    </div>
    <div class="card-body p-0">
        <div class="px-3 py-2 border-bottom small text-muted" id="resumenRutasEstado">Sin datos</div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Fecha programada</th>
                        <th>Estado</th>
                        <th>Contrato</th>
                        <th>Centro educativo</th>
                        <th>Ubicación</th>
                        <th>Responsable</th>
                        <th>Motorista / Vehículo</th>
                    </tr>
                </thead>
                <tbody id="tablaCalendarizacionRutas"></tbody>
            </table>
        </div>
        <p class="text-danger small mb-0 p-3 d-none" id="calendarizacionRutasError"></p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-primary border-4">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Entregas</div>
                <div class="fs-3 fw-bold text-primary" id="kpiEntregas">—</div>
                <div class="small text-muted">Registros de entrega en el periodo</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-success border-4">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Unidades entregadas</div>
                <div class="fs-3 fw-bold text-success" id="kpiUnidades">—</div>
                <div class="small text-muted">Suma de cantidades por producto</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-info border-4">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Centros</div>
                <div class="fs-3 fw-bold text-info" id="kpiCentros">—</div>
                <div class="small text-muted">Instituciones distintas</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100 border-start border-warning border-4">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Contratos</div>
                <div class="fs-3 fw-bold text-warning" id="kpiContratos">—</div>
                <div class="small text-muted">Contratos con entregas en el periodo</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h3 class="h6 mb-0"><i class="fas fa-calendar-alt me-2"></i>Evolución por mes</h3>
    </div>
    <div class="card-body">
        <div style="max-height: 360px;">
            <canvas id="chartEntregasMes"></canvas>
        </div>
        <p class="text-muted small text-center mb-0 mt-2 d-none" id="chartSinDatos">No hay datos en el rango seleccionado.</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <h3 class="h6 mb-0"><i class="fas fa-trophy me-2"></i>Productos más entregados (top 20)</h3>
                <p class="small text-muted mb-0 mt-1">Pulse una fila para ver a qué centros se entregó en este periodo.</p>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-end">Unidades</th>
                            </tr>
                        </thead>
                        <tbody id="tablaTopProductos">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm h-100">
            <div class="card-header">
                <h3 class="h6 mb-0"><i class="fas fa-tags me-2"></i>Entregas por estado</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Estado</th>
                                <th class="text-end">N° entregas</th>
                            </tr>
                        </thead>
                        <tbody id="tablaPorEstado">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCentrosPorProducto" tabindex="-1" aria-labelledby="modalCentrosPorProductoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div>
                    <h5 class="modal-title" id="modalCentrosPorProductoTitulo">Centros que recibieron el producto</h5>
                    <p class="small mb-0 opacity-75" id="modalCentrosPorProductoSubtitulo"></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 border-bottom bg-light d-none" id="modalCentrosProductoCargando">
                    <span class="spinner-border spinner-border-sm text-primary me-2" role="status"></span>
                    Cargando…
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Centro educativo</th>
                                <th>Ubicación</th>
                                <th>Cód. infra.</th>
                                <th class="text-end">Unidades</th>
                                <th class="text-end">Entregas</th>
                            </tr>
                        </thead>
                        <tbody id="modalTablaCentrosProducto">
                        </tbody>
                    </table>
                </div>
                <p class="text-muted small text-center py-3 mb-0 d-none" id="modalCentrosProductoVacio">No hay entregas de este producto en el periodo.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
$extra_scripts = ['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'];
$pagina_js = 'reportes_dashboard.js';
require_once __DIR__ . '/../templates/footer.php';
?>
