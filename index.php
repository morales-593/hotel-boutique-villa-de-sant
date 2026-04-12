<?php
require_once 'config/config.php';
require_once 'config/session.php';
Session::init();

// Obtener la acción de la URL, por defecto la página principal
$action = $_GET['action'] ?? 'home';

// Rutas públicas de usuario (No requieren login)
$publicRoutesUser = ['home', 'nosotros', 'experiencias', 'habitaciones', 'habitacion', 'reserva'];

// Rutas de autenticación
$authRoutes = ['login', 'logout'];

// Enrutador Principal
switch ($action) {
    // === Vistas de Usuario ===
    case 'home':
    case 'nosotros':
    case 'experiencias':
    case 'habitaciones':
    case 'habitacion':
    case 'reserva':
        require_once 'controllers/PublicController.php';
        $controller = new PublicController();
        $controller->$action();
        break;

    // === Autenticación Admin ===
    case 'login':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->login();
        break;

    case 'logout':
        require_once 'controllers/AuthController.php';
        $controller = new AuthController();
        $controller->logout();
        break;

    // === Panel de Administración (Protegido por Sesión) ===
    case 'dashboard':
    case 'admin-habitaciones':
    case 'admin-reservas':
    case 'admin-cupones':
    case 'admin-usuarios':
        require_once 'controllers/AdminController.php';
        $controller = new AdminController();
        
        // Mapeo de acciones a métodos del controlador
        if ($action === 'dashboard') $controller->dashboard();
        if ($action === 'admin-habitaciones') $controller->habitaciones();
        if ($action === 'admin-reservas') $controller->reservas();
        if ($action === 'admin-cupones') $controller->cupones();
        if ($action === 'admin-usuarios') $controller->usuarios();
        break;

    // API endpoints (Opcional si usas Fetch/AJAX)
    case 'api-reservar':
        require_once 'api/reservar.php';
        break;

    // Por defecto redirigir a Home
    default:
        require_once 'controllers/PublicController.php';
        $controller = new PublicController();
        $controller->home();
        break;
}
?>
