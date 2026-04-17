<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión requerida']);
    exit;
}
require_once __DIR__ . '/../includes/verificar_sesion.php';

require_once __DIR__ . '/../clases/Database.php';
require_once __DIR__ . '/../clases/Equipo.php';
require_once __DIR__ . '/../clases/TipoEquipo.php';

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
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para guardar equipos.');
        }
        $id = $_POST['id_equipo'] ?? null;
        $codigo = $_POST['codigo_equipo'] ?? '';
        $nombre = $_POST['nombre_equipo'] ?? '';
        $id_tipo = $_POST['id_tipo_equipo'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $estadoInventario = trim((string) ($_POST['estado_inventario'] ?? 'En inventario'));
        $fechaAdqRaw = trim((string) ($_POST['fecha_adquisicion'] ?? ''));
        $fechaAdquisicion = $fechaAdqRaw === '' ? null : $fechaAdqRaw;

        if (empty($id)) { // Crear
            $nuevoId = $equipo->crear($codigo, $nombre, $id_tipo, $descripcion, $estadoInventario, $fechaAdquisicion);
            $response['success'] = $nuevoId !== false;
            $response['message'] = $nuevoId !== false ? 'Equipo creado exitosamente.' : 'Error al crear el equipo.';
            if ($nuevoId !== false) {
                $response['data'] = ['id_equipo' => $nuevoId];
            }
        } else { // Actualizar
            $resultado = $equipo->actualizar($id, $codigo, $nombre, $id_tipo, $descripcion, $estadoInventario, $fechaAdquisicion);
            $response['message'] = $resultado ? 'Equipo actualizado exitosamente.' : 'Error al actualizar el equipo.';
            $response['success'] = $resultado;
        }
        break;

    /** Alta rápida desde formulario de contrato (nombre + tipo; código opcional). */
    case 'crear_rapido':
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para crear equipos.');
        }
        $nombre = trim((string) ($_POST['nombre_equipo'] ?? ''));
        $codigo = trim((string) ($_POST['codigo_equipo'] ?? ''));
        $idTipoRaw = $_POST['id_tipo_equipo'] ?? '';

        $nombreLen = function_exists('mb_strlen') ? mb_strlen($nombre, 'UTF-8') : strlen($nombre);
        if ($nombre === '' || $nombreLen < 2) {
            $response['message'] = 'Indique un nombre de equipo válido.';
            break;
        }

        $tipoModel = new TipoEquipo();
        $tipos = $tipoModel->leerTodos();
        $idTipo = null;
        if ($idTipoRaw !== '' && $idTipoRaw !== null && is_numeric($idTipoRaw)) {
            $idTipo = (int) $idTipoRaw;
        } elseif (!empty($tipos)) {
            $idTipo = (int) ($tipos[0]['id_tipo_equipo'] ?? 0);
        }

        if ($idTipo === null || $idTipo <= 0) {
            $response['message'] = 'No hay tipos de equipo en el catálogo. Cree uno en la gestión de equipos.';
            break;
        }

        if ($codigo === '') {
            $codigo = 'CAT-' . substr(str_replace('.', '', uniqid('', true)), -10);
        }

        $desc = 'Alta desde formulario de contrato';
        $nuevoId = $equipo->crear($codigo, $nombre, (string) $idTipo, $desc);
        if ($nuevoId === false) {
            $response['message'] = 'No se pudo guardar el equipo. Verifique que el código no esté duplicado.';
            break;
        }

        $response['success'] = true;
        $response['message'] = 'Equipo añadido al catálogo.';
        $response['data'] = [
            'id_equipo' => $nuevoId,
            'nombre_equipo' => $nombre,
            'codigo_equipo' => $codigo,
        ];
        break;

    case 'eliminar':
        if (!usuarioPuedeEliminar()) {
            denegarAccesoApi('Solo administradores pueden eliminar equipos.');
        }
        $id = $_POST['id'] ?? 0;
        $resultado = $equipo->eliminar($id);
        $response['success'] = $resultado;
        $response['message'] = $resultado ? 'Equipo eliminado exitosamente.' : 'Error al eliminar el equipo.';
        break;

    case 'cambiar_estado':
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para cambiar estado de equipos.');
        }
        $id = $_POST['id'] ?? 0;
        $estado = $_POST['estado'] ?? 0;
        $resultado = $equipo->cambiarEstado($id, $estado);
        $response['success'] = $resultado;
        $response['message'] = $resultado ? 'Estado actualizado correctamente.' : 'Error al actualizar el estado.';
        break;

    case 'listar_activos':
        $response['success'] = true;
        $response['data'] = $equipo->leerActivos();
        break;
}

echo json_encode($response);
?>