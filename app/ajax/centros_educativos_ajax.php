<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/verificar_sesion.php';
require_once __DIR__ . '/../clases/CentroEducativo.php';

function ce_json_out(array $response) {
    $flags = JSON_UNESCAPED_UNICODE;
    if (defined('JSON_INVALID_UTF8_SUBSTITUTE')) {
        $flags |= JSON_INVALID_UTF8_SUBSTITUTE;
    }
    $out = json_encode($response, $flags);
    if ($out === false) {
        $out = json_encode(['success' => false, 'message' => 'Error al serializar la respuesta.'], JSON_UNESCAPED_UNICODE);
    }
    echo $out;
}

$model = new CentroEducativo();
$response = ['success' => false, 'message' => 'Acción no válida'];
$action = $_REQUEST['action'] ?? null;

switch ($action) {
    case 'listar':
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = (int) ($_GET['per_page'] ?? 50);
        $perPage = min(200, max(10, $perPage));
        $busqueda = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $busqueda = $busqueda === '' ? null : $busqueda;
        $total = $model->contarTodos($busqueda);
        $totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 0;
        if ($totalPages > 0 && $page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;
        $response['success'] = true;
        $response['data'] = $model->listarPagina($offset, $perPage, $busqueda);
        $response['pagination'] = [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
        break;

    case 'listar_departamentos':
        $response['success'] = true;
        $response['data'] = $model->listarDepartamentosDistintos();
        break;

    case 'listar_municipios':
        $depId = isset($_GET['departamento_id']) ? (int) $_GET['departamento_id'] : 0;
        if ($depId <= 0) {
            $response['message'] = 'Departamento no válido';
            break;
        }
        $response['success'] = true;
        $response['data'] = $model->listarMunicipiosPorDepartamento($depId);
        break;

    case 'obtener':
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $data = $model->leerUno($id);
        if ($data) {
            $response['success'] = true;
            $response['data'] = $data;
        } else {
            $response['message'] = 'Centro educativo no encontrado';
        }
        break;

    case 'guardar':
        if (!usuarioPuedeEscribir()) {
            denegarAccesoApi('No tiene permisos para guardar centros educativos.');
        }
        $id = isset($_POST['centro_id']) ? trim((string) $_POST['centro_id']) : '';
        $nombreCe = trim((string) ($_POST['nombre_ce'] ?? ''));
        $departamentoId = $_POST['departamento_id'] ?? '';
        $departamento = trim((string) ($_POST['departamento'] ?? ''));
        $municipioId = $_POST['municipio_id'] ?? '';
        $municipio = trim((string) ($_POST['municipio'] ?? ''));
        $codigo = $_POST['codigo_infraestructura'] ?? '';
        $codigo = $codigo === '' ? '' : preg_replace('/[^\d]/', '', (string) $codigo);
        $director = trim((string) ($_POST['director_actual'] ?? ''));

        if ($nombreCe === '') {
            $response['message'] = 'El nombre del centro es obligatorio.';
            ce_json_out($response);
            exit;
        }

        if ($id === '') {
            $resultado = $model->crear($nombreCe, $departamentoId, $departamento, $municipioId, $municipio, $codigo, $director);
            $response['message'] = $resultado ? 'Centro educativo creado correctamente.' : 'Error al crear el centro.';
        } else {
            $resultado = $model->actualizar(
                (int) $id,
                $nombreCe,
                $departamentoId,
                $departamento,
                $municipioId,
                $municipio,
                $codigo,
                $director
            );
            $response['message'] = $resultado ? 'Centro educativo actualizado correctamente.' : 'Error al actualizar el centro.';
        }
        $response['success'] = (bool) $resultado;
        break;

    case 'eliminar':
        if (!usuarioPuedeEliminar()) {
            denegarAccesoApi('Solo administradores pueden eliminar centros educativos.');
        }
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        if ($id <= 0) {
            $response['message'] = 'Identificador no válido';
            break;
        }
        $resultado = $model->eliminar($id);
        if ($resultado) {
            $response['success'] = true;
            $response['message'] = 'Centro educativo eliminado correctamente.';
        } else {
            $response['message'] = 'No se pudo eliminar el centro (puede estar en uso).';
        }
        break;
}

ce_json_out($response);
