<?php require_once __DIR__ . "/../../config/config.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Villa de Sant</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin.css">
</head>
    <!-- Mobile Nav Toggle -->
    <button class="mobile-toggle" id="adminMobileToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="sidebar" id="adminSidebar">
        <h2>Villa de Sant</h2>
        <a href="<?php echo BASE_URL; ?>?action=dashboard" class="nav-link <?php echo $action == 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="<?php echo BASE_URL; ?>?action=admin-habitaciones" class="nav-link <?php echo $action == 'admin-habitaciones' ? 'active' : ''; ?>">
            <i class="fas fa-bed"></i> Habitaciones
        </a>
        <a href="<?php echo BASE_URL; ?>?action=admin-reservas" class="nav-link <?php echo $action == 'admin-reservas' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-check"></i> Reservas
        </a>
        <a href="<?php echo BASE_URL; ?>?action=admin-cupones" class="nav-link <?php echo $action == 'admin-cupones' ? 'active' : ''; ?>">
            <i class="fas fa-ticket-alt"></i> Cupones
        </a>
        
        <div style="margin-top: auto;">
            <div style="padding: 1rem; color: var(--text-muted); font-size: 0.8rem; border-top: 1px solid var(--gold-border); margin-bottom: 1rem;">
                Sesión iniciada como:<br>
                <strong style="color: var(--primary);"><?php echo $_SESSION['nombre']; ?></strong>
            </div>
            <a href="<?php echo BASE_URL; ?>?action=logout" class="nav-link" style="color: #e74c3c;">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
        </div>
    </div>
    <div class="main-content">
