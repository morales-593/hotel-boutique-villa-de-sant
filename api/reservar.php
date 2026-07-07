<?php
define('BASE_PATH', dirname(__DIR__));
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido.');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Datos de reserva inválidos.');
    }

    // Sanitización
    $nombre        = trim($input['nombre'] ?? '');
    $dni           = trim($input['dni'] ?? '');
    $email         = ''; // Ya no se pide correo
    $telefono      = trim($input['telefono'] ?? '');
    $idioma        = $input['idioma'] ?? 'es';
    $habitacion_id = intval($input['habitacion_id'] ?? 0);
    $checkin       = $input['checkin'] ?? '';
    $checkout      = $input['checkout'] ?? '';
    $adultos       = intval($input['adultos'] ?? 1);
    $ninos         = intval($input['ninos'] ?? 0);
    $huespedes     = $adultos + $ninos;
    $notas         = trim($input['notas'] ?? '');
    $total         = floatval($input['total'] ?? 0);
    $nights        = intval($input['nights'] ?? 1);
    $room_label    = $input['room_label'] ?? 'Habitación';
    $parqueadero   = intval($input['parqueadero'] ?? 0);

    if (!$nombre || !$dni || !$habitacion_id || !$checkin || !$checkout) {
        throw new Exception('Faltan campos obligatorios (incluyendo DNI/Cédula) para procesar la reserva.');
    }

    $database = new Database();
    $db = $database->getConnection();

    // Guardar reserva en la base de datos
    $stmt = $db->prepare("
        INSERT INTO reservas (
            habitacion_id, nombre_cliente, dni, email_cliente, telefono_cliente, idioma,
            fecha_entrada, fecha_salida, num_huespedes, adultos, ninos, total, moneda,
            notas, estado, parqueadero
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'USD', ?, 'pendiente', ?)
    ");

    $stmt->execute([
        $habitacion_id, $nombre, $dni, $email, $telefono, $idioma,
        $checkin, $checkout, $huespedes, $adultos, $ninos, $total,
        $notas ?: null, $parqueadero
    ]);

    $reservaId = $db->lastInsertId();

    // Marcar habitación como ocupada
    $db->prepare("UPDATE habitaciones SET estado = 'ocupado' WHERE id = ?")->execute([$habitacion_id]);

    echo json_encode([
        'success'    => true,
        'reserva_id' => $reservaId
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
