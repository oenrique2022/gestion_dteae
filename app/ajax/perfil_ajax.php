<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../clases/Usuario.php';

$response = ['success' => false, 'message' => 'Acción no válida.'];

// Seguridad: Asegurarse de que el usuario haya iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Error: Sesión no iniciada.';
    echo json_encode($response);
    exit();
}

$action = $_POST['action'] ?? null;

if ($action === 'cambiar_password') {
    $id_usuario = $_SESSION['id_usuario']; // Obtenemos el ID del usuario de la sesión, no del formulario
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nueva = $_POST['password_nueva'] ?? '';
    $password_confirmar = $_POST['password_confirmar'] ?? '';

    // Validaciones en el servidor
    if (empty($password_actual) || empty($password_nueva) || empty($password_confirmar)) {
        $response['message'] = 'Todos los campos son obligatorios.';
    } elseif ($password_nueva !== $password_confirmar) {
        $response['message'] = 'Las nuevas contraseñas no coinciden.';
    } else {
        // Si las validaciones pasan, llamamos al método del modelo
        $usuarioModel = new Usuario();
        $resultado = $usuarioModel->cambiarPassword($id_usuario, $password_actual, $password_nueva);
        
        if ($resultado) {
            $response['success'] = true;
            $response['message'] = '¡Contraseña actualizada exitosamente!';
        } else {
            $response['message'] = 'La contraseña actual es incorrecta.';
        }
    }
}

echo json_encode($response);