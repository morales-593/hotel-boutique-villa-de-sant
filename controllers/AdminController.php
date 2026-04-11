<?php
require_once 'config/session.php';

class AdminController {
    public function __construct() {
        if (!Session::isLoggedIn()) {
            header("Location: index.php?action=login");
            exit();
        }
    }

    public function dashboard() {
        require_once 'views/admin/dashboard.php';
    }

    public function habitaciones() {
        require_once 'views/admin/habitaciones.php';
    }

    public function reservas() {
        require_once 'views/admin/reservas.php';
    }

    public function cupones() {
        require_once 'views/admin/cupones.php';
    }
}
?>
