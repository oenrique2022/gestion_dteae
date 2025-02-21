<?php
require_once 'Database.php';

class Equipo extends Database
{
    public function crearEquipo($codigo, $nombre, $tipo, $estado, $fechaAdquisicion, $descripcion, $usuarioCreador)
    {
        try {
            $sql = "INSERT INTO equipos (codigo_equipo, nombre_equipo, id_tipo_equipo, estado, fecha_adquisicion, descripcion, usuario_creador) 
                    VALUES (:codigo, :nombre, :tipo, :estado, :fechaAdquisicion, :descripcion, :usuarioCreador)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':tipo', $tipo, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':fechaAdquisicion', $fechaAdquisicion);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':usuarioCreador', $usuarioCreador);
            return $stmt->execute();
        } catch (PDOException $e) {
           
            return false; // Podrías agregar un registro de errores aquí
        }
    }

    public function listarEquipos()
    {
        try {
            $sql = "SELECT e.id_equipo, e.codigo_equipo, e.nombre_equipo, t.nombre_tipo_equipo, e.estado, e.fecha_adquisicion, e.descripcion 
                    FROM equipos e 
                    LEFT JOIN tipos_equipos t ON e.id_tipo_equipo = t.id_tipo_equipo";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerPorId($id) {
        $query = "
            SELECT 
                e.id_equipo,
                e.codigo_equipo,
                e.nombre_equipo,
                e.estado,
                e.fecha_adquisicion,
                e.descripcion,
                t.id_tipo_equipo,
                t.nombre_tipo_equipo
            FROM 
                equipos e
            INNER JOIN 
                tipos_equipos t ON e.id_tipo_equipo = t.id_tipo_equipo
            WHERE 
                e.id_equipo = :id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    

    public function actualizarEquipo($id, $codigo, $nombre, $tipo, $estado, $fechaAdquisicion, $descripcion)
    {
        try {
            $sql = "UPDATE equipos SET 
                        codigo_equipo = :codigo,
                        nombre_equipo = :nombre,
                        id_tipo_equipo = :tipo,
                        estado = :estado,
                        fecha_adquisicion = :fechaAdquisicion,
                        descripcion = :descripcion,
                        fecha_modificacion = NOW()
                    WHERE id_equipo = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':tipo', $tipo, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':fechaAdquisicion', $fechaAdquisicion);
            $stmt->bindParam(':descripcion', $descripcion);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarEquipo($id)
    {
        try {
            $sql = "DELETE FROM equipos WHERE id_equipo = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
