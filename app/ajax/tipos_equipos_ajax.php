<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../clases/TipoEquipo.php';

$model = new TipoEquipo();
$response = ['success' => false, 'message' => 'Acción no válida.'];
$action = $_REQUEST['action'] ?? null;

switch ($action) {
    case 'listar':
        $response = ['success' => true, 'data' => $model->leerTodos()];
        break;

    case 'obtener':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $response['message'] = 'ID no válido.';
            break;
        }
        $row = $model->leerUno($id);
        if ($row) {
            $response = ['success' => true, 'data' => $row];
        } else {
            $response['message'] = 'Tipo de equipo no encontrado.';
        }
        break;

    case 'guardar':
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para guardar tipos de equipo.');
        }
        $id = isset($_POST['id_tipo_equipo']) ? trim((string) $_POST['id_tipo_equipo']) : '';
        $nombre = trim((string) ($_POST['nombre_tipo_equipo'] ?? ''));
        $descripcion = isset($_POST['descripcion']) ? trim((string) $_POST['descripcion']) : '';

        if ($nombre === '') {
            $response['message'] = 'El nombre es obligatorio.';
            break;
        }

        if ($id === '') {
            $nuevoId = $model->crear($nombre, $descripcion !== '' ? $descripcion : null);
            $response['success'] = $nuevoId !== false;
            $response['message'] = $nuevoId !== false ? 'Tipo de equipo creado correctamente.' : 'No se pudo crear (nombre duplicado o error en base de datos).';
            if ($nuevoId !== false) {
                $response['data'] = ['id_tipo_equipo' => $nuevoId];
            }
        } else {
            $resultado = $model->actualizar((int) $id, $nombre, $descripcion !== '' ? $descripcion : null);
            $response['success'] = $resultado;
            $response['message'] = $resultado ? 'Tipo de equipo actualizado correctamente.' : 'No se pudo actualizar.';
        }
        break;

    case 'eliminar':
        if (!usuarioPuedeEliminar()) {
            denegarAccesoApi('Solo administradores pueden eliminar tipos de equipo.');
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            $response['message'] = 'ID no válido.';
            break;
        }
        $n = $model->contarEquiposPorTipo($id);
        if ($n > 0) {
            $response['message'] = 'No se puede eliminar: hay ' . $n . ' equipo(s) asociado(s) a esta categoría.';
            break;
        }
        $ok = $model->eliminar($id);
        $response['success'] = $ok;
        $response['message'] = $ok ? 'Tipo de equipo eliminado.' : 'No se pudo eliminar.';
        break;

    default:
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
