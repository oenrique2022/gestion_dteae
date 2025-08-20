<?php
require_once __DIR__ . '/Database.php';

class TipoEquipo {
    private $conn;
    private $table_name = "tipos_equipos";

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
            return [];
        }
    }
}
?>