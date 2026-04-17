<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h5 mb-0">
            <i class="fas fa-coins me-2"></i>Catálogo — Fuentes de financiamiento
        </h2>
        <button id="btnNuevaFuente" type="button" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nueva fuente
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaFuentesBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="fuenteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="fuenteModalTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="fuenteForm">
                <div class="modal-body">
                    <input type="hidden" name="id_fuente" id="id_fuente">
                    <div class="mb-3">
                        <label for="nombre_fuente" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre_fuente" name="nombre" required maxlength="200">
                    </div>
                    <div class="mb-3">
                        <label for="descripcion_fuente" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion_fuente" name="descripcion" rows="3" placeholder="Opcional"></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="activo_fuente" name="activo" value="1" checked>
                        <label class="form-check-label" for="activo_fuente">Activo (visible en formularios)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$pagina_js = 'fuentes_financiamiento.js';
require_once __DIR__ . '/../templates/footer.php';
