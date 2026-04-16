<?php
if (!defined('BASE_PATH')) {
    header("HTTP/1.1 403 Forbidden");
    exit("Acceso directo no permitido.");
}

class Database {
    private $host = "localhost";
    private $db_name = "hotel_villa_de_sant";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // No hacer echo aquí, lanzar excepción para que la API la capture
            throw new Exception("Error de conexión a la base de datos.");
        }
        return $this->conn;
    }
}
