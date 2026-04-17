<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/verificar_sesion.php';
if (!usuarioEsAdmin()) {
    denegarAccesoApi('Solo administradores pueden gestionar usuarios.');
}
require_once __DIR__ . '/../clases/Usuario.php'; // Usamos la clase que ya creamos

$usuarioModel = new Usuario();
$response = ['success' => false, 'message' => 'Acción no válida.'];
$action = $_REQUEST['action'] ?? null;

switch ($action) {
    case 'listar':
        $response = ['success' => true, 'data' => $usuarioModel->leerTodos()];
        break;
    
    case 'obtener':
        $id = $_GET['id'] ?? 0;
        if ($id) {
            $response = ['success' => true, 'data' => $usuarioModel->leerUno($id)];
        } else {
            $response['message'] = 'ID no proporcionado.';
        }
        break;

    case 'guardar':
        $id = $_POST['id_usuario'] ?? null;
        $nombre = $_POST['nombre_usuario'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $id_rol = $_POST['id_rol'] ?? '';
        $password = $_POST['password'] ?? null;
        
        if (empty($id)) { // Crear nuevo usuario
            if(empty($password)) {
                 $response = ['success' => false, 'message' => 'La contraseña es obligatoria para nuevos usuarios.'];
                 break;
            }
            $resultado = $usuarioModel->crear($nombre, $correo, $password, $id_rol);
            $response['message'] = $resultado ? 'Usuario creado exitosamente.' : 'Error al crear el usuario (el correo podría ya existir).';
        } else { // Actualizar usuario existente
            $resultado = $usuarioModel->actualizar($id, $nombre, $correo, $id_rol, $password);
            $response['message'] = $resultado ? 'Usuario actualizado exitosamente.' : 'Error al actualizar el usuario.';
        }
        $response['success'] = $resultado;
        break;
        
    case 'cambiar_estado':
        $id = $_POST['id'] ?? 0;
        $estado = $_POST['estado'] ?? 0;
        $response['success'] = $usuarioModel->cambiarEstado($id, $estado);
        break;
}

echo json_encode($response);