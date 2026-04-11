<?php
class Coupon {
    private $conn;
    private $table_name = "cupones";

    public $id;
    public $codigo;
    public $descuento;
    public $fecha_inicio;
    public $fecha_fin;
    public $activo;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create($codigo, $descuento, $fecha_inicio, $fecha_fin) {
        $query = "INSERT INTO " . $this->table_name . " (codigo, descuento, fecha_inicio, fecha_fin) VALUES (:codigo, :descuento, :fecha_inicio, :fecha_fin)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->bindParam(":descuento", $descuento);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        return $stmt->execute();
    }

    public function update($id, $codigo, $fecha_inicio, $fecha_fin, $activo) {
        $query = "UPDATE " . $this->table_name . " SET codigo = :codigo, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, activo = :activo WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":codigo", $codigo);
        $stmt->bindParam(":fecha_inicio", $fecha_inicio);
        $stmt->bindParam(":fecha_fin", $fecha_fin);
        $stmt->bindParam(":activo", $activo);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
?>
