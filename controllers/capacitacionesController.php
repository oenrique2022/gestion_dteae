<?php
require_once '../includes/config.php';
require_once '../includes/Capacitacion.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
$capacitacion = new Capacitacion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = htmlspecialchars(trim($_REQUEST['action']), ENT_QUOTES, 'UTF-8');

    switch ($action) {
        case 'crear':
            $nombre = $_POST['nombre_capacitacion'];
            $idEquipo = $_POST['id_equipo'];
            $fecha = $_POST['fecha'];
            $docentes = $_POST['docentes_capacitados'];
            $estudiantes = $_POST['estudiantes_capacitados'];
            $idInstitucion = $_POST['id_institucion'];
            $descripcion = $_POST['descripcion'];
            $duracion = $_POST['duracion'];
            $tipo = $_POST['tipo_capacitacion'];
            $usuarioCreador = $_SESSION['usuario'];
            $result = $capacitacion->crearCapacitacion($nombre, $idEquipo, $fecha, $docentes, $estudiantes, $idInstitucion, $descripcion, $duracion, $tipo, $usuarioCreador);
            echo json_encode(['success' => $result]);
            break;

        case 'listar':
            $data = $capacitacion->listarCapacitaciones();
            echo json_encode($data);
            break;

        case 'obtener':
            $id = $_POST['id'];
            $data = $capacitacion->obtenerPorId($id);
            echo json_encode($data);
            break;

            case 'actualizar':
                $idCapacitacion = $_POST['idCapacitacion'];
                $nombre = $_POST['nombre_capacitacion'];
                $idEquipo = $_POST['id_equipo'];
                $idInstitucion = $_POST['id_institucion'];
                $fecha = $_POST['fecha'];
                $duracion = $_POST['duracion'];
                $tipoCapacitacion = $_POST['tipo_capacitacion'];
                $docentesCapacitados = $_POST['docentes_capacitados'];
                $estudiantesCapacitados = $_POST['estudiantes_capacitados'];
                $descripcion = $_POST['descripcion'];
            
                $resultado = $capacitacion->actualizarCapacitacion(
                    $idCapacitacion,
                    $nombre,
                    $idEquipo,
                    $idInstitucion,
                    $fecha,
                    $duracion,
                    $tipoCapacitacion,
                    $docentesCapacitados,
                    $estudiantesCapacitados,
                    $descripcion
                );
            
                echo json_encode(['success' => $resultado]);
                break;
            
             case 'listarEquipos' :
                $capacitacion = new Capacitacion(); // Instancia de la clase Capacitacion
                $equipos = $capacitacion->listarEquipos(); // Método para obtener equipos
                echo json_encode($equipos); // Retornar datos en formato JSON
            break;
            
        case 'eliminar':
            $id = $_POST['id'];
            $result = $capacitacion->eliminarCapacitacion($id);
            echo json_encode(['success' => $result]);
            break;
        case 'listarInstituciones':
            require_once '../includes/Instituciones.php';
            $instituciones = new Instituciones();
            echo json_encode($instituciones->listarInstituciones());
            break;
        case 'obtenerCapacitacion':
            $idCapacitacion = $_POST['id_capacitacion'];
            $capacitacion = $capacitacion->obtenerPorId($idCapacitacion);
            echo json_encode($capacitacion);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    }
}
