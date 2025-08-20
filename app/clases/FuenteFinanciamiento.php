<?php
require_once __DIR__ . '/Database.php';

class FuenteFinanciamiento {
    private $conn;
    private $table_name = "fuentes_financiamiento";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function leerTodos() {
        try {
            // Limitamos a 500 para no sobrecargar el navegador, puedes ajustar o implementar un buscador
            $query = "SELECT id, nombre FROM " . $this->table_name . " ORDER BY nombre";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>