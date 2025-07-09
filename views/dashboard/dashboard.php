<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Dashboard de Reportes</h1>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">Filtros</div>
        <div class="card-body">
            <form id="formFiltros" class="row g-3">
                <div class="col-md-4">
                    <label for="filtroAño" class="form-label">Año</label>
                    <select id="anioFiltro" class="form-select">
                        <option value="2024">Seleccione un año</option>
                        <!-- Opciones dinámicas -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filtroContrato" class="form-label">Contrato</label>
                    <select id="filtroContrato" class="form-select">
                        <option value="">Seleccione un contrato</option>
                        <!-- Opciones dinámicas -->
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filtroInstitucion" class="form-label">Institución</label>
                    <select id="filtroInstitucion" class="form-select">
                        <option value="">Seleccione una institución</option>
                        <!-- Opciones dinámicas -->
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="button" id="btnAplicarFiltros" class="btn btn-primary">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reportes -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="reportesTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="contratos-tab" data-bs-toggle="tab" data-bs-target="#contratos" type="button" role="tab" aria-controls="contratos" aria-selected="true">Contratos</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="entregas-tab" data-bs-toggle="tab" data-bs-target="#entregas" type="button" role="tab" aria-controls="entregas" aria-selected="false">Entregas</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="globales-tab" data-bs-toggle="tab" data-bs-target="#globales" type="button" role="tab" aria-controls="globales" aria-selected="false">Globales</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="reportesTabContent">
                <!-- Contratos -->
                <div class="tab-pane fade show active" id="contratos" role="tabpanel" aria-labelledby="contratos-tab">
                    <h4>Reporte de Contratos</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Contrato</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaContratos">
                            <!-- Contenido dinámico -->
                        </tbody>
                    </table>
                </div>

                <!-- Entregas -->
                <div class="tab-pane fade" id="entregas" role="tabpanel" aria-labelledby="entregas-tab">
                    <h4>Reporte de Entregas</h4>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Institución</th>
                                <th>Contrato</th>
                                <th>Fecha Entrega</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaEntregas">
                            <!-- Contenido dinámico -->
                        </tbody>
                    </table>
                </div>

                <!-- Globales -->
                <div class="tab-pane fade" id="globales" role="tabpanel" aria-labelledby="globales-tab">
                    <h4>Reportes Globales</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Instituciones con Tecnología Entregada</h5>
                            <div id="instituciones-container">
                            <h3>Total de Instituciones: <span id="institucionesCard">0</span></h3>
                            <canvas id="institucionesChart"></canvas>
                        </div>
                         </div>
                        <div class="col-md-6">
                            <h5>Tecnologías Entregadas</h5>
                            <div id="canvas-container">
                                <canvas id="tecnologiasChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h5>Capacitaciones por Estado</h5>
                            <div id="canvas-container">
                            <canvas id="capacitacionesEstadoChart"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Tipos de Capacitaciones</h5>
                            <div id="canvas-container">
                            <canvas id="capacitacionesChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="text-end mt-4">
                        <button id="descargarPDF" class="btn btn-danger">Descargar PDF</button>
                        <button id="descargarExcel" class="btn btn-success">Descargar Excel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL ?>assets/js/reportes.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include_once '../../includes/footer.php'; ?>
