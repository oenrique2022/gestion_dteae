<?php
include_once '../../includes/header.php';
include_once '../../includes/Equipos.php';
?>
<div class="container mt-4">
    <h1 class="mb-4">Gestión de Contratos</h1>

    <!-- Card de Tabs -->
    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="contratosTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="registrar-tab" data-bs-toggle="tab" data-bs-target="#registrar" type="button" role="tab" aria-controls="registrar" aria-selected="true">Registrar Contrato</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="listado-tab" data-bs-toggle="tab" data-bs-target="#listado" type="button" role="tab" aria-controls="listado" aria-selected="false">Listado de Contratos</button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="contratosTabContent">
                <!-- Tab: Registrar Contrato -->
                <div id="spinner" style="display: none; align-items: center; justify-content: center; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); z-index: 1050;">
                <div class="spinner-border text-light" role="status">
                    <span class="visually-hidden">Guardando...</span>
                </div>
            </div>

    <div class="tab-pane fade show active" id="registrar" role="tabpanel" aria-labelledby="registrar-tab">
    <form id="formContrato" class="mb-4" enctype="multipart/form-data">
        <input type="hidden" id="idContrato" name="idContrato">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="numeroContrato" class="form-label">Número de Contrato</label>
                    <input type="text" id="numero_contrato" name="numero_contrato" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="nombreContrato" class="form-label">Nombre del Contrato</label>
                    <input type="text" id="nombre_contrato" name="nombre_contrato" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="proveedor" class="form-label">Proveedor</label>
                    <select id="proveedor_id" name="proveedor_id" class="form-select" required>
                        <option value="">Seleccione un proveedor</option>
                        <!-- Opciones dinámicas -->
                    </select>
                </div>
                <div class="mb-3">
                    <label for="fuenteFinanciamiento" class="form-label">Fuente de Financiamiento</label>
                    <select id="fuente_financiamiento_id" name="fuente_financiamiento_id" class="form-select" required>
                        <option value="">Seleccione una fuente</option>
                        <!-- Opciones dinámicas -->
                    </select>
                </div>
                <div class="mb-3">
                    <label for="fechaContrato" class="form-label">Fecha de Contrato</label>
                    <input type="date" id="fecha_contrato" name="fecha_contrato" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="fechaCierreContrato" class="form-label">Fecha de Cierre de Contrato</label>
                    <input type="date" id="fecha_cierre_contrato" name="fecha_cierre_contrato" class="form-control" >
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="nombreEncargado" class="form-label">Encargado del Contrato</label>
                    <input type="text" id="nombre_encargado" name="nombre_encargado" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="comentarios" class="form-label">Comentarios</label>
                    <textarea id="comentarios" name="comentarios" class="form-control" rows="4"></textarea>
                </div>
                <div class="mb-3">
                    <label for="documentos" class="form-label">Subir Documentos</label>
                    <input type="file" id="documentos" name="documentos[]" class="form-control" multiple>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
        <button type="reset" class="btn btn-secondary" id="btnCancelar">Cancelar</button>
    </form>
    <?php include_once("equiposContratos.inc.php") ?>
</div>


                <!-- Tab: Listado de Contratos -->
                <div class="tab-pane fade" id="listado" role="tabpanel" aria-labelledby="listado-tab">
                    <div class="table-responsive">
                        <table class="table datatable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre del Contrato</th>
            <th>Proveedor</th>
            <th>Fuente de Financiamiento</th>
            <th>Nombre del Encargado</th>
            <th>Comentarios</th>
            <th>Fecha de Creación</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="tablaContratosBody">
        <!-- Las filas serán insertadas aquí dinámicamente -->
    </tbody>
</table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo BASE_URL ?>assets/js/contratos.js"></script>
<?php
include_once '../../includes/footer.php';
?>
