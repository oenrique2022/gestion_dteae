<?php
require_once __DIR__ . '/Database.php';


class Proveedor {
    private $conn;
    private $table_name = "proveedores";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function leerTodos() {
        try {
            $query = "SELECT * FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Manejar el error, por ejemplo, loguearlo
            return [];
        }
    }

    public function leerUno($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id_proveedor = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function crear($nombre, $contacto, $telefono, $email, $descripcion) {
        try {
            $query = "INSERT INTO " . $this->table_name . " (nombre_proveedor, contacto, telefono_contacto, email_contacto, descripcion, usuario_creador) VALUES (:nombre, :contacto, :tel, :email, :desc, 'System')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':contacto', $contacto);
            $stmt->bindParam(':tel', $telefono);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':desc', $descripcion);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizar($id, $nombre, $contacto, $telefono, $email, $descripcion) {
        try {
            $query = "UPDATE " . $this->table_name . " SET nombre_proveedor = :nombre, contacto = :contacto, telefono_contacto = :tel, email_contacto = :email, descripcion = :desc WHERE id_proveedor = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':contacto', $contacto);
            $stmt->bindParam(':tel', $telefono);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':desc', $descripcion);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminar($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id_proveedor = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>