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
}
