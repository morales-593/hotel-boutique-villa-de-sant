<?php
$current_action = $_GET['action'] ?? 'home';
?>
<header id="main-header">
    <div class="nav-container">
        <a href="<?php echo BASE_URL; ?>" class="logo">
            <!-- Hummingbird SVG Logo -->
            <svg class="nav-logo-icon" viewBox="0 0 24 24" width="32" height="32" fill="currentColor" style="color: var(--primary-gold);">
                <path d="M21.5 12.3c-.6-.2-1.3-.4-2-.5.5-.3.9-.7 1.1-1.3-.5.3-1 .5-1.6.6-.4-.5-1.1-.8-1.7-.8-1.3 0-2.4 1.1-2.4 2.4 0 .2 0 .4.1.6-2 0-3.7-1-4.8-2.5 0 0-.1.2-.1.4 0 .8.4 1.6 1 2-.4 0-.8-.1-1.1-.3 0 1.2.8 2.1 2 2.3-.2.1-.4.1-.7.1 0 0-.1 0-.2 0 .3 1 1.2 1.7 2.3 1.7-.8.7-1.9 1.1-3.1 1.1 0 0-.2 0-.3 0 1.1.7 2.4 1.1 3.9 1.1 4.7 0 7.3-3.9 7.3-7.3v-.3c.5-.4.9-.8 1.2-1.3z" fill="currentColor"/>
            </svg>
            <span class="logo-text">VILLA DE SANT</span>
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
