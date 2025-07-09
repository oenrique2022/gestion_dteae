<?php
//Conectar a la base de datos
class Database{
    private $host = '203.161.62.79';
    private $dbname = 'desarrollo_dteae_administracion';
    private $username = 'dtae';
    private $password = 'dtae2025@';
    protected $conn;
    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->dbname}", $this->username, $this->password);
            $this->conn->exec("SET NAMES 'utf8mb4'");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}