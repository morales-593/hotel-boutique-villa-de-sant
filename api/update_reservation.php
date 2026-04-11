<?php
require_once "../config/database.php";
require_once "../models/Reservation.php";
require_once "../config/session.php";
checkLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $res = new Reservation($db);

    $id = $_POST['id'];
    $estado = $_POST['estado'];

    if ($res->updateStatus($id, $estado)) {
        header("Location: ../views/admin/reservas.php?success=1");
    } else {
        header("Location: ../views/admin/reservas.php?error=1");
    }
}
?>
