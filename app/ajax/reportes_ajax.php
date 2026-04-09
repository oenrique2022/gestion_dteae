<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../clases/Reporte.php';

$reporteModel = new Reporte();
$response = ['success' => false, 'message' => 'Acción no válida o no especificada.'];
$action = $_REQUEST['action'] ?? null;

switch ($action) {
    case 'listar_entregas_centros':
        $datos = $reporteModel->listarEntregasCentrosContratos();
        $response = ['success' => true, 'data' => $datos];
        break;

    case 'listar_productos_por_centro':
        $datos = $reporteModel->listarProductosEntregadosPorCentro();
        $response = ['success' => true, 'data' => $datos];
        break;

    case 'resumen_gerencial':
        $desde = $_REQUEST['fecha_desde'] ?? '';
        $hasta = $_REQUEST['fecha_hasta'] ?? '';
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $response = ['success' => false, 'message' => 'Indique fechas válidas (AAAA-MM-DD).'];
            break;
        }
        if (strcmp($desde, $hasta) > 0) {
            $response = ['success' => false, 'message' => 'La fecha inicial no puede ser posterior a la final.'];
            break;
        }
        $response = ['success' => true, 'data' => $reporteModel->resumenGerencial($desde, $hasta)];
        break;

    case 'centros_por_producto':
        $desde = $_REQUEST['fecha_desde'] ?? '';
        $hasta = $_REQUEST['fecha_hasta'] ?? '';
        $idEquipo = isset($_REQUEST['id_equipo']) ? (int) $_REQUEST['id_equipo'] : 0;
        if ($idEquipo < 1) {
            $response = ['success' => false, 'message' => 'Producto no válido.'];
            break;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $desde) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $hasta)) {
            $response = ['success' => false, 'message' => 'Indique fechas válidas (AAAA-MM-DD).'];
            break;
        }
        if (strcmp($desde, $hasta) > 0) {
            $response = ['success' => false, 'message' => 'La fecha inicial no puede ser posterior a la final.'];
            break;
        }
        $filas = $reporteModel->centrosPorProductoEnRango($idEquipo, $desde, $hasta);
        $response = ['success' => true, 'data' => $filas];
        break;

    default:
        break;
}

echo json_encode($response);
