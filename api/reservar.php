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
    $nombre           = trim($input['nombre'] ?? '');
    $email            = trim($input['email'] ?? '');
    $telefono         = trim($input['telefono'] ?? '');
    $idioma           = $input['idioma'] ?? 'es';
    $habitacion_id    = intval($input['habitacion_id'] ?? 0);
    $checkin          = $input['checkin'] ?? '';
    $checkout         = $input['checkout'] ?? '';
    $huespedes        = intval($input['huespedes'] ?? 1);
    $notas            = trim($input['notas'] ?? '');
    $cupon            = strtoupper(trim($input['cupon'] ?? ''));
    $descuento        = intval($input['descuento'] ?? 0);
    $total            = floatval($input['total'] ?? 0);
    $currency         = strtoupper(trim($input['currency'] ?? 'USD'));
    $nights           = intval($input['nights'] ?? 1);
    $room_label       = $input['room_label'] ?? 'Habitación';
    $extra_transporte = $input['extra_transporte'] ?? false;
    $extra_tour       = $input['extra_tour'] ?? false;

    if (!$nombre || !$email || !$habitacion_id || !$checkin || !$checkout) {
        throw new Exception('Faltan campos obligatorios para procesar la reserva.');
    }

    $database = new Database();
    $db = $database->getConnection();

    // Guardar reserva
    $stmt = $db->prepare("
        INSERT INTO reservas (
            habitacion_id, nombre_cliente, email_cliente, telefono_cliente, idioma,
            fecha_entrada, fecha_salida, num_huespedes, total, moneda, cupon_codigo, 
            descuento_aplicado, notas, estado
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
    ");

    $stmt->execute([
        $habitacion_id, $nombre, $email, $telefono, $idioma,
        $checkin, $checkout, $huespedes, $total, $currency,
        $cupon ?: null, $descuento, $notas ?: null
    ]);

    $reservaId = $db->lastInsertId();

    // Marcar habitación como ocupada (temporalmente)
    $db->prepare("UPDATE habitaciones SET estado = 'ocupado' WHERE id = ?")->execute([$habitacion_id]);

    // ===== CREAR PREFERENCIA MERCADO PAGO =====
    $mp_items = [
        [
            "title"       => "Reserva: " . $room_label,
            "description" => "Estancia del $checkin al $checkout",
            "quantity"    => 1,
            "currency_id" => $currency,
            "unit_price"  => floatval($total)
        ]
    ];

    $mp_data = [
        "items" => $mp_items,
        "payer" => [
            "name"    => $nombre,
            "email"   => $email,
            "phone"   => ["number" => $telefono]
        ],
        "back_urls" => [
            "success" => MP_SUCCESS_URL,
            "failure" => MP_FAILURE_URL,
            "pending" => MP_PENDING_URL
        ],
        "auto_return"        => "approved",
        "external_reference" => (string)$reservaId,
        "notification_url"   => BASE_URL . "api/webhook_mercadopago.php",
    ];

    $ch = curl_init("https://api.mercadopago.com/checkout/preferences");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mp_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . MP_ACCESS_TOKEN,
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $preference = json_decode($response, true);
    curl_close($ch);

    $preference_id = $preference['id'] ?? null;
    $init_point    = $preference['init_point'] ?? null;

    // ===== RESPUESTA FINAL =====
    echo json_encode([
        'success'         => true,
        'reserva_id'      => $reservaId,
        'preference_id'   => $preference_id,
        'init_point'      => $init_point
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
