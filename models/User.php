<?php
class User {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre;
    public $email;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $query = "SELECT id, nombre, password, role FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    public function create($nombre, $email, $password, $role = 'admin') {
        try {
            $query = "INSERT INTO " . $this->table_name . " (nombre, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            return $stmt->execute([$nombre, $email, $hashed_password, $role]);
        } catch (PDOException $e) {
            // Error 23000 es duplicidad
            if ($e->getCode() == 23000) {
                return "duplicate";
            }
            throw $e;
        }
    }

    public function getAll() {
        $query = "SELECT id, nombre, email, role, created_at FROM " . $this->table_name . " ORDER BY created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($id, $nombre, $email, $role, $password = null) {
        try {
            if ($password) {
                $query = "UPDATE " . $this->table_name . " SET nombre = ?, email = ?, role = ?, password = ? WHERE id = ?";
                $stmt = $this->conn->prepare($query);
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                return $stmt->execute([$nombre, $email, $role, $hashed_password, $id]);
            } else {
                $query = "UPDATE " . $this->table_name . " SET nombre = ?, email = ?, role = ? WHERE id = ?";
                $stmt = $this->conn->prepare($query);
                return $stmt->execute([$nombre, $email, $role, $id]);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) return "duplicate";
            throw $e;
        }
    }

    public function delete($id) {
        // 1. Eliminar el usuario
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $result = $stmt->execute([$id]);

        if ($result) {
            // 2. Re-secuenciar los IDs para no dejar huecos
            $this->conn->query("SET @count = 0;");
            $this->conn->query("UPDATE " . $this->table_name . " SET id = (@count := @count + 1);");
            
            // 3. Resetear el valor de AUTO_INCREMENT
            $res = $this->conn->query("SELECT MAX(id) as max_id FROM " . $this->table_name);
            $row = $res->fetch(PDO::FETCH_ASSOC);
            $next_id = ($row['max_id'] ?? 0) + 1;
            $this->conn->query("ALTER TABLE " . $this->table_name . " AUTO_INCREMENT = $next_id;");
        }

        return $result;
    }
}
?>
