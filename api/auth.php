<?php
require_once "../config/database.php";
require_once "../models/User.php";
require_once "../config/session.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    $email = $_POST['email'];
    $password = $_POST['password'];

    Session::init();
    $userData = $user->login($email, $password);

    if ($userData) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['nombre'] = $userData['nombre'];
        $_SESSION['role'] = $userData['role'];
        header("Location: ../index.php?action=dashboard");
    } else {
        header("Location: ../index.php?action=login&error=1");
    }
}
?>
