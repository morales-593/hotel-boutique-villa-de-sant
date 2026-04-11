<?php
require_once "../config/database.php";
require_once "../models/Coupon.php";
require_once "../config/session.php";
checkLogin();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $coupon = new Coupon($db);

    $codigo = $_POST['codigo'];
    $descuento = $_POST['descuento'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    if ($coupon->create($codigo, $descuento, $fecha_inicio, $fecha_fin)) {
        header("Location: ../views/admin/cupones.php?success=1");
    } else {
        header("Location: ../views/admin/cupones.php?error=1");
    }
}
?>
