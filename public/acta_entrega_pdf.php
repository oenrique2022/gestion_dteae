<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/includes/verificar_sesion.php';
require_once __DIR__ . '/../app/includes/config.php';

if (!is_readable(PROJECT_ROOT . '/vendor/autoload.php')) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Dependencias no instaladas. Ejecute composer install en la raíz del proyecto.';
    exit;
}

require_once PROJECT_ROOT . '/vendor/autoload.php';
require_once __DIR__ . '/../app/clases/ActaEntregaPdf.php';

$id = isset($_GET['id_entrega']) ? (int) $_GET['id_entrega'] : 0;
if ($id <= 0) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Parámetro id_entrega no válido.';
    exit;
}

ActaEntregaPdf::enviarPdf($id);
