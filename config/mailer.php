<?php
/**
 * Función para enviar la factura/confirmación de reserva por correo
 */
function enviarFacturaReserva($email_cliente, $nombre_cliente, $reserva_id, $detalle_reserva) {
    $to = $email_cliente;
    $subject = "Factura de Reserva #" . $reserva_id . " - Hotel Boutique Villa de Sant";
    
    // Plantilla de factura profesional HTML
    $message = "
    <html>
    <head>
        <style>
            .invoice-box {
                max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.15); font-size: 16px; line-height: 24px;
                font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #555;
            }
            .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
            .invoice-box table td { padding: 5px; vertical-align: top; }
            .invoice-box table tr td:nth-child(2) { text-align: right; }
            .invoice-box table tr.top table td { padding-bottom: 20px; }
            .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: #c5a059; }
            .invoice-box table tr.information table td { padding-bottom: 40px; }
            .invoice-box table tr.heading td { background: #f9f9f9; border-bottom: 1px solid #ddd; font-weight: bold; }
            .invoice-box table tr.details td { padding-bottom: 20px; }
            .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
            .invoice-box table tr.item.last td { border-bottom: none; }
            .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #c5a059; font-weight: bold; font-size: 1.2rem; color: #c5a059; }
            .footer { margin-top: 40px; text-align: center; font-size: 0.8rem; color: #999; }
        </style>
    </head>
    <body>
        <div class='invoice-box'>
            <table cellpadding='0' cellspacing='0'>
                <tr class='top'>
                    <td colspan='2'>
                        <table>
                            <tr>
                                <td class='title'>Villa de Sant</td>
                                <td>
                                    Factura #: $reserva_id<br>
                                    Fecha: " . date('d/m/Y') . "<br>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class='information'>
                    <td colspan='2'>
                        <table>
                            <tr>
                                <td>
                                    <strong>Hotel Boutique Villa de Sant</strong><br>
                                    Quito, Ecuador<br>
                                    villadesant.com
                                </td>
                                <td>
                                    <strong>Huésped:</strong><br>
                                    $nombre_cliente<br>
                                    $email_cliente
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr class='heading'><td>Descripción</td><td>Costo</td></tr>
                <tr class='item'>
                    <td>Habitación: " . $detalle_reserva['room_label'] . " (" . $detalle_reserva['nights'] . " noches)</td>
                    <td>" . number_format($detalle_reserva['total'], 2) . " " . $detalle_reserva['currency'] . "</td>
                </tr>
                <tr class='item last'>
                    <td>Check-in: " . $detalle_reserva['checkin'] . " | Check-out: " . $detalle_reserva['checkout'] . "</td>
                    <td>-</td>
                </tr>
                <tr class='total'>
                    <td></td>
                    <td>TOTAL: " . number_format($detalle_reserva['total'], 2) . " " . $detalle_reserva['currency'] . "</td>
                </tr>
            </table>
            <div class='footer'>
                <p>Esta es una factura generada automáticamente. Gracias por su preferencia.</p>
                <p><strong>Hotel Boutique Villa de Sant - Experiencia Premium</strong></p>
            </div>
        </div>
    </body>
    </html>
    ";

    // Headers para HTML
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Hotel Villa de Sant <no-reply@villadesant.com>" . "\r\n";

    return mail($to, $subject, $message, $headers);
}
