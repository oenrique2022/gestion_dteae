
<?php
require_once 'Database.php';

class Instituciones extends Database
{
    public function listarInstituciones()
    {
        try {
            $sql = "SELECT centro_id, nombre_ce FROM centros_educativos";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}