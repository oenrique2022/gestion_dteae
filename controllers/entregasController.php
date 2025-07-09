<?php
require_once '../includes/config.php';
require_once '../includes/Entrega.php';

$entrega = new Entrega();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_REQUEST['action'];

    switch ($action) {
        case 'crear':
            $idContrato = intval($_POST['contrato_id']);
            $idInstitucion = intval($_POST['institucion_id']);
            $fechaEntrega = $_POST['fecha_entrega'];
            $estado = htmlspecialchars(trim($_POST['estado']), ENT_QUOTES, 'UTF-8');
            $firmaResponsable = htmlspecialchars(trim($_POST['firma_responsable']), ENT_QUOTES, 'UTF-8');
            $comentarios = htmlspecialchars(trim($_POST['comentarios']), ENT_QUOTES, 'UTF-8');
            $usuarioCreador = $_SESSION['usuario'];
        
            $idEntrega = $entrega->crearEntrega($idContrato, $idInstitucion, $fechaEntrega, $estado, $firmaResponsable, $comentarios, $usuarioCreador);
            echo json_encode(['success' => (bool) $idEntrega, 'id_entrega' => $idEntrega]);
            break;

        case 'agregarDetalle':
            $idEntrega = intval($_POST['entrega_id']);
            $idEquipo = intval($_POST['equipo_id']);
            $cantidad = intval($_POST['cantidad']);
            $precio = floatval($_POST['precio']);
            $comentario = htmlspecialchars(trim($_POST['comentario']), ENT_QUOTES, 'UTF-8');

            $resultado = $entrega->agregarDetalle($idEntrega, $idEquipo, $cantidad, $precio, $comentario);
            echo json_encode(['success' => $resultado]);
            break;

        case 'listarContratos':
            $data = $entrega->listarContratos();
            echo json_encode($data);
            break;

        case 'listarInstituciones':
            $data = $entrega->listarInstituciones();
            echo json_encode($data);
            break;

        case 'listarEquipos':
            $data = $entrega->listarEquipos();
            echo json_encode($data);
            break;

        case 'listarDetalles': 
            $idEntrega = intval($_REQUEST['id_entrega']);
            $data = $entrega->listarDetalles($idEntrega);
            echo json_encode($data);
            break;
        case 'listarEntregas':
            $data = $entrega->listarEntregas();
            header('Content-Type: application/json');
            echo json_encode($data);
            break;

        case 'obtenerEntrega':
            $idEntrega = intval($_POST['id_entrega']);
            $data = $entrega->obtenerPorId($idEntrega);
            header('Content-Type: application/json');
            echo json_encode($data);
            break;
        case 'obtener':
            $id = intval($_GET['id'] ?? 0);
            echo json_encode($entrega->obtenerPorId($id));
            break;
        case 'eliminarDetalle':
            $id = intval($_POST['id'] ?? 0);
            echo json_encode(['success' => $entrega->eliminarDetalleEntrega($id)]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
}
?>
