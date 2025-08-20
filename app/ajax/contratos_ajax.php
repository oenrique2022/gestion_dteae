<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../clases/Database.php';
require_once __DIR__ . '/../clases/Contrato.php';

$contratoModel = new Contrato();
$response = ['success' => false, 'message' => 'Acción no válida o no especificada.'];
$action = $_REQUEST['action'] ?? null;

switch ($action) {
    case 'listar':
        $datos = $contratoModel->leerTodos();
        $response = ['success' => true, 'data' => $datos];
        break;

    case 'guardar':
    case 'actualizar':
        
        $datosContrato = [
            'numero_contrato' => $_POST['numero_contrato'] ?? null,
            'nombre_contrato' => $_POST['nombre_contrato'] ?? null,
            'proveedor_id' => $_POST['proveedor_id'] ?? null,
            'fuente_financiamiento_id' => $_POST['fuente_financiamiento_id'] ?? null,
            'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
            'fecha_fin' => !empty($_POST['fecha_fin']) ? $_POST['fecha_fin'] : null,
            'nombre_encargado' => $_POST['nombre_encargado'] ?? null,
            'comentarios' => $_POST['comentarios'] ?? null
        ];

        $detallesEquipos = [];
        if (!empty($_POST['equipos'])) {
            foreach ($_POST['equipos'] as $equipoData) {
                if (!empty($equipoData['id'])) {
                    $detallesEquipos[] = [
                        'id' => $equipoData['id'],
                        'marca' => $equipoData['marca'] ?? '',
                        'cantidad' => $equipoData['cantidad'] ?? 0,
                        'precio' => $equipoData['precio'] ?? 0
                    ];
                }
            }
        }

        $entregas = [];
        if (!empty($_POST['entregas'])) {
            foreach ($_POST['entregas'] as $entregaData) {
                if (!empty($entregaData['id_institucion'])) {
                    $entregas[] = [
                        'id_institucion' => $entregaData['id_institucion'],
                        'fecha_entrega' => $entregaData['fecha_entrega'],
                        'firma_responsable' => $entregaData['firma_responsable'],
                        'estado' => $entregaData['estado'],
                        'comentarios' => $entregaData['comentarios'],
                        'items' => $entregaData['items'] ?? []
                    ];
                }
            }
        }

        $archivos = [];
        if (!empty($_FILES['documentos']['name']) && is_array($_FILES['documentos']['name'])) {
            foreach ($_FILES['documentos']['name'] as $key => $name) {
                if ($_FILES['documentos']['error'][$key] === UPLOAD_ERR_OK) {
                    $archivos[] = [
                        'nombre' => $name,
                        'type' => $_FILES['documentos']['type'][$key],
                        'tmp_name' => $_FILES['documentos']['tmp_name'][$key],
                        'error' => $_FILES['documentos']['error'][$key],
                        'size' => $_FILES['documentos']['size'][$key],
                        'descripcion' => $_POST['descripcion_archivo'][$key] ?? 'Sin descripción'
                    ];
                }
            }
        }
        
        if ($action === 'guardar') {
            $response = $contratoModel->crearContratoCompleto($datosContrato, $detallesEquipos, $entregas, $archivos);
        } else {
            $idContrato = $_POST['id_contrato'] ?? null;
            if (!$idContrato) {
                $response['message'] = 'Error: No se proporcionó un ID de contrato para actualizar.';
                break;
            }
            $response = $contratoModel->actualizarContratoCompleto($idContrato, $datosContrato, $detallesEquipos, $entregas, $archivos);
        }
        
        break;

    case 'eliminar':
        $id = $_POST['id'] ?? 0;
        if ($id) {
            $resultado = $contratoModel->eliminar($id);
            $response = ['success' => $resultado, 'message' => $resultado ? 'Contrato eliminado.' : 'Error al eliminar.'];
        } else {
            $response['message'] = 'No se proporcionó un ID válido.';
        }
        break;
    
    case 'eliminar_documento':
        $id_documento = $_POST['id_documento'] ?? 0;
        if ($id_documento) {
            $resultado = $contratoModel->eliminarDocumento($id_documento);
            $response = ['success' => $resultado, 'message' => $resultado ? 'Documento eliminado.' : 'Error al eliminar.'];
        } else {
            $response['message'] = 'ID de documento no válido.';
        }
        break;
}

echo json_encode($response);