<?php
define('BASE_PATH', __DIR__);
require_once 'config/database.php';
$db = (new Database())->getConnection();
try {
    $db->exec('ALTER TABLE reservas ADD COLUMN dni VARCHAR(50) DEFAULT NULL COMMENT "Cédula o DNI del cliente" AFTER nombre_cliente');
    echo "Columna dni agregada\n";
} catch(Exception $e) {
    echo "Error agregando dni: " . $e->getMessage() . "\n";
}
$r = $db->query('DESCRIBE reservas');
foreach($r->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . ' - ' . $col['Type'] . "\n";
}
