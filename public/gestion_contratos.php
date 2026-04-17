<?php require_once __DIR__ . '/../templates/header.php'; ?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0">
            <i class="fas fa-file-signature me-2"></i>Gestión de Contratos
        </h2>
        <?php if (usuarioPuedeEscribir()): ?>
            <a href="crear_contrato.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Crear Contrato
            </a>
        <?php endif; ?>
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

<div class="modal fade" id="modalRutasEntrega" tabindex="-1" aria-labelledby="modalRutasEntregaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalRutasEntregaTitulo"><i class="fas fa-route me-2"></i>Ruta de entrega</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rutas_id_contrato_actual" value="">
                <p class="small text-muted mb-3" id="rutasContratoInfo"></p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Centro educativo</th>
                                <th>Responsable</th>
                                <th>Motorista</th>
                                <th>Vehículo</th>
                                <th>Placas</th>
                                <th>Estado</th>
                                <th>Fecha programada</th>
                                <th>Bitácora</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaRutasEntregaBody"></tbody>
                    </table>
                </div>
                <p class="text-muted small mb-0 d-none" id="rutasEntregaVacia">Este contrato no tiene centros con entregas registradas.</p>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDocsRutaEntrega" tabindex="-1" aria-labelledby="modalDocsRutaEntregaTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalDocsRutaEntregaTitulo"><i class="fas fa-file-upload me-2"></i>Documentos de ruta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="docsRutaIdActual" value="">
                <p class="small text-muted mb-2" id="docsRutaCentroInfo"></p>
                <div id="listaDocsRutaEntrega" class="list-group mb-3"></div>
                <p class="small text-muted d-none mb-3" id="docsRutaVacio">No hay documentos en esta ruta.</p>
                <hr class="my-3">
                <form id="formSubirDocRuta" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="docRutaArchivo" class="form-label">Archivo PDF</label>
                        <input type="file" class="form-control" id="docRutaArchivo" accept="application/pdf,.pdf" required>
                    </div>
                    <div class="mb-3">
                        <label for="docRutaComentario" class="form-label">Comentario</label>
                        <textarea id="docRutaComentario" class="form-control" rows="2" placeholder="Detalle del archivo"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" id="btnSubirDocRuta">
                        <i class="fas fa-cloud-upload-alt me-1"></i>Subir PDF
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.URL_AJAX_CONTRATOS = '../app/ajax/contratos_ajax.php';
    window.APP_PUBLIC_BASE = '<?= rtrim(APP_URL, '/') ?>';
</script>

<?php 
$pagina_js = 'contratos_listado.js'; // Usaremos un JS dedicado para esta vista
require_once __DIR__ . '/../templates/footer.php'; 
?>