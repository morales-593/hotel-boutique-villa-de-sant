<?php
class PublicController {
    public function home() {
        require_once 'views/usuario/home.php';
    }

    public function nosotros() {
        require_once 'views/usuario/nosotros.php';
    }

    public function experiencias() {
        require_once 'views/usuario/experiencias.php';
    }

    public function habitaciones() {
        require_once 'views/usuario/habitaciones.php';
    }

    public function habitacion() {
        require_once 'views/usuario/habitacion-detalle.php';
    }

    public function reserva() {
        require_once 'views/usuario/reserva.php';
    }
}
?>
