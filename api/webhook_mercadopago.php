<?php
require_once "../config/config.php";
require_once "../config/database.php";
require_once "../config/mailer.php";

// Webhook de Mercado Pago para confirmar pagos de forma segura
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['type']) && $data['type'] == 'payment') {
    $payment_id = $data['data']['id'];
    
    // Consultar el estado del pago al API de Mercado Pago
    $ch = curl_init("https://api.mercadopago.com/v1/payments/" . $payment_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . MP_ACCESS_TOKEN
    ]);
    
    $response = curl_exec($ch);
    $payment_info = json_decode($response, true);
    curl_close($ch);
    
    if ($payment_info['status'] == 'approved') {
        $reserva_id = $payment_info['external_reference'];
        
        try {
            $database = new Database();
            $db = $database->getConnection();
            
            // Obtener detalles para la factura
            $stmtRes = $db->prepare("
                SELECT r.*, h.nombre as room_label 
                FROM reservas r 
                JOIN habitaciones h ON r.habitacion_id = h.id 
                WHERE r.id = ?
            ");
            $stmtRes->execute([$reserva_id]);
            $resData = $stmtRes->fetch(PDO::FETCH_ASSOC);

            if ($resData) {
                // Actualizar estado
                $stmt = $db->prepare("UPDATE reservas SET estado = 'confirmada' WHERE id = ?");
                $stmt->execute([$reserva_id]);
                
                // Enviar Factura por Correo
                $detalle = [
                    'room_label' => $resData['room_label'],
                    'checkin'    => $resData['fecha_entrada'],
                    'checkout'   => $resData['fecha_salida'],
                    'total'      => $resData['total'],
                    'currency'   => $resData['moneda'],
                    'nights'     => (strtotime($resData['fecha_salida']) - strtotime($resData['fecha_entrada'])) / 86400
                ];
                enviarFacturaReserva($resData['email_cliente'], $resData['nombre_cliente'], $reserva_id, $detalle);
            }
            
            http_response_code(200);
            echo "Reserva confirmada y correo enviado";
        } catch (Exception $e) {
            http_response_code(500);
            echo "Error DB";
        }
    }
} else {
    http_response_code(200); // Responder OK para otros eventos
    echo "Evento ignorado";
}
