<?php
require_once '../includes/config.php'; // Configuración y conexión a la base de datos
require_once '../includes/Reportes.php';

$reportes = new Reportes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $anio = isset($_POST['anio']) ? $_POST['anio']   :  0; // Valor por defecto si no se envía un año

    switch ($action) {
        case 'institucionesConTecnologia':
            $data = $reportes->institucionesConTecnologia($anio);
            echo json_encode([
                'success' => true,
                'data' => $data,
            ]);
            break;

        case 'tecnologiasEntregadas':
            $data = $reportes->tecnologiasEntregadas($anio);
           // print_r($data);
            echo json_encode([
                'success' => true,
                'data' => $data,
            ]);
            break;

        case 'capacitacionesPorTipo':
            $data = $reportes->capacitacionesPorTipo($anio);
            echo json_encode([
                'success' => true,
                'data' => $data,
            ]);
            break;

        case 'listarAnios':
            $data = $reportes->obtenerAnios();
            echo json_encode([
                'success' => true,
                'data' => $data,
            ]);
            break;
        case 'modalidadesCapacitacion':
           
            $data = $reportes->obtenerCapacitacionesPorModalidad($anio);

            if ($data) {
                echo json_encode([
                    'success' => true,
                    'data' => $data,
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontraron datos.',
                ]);
            }
            break;
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida',
            ]);
    }
}
