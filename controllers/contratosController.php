<?php
require_once '../includes/config.php';
require_once '../includes/Contrato.php';
require_once '../includes/FuenteFinanciamiento.php';
require_once '../includes/Proveedor.php';

$contrato = new Contrato();

// Validar acción enviada desde el frontend
$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : null;

switch ($accion) {
    case 'crear':
        $nombreContrato = $_POST['nombre_contrato'];
        $proveedorId = $_POST['proveedor_id'];
        $fuenteFinanciamientoId = $_POST['fuente_financiamiento_id'];
        $nombreEncargado = $_POST['nombre_encargado'];
        $comentarios = $_POST['comentarios'];
        $numeroContrato = $_POST['numero_contrato'];
        $fechaInicio = $_POST['fecha_contrato'];
        $fechaFinal = empty($_POST['fecha_cierre_contrato']) ? null : $_POST['fecha_cierre_contrato']; // Manejar valor nulo
        $usuarioCreador = $_SESSION['usuario'];
        
        $resultado = $contrato->crearContrato($nombreContrato, $proveedorId, $fuenteFinanciamientoId, $nombreEncargado, $comentarios, $usuarioCreador,$numeroContrato, $fechaInicio, $fechaFinal);
        echo json_encode(['success' => $resultado]);
        break;

    case 'listar':
        $contratos = $contrato->listarContratos();
        //print_r($contratos);
        echo json_encode($contratos, JSON_UNESCAPED_UNICODE);
        break;

    case 'obtener':
        $id = $_POST['id'];
        $contratoData = $contrato->obtenerPorId($id);
        echo json_encode($contratoData);
        break;

        case 'actualizar':
            $id = intval($_POST['idContrato']);
            $nombreContrato = htmlspecialchars(trim($_POST['nombre_contrato']));
            $proveedorId = intval($_POST['proveedor_id']);
            $fuenteFinanciamientoId = intval($_POST['fuente_financiamiento_id']);
            $nombreEncargado = htmlspecialchars(trim($_POST['nombre_encargado']));
            $comentarios = htmlspecialchars(trim($_POST['comentarios']));
            $numeroContrato = htmlspecialchars(trim($_POST['numero_contrato']));
            $fechaInicio = htmlspecialchars(trim($_POST['fecha_contrato']));
            $fechaFinal = empty($_POST['fecha_cierre_contrato']) ? null : htmlspecialchars(trim($_POST['fecha_cierre_contrato']));
    
            // Manejar archivos si se suben
            if (!empty($_FILES['documentos']['name'][0])) {
                $documentos = [];
                foreach ($_FILES['documentos']['tmp_name'] as $key => $tmpName) {
                    $nombreArchivo = basename($_FILES['documentos']['name'][$key]);
                    $rutaDestino = "../docs_contratos/" . $nombreArchivo;
                    if (move_uploaded_file($tmpName, $rutaDestino)) {
                        $documentos[] = $nombreArchivo;
                    }
                }
                $contrato->guardarDocumentos($id, $documentos);
            }
    
            $resultado = $contrato->actualizarContrato(
                $id,
                $nombreContrato,
                $proveedorId,
                $fuenteFinanciamientoId,
                $nombreEncargado,
                $comentarios,
                $numeroContrato,
                $fechaInicio,
                $fechaFinal
            );
            echo json_encode(['success' => $resultado]);
            break;

    case 'eliminar':
        $id = $_POST['id'];
        $resultado = $contrato->eliminarContrato($id);
        echo json_encode(['success' => $resultado]);
        break;

    case 'subir_documento':
        if (!empty($_FILES['archivo']['name']) && isset($_POST['id_contrato'])) {
            $idContrato = $_POST['id_contrato'];
            $descripcion = $_POST['descripcion'];
            $archivo = $_FILES['archivo'];
            $nombreArchivo = $archivo['name'];
            $rutaDestino = "../../docs_contratos/$idContrato-$nombreArchivo";

            if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                // Guardar referencia en base de datos si es necesario
                echo json_encode(['success' => true, 'message' => 'Archivo subido correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al subir el archivo.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Falta archivo o ID del contrato.']);
        }
        break;
        case 'listar_proveedores':
            // Simulación de carga de proveedores desde la base de datos
            $proveedor=new Proveedor;
            $proveedores = $proveedor->listarProveedores();
            echo json_encode(['proveedores' => $proveedores]);
            break;
    
        case 'listar_fuentes':
            $fuente = new FuenteFinanciamiento(); // Clase que maneja la tabla de fuentes
            $resultados = $fuente->listarFuentesFinanciamiento(); // Método que obtiene todas las fuentes de financiamiento
            echo json_encode($resultados); // Devuelve los datos de las fuentes
            break;
        case 'agregarEquipo':
            $contratoId = intval($_POST['contrato_id_equipo']);
            $equipoId = intval($_POST['equipo_id']);
            $cantidad = intval($_POST['cantidad']);
            $marca = htmlspecialchars(trim($_POST['marca']), ENT_QUOTES, 'UTF-8');
            $precio = floatval($_POST['precio']);
            $resultado = $contrato->agregarEquipo($contratoId, $equipoId, $cantidad, $marca, $precio);
            echo json_encode(['success' => $resultado]);
            break;
        
        case 'listarEquiposAsociados':
            $contratoId = intval($_GET['contrato_id']);
            $equipos = $contrato->listarEquiposAsociados($contratoId);
            echo json_encode($equipos);
            break;
            
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
        break;
        
}
