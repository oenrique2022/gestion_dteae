<?php
require_once __DIR__ . '/Database.php';

class FuenteFinanciamiento {
    private $conn;
    private $table_name = 'fuentes_financiamiento';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /** Para selects en formularios (solo activas). */
    public function leerActivos(): array {
        try {
            $query = 'SELECT id, nombre FROM ' . $this->table_name
                . ' WHERE activo = 1 ORDER BY nombre ASC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /** Listado completo para mantenimiento. */
    public function leerTodos(): array {
        try {
            $query = 'SELECT * FROM ' . $this->table_name . ' ORDER BY nombre ASC';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function leerUno(int $id): ?array {
        if ($id <= 0) {
            return null;
        }
        try {
            $stmt = $this->conn->prepare('SELECT * FROM ' . $this->table_name . ' WHERE id = :id');
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
     * @return int|false id insertado
     */
    public function crear(string $nombre, ?string $descripcion, int $activo = 1) {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return false;
        }
        $desc = $descripcion !== null && trim($descripcion) !== '' ? trim($descripcion) : null;
        $activo = $activo ? 1 : 0;
        try {
            $sql = 'INSERT INTO ' . $this->table_name . ' (nombre, descripcion, activo) VALUES (:nombre, :desc, :activo)';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':desc', $desc, $desc === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':activo', $activo, PDO::PARAM_INT);
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

    public function actualizar(int $id, string $nombre, ?string $descripcion, int $activo): bool {
        $nombre = trim($nombre);
        if ($nombre === '' || $id <= 0) {
            return false;
        }
        $desc = $descripcion !== null && trim($descripcion) !== '' ? trim($descripcion) : null;
        $activo = $activo ? 1 : 0;
        try {
            $sql = 'UPDATE ' . $this->table_name
                . ' SET nombre = :nombre, descripcion = :desc, activo = :activo WHERE id = :id';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':nombre', $nombre);
            $stmt->bindValue(':desc', $desc, $desc === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':activo', $activo, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function contarContratosPorFuente(int $id): int {
        if ($id <= 0) {
            return 0;
        }
        try {
            $stmt = $this->conn->prepare('SELECT COUNT(*) FROM contratos WHERE fuente_financiamiento_id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function eliminar(int $id): bool {
        if ($id <= 0) {
            return false;
        }
        try {
            $stmt = $this->conn->prepare('DELETE FROM ' . $this->table_name . ' WHERE id = :id');
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
