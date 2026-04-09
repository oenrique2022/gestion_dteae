<?php 
// ------------------- SECCIÓN DE CARGA DE DATOS -------------------
// El header debe incluirse aquí para tener acceso a las variables
require_once __DIR__ . '/../templates/header.php'; 

// Cargar todos los modelos necesarios para los menús desplegables
require_once __DIR__ . '/../app/clases/Proveedor.php';
require_once __DIR__ . '/../app/clases/Equipo.php';
require_once __DIR__ . '/../app/clases/CentroEducativo.php';
require_once __DIR__ . '/../app/clases/FuenteFinanciamiento.php'; // Asegúrate de haber creado este archivo

// Instanciar y obtener los datos
$proveedorModel = new Proveedor();
$proveedores = $proveedorModel->leerTodos();

$equipoModel = new Equipo();
$equipos = $equipoModel->leerActivos(); // Usaremos esto para el JavaScript

$ceModel = new CentroEducativo();
$centros = $ceModel->leerTodos();

$ffModel = new FuenteFinanciamiento();
$fuentes = $ffModel->leerTodos();
// ------------------- FIN DE CARGA DE DATOS -------------------
?>

<h1 class="mb-4"><?= $titulo_pagina ?></h1>

<form id="formContrato" enctype="multipart/form-data" method="POST">
    <input type="hidden" name="id_contrato" id="id_contrato" value="<?= htmlspecialchars($contrato['generales']['id'] ?? '') ?>">

    <ul class="nav nav-tabs flex-nowrap overflow-auto" id="contratoTabs" role="tablist" style="border-bottom: 0;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active px-3" id="detalles-tab" data-bs-toggle="tab" data-bs-target="#detalles" type="button" role="tab" aria-controls="detalles" aria-selected="true"><span class="badge bg-secondary me-1">1</span>Detalles y equipos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-3" id="archivos-tab" data-bs-toggle="tab" data-bs-target="#archivos" type="button" role="tab" aria-controls="archivos" aria-selected="false"><span class="badge bg-secondary me-1">2</span>Archivos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-3" id="entregas-tab" data-bs-toggle="tab" data-bs-target="#entregas" type="button" role="tab" aria-controls="entregas" aria-selected="false"><span class="badge bg-secondary me-1">3</span>Entregas por centro</button>
        </li>
    </ul>
    <p class="small text-muted mb-3 px-1">Defina primero los equipos del contrato; luego asigne entregas por centro. Guarde al final.</p>

    <div class="tab-content" id="contratoTabsContent">
        
        <div class="tab-pane fade show active" id="detalles" role="tabpanel" aria-labelledby="detalles-tab">
            <div class="card border-top-0 rounded-0 rounded-bottom">
                <div class="card-body p-4">
                    <h5>Datos Generales del Contrato</h5>
                    <hr>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="numero_contrato" class="form-label">Número de Contrato</label>
                            <input type="text" class="form-control" id="numero_contrato" name="numero_contrato" value="<?= htmlspecialchars($contrato['generales']['numero_contrato'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nombre_contrato" class="form-label">Nombre del Contrato</label>
                            <input type="text" class="form-control" id="nombre_contrato" name="nombre_contrato" value="<?= htmlspecialchars($contrato['generales']['nombre_contrato'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="row">
                         <div class="col-md-6 mb-3">
                            <label for="proveedor_id" class="form-label">Proveedor</label>
                            <select class="form-select" id="proveedor_id" name="proveedor_id" required>
                                <option value="">Seleccione un proveedor...</option>
                                <?php foreach($proveedores as $p): ?>
                                    <option value="<?= $p['id_proveedor'] ?>" <?= (isset($contrato['generales']['proveedor_id']) && $contrato['generales']['proveedor_id'] == $p['id_proveedor']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nombre_proveedor']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fuente_financiamiento_id" class="form-label">Fuente de Financiamiento</label>
                            <select class="form-select" id="fuente_financiamiento_id" name="fuente_financiamiento_id" required>
                                <option value="">Seleccione una fuente...</option>
                                <?php foreach($fuentes as $f): ?>
                                     <option value="<?= $f['id'] ?>" <?= (isset($contrato['generales']['fuente_financiamiento_id']) && $contrato['generales']['fuente_financiamiento_id'] == $f['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($f['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($contrato['generales']['fecha_inicio'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin (Opcional)</label>
                            <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($contrato['generales']['fecha_fin'] ?? '') ?>">
                        </div>
                         <div class="col-md-4 mb-3">
                            <label for="nombre_encargado" class="form-label">Nombre del Encargado</label>
                            <input type="text" class="form-control" id="nombre_encargado" name="nombre_encargado" value="<?= htmlspecialchars($contrato['generales']['nombre_encargado'] ?? '') ?>">
                        </div>
                    </div>
                     <div class="mb-3">
                        <label for="comentarios" class="form-label">Comentarios</label>
                        <textarea class="form-control" id="comentarios" name="comentarios" rows="2"><?= htmlspecialchars($contrato['generales']['comentarios'] ?? '') ?></textarea>
                    </div>

                    <h5 class="mt-4">Detalle de Equipos</h5>
                    <hr>
                    <table class="table table-sm">
                        <thead class="table-light"><tr><th>Equipo</th><th>Marca</th><th>Cantidad</th><th>Precio Unitario</th><th><button type="button" id="btnAgregarEquipo" class="btn btn-success btn-sm py-0 px-1" title="Agregar Fila"><i class="fas fa-plus"></i></button></th></tr></thead>
                        <tbody id="detalleEquiposBody">
                            <?php /* Las filas se generan en JS desde equiposContrato (crear/editar) */ ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="archivos" role="tabpanel" aria-labelledby="archivos-tab">
     <div class="card border-top-0 rounded-0 rounded-bottom">
         <div class="card-body p-4">
            
            <?php // Esta sección SOLO se mostrará si estamos en modo 'editar' y si existen documentos ?>
            <?php if ($modo === 'editar' && !empty($contrato['documentos'])): ?>
                <h5>Archivos Existentes</h5>
                <ul class="list-group mb-4" id="listaArchivosExistentes">
                    <?php foreach($contrato['documentos'] as $doc): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center" id="doc-<?= $doc['id'] ?>">
                            <div>
                                <a href="<?= APP_URL . htmlspecialchars($doc['ruta_archivo']) ?>" target="_blank" title="Ver/Descargar Archivo">
                                    <i class="fas fa-file-alt me-2"></i><?= htmlspecialchars($doc['nombre_archivo']) ?>
                                </a>
                                <p class="mb-0 text-muted small fst-italic">"<?= htmlspecialchars($doc['descripcion']) ?>"</p>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm btn-eliminar-documento" data-id="<?= $doc['id'] ?>" title="Eliminar este archivo">
                                <i class="fas fa-trash"></i>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <hr>
            <?php endif; ?>

            <h5 class="mt-2">Subir Nuevos Archivos</h5>
            <div class="mb-3">
                <label for="documentos" class="form-label">Seleccionar archivos para agregar al contrato</label>
                <input class="form-control" type="file" name="documentos[]" id="documentos" multiple>
            </div>
            <div id="listaArchivosNuevos">
                </div>

         </div>
     </div>
</div>

<div class="tab-pane fade" id="entregas" role="tabpanel" aria-labelledby="entregas-tab">
    <div class="card border-top-0 rounded-0 rounded-bottom">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="mb-0">Entregas por institución</h5>
                <button type="button" id="btnAgregarEntrega" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Asignar nueva entrega
                </button>
            </div>
            <div class="alert alert-light border small py-2 mb-3">
                <i class="fas fa-info-circle text-primary me-1"></i>
                Use <strong>Asignar nueva entrega</strong> para abrir un asistente en ventana; al confirmar, la entrega se añade a esta lista. Las cantidades parten de las líneas de <strong>Detalles y equipos</strong>.
            </div>
            <select id="centros_educativos_hidden" style="display: none;">
            <option value="">Seleccione...</option>
            <?php foreach($centros as $centro): ?>
            <option value="<?= $centro['centro_id'] ?>"><?= htmlspecialchars($centro['codigo_infraestructura']." ".$centro['nombre_ce']) ?></option>
            <?php endforeach; ?>
            </select>

            <div class="accordion" id="contenedorEntregas"></div>
        </div>
    </div>
</div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 pt-3 border-top">
        <a href="gestion_contratos.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Volver al listado</a>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-lg px-4"><i class="fas fa-save me-1"></i>Guardar contrato</button>
        </div>
    </div>
</form>

<!-- Modal: nueva entrega (flujo centrado; al confirmar se añade al listado de la pestaña Entregas) -->
<div class="modal fade" id="modalNuevaEntrega" tabindex="-1" aria-labelledby="modalNuevaEntregaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalNuevaEntregaTitulo"><i class="fas fa-truck me-2"></i>Nueva entrega por centro</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="small text-muted">Complete los datos y las cantidades; luego pulse <strong>Agregar a la lista</strong>. Podrá revisar y guardar el contrato al final.</p>
                <div class="mb-3">
                    <label for="modal_entrega_institucion" class="form-label">Centro educativo <span class="text-danger">*</span></label>
                    <select id="modal_entrega_institucion" class="form-select" required>
                        <option value="">Seleccione un centro…</option>
                        <?php foreach ($centros as $centro): ?>
                            <option value="<?= htmlspecialchars((string)$centro['centro_id']) ?>"><?= htmlspecialchars($centro['codigo_infraestructura'] . ' ' . $centro['nombre_ce']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label for="modal_entrega_fecha" class="form-label">Fecha de entrega</label>
                        <input type="date" id="modal_entrega_fecha" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label for="modal_entrega_firma" class="form-label">Persona que firma</label>
                        <input type="text" id="modal_entrega_firma" class="form-control form-control-sm" placeholder="Opcional">
                    </div>
                    <div class="col-md-4">
                        <label for="modal_entrega_estado" class="form-label">Estado</label>
                        <select id="modal_entrega_estado" class="form-select form-select-sm">
                            <option value="En proceso">En proceso</option>
                            <option value="Entregado">Entregado</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="modal_entrega_comentarios" class="form-label">Comentarios</label>
                    <textarea id="modal_entrega_comentarios" class="form-control form-control-sm" rows="2" placeholder="Opcional"></textarea>
                </div>
                <h6 class="border-bottom pb-2">Cantidades por equipo (según líneas del contrato)</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr><th>Equipo</th><th>Cant. en contrato</th><th>Cant. a entregar</th></tr>
                        </thead>
                        <tbody id="modalNuevaEntregaEquipos"></tbody>
                    </table>
                </div>
                <p id="modalNuevaEntregaSinEquipos" class="text-warning small d-none mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>No hay líneas de equipo en el contrato. Agregue equipos en la pestaña <strong>Detalles y equipos</strong>.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnModalConfirmarEntrega">
                    <i class="fas fa-check me-1"></i>Agregar a la lista de entregas
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const modoFormulario = '<?= $modo ?>';
    const equiposContrato = <?= json_encode($contrato['equipos'] ?? []) ?>;
    const equiposDisponibles = <?= json_encode($equipos ?? []) ?>;
    const entregasContrato = <?= json_encode($contrato['entregas'] ?? []) ?>; // <-- AÑADE ESTA LÍNEA
</script>

<?php 
$pagina_js = 'contratos_formulario.js';
require_once __DIR__ . '/../templates/footer.php'; 
?>