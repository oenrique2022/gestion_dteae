<?php
require_once __DIR__ . '/Database.php';

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Autentica a un usuario y, si es exitoso, devuelve sus datos.
     * Clave para el inicio de sesión.
     */
    public function autenticar($correo, $password) {
        try {
            // Busca un usuario con ese correo que además esté activo
            $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " WHERE correo = :correo AND activo = 1");
            $stmt->execute([':correo' => $correo]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Si se encontró un usuario y la contraseña coincide con el hash guardado
            if ($usuario && password_verify($password, $usuario['password'])) {
                unset($usuario['password']); // Por seguridad, nunca devolver el hash
                return $usuario;
            }
            return false; // Si no, las credenciales son incorrectas
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * Encripta la contraseña automáticamente.
     */
    public function crear($nombre, $correo, $password, $id_rol) {
        try {
            // Encriptar la contraseña antes de guardarla
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO " . $this->table_name . " (nombre_usuario, correo, password, id_rol, activo, usuario_creador) VALUES (:nombre, :correo, :password, :id_rol, 1, 'System')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id_rol', $id_rol);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Lee todos los usuarios para la página de gestión.
     */
    public function leerTodos() {
        // Unimos con la tabla roles para obtener el nombre del rol
        $query = "SELECT u.id_usuario, u.nombre_usuario, u.correo, u.activo, r.nombre_rol 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN roles r ON u.id_rol = r.id_rol 
                  ORDER BY u.nombre_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Lee los datos de un solo usuario por su ID.
     */
    public function leerUno($id_usuario) {
        $query = "SELECT id_usuario, nombre_usuario, correo, id_rol, activo FROM " . $this->table_name . " WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_usuario);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    /**
     * Actualiza los datos de un usuario.
     * Si la contraseña se deja en blanco, no se actualiza.
     */
    public function actualizar($id, $nombre, $correo, $id_rol, $password = null) {
        try {
            $query = "UPDATE " . $this->table_name . " SET nombre_usuario = :nombre, correo = :correo, id_rol = :id_rol";
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $query .= ", password = :password";
            }
            $query .= " WHERE id_usuario = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':correo', $correo);
            $stmt->bindParam(':id_rol', $id_rol);
            if (!empty($password)) {
                $stmt->bindParam(':password', $hashed_password);
            }
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Cambia el estado de un usuario (activo/inactivo).
     */
    public function cambiarEstado($id_usuario, $estado) {
        $query = "UPDATE " . $this->table_name . " SET activo = :estado WHERE id_usuario = :id_usuario";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id_usuario', $id_usuario);
        return $stmt->execute();
    }

    /**
     * Permite a un usuario cambiar su propia contraseña, verificando la antigua primero.
     */
    public function cambiarPassword($id_usuario, $password_actual, $password_nueva) {
        try {
            $stmt = $this->conn->prepare("SELECT password FROM " . $this->table_name . " WHERE id_usuario = :id");
            $stmt->execute([':id' => $id_usuario]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password_actual, $usuario['password'])) {
                // La contraseña actual es correcta, proceder a cambiarla
                $hashed_password = password_hash($password_nueva, PASSWORD_DEFAULT);
                $update_stmt = $this->conn->prepare("UPDATE " . $this->table_name . " SET password = :password WHERE id_usuario = :id");
                $update_stmt->bindParam(':password', $hashed_password);
                $update_stmt->bindParam(':id', $id_usuario);
                return $update_stmt->execute();
            }
            return false; // La contraseña actual no coincide
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
?>