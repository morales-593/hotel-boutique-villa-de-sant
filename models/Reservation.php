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

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre_cliente, email_cliente, telefono_cliente, habitacion_id, num_huespedes, fecha_entrada, fecha_salida, total, estado) 
                  VALUES (:nombre, :email, :telefono, :habitacion_id, :huespedes, :fecha_inicio, :fecha_fin, :total, :estado)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ":nombre" => $data['nombre_cliente'],
            ":email" => $data['email_cliente'],
            ":telefono" => $data['telefono_cliente'],
            ":habitacion_id" => $data['habitacion_id'],
            ":huespedes" => $data['num_huespedes'],
            ":fecha_inicio" => $data['fecha_entrada'],
            ":fecha_fin" => $data['fecha_salida'],
            ":total" => $data['total'],
            ":estado" => $data['estado'] ?? 'pendiente'
        ]);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([$id]);

        if ($result) {
            // Re-secuenciar IDs
            $this->conn->query("SET @count = 0;");
            $this->conn->query("UPDATE " . $this->table_name . " SET id = (@count := @count + 1);");
            
            // Resetear AUTO_INCREMENT
            $res = $this->conn->query("SELECT MAX(id) as max_id FROM " . $this->table_name);
            $row = $res->fetch(PDO::FETCH_ASSOC);
            $next_id = ($row['max_id'] ?? 0) + 1;
            $this->conn->query("ALTER TABLE " . $this->table_name . " AUTO_INCREMENT = $next_id;");
        }
        return $result;
    }
}
?>
