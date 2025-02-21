<?php
require_once '../includes/config.php';  
require_once '../includes/Proveedor.php';

$proveedor = new Proveedor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'crear':
            $nombreProveedor = $_POST['nombre_proveedor'];
            $telefonoContacto = $_POST['telefono_contacto'];
            $emailContacto = $_POST['email_contacto'];
            $contacto = $_POST['contacto'];
            $descripcion = $_POST['descripcion'];
            $usuarioCreador = $_SESSION['usuario'];
            $result = $proveedor->crearProveedor($nombreProveedor, $telefonoContacto, $emailContacto, $contacto, $descripcion, $usuarioCreador);
            echo json_encode(['success' => $result]);
            break;

        case 'listar':
            $data = $proveedor->listarProveedores();
            echo json_encode($data);
            break;

        case 'obtener':
            $id = $_POST['id'];
            $data = $proveedor->obtenerPorId($id);
            echo json_encode($data);
            break;

        case 'actualizar':
            $id = $_POST['idProveedor'];
            $nombreProveedor = $_POST['nombre_proveedor'];
            $telefonoContacto = $_POST['telefono_contacto'];
            $emailContacto = $_POST['email_contacto'];
            $contacto = $_POST['contacto'];
            $descripcion = $_POST['descripcion'];
            $result = $proveedor->actualizarProveedor($id, $nombreProveedor, $telefonoContacto, $emailContacto, $contacto, $descripcion);
            echo json_encode(['success' => $result]);
            break;

        case 'eliminar':
            $id = $_POST['id'];
            $result = $proveedor->eliminarProveedor($id);
            echo json_encode(['success' => $result]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
}
?>
