<?php
class AuthController {
    public function login() {
        require_once 'views/admin/login.php';
    }

    public function logout() {
        require_once 'config/session.php';
        Session::destroy();
        header("Location: index.php?action=login");
    }
}
?>
