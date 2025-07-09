<?php
session_start();
ini_set('display_errors', '1');
require_once '../../includes/Usuario.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpiar datos recibidos
    $nombre = htmlspecialchars(trim($_POST['nombre']), ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = htmlspecialchars(trim($_POST['password']), ENT_QUOTES, 'UTF-8');
    $id_rol = intval($_POST['rol']);

    // Validar datos después de limpiarlos
    if (empty($nombre) || empty($email) || empty($password) || $id_rol <= 0) {
        die("Todos los campos son obligatorios y deben ser válidos.");
    }

    // Encriptar contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $usuarioModel = new Usuario();
    $exito = $usuarioModel->crearUsuario($nombre, $email, $passwordHash, $id_rol);

    if ($exito) {
        $_SESSION['mensaje'] = "Usuario registrado exitosamente.";
        header("Location: listar_usuarios");
    } else {
        echo "Error al registrar el usuario.";
    }
} else {
    echo "Método no permitido.";
}
