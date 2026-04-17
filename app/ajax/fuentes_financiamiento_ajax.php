<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../clases/FuenteFinanciamiento.php';

$model = new FuenteFinanciamiento();
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
            $response['message'] = 'Fuente de financiamiento no encontrada.';
        }
        break;

    case 'guardar':
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para guardar fuentes de financiamiento.');
        }
        $id = isset($_POST['id_fuente']) ? trim((string) $_POST['id_fuente']) : '';
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $descripcion = isset($_POST['descripcion']) ? trim((string) $_POST['descripcion']) : '';
        $activo = isset($_POST['activo']) ? (int) $_POST['activo'] : 1;

        if ($nombre === '') {
            $response['message'] = 'El nombre es obligatorio.';
            break;
        }

        if ($id === '') {
            $nuevoId = $model->crear($nombre, $descripcion !== '' ? $descripcion : null, $activo);
            $response['success'] = $nuevoId !== false;
            $response['message'] = $nuevoId !== false ? 'Fuente creada correctamente.' : 'No se pudo crear (error en base de datos).';
            if ($nuevoId !== false) {
                $response['data'] = ['id' => $nuevoId];
            }
        } else {
            $resultado = $model->actualizar((int) $id, $nombre, $descripcion !== '' ? $descripcion : null, $activo);
            $response['success'] = $resultado;
            $response['message'] = $resultado ? 'Fuente actualizada correctamente.' : 'No se pudo actualizar.';
        }
        break;

    case 'eliminar':
        if (!usuarioPuedeEliminar()) {
            denegarAccesoApi('Solo administradores pueden eliminar fuentes de financiamiento.');
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            $response['message'] = 'ID no válido.';
            break;
        }
        $n = $model->contarContratosPorFuente($id);
        if ($n > 0) {
            $response['message'] = 'No se puede eliminar: hay ' . $n . ' contrato(s) que usan esta fuente.';
            break;
        }
        $ok = $model->eliminar($id);
        $response['success'] = $ok;
        $response['message'] = $ok ? 'Fuente eliminada.' : 'No se pudo eliminar.';
        break;

    default:
        break;
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
