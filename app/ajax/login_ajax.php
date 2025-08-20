<?php
declare(strict_types=1);
header('Content-Type: application/json');

// Incluimos la clase Usuario que ya tiene el método autenticar()
require_once __DIR__ . '/../clases/Usuario.php';

// Iniciamos la sesión para poder guardar los datos del usuario si el login es exitoso
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Recibimos los datos del formulario
$correo = $_POST['correo'] ?? '';
$password = $_POST['password'] ?? '';

// Creamos una instancia del modelo y autenticamos
$usuarioModel = new Usuario();
$usuario = $usuarioModel->autenticar($correo, $password);

if ($usuario) {
    // ¡Credenciales correctas! Creamos las variables de sesión.
    $_SESSION['id_usuario'] = $usuario['id_usuario'];
    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
    $_SESSION['id_rol'] = $usuario['id_rol'];
    
    // Enviamos una respuesta de éxito a JavaScript
    echo json_encode(['success' => true]);
} else {
    // Credenciales incorrectas, enviamos un mensaje de error
    echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos, o el usuario está inactivo.']);
}