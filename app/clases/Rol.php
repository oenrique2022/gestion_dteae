<?php
require_once __DIR__ . '/Database.php';

class Rol {
    private $conn;
    private $table_name = "roles";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function leerTodos() {
        $stmt = $this->conn->prepare("SELECT * FROM " . $this->table_name . " ORDER BY nombre_rol");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>