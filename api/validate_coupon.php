<?php
define('BASE_PATH', dirname(__DIR__));
require_once '../config/database.php';
header('Content-Type: application/json');

$code = strtoupper(trim($_GET['code'] ?? ''));
$tipo = strtolower(trim($_GET['tipo'] ?? ''));

if (!$code) {
    echo json_encode(['valid' => false, 'message' => 'Código vacío.']);
    exit();
}

try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("
        SELECT * FROM cupones
        WHERE codigo = ?
          AND activo = 1
          AND fecha_inicio <= CURDATE()
          AND fecha_fin    >= CURDATE()
        LIMIT 1
    ");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        echo json_encode(['valid' => false, 'message' => 'Cupón no válido o expirado.']);
        exit();
    }

    // If coupon is type-specific, validate it matches the selected room type
    if ($coupon['habitacion_tipo'] && $tipo && $coupon['habitacion_tipo'] !== $tipo) {
        echo json_encode(['valid' => false, 'message' => 'Este cupón no aplica a este tipo de habitación.']);
        exit();
    }

    echo json_encode([
        'valid'    => true,
        'discount' => intval($coupon['descuento']),
        'code'     => $coupon['codigo'],
    ]);

} catch (Exception $e) {
    echo json_encode(['valid' => false, 'message' => 'Error del servidor.']);
}
?>
