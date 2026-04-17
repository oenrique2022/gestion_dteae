<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="h5 mb-0">
            <i class="fas fa-tags me-2"></i>Catálogo — Tipos / categorías de equipo
        </h2>
        <button id="btnNuevoTipoEquipo" type="button" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nueva categoría
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover table-sm align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Creado</th>
                        <th>Modificado</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaTiposEquiposBody"></tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="tipoEquipoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="tipoEquipoModalTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="tipoEquipoForm">
                <div class="modal-body">
                    <input type="hidden" name="id_tipo_equipo" id="id_tipo_equipo">
                    <div class="mb-3">
                        <label for="nombre_tipo_equipo" class="form-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre_tipo_equipo" name="nombre_tipo_equipo" required maxlength="120">
                    </div>
                    <div class="mb-0">
                        <label for="descripcion_tipo" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion_tipo" name="descripcion" rows="3" placeholder="Opcional"></textarea>
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
$pagina_js = 'tipos_equipos.js';
require_once __DIR__ . '/../templates/footer.php';
