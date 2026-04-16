<?php
define('BASE_PATH', dirname(__DIR__));
require_once "../config/database.php";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $sql = "ALTER TABLE reservas ADD moneda VARCHAR(5) DEFAULT 'USD' AFTER total;";
    $db->exec($sql);
    
    echo "Migration successful: Column 'moneda' added to 'reservas' table.";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
