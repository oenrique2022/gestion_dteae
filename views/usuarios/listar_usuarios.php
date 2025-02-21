<?php
ini_set('display_errors', '1');
$titulo = "Listado de Usuarios"; // Título específico para la página
include '../../includes/header.php'; // Incluir el encabezado

// Iniciar la sesión
session_start();

// Verificar si el usuario tiene permisos
/*if (!isset($_SESSION['usuario_id']) || $_SESSION['rol'] != 'admin') {
   header('Location: login.php');
    exit;
}
*/
// Crear instancia de la clase Usuario
require_once '../../includes/Usuario.php';
$usuario = new Usuario();
$usuarios = $usuario->listarUsuarios(); // Obtener la lista de usuarios
?>

<div class="container mt-4">
    <h1><?php echo $titulo; ?></h1>
    <p>Desde aquí puedes gestionar los usuarios registrados en el sistema.</p>

    <!-- Tabla de usuarios -->
    <table class="table table-bordered datatable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['nombre_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                    <td>
                        <a href="editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-warning">Editar</a>
                        <a href="eliminar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" class="btn btn-danger">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../../includes/footer.php'; ?> <!-- Incluir el pie de página -->
