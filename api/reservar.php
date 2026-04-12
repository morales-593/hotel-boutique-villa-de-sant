<?php
define('BASE_PATH', dirname(__DIR__));
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit();
}

// Sanitize inputs
$nombre       = trim($input['nombre']       ?? '');
$email        = trim($input['email']        ?? '');
$telefono     = trim($input['telefono']     ?? '');
$habitacion_id= intval($input['habitacion_id'] ?? 0);
$checkin      = $input['checkin']  ?? '';
$checkout     = $input['checkout'] ?? '';
$huespedes    = intval($input['huespedes']  ?? 1);
$notas        = trim($input['notas']        ?? '');
$cupon        = strtoupper(trim($input['cupon'] ?? ''));
$descuento    = intval($input['descuento']  ?? 0);
$total        = floatval($input['total']    ?? 0);
$nights       = intval($input['nights']     ?? 1);
$room_label   = $input['room_label'] ?? 'Habitación';

// Basic validation
if (!$nombre || !$email || !$habitacion_id || !$checkin || !$checkout) {
    echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes.']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    // Save reservation
    $stmt = $db->prepare("
        INSERT INTO reservas (habitacion_id, nombre_cliente, email_cliente, telefono_cliente,
            fecha_entrada, fecha_salida, num_huespedes, total, cupon_codigo, descuento_aplicado, notas, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
    ");
    $stmt->execute([
        $habitacion_id, $nombre, $email, $telefono,
        $checkin, $checkout, $huespedes, $total,
        $cupon ?: null, $descuento, $notas ?: null
    ]);

    $reservaId = $db->lastInsertId();

    // Mark room as occupied
    $db->prepare("UPDATE habitaciones SET estado = 'ocupado' WHERE id = ?")
       ->execute([$habitacion_id]);

    // ===== BUILD WHATSAPP MESSAGE =====
    $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    $fi = $meses[intval(date('n', strtotime($checkin)))-1] . ' ' . date('d, Y', strtotime($checkin));
    $fo = $meses[intval(date('n', strtotime($checkout)))-1] . ' ' . date('d, Y', strtotime($checkout));

    $msg  = "🏨 *NUEVA RESERVA - Hotel Boutique Villa de Sant*\n";
    $msg .= "✨ ¡Gracias por elegirnos! ✨\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";

    $msg .= "👤 *Información Personal*\n";
    $msg .= "• *Huésped:* $nombre\n";
    $msg .= "• *Email:* $email\n";
    $msg .= "• *Teléfono:* $telefono\n\n";

    $msg .= "🛌 *Selecciona tu Habitación*\n";
    $msg .= "• *Habitación:* $room_label\n";
    $msg .= "• *Check-in:* $fi\n";
    $msg .= "• *Check-out:* $fo\n";
    $msg .= "• *Noches:* $nights\n";
    $msg .= "• *Huéspedes:* $huespedes\n";
    if ($cupon) {
        $msg .= "• *Cupón:* $cupon (-$descuento%)\n";
    }

    $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n";
    $msg .= "💰 *TOTAL: \$$total USD*\n";
    $msg .= "━━━━━━━━━━━━━━━━━━━━━━\n\n";

    if ($notas) $msg .= "✍️ *Notas:* $notas\n";
    $msg .= "📌 *Aviso:* toda reserva se guardara en el sistema por cualquier modifiicacion\n";
    $msg .= "\n🌐 _Reserva realizada desde villadesant.com_";

    echo json_encode([
        'success'        => true,
        'reserva_id'     => $reservaId,
        'whatsapp_msg'   => $msg,
        'whatsapp_number'=> '984606212', // Hotel Boutique Villa de Sant
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
