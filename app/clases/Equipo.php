<?php
require_once __DIR__ . '/Database.php';

class Equipo {
    private $conn;
    private $table_name = "equipos";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // Unimos las tablas para obtener el nombre del tipo y el estado ACTIVO
    public function leerTodos() {
        try {
            $query = "SELECT e.id_equipo, e.codigo_equipo, e.nombre_equipo, te.nombre_tipo_equipo, e.activo 
                      FROM " . $this->table_name . " e
                      LEFT JOIN tipos_equipos te ON e.id_tipo_equipo = te.id_tipo_equipo
                      ORDER BY e.nombre_equipo";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function leerUno($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id_equipo = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function crear($codigo, $nombre, $id_tipo, $descripcion) {
        try {
            $query = "INSERT INTO " . $this->table_name . " (codigo_equipo, nombre_equipo, id_tipo_equipo, descripcion, usuario_creador) VALUES (:codigo, :nombre, :id_tipo, :desc, 'System')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':id_tipo', $id_tipo);
            $stmt->bindParam(':desc', $descripcion);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizar($id, $codigo, $nombre, $id_tipo, $descripcion) {
        try {
            $query = "UPDATE " . $this->table_name . " SET codigo_equipo = :codigo, nombre_equipo = :nombre, id_tipo_equipo = :id_tipo, descripcion = :desc WHERE id_equipo = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':id_tipo', $id_tipo);
            $stmt->bindParam(':desc', $descripcion);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function cambiarEstado($id, $estado) {
        try {
            $query = "UPDATE " . $this->table_name . " SET activo = :estado WHERE id_equipo = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminar($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id_equipo = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    public function leerActivos() {
        try {
            $query = "SELECT id_equipo, nombre_equipo FROM " . $this->table_name . " WHERE activo = 1 ORDER BY nombre_equipo";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>