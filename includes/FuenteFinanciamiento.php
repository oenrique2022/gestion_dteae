<?php
require_once 'Database.php';

class FuenteFinanciamiento extends Database
{
    // Método para crear una nueva fuente de financiamiento
    public function crearFuenteFinanciamiento($nombre, $descripcion, $usuarioCreador)
    {
        try {
            $sql = "INSERT INTO fuentes_financiamiento (nombre, descripcion, usuario_creador)
                    VALUES (:nombre, :descripcion, :usuarioCreador)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':usuarioCreador', $usuarioCreador);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false; // Error en la inserción
        }
    }

    // Método para listar todas las fuentes de financiamiento
    public function listarFuentesFinanciamiento()
    {
        try {
            $sql = "SELECT id, nombre, descripcion FROM fuentes_financiamiento";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    // Método para obtener una fuente de financiamiento por su ID
    public function obtenerPorId($id)
    {
        $query = "
            SELECT id, nombre, descripcion 
            FROM fuentes_financiamiento 
            WHERE id = :id
        ";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método para actualizar una fuente de financiamiento
    public function actualizarFuenteFinanciamiento($id, $nombre, $descripcion)
    {
        try {
            $sql = "UPDATE fuentes_financiamiento SET
                        nombre = :nombre,
                        descripcion = :descripcion,
                        fecha_modificacion = NOW()
                    WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Método para eliminar una fuente de financiamiento
    public function eliminarFuenteFinanciamiento($id)
    {
        try {
            $sql = "DELETE FROM fuentes_financiamiento WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>
