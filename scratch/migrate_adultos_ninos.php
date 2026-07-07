<?php
define('BASE_PATH', __DIR__);
require_once 'config/database.php';
$db = (new Database())->getConnection();
try {
    $db->exec('ALTER TABLE reservas ADD COLUMN adultos INT DEFAULT 1 COMMENT "Número de adultos ($10/noche)"');
    echo "adultos agregada\n";
} catch(Exception $e) {
    echo "adultos: " . $e->getMessage() . "\n";
}
try {
    $db->exec('ALTER TABLE reservas ADD COLUMN ninos INT DEFAULT 0 COMMENT "Número de niños ($5/noche)"');
    echo "ninos agregada\n";
} catch(Exception $e) {
    echo "ninos: " . $e->getMessage() . "\n";
}
try {
    // Migrar datos existentes (asumimos que todos los huespedes eran adultos)
    $db->exec('UPDATE reservas SET adultos = num_huespedes WHERE num_huespedes IS NOT NULL AND adultos = 1');
    echo "datos migrados\n";
} catch(Exception $e) {
    echo "migracion: " . $e->getMessage() . "\n";
}
$r = $db->query('DESCRIBE reservas');
foreach($r->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . ' - ' . $col['Type'] . "\n";
}
