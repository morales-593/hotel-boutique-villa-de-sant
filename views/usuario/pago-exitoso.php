<?php
require_once "../../config/config.php";
require_once "../../config/database.php";

$payment_id = $_GET['payment_id'] ?? null;
$status     = $_GET['status'] ?? null;
$external_ref = $_GET['external_reference'] ?? null;

// En un entorno real, aquí validaríamos con el API de Mercado Pago antes de confirmar
// Pero para este demo, si llega aquí con status=approved, actualizamos la reserva

if ($status === 'approved' && $external_ref) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $stmt = $db->prepare("UPDATE reservas SET estado = 'confirmada' WHERE id = ?");
        $stmt->execute([$external_ref]);
    } catch (Exception $e) {
        // Log error
    }
}

$pageTitle = "¡Pago Exitoso! | Hotel Boutique Villa de Sant";
$extraCSS = '
<style>
    .success-container {
        max-width: 800px;
        margin: 100px auto;
        padding: 60px 40px;
        background: rgba(12,16,12,0.9);
        border: 1px solid var(--primary-gold);
        border-radius: 20px;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    }
    .success-icon {
        font-size: 5rem;
        color: #2ecc71;
        margin-bottom: 30px;
        filter: drop-shadow(0 0 15px rgba(46,204,113,0.4));
    }
    .success-title {
        font-family: var(--font-serif);
        font-size: 2.5rem;
        color: var(--primary-gold);
        margin-bottom: 20px;
    }
    .success-text {
        font-size: 1.1rem;
        color: var(--text-white);
        line-height: 1.6;
        margin-bottom: 40px;
    }
    .booking-details {
        background: rgba(255,255,255,0.05);
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 40px;
        text-align: left;
    }
    .detail-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid rgba(212,175,55,0.1);
    }
    .detail-label { color: var(--text-gray); font-size: 0.9rem; }
    .detail-val { color: var(--primary-gold); font-weight: 600; }
    
    .btn-home {
        display: inline-block;
        padding: 15px 40px;
        background: var(--primary-gold);
        color: #000;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        transition: all 0.3s;
    }
    .btn-home:hover {
        background: #c09b2a;
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(212,175,55,0.3);
    }
</style>
';

include_once "../layouts/header.php";
?>

<main style="padding: 20px;">
    <div class="success-container scroll-anim">
        <div class="success-icon">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h1 class="success-title">¡Reserva Confirmada!</h1>
        <p class="success-text">
            Gracias por elegir Hotel Boutique Villa de Sant. Tu pago ha sido procesado exitosamente con Visa/MasterCard y tu habitación ha sido reservada en USD.
        </p>

        <div class="booking-details">
            <div class="detail-row">
                <span class="detail-label">ID de Operación</span>
                <span class="detail-val">#<?php echo htmlspecialchars($payment_id); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ID de Reserva</span>
                <span class="detail-val">#<?php echo htmlspecialchars($external_ref); ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Estado del Pago</span>
                <span class="detail-val">Aprobado</span>
            </div>
        </div>

        <p class="success-text" style="font-size: 0.9rem; color: var(--text-gray);">
            Hemos enviado un correo electrónico con los detalles de tu estancia. Si tienes alguna duda, puedes contactarnos por WhatsApp.
        </p>

        <a href="<?php echo BASE_URL; ?>" class="btn-home">Volver al Inicio</a>
    </div>
</main>

<?php include_once "../layouts/footer.php"; ?>
