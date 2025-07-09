<?php
require_once 'Database.php';

class Proveedor extends Database
{
    public function crearProveedor($nombre, $telefono, $email, $contacto, $descripcion, $usuarioCreador)
    {
        $sql = "INSERT INTO proveedores (nombre_proveedor, telefono_contacto, email_contacto, contacto, descripcion, usuario_creador) 
                VALUES (:nombre, :telefono, :email, :contacto, :descripcion, :usuarioCreador)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contacto', $contacto);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':usuarioCreador', $usuarioCreador);
        return $stmt->execute();
    }

    public function listarProveedores()
    {
        $sql = "SELECT id_proveedor, nombre_proveedor, telefono_contacto, email_contacto, contacto, descripcion, fecha_creacion 
                FROM proveedores";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM proveedores WHERE id_proveedor = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarProveedor($id, $nombre, $telefono, $email, $contacto, $descripcion)
    {
        $sql = "UPDATE proveedores SET 
                    nombre_proveedor = :nombre, 
                    telefono_contacto = :telefono, 
                    email_contacto = :email, 
                    contacto = :contacto, 
                    descripcion = :descripcion,
                    fecha_modificacion = NOW()
                WHERE id_proveedor = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':telefono', $telefono);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':contacto', $contacto);
        $stmt->bindParam(':descripcion', $descripcion);
        return $stmt->execute();
    }

    public function eliminarProveedor($id)
    {
        $sql = "DELETE FROM proveedores WHERE id_proveedor = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
?>
