<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../clases/Proveedor.php';

$proveedor = new Proveedor();
$response = ['success' => false, 'message' => 'Acción no válida'];
$action = $_REQUEST['action'] ?? null;

switch ($action) {
    case 'listar':
        $response['success'] = true;
        $response['data'] = $proveedor->leerTodos();
        break;

    case 'obtener':
        $id = $_GET['id'] ?? 0;
        $data = $proveedor->leerUno($id);
        if ($data) {
            $response['success'] = true;
            $response['data'] = $data;
        } else {
            $response['message'] = 'Proveedor no encontrado';
        }
        break;

    case 'guardar':
        // Recoger datos del POST
        $id = $_POST['id_proveedor'] ?? null;
        $nombre = $_POST['nombre_proveedor'] ?? '';
        $contacto = $_POST['contacto'] ?? '';
        $telefono = $_POST['telefono_contacto'] ?? '';
        $email = $_POST['email_contacto'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        if (empty($id)) { // Crear nuevo
            $resultado = $proveedor->crear($nombre, $contacto, $telefono, $email, $descripcion);
            $response['message'] = $resultado ? 'Proveedor creado exitosamente.' : 'Error al crear el proveedor.';
        } else { // Actualizar existente
            $resultado = $proveedor->actualizar($id, $nombre, $contacto, $telefono, $email, $descripcion);
            $response['message'] = $resultado ? 'Proveedor actualizado exitosamente.' : 'Error al actualizar el proveedor.';
        }
        $response['success'] = $resultado;
        break;

    case 'eliminar':
        $id = $_POST['id'] ?? 0;
        $resultado = $proveedor->eliminar($id);
        if ($resultado) {
            $response['success'] = true;
            $response['message'] = 'Proveedor eliminado exitosamente.';
        } else {
            $response['message'] = 'Error al eliminar el proveedor.';
        }
        break;
}

echo json_encode($response);