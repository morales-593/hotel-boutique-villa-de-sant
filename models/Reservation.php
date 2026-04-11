<?php
class Reservation {
    private $conn;
    private $table_name = "reservas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT r.*, h.nombre as habitacion_nombre 
                  FROM " . $this->table_name . " r 
                  LEFT JOIN habitaciones h ON r.habitacion_id = h.id 
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function updateStatus($id, $estado) {
        $query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>
