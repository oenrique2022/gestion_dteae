<?php require_once __DIR__ . '/../templates/header.php';
$centros_ajax_url = preg_replace('#/public/?$#', '', APP_URL) . '/app/ajax/centros_educativos_ajax.php';
?>

<div class="card shadow-sm" id="centrosCatalogCard" data-ajax-centros="<?php echo htmlspecialchars($centros_ajax_url, ENT_QUOTES, 'UTF-8'); ?>">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h5 mb-0">
            <i class="fas fa-school me-2"></i>Catálogo — Centros educativos
        </h2>
        <button id="btnNuevoCentro" type="button" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nuevo centro
        </button>
    </div>
    <div class="card-body">
        <div class="row g-2 align-items-end mb-3">
            <div class="col-md-6 col-lg-5">
                <label for="buscarCentro" class="form-label small text-muted mb-0">Buscar</label>
                <input type="search" class="form-control form-control-sm" id="buscarCentro" placeholder="Nombre, departamento, municipio, código, director…" autocomplete="off">
            </div>
            <div class="col-md-6 col-lg-7">
                <div id="centrosPaginationInfo" class="small text-muted text-md-end"></div>
                <nav id="centrosPaginationNav" class="d-flex flex-wrap justify-content-md-end gap-1 mt-1" aria-label="Paginación"></nav>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Departamento</th>
                        <th>Municipio</th>
                        <th>Código infraestructura</th>
                        <th>Director</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaCentrosBody">
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="centroModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="centroModalTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="centroForm">
                    <input type="hidden" name="centro_id" id="centro_id">
                    <div class="mb-3">
                        <label for="nombre_ce" class="form-label">Nombre del centro <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre_ce" name="nombre_ce" maxlength="83" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="departamento_id" class="form-label">Departamento</label>
                            <select class="form-select" id="departamento_id" name="departamento_id">
                                <option value="">— Seleccione —</option>
                            </select>
                            <input type="hidden" id="departamento" name="departamento">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="municipio_id" class="form-label">Municipio</label>
                            <select class="form-select" id="municipio_id" name="municipio_id" disabled>
                                <option value="">— Primero elija departamento —</option>
                            </select>
                            <input type="hidden" id="municipio" name="municipio">
                        </div>
                    </div>
                    <p class="small text-muted">Los listados se generan a partir de los departamentos y municipios ya registrados en este catálogo.</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="codigo_infraestructura" class="form-label">Código infraestructura</label>
                            <input type="text" class="form-control" id="codigo_infraestructura" name="codigo_infraestructura" inputmode="numeric" maxlength="12" placeholder="Ej. 11182">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="director_actual" class="form-label">Director actual</label>
                            <input type="text" class="form-control" id="director_actual" name="director_actual" maxlength="255">
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$pagina_js = 'centros_educativos.js';
require_once __DIR__ . '/../templates/footer.php';
