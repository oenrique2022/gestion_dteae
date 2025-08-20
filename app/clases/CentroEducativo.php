<?php
require_once __DIR__ . '/Database.php';

class CentroEducativo {
    private $conn;
    private $table_name = "centros_educativos";

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    public function leerTodos() {
        try {
            // Limitamos a 500 para no sobrecargar el navegador, puedes ajustar o implementar un buscador
            $query = "SELECT centro_id, nombre_ce,codigo_infraestructura FROM " . $this->table_name . " ORDER BY nombre_ce";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>