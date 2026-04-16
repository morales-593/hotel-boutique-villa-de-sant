<?php
// Define physical path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Define URL path
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_name = $_SERVER['SCRIPT_NAME'] ?? '';
$base_dir = str_replace(basename($script_name), '', $script_name);
$base_dir = str_replace(['views/usuario/', 'views/admin/', 'api/'], '', $base_dir);

if (!defined('BASE_URL')) {
    define('BASE_URL', $protocol . "://" . $host . rtrim($base_dir, '/') . '/');
}

// Mercado Pago Configuration (Test Credentials - Replace with your own)
define('MP_PUBLIC_KEY', 'APP_USR-786d79a2-5813-431d-9e67-0e9ffca15a13'); // TEST PUBLIC KEY
define('MP_ACCESS_TOKEN', 'APP_USR-6842813583277051-041712-4f3b14567890abcdef12345'); // TEST ACCESS TOKEN

// Redirect URLs
define('MP_SUCCESS_URL', BASE_URL . 'views/usuario/pago-exitoso.php');
define('MP_FAILURE_URL', BASE_URL . 'views/usuario/reserva.php?status=error');
define('MP_PENDING_URL', BASE_URL . 'views/usuario/reserva.php?status=pending');

/**
 * Función auxiliar para prevenir ataques XSS al imprimir datos en HTML
 */
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}
