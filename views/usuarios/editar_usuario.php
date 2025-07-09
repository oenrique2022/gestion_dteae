<?php
$titulo = "Editar Usuario";
require_once '../../includes/Usuario.php';
include '../../includes/header.php';
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID de usuario no válido.");
}

$usuarioModel = new Usuario();
$usuario = $usuarioModel->obtenerPorId($_GET['id']);

if (!$usuario) {
    die("Usuario no encontrado.");
}
?>


<div class="container mt-4">
    <h2>Editar Usuario</h2>
        <form action="procesar_edicion" method="POST">
            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($usuario['id_usuario']); ?>">

            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" class="form-control" id="nombre" name="nombre" 
                       value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Nueva Contraseña (opcional)</label>
                <input type="password" class="form-control" id="password" name="password">
                <small>Déjelo en blanco para no cambiar la contraseña.</small>
            </div>

            <div class="form-group">
                <label for="rol">Rol</label>
                <select class="form-control" id="rol" name="rol" required>
                    <option value="1" <?php echo ($usuario['id_rol'] == 1) ? 'selected' : ''; ?>>Administrador</option>
                    <option value="2" <?php echo ($usuario['id_rol'] == 2) ? 'selected' : ''; ?>>Digitador</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Guardar Cambios</button>
            <a href="listar_usuarios.php" class="btn btn-secondary mt-3">Cancelar</a>
        </form>
</div>

<?php include '../../includes/footer.php'; ?>
