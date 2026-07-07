<?php
define('BASE_PATH', __DIR__);
require_once 'config/database.php';
$db = (new Database())->getConnection();
try {
    $db->exec('ALTER TABLE reservas ADD COLUMN camas_extra INT DEFAULT 0 COMMENT "Camas extra ($10 c/u)"');
    echo "camas_extra agregada\n";
} catch(Exception $e) {
    echo "camas_extra: " . $e->getMessage() . "\n";
}
try {
    $db->exec('ALTER TABLE reservas ADD COLUMN parqueadero TINYINT(1) DEFAULT 0 COMMENT "Parqueadero ($10)"');
    echo "parqueadero agregada\n";
} catch(Exception $e) {
    echo "parqueadero: " . $e->getMessage() . "\n";
}
$r = $db->query('DESCRIBE reservas');
foreach($r->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . ' - ' . $col['Type'] . "\n";
}
