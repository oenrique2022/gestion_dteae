<?php
require_once '../includes/config.php';
require_once '../includes/Equipos.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = htmlspecialchars(trim($_POST['action']), ENT_QUOTES, 'UTF-8');

    $equipo = new Equipo();

    switch ($action) {
        case 'crear':
            $codigo = htmlspecialchars(trim($_POST['codigo_equipo']), ENT_QUOTES, 'UTF-8');
            $nombre = htmlspecialchars(trim($_POST['nombre_equipo']), ENT_QUOTES, 'UTF-8');
            $tipo = intval($_POST['id_tipo_equipo']);
            $estado = htmlspecialchars(trim($_POST['estado']), ENT_QUOTES, 'UTF-8');
            $fecha_adquisicion = htmlspecialchars(trim($_POST['fecha_adquisicion']), ENT_QUOTES, 'UTF-8');
            $descripcion = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');
            $usuario_creador = htmlspecialchars(trim($_SESSION['usuario']), ENT_QUOTES, 'UTF-8');

            $result = $equipo->crearEquipo($codigo, $nombre, $tipo, $estado, $fecha_adquisicion, $descripcion, $usuario_creador);
            echo json_encode(['success' => $result]);
            break;

        case 'listar':
            $equipos = $equipo->listarEquipos();
            echo json_encode($equipos);
            break;

        case 'obtener': // Nueva acción para obtener un equipo por ID
            $id = intval($_POST['id']);
            $equipoData = $equipo->obtenerPorId($id);
            if ($equipoData) {
                echo json_encode(['success' => true, 'data' => $equipoData]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se encontró el equipo.']);
            }
            break;

        case 'editar':
            $id = intval($_POST['id']);
            $codigo = htmlspecialchars(trim($_POST['codigo_equipo']), ENT_QUOTES, 'UTF-8');
            $nombre = htmlspecialchars(trim($_POST['nombre_equipo']), ENT_QUOTES, 'UTF-8');
            $tipo = intval($_POST['id_tipo_equipo']);
            $estado = htmlspecialchars(trim($_POST['estado']), ENT_QUOTES, 'UTF-8');
            $fecha_adquisicion = htmlspecialchars(trim($_POST['fecha_adquisicion']), ENT_QUOTES, 'UTF-8');
            $descripcion = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');

            $result = $equipo->actualizarEquipo($id, $codigo, $nombre, $tipo, $estado, $fecha_adquisicion, $descripcion);
            echo json_encode(['success' => $result]);
            break;

        case 'eliminar':
            $id = intval($_POST['id']);
            $result = $equipo->eliminarEquipo($id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Equipo eliminado con éxito.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el equipo.']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
            break;
    }
}
