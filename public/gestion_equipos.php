<?php 
require_once __DIR__ . '/../templates/header.php'; 
require_once __DIR__ . '/../app/clases/TipoEquipo.php';

// Obtenemos los tipos de equipo para llenar el <select> del formulario
$tipoEquipo = new TipoEquipo();
$tipos = $tipoEquipo->leerTodos();
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0"><i class="fas fa-laptop-code me-2"></i>Gestión de Equipos y Tecnologías</h2>
        <button id="btnNuevoEquipo" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Equipo
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nombre del Equipo</th>
                        <th>Tipo</th>
                        <th class="text-center">Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaEquiposBody">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="equipoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="equipoModalTitle"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="equipoForm">
                    <input type="hidden" name="id_equipo" id="id_equipo">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="codigo_equipo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="codigo_equipo" name="codigo_equipo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nombre_equipo" class="form-label">Nombre del Equipo</label>
                            <input type="text" class="form-control" id="nombre_equipo" name="nombre_equipo" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="id_tipo_equipo" class="form-label">Tipo de Equipo</label>
                        <select class="form-select" id="id_tipo_equipo" name="id_tipo_equipo" required>
                            <option value="">Seleccione un tipo...</option>
                            <?php foreach ($tipos as $tipo): ?>
                                <option value="<?php echo $tipo['id_tipo_equipo']; ?>">
                                    <?php echo $tipo['nombre_tipo_equipo']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<?php 
 $pagina_js = 'equipos.js';
require_once __DIR__ . '/../templates/footer.php'; ?>