<?php
require_once __DIR__ . '/Database.php';

class TipoEquipo {
    private $conn;
    private $table_name = 'tipos_equipos';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function leerTodos(): array {
        try {
            $query = 'SELECT * FROM ' . $this->table_name . ' ORDER BY nombre_tipo_equipo ASC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function leerUno($id): ?array {
        try {
            $stmt = $this->conn->prepare('SELECT * FROM ' . $this->table_name . ' WHERE id_tipo_equipo = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * @return int|false id_tipo_equipo insertado
     */
    public function crear(string $nombre, ?string $descripcion = null) {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return false;
        }
        $desc = $descripcion !== null && trim($descripcion) !== '' ? trim($descripcion) : null;
        try {
            $sql = 'INSERT INTO ' . $this->table_name
                . ' (nombre_tipo_equipo, descripcion, fecha_creacion, usuario_creador)'
                . ' VALUES (:nombre, :desc, NOW(), :usr)';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':desc', $desc, $desc === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':usr', 'System');
            if (!$stmt->execute()) {
                return false;
            }
            $id = (int) $this->conn->lastInsertId();
            return $id > 0 ? $id : false;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function actualizar(int $id, string $nombre, ?string $descripcion = null): bool {
        $nombre = trim($nombre);
        if ($nombre === '' || $id <= 0) {
            return false;
        }
        $desc = $descripcion !== null && trim($descripcion) !== '' ? trim($descripcion) : null;
        try {
            $sql = 'UPDATE ' . $this->table_name
                . ' SET nombre_tipo_equipo = :nombre, descripcion = :desc, fecha_modificacion = NOW()'
                . ' WHERE id_tipo_equipo = :id';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':desc', $desc, $desc === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function eliminar(int $id): bool {
        if ($id <= 0) {
            return false;
        }
        try {
            $stmt = $this->conn->prepare('DELETE FROM ' . $this->table_name . ' WHERE id_tipo_equipo = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /** Cuenta equipos que usan este tipo (para validar antes de borrar). */
    public function contarEquiposPorTipo(int $idTipo): int {
        if ($idTipo <= 0) {
            return 0;
        }
        try {
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM equipos WHERE id_tipo_equipo = :id');
            $stmt->bindValue(':id', $idTipo, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }
}