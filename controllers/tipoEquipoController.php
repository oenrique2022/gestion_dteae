<?php
require_once '../includes/config.php';  
require_once '../includes/TipoEquipos.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = htmlspecialchars(trim($_POST['action']), ENT_QUOTES, 'UTF-8');  // Obtenemos la acción

    // Instanciar el objeto TipoEquipos
    $tipoEquipo = new TipoEquipos();

    // Procesamos las acciones según el valor de "action"
    switch ($action) {
        // Crear un nuevo tipo de equipo
        case 'crear':
            $nombre = htmlspecialchars(trim($_POST['nombre_tipo_equipo']), ENT_QUOTES, 'UTF-8');
            $descripcion = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');
            $usuario_creador = htmlspecialchars(trim($_SESSION['usuario']), ENT_QUOTES, 'UTF-8'); // Suponiendo que el usuario está en sesión

            // Llamar al método de la clase para crear el tipo de equipo
            $result = $tipoEquipo->crearTipoEquipo($nombre, $descripcion, $usuario_creador);
            echo json_encode(['success' => $result]);
            break;

        // Listar todos los tipos de equipos
        case 'listar':
            $tiposEquipos = $tipoEquipo->listarTiposEquipos();
            echo json_encode($tiposEquipos);
            break;

        // Editar un tipo de equipo
        case 'editar':
            $id = intval($_POST['id']);
            $nombre = htmlspecialchars(trim($_POST['nombre_tipo_equipo']), ENT_QUOTES, 'UTF-8');
            $descripcion = htmlspecialchars(trim($_POST['descripcion']), ENT_QUOTES, 'UTF-8');

            // Llamar al método de la clase para editar el tipo de equipo
            $result = $tipoEquipo->actualizarTipoEquipo($id, $nombre, $descripcion);
            echo json_encode(['success' => $result]);
            break;

        // Eliminar un tipo de equipo
        case 'eliminar':
            $id = intval($_POST['id']);
            $result = $tipoEquipo->eliminarTipoEquipo($id);
            echo json_encode(['success' => $result]);
            break;

        // Obtener un tipo de equipo por ID
        case 'obtener':
            $id = intval($_POST['id']);
            $tipoEquipoData = $tipoEquipo->obtenerPorId($id);
            echo json_encode($tipoEquipoData);
            break;

        // Acción por defecto para cuando la acción no es válida
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    }
}
?>
