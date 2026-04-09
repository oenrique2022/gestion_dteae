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

    default:
        break;
}

echo json_encode($response);
