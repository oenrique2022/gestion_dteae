<?php

require_once 'Database.php';

class Usuario extends Database {

    // Obtener un usuario por su correo
    public function obtenerPorEmail($email){
        $query = "SELECT * FROM usuarios WHERE nombre_usuario = :nombre_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre_usuario', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear un nuevo usuario
    public function crearUsuario($nombre, $email, $password, $id_rol){
        $query = "INSERT INTO usuarios (nombre_usuario, correo, password, id_rol) VALUES (:nombre_usuario, :correo, :password, :id_rol)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre_usuario', $nombre);
        $stmt->bindParam(':correo', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':id_rol', $id_rol);
        return $stmt->execute();
    }

    // Listar todos los usuarios
    public function listarUsuarios() {
        $query = "SELECT u.id_usuario, u.nombre_usuario, u.correo, r.nombre_rol AS rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un usuario por su ID
    public function obtenerPorId($id){
        $query = "SELECT * FROM usuarios WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar un usuario
    public function actualizarUsuario($id, $nombre, $email, $password, $id_rol){
        $query = "UPDATE usuarios SET nombre_usuario = :nombre_usuario, correo = :correo, password = :password, id_rol = :id_rol WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id);
        $stmt->bindParam(':nombre_usuario', $nombre);
        $stmt->bindParam(':correo', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':id_rol', $id_rol);
        return $stmt->execute();
    }

    // Eliminar un usuario
    public function eliminarUsuario($id){
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
