<?php
$current_action = $_GET['action'] ?? 'home';
?>
<header id="main-header">
    <div class="nav-container">
        <a href="<?php echo BASE_URL; ?>" class="logo">
            <!-- Hotel Building Icon Logo -->
            <i class="fa-solid fa-hotel nav-logo-icon" style="font-size: 1.6rem; color: var(--primary-gold); filter: drop-shadow(0 0 5px rgba(212, 175, 55, 0.4));"></i>
            <span class="logo-text">HOTEL CENTRO</span>
        </a>
        <nav class="desktop-nav">
            <ul class="nav-links">
                <li><a href="<?php echo BASE_URL; ?>?action=home" class="nav-link <?php echo $current_action == 'home' ? 'active' : ''; ?>"><i class="fa-solid fa-house"></i> INICIO</a></li>
                <li><a href="<?php echo BASE_URL; ?>?action=habitaciones" class="nav-link <?php echo $current_action == 'habitaciones' ? 'active' : ''; ?>"><i class="fa-solid fa-bed"></i> HABITACIONES</a></li>
                <li><a href="<?php echo BASE_URL; ?>?action=experiencias" class="nav-link <?php echo $current_action == 'experiencias' ? 'active' : ''; ?>"><i class="fa-solid fa-compass"></i> EXPERIENCIAS</a></li>
                <li><a href="<?php echo BASE_URL; ?>?action=nosotros" class="nav-link <?php echo $current_action == 'nosotros' ? 'active' : ''; ?>"><i class="fa-solid fa-users"></i> NOSOTROS</a></li>
            </ul>
        </nav>

        <a href="<?php echo BASE_URL; ?>?action=reserva" class="header-book-btn">
            <i class="fa-solid fa-calendar-days"></i> 
            <span class="btn-text">RESERVAR</span>
        </a>

    </div>
    <div class="header-scroll-line"></div>
</header>

<!-- Mobile Bottom Tab Bar -->
<nav class="mobile-bottom-nav">
    <a href="<?php echo BASE_URL; ?>?action=home" class="mob-nav-item <?php echo $current_action == 'home' ? 'active' : ''; ?>">
        <i class="fa-solid fa-house"></i>
    </a>
    <a href="<?php echo BASE_URL; ?>?action=habitaciones" class="mob-nav-item <?php echo $current_action == 'habitaciones' ? 'active' : ''; ?>">
        <i class="fa-solid fa-bed"></i>
    </a>
    <a href="<?php echo BASE_URL; ?>?action=experiencias" class="mob-nav-item <?php echo $current_action == 'experiencias' ? 'active' : ''; ?>">
        <i class="fa-solid fa-compass"></i>
    </a>
    <a href="<?php echo BASE_URL; ?>?action=nosotros" class="mob-nav-item <?php echo $current_action == 'nosotros' ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i>
    </a>

</nav>
