<?php
require_once "config/session.php";
require_once "config/database.php";
checkLogin();

$database = new Database();
$db = $database->getConnection();

// Quick Stats
$stmtRooms = $db->query("SELECT COUNT(*) FROM habitaciones");
$totalRooms = $stmtRooms->fetchColumn();

$stmtRes = $db->query("SELECT COUNT(*) FROM reservas");
$totalReservations = $stmtRes->fetchColumn();

$stmtAvail = $db->query("SELECT COUNT(*) FROM habitaciones WHERE estado = 'disponible'");
$availRooms = $stmtAvail->fetchColumn();

include_once "views/layouts/admin_header.php";
?>

<div class="admin-container" style="color: white; padding: 20px;">
    <div class="glass-card" style="margin-bottom: 2rem;">
        <h1 style="color: var(--primary); font-family: 'Playfair Display', serif; margin: 0;">Panel de Control</h1>
        <p style="color: var(--text-muted); margin: 0;">Hola, <?php echo $_SESSION['nombre']; ?>. Aquí tienes un resumen del estado del hotel.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 350px), 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="glass-card" style="padding: 1.5rem; border-color: rgba(197, 160, 89, 0.2);">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Habitaciones Totales</div>
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary);"><?php echo $totalRooms; ?></div>
        </div>
        <div class="glass-card" style="padding: 1.5rem; border-color: rgba(46, 204, 113, 0.2);">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Disponibles</div>
            <div style="font-size: 2.5rem; font-weight: 700; color: #2ecc71;"><?php echo $availRooms; ?></div>
        </div>
        <div class="glass-card" style="padding: 1.5rem; border-color: rgba(197, 160, 89, 0.2);">
            <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--text-muted);">Reservas Hechas</div>
            <div style="font-size: 2.5rem; font-weight: 700; color: var(--primary);"><?php echo $totalReservations; ?></div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">
        <div class="glass-card" style="padding: 2rem;">
            <h3 class="serif gold-text">Acceso Rápido</h3>
            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 1rem;">
                <a href="index.php?action=admin-habitaciones" class="btn-gold"><i class="fas fa-bed"></i> Gestionar Habitaciones</a>
                <a href="index.php?action=admin-reservas" class="btn-gold"><i class="fas fa-calendar-alt"></i> Ver Reservas Recientes</a>
                <a href="index.php?action=admin-cupones" class="btn-gold"><i class="fas fa-ticket-alt"></i> Configurar Cupones</a>
            </div>
        </div>
        
        <div class="glass-card" style="padding: 2rem;">
            <h3 class="serif gold-text">Estado del Sistema</h3>
            <div style="margin-top: 1rem;">
                <p style="margin-bottom: 0.8rem;"><i class="fas fa-check-circle" style="color: #2ecc71;"></i> Base de Datos: <span style="color: white; font-weight: 600;">Conectada</span></p>
                <p style="margin-bottom: 0.8rem;"><i class="fas fa-check-circle" style="color: #2ecc71;"></i> Modo: <span style="color: white; font-weight: 600;">Producción</span></p>
                <p><i class="fas fa-clock" style="color: var(--primary);"></i> Hora Servidor: <span style="color: white; font-weight: 600;"><?php echo date('H:i'); ?></span></p>
            </div>
        </div>
    </div>
</div>

<?php include_once "views/layouts/admin_footer.php"; ?>
