<?php
require_once __DIR__ . '/DataBase.php';

class Reporte {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Lista cada entrega con el centro educativo y el contrato asociados.
     */
    public function listarEntregasCentrosContratos(): array {
        try {
            $sql = "SELECT 
                        e.id_entrega,
                        e.id_contrato,
                        e.id_institucion,
                        e.fecha_entrega,
                        e.estado,
                        e.firma_responsable,
                        e.comentarios AS comentarios_entrega,
                        c.numero_contrato,
                        c.nombre_contrato,
                        c.fecha_inicio AS contrato_fecha_inicio,
                        c.fecha_fin AS contrato_fecha_fin,
                        c.nombre_encargado,
                        c.comentarios AS comentarios_contrato,
                        p.nombre_proveedor,
                        ff.nombre AS fuente_financiamiento,
                        ce.centro_id,
                        ce.nombre_ce,
                        ce.codigo_infraestructura,
                        (
                            SELECT GROUP_CONCAT(
                                CONCAT(
                                    COALESCE(eq.nombre_equipo, CONCAT('Equipo #', CAST(ed.id_equipo AS CHAR))),
                                    ' × ',
                                    ed.cantidad
                                )
                                ORDER BY eq.nombre_equipo, ed.id_equipo
                                SEPARATOR ' | '
                            )
                            FROM entregas_detalle ed
                            LEFT JOIN equipos eq ON ed.id_equipo = eq.id_equipo
                            WHERE ed.id_entrega = e.id_entrega
                        ) AS productos_entregados
                    FROM entregas e
                    INNER JOIN contratos c ON e.id_contrato = c.id
                    LEFT JOIN proveedores p ON c.proveedor_id = p.id_proveedor
                    LEFT JOIN fuentes_financiamiento ff ON c.fuente_financiamiento_id = ff.id
                    LEFT JOIN centros_educativos ce ON e.id_institucion = ce.centro_id
                    ORDER BY COALESCE(ce.nombre_ce, CONCAT('ID ', e.id_institucion)) ASC,
                             c.numero_contrato ASC,
                             e.fecha_entrega DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Cada fila = una línea de entrega (producto) con el centro y contrato asociados.
     */
    public function listarProductosEntregadosPorCentro(): array {
        try {
            $sql = "SELECT 
                        ed.id_entrega,
                        ed.id_equipo,
                        ed.cantidad,
                        ed.comentario AS detalle_comentario,
                        COALESCE(eq.nombre_equipo, CONCAT('Equipo #', CAST(ed.id_equipo AS CHAR))) AS nombre_equipo,
                        eq.codigo_equipo,
                        te.nombre_tipo_equipo,
                        e.id_institucion,
                        e.fecha_entrega,
                        e.estado,
                        ce.centro_id,
                        ce.nombre_ce,
                        ce.codigo_infraestructura,
                        c.id AS id_contrato,
                        c.numero_contrato,
                        c.nombre_contrato
                    FROM entregas_detalle ed
                    INNER JOIN entregas e ON ed.id_entrega = e.id_entrega
                    INNER JOIN contratos c ON e.id_contrato = c.id
                    LEFT JOIN equipos eq ON ed.id_equipo = eq.id_equipo
                    LEFT JOIN tipos_equipos te ON eq.id_tipo_equipo = te.id_tipo_equipo
                    LEFT JOIN centros_educativos ce ON e.id_institucion = ce.centro_id
                    ORDER BY nombre_equipo ASC,
                             COALESCE(ce.nombre_ce, CONCAT('ID ', e.id_institucion)) ASC,
                             e.fecha_entrega DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Opciones de filtros territoriales basadas en los centros educativos.
     */
    public function obtenerFiltrosTerritorio(): array {
        try {
            $sqlDeps = "SELECT DISTINCT
                            departamento_id,
                            TRIM(COALESCE(departamento, '')) AS departamento
                        FROM centros_educativos
                        WHERE departamento_id IS NOT NULL
                        ORDER BY departamento ASC";
            $st = $this->conn->prepare($sqlDeps);
            $st->execute();
            $rowsDeps = $st->fetchAll(PDO::FETCH_ASSOC);

            $departamentos = [];
            $municipiosPorDepartamento = [];
            foreach ($rowsDeps as $r) {
                $depId = isset($r['departamento_id']) ? (int) $r['departamento_id'] : 0;
                $depNombre = trim((string) ($r['departamento'] ?? ''));
                if ($depId < 1) {
                    continue;
                }
                $departamentos[] = [
                    'id' => $depId,
                    'nombre' => $depNombre !== '' ? $depNombre : ("Departamento " . $depId),
                ];
                $municipiosPorDepartamento[(string) $depId] = [];
            }

            $sqlMuns = "SELECT DISTINCT
                            departamento_id,
                            municipio_id,
                            TRIM(COALESCE(municipio, '')) AS municipio
                        FROM centros_educativos
                        WHERE departamento_id IS NOT NULL
                          AND municipio_id IS NOT NULL
                        ORDER BY municipio ASC";
            $st = $this->conn->prepare($sqlMuns);
            $st->execute();
            $rowsMuns = $st->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rowsMuns as $r) {
                $depId = isset($r['departamento_id']) ? (int) $r['departamento_id'] : 0;
                $munId = isset($r['municipio_id']) ? (int) $r['municipio_id'] : 0;
                if ($depId < 1 || $munId < 1) {
                    continue;
                }
                $munNombre = trim((string) ($r['municipio'] ?? ''));
                $depKey = (string) $depId;
                if (!isset($municipiosPorDepartamento[$depKey])) {
                    $municipiosPorDepartamento[$depKey] = [];
                }
                $municipiosPorDepartamento[$depKey][] = [
                    'id' => $munId,
                    'nombre' => $munNombre !== '' ? $munNombre : ("Municipio " . $munId),
                ];
            }

            return [
                'departamentos' => $departamentos,
                'municipios_por_departamento' => $municipiosPorDepartamento,
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [
                'departamentos' => [],
                'municipios_por_departamento' => [],
            ];
        }
    }

    private function construirFiltroTerritorial(int $departamentoId = 0, int $municipioId = 0): array {
        $sql = '';
        $params = [];
        if ($departamentoId > 0) {
            $sql .= ' AND ce.departamento_id = :departamento_id';
            $params[':departamento_id'] = $departamentoId;
        }
        if ($municipioId > 0) {
            $sql .= ' AND ce.municipio_id = :municipio_id';
            $params[':municipio_id'] = $municipioId;
        }
        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Resumen gerencial: métricas y desgloses filtrados por fecha de entrega (inclusive).
     * Solo se consideran entregas con fecha_entrega no nula.
     */
    public function resumenGerencial(string $fechaDesde, string $fechaHasta, int $departamentoId = 0, int $municipioId = 0): array {
        $vacio = [
            'rango' => ['desde' => $fechaDesde, 'hasta' => $fechaHasta],
            'filtros' => ['departamento_id' => $departamentoId, 'municipio_id' => $municipioId],
            'kpis' => [
                'total_entregas' => 0,
                'total_unidades' => 0,
                'centros_unicos' => 0,
                'contratos_unicos' => 0,
            ],
            'por_estado' => [],
            'top_productos' => [],
            'por_mes' => [],
        ];

        try {
            $rango = 'e.fecha_entrega IS NOT NULL AND DATE(e.fecha_entrega) BETWEEN :desde AND :hasta';
            $filtroTerritorial = $this->construirFiltroTerritorial($departamentoId, $municipioId);
            $whereBase = "{$rango}{$filtroTerritorial['sql']}";
            $params = array_merge([':desde' => $fechaDesde, ':hasta' => $fechaHasta], $filtroTerritorial['params']);

            $sqlKpi = "SELECT 
                            COUNT(DISTINCT e.id_entrega) AS total_entregas,
                            COUNT(DISTINCT e.id_institucion) AS centros_unicos,
                            COUNT(DISTINCT e.id_contrato) AS contratos_unicos
                       FROM entregas e
                       LEFT JOIN centros_educativos ce ON e.id_institucion = ce.centro_id
                       WHERE {$whereBase}";
            $st = $this->conn->prepare($sqlKpi);
            $st->execute($params);
            $k = $st->fetch(PDO::FETCH_ASSOC) ?: [];

            $sqlUni = "SELECT COALESCE(SUM(ed.cantidad), 0) AS total_unidades
                       FROM entregas e
                       INNER JOIN entregas_detalle ed ON ed.id_entrega = e.id_entrega
                       LEFT JOIN centros_educativos ce ON e.id_institucion = ce.centro_id
                       WHERE {$whereBase}";
            $st = $this->conn->prepare($sqlUni);
            $st->execute($params);
            $u = $st->fetch(PDO::FETCH_ASSOC);

            $kpis = [
                'total_entregas' => (int) ($k['total_entregas'] ?? 0),
                'total_unidades' => (int) ($u['total_unidades'] ?? 0),
                'centros_unicos' => (int) ($k['centros_unicos'] ?? 0),
                'contratos_unicos' => (int) ($k['contratos_unicos'] ?? 0),
            ];

            $sqlEst = "SELECT e.estado, COUNT(DISTINCT e.id_entrega) AS cantidad
                       FROM entregas e
                       LEFT JOIN centros_educativos ce ON e.id_institucion = ce.centro_id
                       WHERE {$whereBase}
                       GROUP BY e.estado
                       ORDER BY cantidad DESC";
            $st = $this->conn->prepare($sqlEst);
            $st->execute($params);
            $porEstado = $st->fetchAll(PDO::FETCH_ASSOC);

            $sqlTop = "SELECT 
                            ed.id_equipo,
                            COALESCE(MAX(eq.nombre_equipo), CONCAT('Equipo #', ed.id_equipo)) AS nombre_equipo,
                            SUM(ed.cantidad) AS total_cantidad
                       FROM entregas e
                       INNER JOIN entregas_detalle ed ON ed.id_entrega = e.id_entrega
                       LEFT JOIN equipos eq ON ed.id_equipo = eq.id_equipo
                       LEFT JOIN centros_educativos ce ON e.id_institucion = ce.centro_id
                       WHERE {$whereBase}
                       GROUP BY ed.id_equipo
                       ORDER BY total_cantidad DESC
                       LIMIT 20";
            $st = $this->conn->prepare($sqlTop);
            $st->execute($params);
            $top = $st->fetchAll(PDO::FETCH_ASSOC);

            $sqlMes = "SELECT 
                            DATE_FORMAT(e.fecha_entrega, '%Y-%m') AS periodo,
                            COUNT(DISTINCT e.id_entrega) AS num_entregas,
                            COALESCE(SUM(ed.cantidad), 0) AS unidades
                       FROM entregas e
                       LEFT JOIN entregas_detalle ed ON ed.id_entrega = e.id_entrega
                       LEFT JOIN centros_educativos ce ON e.id_institucion = ce.centro_id
                       WHERE {$whereBase}
                       GROUP BY DATE_FORMAT(e.fecha_entrega, '%Y-%m')
                       ORDER BY periodo ASC";
            $st = $this->conn->prepare($sqlMes);
            $st->execute($params);
            $porMes = $st->fetchAll(PDO::FETCH_ASSOC);

            foreach ($porMes as &$fila) {
                $fila['num_entregas'] = (int) $fila['num_entregas'];
                $fila['unidades'] = (int) $fila['unidades'];
            }
            unset($fila);

            foreach ($top as &$t) {
                $t['total_cantidad'] = (int) $t['total_cantidad'];
                $t['id_equipo'] = (int) $t['id_equipo'];
            }
            unset($t);

            foreach ($porEstado as &$pe) {
                $pe['cantidad'] = (int) $pe['cantidad'];
            }
            unset($pe);

            return [
                'rango' => ['desde' => $fechaDesde, 'hasta' => $fechaHasta],
                'filtros' => ['departamento_id' => $departamentoId, 'municipio_id' => $municipioId],
                'kpis' => $kpis,
                'por_estado' => $porEstado,
                'top_productos' => $top,
                'por_mes' => $porMes,
            ];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return $vacio;
        }
    }

    /**
     * Centros que recibieron un producto (equipo) en el rango de fechas, con unidades agregadas.
     */
    public function centrosPorProductoEnRango(int $idEquipo, string $fechaDesde, string $fechaHasta, int $departamentoId = 0, int $municipioId = 0): array {
        try {
            $filtroTerritorial = $this->construirFiltroTerritorial($departamentoId, $municipioId);
            $sql = "SELECT 
                        e.id_institucion,
                        COALESCE(MAX(ce.nombre_ce), CONCAT('Institución ID ', e.id_institucion)) AS nombre_ce,
                        MAX(ce.departamento) AS departamento,
                        MAX(ce.municipio) AS municipio,
                        MAX(ce.codigo_infraestructura) AS codigo_infraestructura,
                        SUM(ed.cantidad) AS unidades,
                        COUNT(DISTINCT e.id_entrega) AS num_entregas
                    FROM entregas e
                    INNER JOIN entregas_detalle ed ON ed.id_entrega = e.id_entrega
                    LEFT JOIN centros_educativos ce ON e.id_institucion = ce.centro_id
                    WHERE ed.id_equipo = :id_equipo
                      AND e.fecha_entrega IS NOT NULL
                      AND DATE(e.fecha_entrega) BETWEEN :desde AND :hasta
                      {$filtroTerritorial['sql']}
                    GROUP BY e.id_institucion
                    ORDER BY unidades DESC, nombre_ce ASC";
            $st = $this->conn->prepare($sql);
            $st->execute(array_merge([
                ':id_equipo' => $idEquipo,
                ':desde' => $fechaDesde,
                ':hasta' => $fechaHasta,
            ], $filtroTerritorial['params']));
            $filas = $st->fetchAll(PDO::FETCH_ASSOC);
            foreach ($filas as &$f) {
                $f['unidades'] = (int) $f['unidades'];
                $f['num_entregas'] = (int) $f['num_entregas'];
                $f['id_institucion'] = isset($f['id_institucion']) ? (int) $f['id_institucion'] : 0;
            }
            unset($f);
            return $filas;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /**
     * Vista general de calendarización de rutas de entrega por rango de fecha programada.
     */
    public function calendarizacionRutas(string $fechaDesde, string $fechaHasta, int $departamentoId = 0, int $municipioId = 0): array {
        $vacio = ['resumen_estado' => [], 'rutas' => []];
        try {
            $filtroTerritorial = $this->construirFiltroTerritorial($departamentoId, $municipioId);
            $where = "r.fecha_programada IS NOT NULL
                      AND r.fecha_programada BETWEEN :desde AND :hasta
                      {$filtroTerritorial['sql']}";
            $params = array_merge([':desde' => $fechaDesde, ':hasta' => $fechaHasta], $filtroTerritorial['params']);

            $sqlResumen = "SELECT r.estado, COUNT(*) AS cantidad
                           FROM rutas_entrega r
                           LEFT JOIN centros_educativos ce ON ce.centro_id = r.id_institucion
                           WHERE {$where}
                           GROUP BY r.estado
                           ORDER BY cantidad DESC, r.estado ASC";
            $st = $this->conn->prepare($sqlResumen);
            $st->execute($params);
            $resumen = $st->fetchAll(PDO::FETCH_ASSOC);
            foreach ($resumen as &$r) {
                $r['cantidad'] = (int) ($r['cantidad'] ?? 0);
            }
            unset($r);

            $sqlRutas = "SELECT
                            r.id,
                            r.contrato_id,
                            COALESCE(c.numero_contrato, CONCAT('Contrato #', r.contrato_id)) AS numero_contrato,
                            r.id_institucion,
                            COALESCE(ce.nombre_ce, CONCAT('Institución ID ', r.id_institucion)) AS nombre_ce,
                            COALESCE(ce.departamento, '') AS departamento,
                            COALESCE(ce.municipio, '') AS municipio,
                            ce.codigo_infraestructura,
                            r.responsable_entrega,
                            r.motorista,
                            r.vehiculo,
                            r.placas,
                            r.estado,
                            r.fecha_programada,
                            r.fecha_en_ruta,
                            r.fecha_entregado,
                            r.comentarios
                         FROM rutas_entrega r
                         LEFT JOIN centros_educativos ce ON ce.centro_id = r.id_institucion
                         LEFT JOIN contratos c ON c.id = r.contrato_id
                         WHERE {$where}
                         ORDER BY r.fecha_programada ASC, r.estado ASC, nombre_ce ASC
                         LIMIT 300";
            $st = $this->conn->prepare($sqlRutas);
            $st->execute($params);
            $rutas = $st->fetchAll(PDO::FETCH_ASSOC);

            return ['resumen_estado' => $resumen, 'rutas' => $rutas];
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return $vacio;
        }
    }
}
