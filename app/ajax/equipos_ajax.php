<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../clases/Database.php';
require_once __DIR__ . '/../clases/Equipo.php';

$equipo = new Equipo();
$response = ['success' => false, 'message' => 'Acción no válida'];
$action = $_REQUEST['action'] ?? null;

switch ($action) {
    case 'listar':
        $response['success'] = true;
        $response['data'] = $equipo->leerTodos();
        break;

    case 'obtener':
        $id = $_GET['id'] ?? 0;
        $data = $equipo->leerUno($id);
        if ($data) {
            $response['success'] = true;
            $response['data'] = $data;
        } else {
            $response['message'] = 'Equipo no encontrado';
        }
        break;

    case 'guardar':
        $id = $_POST['id_equipo'] ?? null;
        $codigo = $_POST['codigo_equipo'] ?? '';
        $nombre = $_POST['nombre_equipo'] ?? '';
        $id_tipo = $_POST['id_tipo_equipo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        if (empty($id)) { // Crear
            $resultado = $equipo->crear($codigo, $nombre, $id_tipo, $descripcion);
            $response['message'] = $resultado ? 'Equipo creado exitosamente.' : 'Error al crear el equipo.';
        } else { // Actualizar
            $resultado = $equipo->actualizar($id, $codigo, $nombre, $id_tipo, $descripcion);
            $response['message'] = $resultado ? 'Equipo actualizado exitosamente.' : 'Error al actualizar el equipo.';
        }
        $response['success'] = $resultado;
        break;

    case 'eliminar':
        $id = $_POST['id'] ?? 0;
        $resultado = $equipo->eliminar($id);
        $response['success'] = $resultado;
        $response['message'] = $resultado ? 'Equipo eliminado exitosamente.' : 'Error al eliminar el equipo.';
        break;

    case 'cambiar_estado':
        $id = $_POST['id'] ?? 0;
        $estado = $_POST['estado'] ?? 0;
        $resultado = $equipo->cambiarEstado($id, $estado);
        $response['success'] = $resultado;
        $response['message'] = $resultado ? 'Estado actualizado correctamente.' : 'Error al actualizar el estado.';
        break;
        // ... dentro del switch ($action) ...
        case 'listar_activos':
        // Este método necesitará ser creado en tu clase Equipo.php
        // Deberá hacer: "SELECT id_equipo, nombre_equipo FROM equipos WHERE activo = 1"
        $response['success'] = true;
        $response['data'] = $equipo->leerActivos(); // Asegúrate de crear este método en el modelo
        break;
}

echo json_encode($response);
?>