<?php
session_start();
ini_set('display_errors', '1');
//require_once '../includes/DataBase.php';
require_once '../includes/Usuario.php';

$email = $_POST['email'];
$password = $_POST['password'];

$usuarioModel = new Usuario();
$usuario = $usuarioModel->obtenerPorEmail($email);

if ($usuario) {
    if (password_verify($password, $usuario['password'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_rol'] = $usuario['rol'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        header("Location: dashboard/dashboard");
    } else {
        header("Location: login?error=1");
    }
} else {
    header("Location: login?error=1");
}
