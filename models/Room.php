<?php
class Room {
    private $conn;
    private $table_name = "habitaciones";

    public $id;
    public $nombre;
    public $tipo;
    public $descripcion;
    public $precio;
    public $estado;
    public $imagen;
    public $caracteristicas;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $estado) {
        $query = "UPDATE " . $this->table_name . " SET estado = :estado WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function update($id, $nombre, $tipo, $descripcion, $precio, $estado, $imagen = null, $caracteristicas = null) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = :nombre, tipo = :tipo, descripcion = :descripcion,
                      precio = :precio, estado = :estado, imagen = :imagen,
                      caracteristicas = :caracteristicas
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":nombre", $nombre);
        $stmt->bindParam(":tipo", $tipo);
        $stmt->bindParam(":descripcion", $descripcion);
        $stmt->bindParam(":precio", $precio);
        $stmt->bindParam(":estado", $estado);
        $stmt->bindParam(":imagen", $imagen);
        $stmt->bindParam(":caracteristicas", $caracteristicas);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    /**
     * Parse the caracteristicas JSON field into a PHP array
     * Each entry is "fa-icon:Label"
     */
    public static function parseFeatures($json) {
        if (!$json) return [];
        $features = json_decode($json, true);
        if (!is_array($features)) return [];
        $result = [];
        foreach ($features as $f) {
            $parts = explode(':', $f, 2);
            $result[] = [
                'icon'  => $parts[0] ?? 'fa-circle',
                'label' => $parts[1] ?? $f
            ];
        }
        return $result;
    }
}
?>
