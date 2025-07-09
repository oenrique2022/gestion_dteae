<?php
require_once 'Database.php';

class Reportes extends Database
{
    public function institucionesConTecnologia($anio = null)
    {
        $sql = "SELECT COUNT(DISTINCT e.id_institucion) AS total_instituciones
                FROM entregas e
                INNER JOIN contratos c ON e.id_contrato = c.id";
    
        $params = []; // Inicializa como un array vacío
        if ($anio) {
            $sql .= " WHERE YEAR(c.fecha_inicio) = :anio";
            $params = [':anio' => $anio]; // Asegúrate de construir el array correctamente
        }
    
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params); // Esto ejecuta la consulta con los parámetros
        return $stmt->fetch(PDO::FETCH_ASSOC); // Retorna el resultado
    }
    

    public function tecnologiasEntregadas($anio = 0)
    {
        $sql = "SELECT t.nombre_tipo_equipo AS tecnologia, COUNT(ed.id_equipo) AS total
                FROM entregas_detalle ed
                INNER JOIN equipos e ON ed.id_equipo = e.id_equipo
                INNER JOIN tipos_equipos t ON e.id_tipo_equipo = t.id_tipo_equipo";

        $params = [];
        if($anio>2000) {
           
            $sql .= " WHERE YEAR(ed.fecha_creacion) = :anio";
            $params[':anio'] = $anio;
        }
        $sql .= " GROUP BY t.nombre_tipo_equipo";
     
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function capacitacionesPorTipo($anio = null)
    {
        $sql = "SELECT c.tipo_capacitacion, COUNT(c.id_capacitacion) AS total
                FROM capacitaciones c";

        $params = [];
        if ($anio) {
            $sql .= " WHERE YEAR(c.fecha) = :anio";
            $params[':anio'] = $anio;
        }

        $sql .= " GROUP BY c.tipo_capacitacion";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function obtenerAnios() {
        try {
            $query = "SELECT DISTINCT YEAR(fecha_creacion) AS anio FROM contratos
                      UNION
                      SELECT DISTINCT YEAR(fecha_creacion) AS anio FROM entregas
                      UNION
                      SELECT DISTINCT YEAR(fecha_creacion) AS anio FROM capacitaciones
                      ORDER BY anio DESC";
            $stmt = $this->conn->query($query);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }
    public function obtenerCapacitacionesPorModalidad($anio = 0)
    {
        $sql = "SELECT c.tipo_capacitacion as modalidad, 
                       SUM(c.docentes_capacitados) AS total_docentes, 
                       SUM(c.estudiantes_capacitados) AS total_estudiantes
                FROM capacitaciones c";
    
        $params = [];
        if ($anio>2000) {
            $sql .= " WHERE YEAR(c.fecha) = :anio";
            $params[':anio'] = $anio;
        }
    
        $sql .= " GROUP BY c.tipo_capacitacion";
    //echo $anio.$sql ;
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    
    
}
