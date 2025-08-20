<?php
// Este archivo está protegido por el guardián que ya incluimos en el header
require_once __DIR__ . '/../templates/header.php';
// Necesitamos la lista de roles para el menú desplegable del formulario
require_once __DIR__ . '/../app/clases/Rol.php';

$rolModel = new Rol();
$roles = $rolModel->leerTodos();
?>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="h5 mb-0"><i class="fas fa-users-cog me-2"></i>Gestión de Usuarios</h2>
        <button id="btnNuevoUsuario" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Usuario
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th class="text-center">Activo</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaUsuariosBody">
                    </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="usuarioModal" tabindex="-1" aria-labelledby="usuarioModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="usuarioModalTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="usuarioForm" novalidate>
                <div class="modal-body">
                    <input type="hidden" name="id_usuario" id="id_usuario">
                    <div class="mb-3">
                        <label for="nombre_usuario" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre_usuario" name="nombre_usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico</label>
                        <input type="email" class="form-control" id="correo" name="correo" required>
                    </div>
                    <div class="mb-3">
                        <label for="id_rol" class="form-label">Rol</label>
                        <select class="form-select" id="id_rol" name="id_rol" required>
                            <option value="">Seleccione un rol...</option>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?= $rol['id_rol'] ?>"><?= htmlspecialchars($rol['nombre_rol']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password">
                        <div id="passwordHelp" class="form-text">Dejar en blanco para no cambiar la contraseña al editar.</div>
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
// Asignamos el archivo JS específico para esta página
$pagina_js = 'usuarios.js';
require_once __DIR__ . '/../templates/footer.php'; 
?>