<?php
require_once __DIR__ . '/Database.php';

class CentroEducativo {
    private $conn;
    private $table_name = 'centros_educativos';

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Listado reducido para selects en contratos (centro_id, nombre, código).
     */
    public function leerTodos() {
        try {
            $query = 'SELECT centro_id, nombre_ce, codigo_infraestructura FROM ' . $this->table_name . ' ORDER BY nombre_ce';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarTodos() {
        try {
            $query = 'SELECT * FROM ' . $this->table_name . ' ORDER BY nombre_ce';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Total de filas (opcionalmente filtrado por texto en varios campos).
     */
    public function contarTodos(?string $busqueda = null) {
        try {
            if ($busqueda === null || trim($busqueda) === '') {
                $stmt = $this->conn->query('SELECT COUNT(*) FROM ' . $this->table_name);
                return (int) $stmt->fetchColumn();
            }
            $pat = '%' . trim($busqueda) . '%';
            $sql = 'SELECT COUNT(*) FROM ' . $this->table_name
                . ' WHERE nombre_ce LIKE :b OR departamento LIKE :b2 OR municipio LIKE :b3 OR director_actual LIKE :b4'
                . ' OR CAST(codigo_infraestructura AS CHAR) LIKE :b5';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':b' => $pat, ':b2' => $pat, ':b3' => $pat, ':b4' => $pat, ':b5' => $pat]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Listado paginado para la grilla (no cargar miles de registros en un solo JSON).
     */
    public function listarPagina(int $offset, int $limit, ?string $busqueda = null) {
        $offset = max(0, $offset);
        $limit = min(200, max(1, $limit));
        try {
            if ($busqueda === null || trim($busqueda) === '') {
                $sql = 'SELECT * FROM ' . $this->table_name
                    . ' ORDER BY nombre_ce LIMIT ' . $limit . ' OFFSET ' . $offset;
                $stmt = $this->conn->query($sql);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            $pat = '%' . trim($busqueda) . '%';
            $sql = 'SELECT * FROM ' . $this->table_name
                . ' WHERE nombre_ce LIKE :b OR departamento LIKE :b2 OR municipio LIKE :b3 OR director_actual LIKE :b4'
                . ' OR CAST(codigo_infraestructura AS CHAR) LIKE :b5'
                . ' ORDER BY nombre_ce LIMIT ' . $limit . ' OFFSET ' . $offset;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':b' => $pat, ':b2' => $pat, ':b3' => $pat, ':b4' => $pat, ':b5' => $pat]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function leerUno($id) {
        try {
            $query = 'SELECT * FROM ' . $this->table_name . ' WHERE centro_id = :id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }

    public function listarDepartamentosDistintos() {
        try {
            $query = 'SELECT DISTINCT departamento_id, departamento FROM ' . $this->table_name
                . ' WHERE departamento_id IS NOT NULL ORDER BY departamento';
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function listarMunicipiosPorDepartamento($departamentoId) {
        try {
            $query = 'SELECT DISTINCT municipio_id, municipio FROM ' . $this->table_name
                . ' WHERE departamento_id = :dep_id AND municipio_id IS NOT NULL ORDER BY municipio';
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':dep_id', $departamentoId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function crear(
        $nombreCe,
        $departamentoId,
        $departamento,
        $municipioId,
        $municipio,
        $codigoInfraestructura,
        $directorActual
    ) {
        try {
            $query = 'INSERT INTO ' . $this->table_name
                . ' (nombre_ce, departamento_id, departamento, municipio_id, municipio, codigo_infraestructura, director_actual)'
                . ' VALUES (:nombre_ce, :dep_id, :dep, :mun_id, :mun, :codigo, :director)';
            $stmt = $this->conn->prepare($query);
            $this->bindCatalogo($stmt, $nombreCe, $departamentoId, $departamento, $municipioId, $municipio, $codigoInfraestructura, $directorActual);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizar(
        $id,
        $nombreCe,
        $departamentoId,
        $departamento,
        $municipioId,
        $municipio,
        $codigoInfraestructura,
        $directorActual
    ) {
        try {
            $query = 'UPDATE ' . $this->table_name . ' SET nombre_ce = :nombre_ce, departamento_id = :dep_id,'
                . ' departamento = :dep, municipio_id = :mun_id, municipio = :mun,'
                . ' codigo_infraestructura = :codigo, director_actual = :director WHERE centro_id = :id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $this->bindCatalogo($stmt, $nombreCe, $departamentoId, $departamento, $municipioId, $municipio, $codigoInfraestructura, $directorActual);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminar($id) {
        try {
            $query = 'DELETE FROM ' . $this->table_name . ' WHERE centro_id = :id';
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    private function bindCatalogo(
        \PDOStatement $stmt,
        $nombreCe,
        $departamentoId,
        $departamento,
        $municipioId,
        $municipio,
        $codigoInfraestructura,
        $directorActual
    ) {
        $stmt->bindValue(':nombre_ce', $nombreCe !== '' ? $nombreCe : null);
        if ($departamentoId === '' || $departamentoId === null) {
            $stmt->bindValue(':dep_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':dep_id', (int) $departamentoId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':dep', $departamento !== '' ? $departamento : null);
        if ($municipioId === '' || $municipioId === null) {
            $stmt->bindValue(':mun_id', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':mun_id', (int) $municipioId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':mun', $municipio !== '' ? $municipio : null);
        if ($codigoInfraestructura === '' || $codigoInfraestructura === null) {
            $stmt->bindValue(':codigo', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':codigo', (int) $codigoInfraestructura, PDO::PARAM_INT);
        }
        $stmt->bindValue(':director', $directorActual !== '' ? $directorActual : null);
    }
}
