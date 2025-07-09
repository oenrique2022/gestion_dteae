<?php
require_once 'Database.php';

class Capacitacion extends Database
{
    public function crearCapacitacion($nombre, $idEquipo, $fecha, $docentes, $estudiantes, $idInstitucion, $descripcion, $duracion, $tipo, $usuarioCreador)
    {
        try {
            $sql = "INSERT INTO capacitaciones 
                    (nombre_capacitacion, id_equipo, fecha, docentes_capacitados, estudiantes_capacitados, id_institucion, descripcion, duracion, tipo_capacitacion, usuario_creador) 
                    VALUES (:nombre, :idEquipo, :fecha, :docentes, :estudiantes, :idInstitucion, :descripcion, :duracion, :tipo, :usuarioCreador)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':idEquipo', $idEquipo, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':docentes', $docentes, PDO::PARAM_INT);
            $stmt->bindParam(':estudiantes', $estudiantes, PDO::PARAM_INT);
            $stmt->bindParam(':idInstitucion', $idInstitucion, PDO::PARAM_INT);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':duracion', $duracion, PDO::PARAM_INT);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':usuarioCreador', $usuarioCreador);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listarCapacitaciones()
    {
        try {
            $sql = "SELECT c.id_capacitacion, c.nombre_capacitacion, c.fecha, c.duracion, c.tipo_capacitacion, 
                           c.docentes_capacitados, c.estudiantes_capacitados, c.descripcion, 
                           i.nombre_ce AS institucion, codigo_infraestructura,e.nombre_equipo AS equipo 
                    FROM capacitaciones c
                    LEFT JOIN centros_educativos i ON c.id_institucion = i.centro_id
                    LEFT JOIN equipos e ON c.id_equipo = e.id_equipo";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId($id)
    {
        try {
            $sql = "SELECT * FROM capacitaciones WHERE id_capacitacion = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarCapacitacion(
        $idCapacitacion,
        $nombre,
        $idEquipo,
        $idInstitucion,
        $fecha,
        $duracion,
        $tipoCapacitacion,
        $docentesCapacitados,
        $estudiantesCapacitados,
        $descripcion
    ) {
        try {
            $sql = "UPDATE capacitaciones SET 
                        nombre_capacitacion = :nombre,
                        id_equipo = :idEquipo,
                        id_institucion = :idInstitucion,
                        fecha = :fecha,
                        duracion = :duracion,
                        tipo_capacitacion = :tipoCapacitacion,
                        docentes_capacitados = :docentesCapacitados,
                        estudiantes_capacitados = :estudiantesCapacitados,
                        descripcion = :descripcion,
                        fecha_modificacion = NOW()
                    WHERE id_capacitacion = :idCapacitacion";
    
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':idCapacitacion', $idCapacitacion, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':idEquipo', $idEquipo, PDO::PARAM_INT);
            $stmt->bindParam(':idInstitucion', $idInstitucion, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':duracion', $duracion, PDO::PARAM_INT);
            $stmt->bindParam(':tipoCapacitacion', $tipoCapacitacion);
            $stmt->bindParam(':docentesCapacitados', $docentesCapacitados, PDO::PARAM_INT);
            $stmt->bindParam(':estudiantesCapacitados', $estudiantesCapacitados, PDO::PARAM_INT);
            $stmt->bindParam(':descripcion', $descripcion);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function listarEquipos()
    {
        try {
            $sql = "SELECT id_equipo, nombre_equipo FROM equipos";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    
    public function eliminarCapacitacion($id)
    {
        try {
            $sql = "DELETE FROM capacitaciones WHERE id_capacitacion = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
