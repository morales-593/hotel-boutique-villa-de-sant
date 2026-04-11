<?php
require_once "../config/database.php";
require_once "../models/Room.php";
require_once "../config/session.php";
checkLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $room = new Room($db);

    $id = $_POST['id'];
    $estado = $_POST['estado'];

    if ($room->updateStatus($id, $estado)) {
        header("Location: ../views/admin/habitaciones.php?success=1");
    } else {
        header("Location: ../views/admin/habitaciones.php?error=1");
    }
}
?>
