<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/verificar_sesion.php';

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
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para guardar o actualizar contratos.');
        }
        
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
        if (!usuarioPuedeEliminar()) {
            denegarAccesoApi('Solo administradores pueden eliminar contratos.');
        }
        $id = $_POST['id'] ?? 0;
        if ($id) {
            $resultado = $contratoModel->eliminar($id);
            $response = ['success' => $resultado, 'message' => $resultado ? 'Contrato eliminado.' : 'Error al eliminar.'];
        } else {
            $response['message'] = 'No se proporcionó un ID válido.';
        }
        break;
    
    case 'eliminar_documento':
        if (!usuarioPuedeEliminar()) {
            denegarAccesoApi('Solo administradores pueden eliminar documentos.');
        }
        $id_documento = $_POST['id_documento'] ?? 0;
        if ($id_documento) {
            $resultado = $contratoModel->eliminarDocumento($id_documento);
            $response = ['success' => $resultado, 'message' => $resultado ? 'Documento eliminado.' : 'Error al eliminar.'];
        } else {
            $response['message'] = 'ID de documento no válido.';
        }
        break;

    case 'subir_documento_entrega':
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para subir documentos de entrega.');
        }
        $idContrato = (int) ($_POST['id_contrato'] ?? 0);
        $idInstitucion = (int) ($_POST['id_institucion'] ?? 0);
        if (!empty($_FILES['archivo_pdf']['name']) && ($_FILES['archivo_pdf']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $archivo = [
                'nombre' => $_FILES['archivo_pdf']['name'],
                'tmp_name' => $_FILES['archivo_pdf']['tmp_name'],
                'size' => (int) ($_FILES['archivo_pdf']['size'] ?? 0),
                'type' => $_FILES['archivo_pdf']['type'] ?? '',
                'error' => (int) ($_FILES['archivo_pdf']['error'] ?? 0),
            ];
            $comentario = $_POST['comentario_archivo'] ?? '';
            $response = $contratoModel->subirDocumentoEntregaCentro($idContrato, $idInstitucion, $archivo, $comentario);
        } else {
            $response = ['success' => false, 'message' => 'Seleccione un archivo PDF.'];
        }
        break;

    case 'agregar_comentario_documento':
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para comentar documentos.');
        }
        $idDocumento = (int) ($_POST['id_documento'] ?? 0);
        $comentario = $_POST['comentario'] ?? '';
        $response = $contratoModel->agregarComentarioDocumento($idDocumento, $comentario);
        break;

    case 'listar_rutas_entrega':
        $idContrato = (int) ($_GET['id_contrato'] ?? $_POST['id_contrato'] ?? 0);
        $response = $contratoModel->obtenerRutasEntregaContrato($idContrato);
        break;

    case 'guardar_ruta_entrega':
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para guardar rutas de entrega.');
        }
        $payload = [
            'id_contrato' => (int) ($_POST['id_contrato'] ?? 0),
            'id_institucion' => (int) ($_POST['id_institucion'] ?? 0),
            'responsable_entrega' => $_POST['responsable_entrega'] ?? '',
            'motorista' => $_POST['motorista'] ?? '',
            'vehiculo' => $_POST['vehiculo'] ?? '',
            'placas' => $_POST['placas'] ?? '',
            'estado' => $_POST['estado'] ?? 'Programada',
            'fecha_programada' => $_POST['fecha_programada'] ?? '',
            'fecha_en_ruta' => $_POST['fecha_en_ruta'] ?? '',
            'fecha_entregado' => $_POST['fecha_entregado'] ?? '',
            'comentarios' => $_POST['comentarios'] ?? '',
        ];
        $response = $contratoModel->guardarRutaEntrega($payload);
        break;

    case 'listar_documentos_ruta':
        $idRuta = (int) ($_GET['id_ruta'] ?? $_POST['id_ruta'] ?? 0);
        $response = $contratoModel->listarDocumentosRutaEntrega($idRuta);
        break;

    case 'subir_documento_ruta':
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para subir documentos de ruta.');
        }
        $idRuta = (int) ($_POST['id_ruta'] ?? 0);
        if (!empty($_FILES['archivo_pdf']['name']) && ($_FILES['archivo_pdf']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $archivo = [
                'nombre' => $_FILES['archivo_pdf']['name'],
                'tmp_name' => $_FILES['archivo_pdf']['tmp_name'],
                'size' => (int) ($_FILES['archivo_pdf']['size'] ?? 0),
                'type' => $_FILES['archivo_pdf']['type'] ?? '',
                'error' => (int) ($_FILES['archivo_pdf']['error'] ?? 0),
            ];
            $comentario = $_POST['comentario_archivo'] ?? '';
            $response = $contratoModel->subirDocumentoRutaEntrega($idRuta, $archivo, $comentario);
        } else {
            $response = ['success' => false, 'message' => 'Seleccione un archivo PDF.'];
        }
        break;
}

echo json_encode($response);