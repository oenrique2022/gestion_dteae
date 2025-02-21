<?php
session_start();
require_once '../../includes/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = intval($_POST['id_usuario']);
    $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $id_rol = intval($_POST['rol']);
    $password = trim($_POST['password']);

    if (empty($nombre) || empty($email) || $id_rol <= 0) {
        die("Todos los campos son obligatorios.");
    }

    $usuarioModel = new Usuario();

    if (!empty($password)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    } else {
        // Obtener la contraseña actual si no se cambia
        $usuarioActual = $usuarioModel->obtenerPorId($id_usuario);
        $passwordHash = $usuarioActual['password'];
    }

    $exito = $usuarioModel->actualizarUsuario($id_usuario, $nombre, $email, $passwordHash, $id_rol);

    if ($exito) {
        $_SESSION['mensaje'] = "Usuario actualizado exitosamente.";
        header("Location: listar_usuarios");
    } else {
        die("Error al actualizar usuario.");
    }
}
