<?php
// Fix UTF-8 encoding for experience data
define('BASE_PATH', __DIR__);
require_once 'config/database.php';

$db = (new Database())->getConnection();
$db->exec("SET NAMES utf8mb4");

// Fix Transporte section
$stmt = $db->prepare("UPDATE experiencias SET 
    descripcion = ?,
    lista = ?
    WHERE seccion = 'transporte'");
$stmt->execute([
    'Inicie su estancia con la tranquilidad y el confort que merece. Brindamos un servicio de traslado privado desde y hacia el Aeropuerto Internacional Mariscal Sucre en vehículos de alta gama, conducidos por personal profesional.',
    json_encode([
        ['icono' => 'fa-solid fa-check', 'texto' => 'Monitoreo de vuelos en tiempo real.'],
        ['icono' => 'fa-solid fa-check', 'texto' => 'Asistencia con el equipaje y bienvenida personalizada.'],
        ['icono' => 'fa-solid fa-check', 'texto' => 'Vehículos climatizados con WiFi y agua de cortesía.']
    ], JSON_UNESCAPED_UNICODE)
]);

// Fix Tours section
$stmt = $db->prepare("UPDATE experiencias SET 
    descripcion = ?,
    lista = ?
    WHERE seccion = 'tours'");
$stmt->execute([
    'Déjenos guiarle por los rincones más mágicos de la ciudad y sus alrededores. Desde la majestuosidad del Centro Histórico hasta la aventura en el Teleférico o la Mitad del Mundo, organizamos experiencias a su medida.',
    json_encode([
        ['icono' => 'fa-solid fa-map-location-dot', 'texto' => 'Tours privados con guías certificados.'],
        ['icono' => 'fa-solid fa-mountain-city', 'texto' => 'Visitas a museos, iglesias y miradores icónicos.'],
        ['icono' => 'fa-solid fa-car-side', 'texto' => 'Excursiones de un día a Otavalo, Cotopaxi o Mindo.']
    ], JSON_UNESCAPED_UNICODE)
]);

echo "UTF-8 encoding fixed successfully!\n";

// Verify
$stmt = $db->query("SELECT seccion, descripcion FROM experiencias");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['seccion'] . ": " . substr($row['descripcion'], 0, 80) . "...\n";
}
?>
