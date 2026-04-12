<?php
class Session {
    /**
     * Start session if not already started
     */
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            // Configurar parámetros de cookie de sesión antes de iniciarla
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => isset($_SERVER['HTTPS']), // Solo si es HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn() {
        self::init();
        return isset($_SESSION['user_id']);
    }

    /**
     * Check if user is admin
     */
    public static function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    /**
     * Destroy session
     */
    public static function destroy() {
        self::init();
        session_unset();
        session_destroy();
    }
}

// Global functions for backward compatibility if needed
function checkLogin() {
    if (!Session::isLoggedIn()) {
        header("Location: index.php?action=login");
        exit();
    }
}
?>
