<?php
require_once 'Database.php';

class TipoEquipos extends Database
{
    public function crearTipoEquipo($nombre, $descripcion, $usuario_creador)
    {
        try {
            $sql = "INSERT INTO tipos_equipos (nombre_tipo_equipo, descripcion, usuario_creador) 
                    VALUES (:nombre, :descripcion, :usuario_creador)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':usuario_creador', $usuario_creador);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function listarTiposEquipos()
    {
        try {
            $sql = "SELECT * FROM tipos_equipos";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM tipos_equipos WHERE id_tipo_equipo = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarTipoEquipo($id, $nombre, $descripcion)
    {
        try {
            $sql = "UPDATE tipos_equipos SET nombre_tipo_equipo = :nombre, descripcion = :descripcion 
                    WHERE id_tipo_equipo = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarTipoEquipo($id)
    {
        try {
            $sql = "DELETE FROM tipos_equipos WHERE id_tipo_equipo = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
