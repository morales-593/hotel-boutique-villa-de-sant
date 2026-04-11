<?php
$pageTitle = "Suite Real | Hotel Boutique Villa de Sant";
$extraCSS = '<link rel="stylesheet" href="' . BASE_URL . 'assets/css/rooms.css">';
include_once "../layouts/header.php";
?>

<main>
    <!-- Room Detail Hero -->
    <section class="room-detail-hero" style="background-image: url('https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80')">
        <div class="room-detail-overlay"></div>
        <div class="container room-header-content text-center scroll-anim scroll-fade">
            <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Villa de Sant" class="hero-logo-glow" style="max-width: 50px;">
            <div style="width: 60px; height: 2px; background: var(--primary-gold); margin: 0 auto 20px;"></div>
            <h1 class="gold-text" style="font-size: 3rem; margin-bottom: 20px;">Nuestras Estancias</h1>
            <p class="hero-subtitle" style="font-size: 1.1rem; color: #EEE;">Descubre el equilibrio perfecto entre la historia colonial y el confort contemporáneo.</p>
        </div>
    </section>

    <section class="container room-info-grid section-padding">
        <div class="room-main-content scroll-anim scroll-left">
            <h2 class="serif gold-text">Elegancia y Confort</h2>
            <p style="margin-top: 20px; font-size: 1.1rem; color: var(--text-gray);">
                Nuestras suites combinan la arquitectura original con toques modernos de lujo. Cada detalle ha sido pensado para ofrecerte un descanso reparador en un ambiente sofisticado.
            </p>

            <div class="room-gallery-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px;">
                <div class="gallery-item scroll-anim scroll-fade">
                    <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Suite View 1" style="width: 100%; border-radius: 8px;">
                </div>
                <div class="gallery-item scroll-anim scroll-right">
                    <img src="https://images.unsplash.com/photo-1591088398332-8a7701972843?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Suite View 2" style="width: 100%; border-radius: 8px;">
                </div>
            </div>
        </div>

        <aside class="booking-sidebar scroll-anim scroll-right">
            <h3 class="serif gold-text">Reserva tu Estancia</h3>
            <p style="color: var(--text-gray); font-size: 0.9rem; margin-bottom: 25px;">Selecciona tus fechas y vive la experiencia del colibrí.</p>

            <a href="reserva.php" class="btn btn-gold" style="width: 100%; display: block; text-align: center; padding: 15px; text-decoration: none;">
                <i class="fa-solid fa-calendar-days"></i> CONSULTAR DISPONIBILIDAD
            </a>

            <!-- Coupon Display (Managed via Admin) -->
            <div class="coupon-box-mini" style="margin-top: 30px; border: 1px dashed var(--primary-gold); padding: 20px; text-align: center; border-radius: 8px; background: rgba(197, 160, 89, 0.05);">
                <div class="coupon-header" style="color: var(--primary-gold); font-size: 0.8rem; font-weight: 700; margin-bottom: 10px;"><i class="fa-solid fa-gift"></i> CUPÓN DISPONIBLE</div>
                <div class="coupon-code" style="font-size: 1.5rem; font-weight: 700; letter-spacing: 2px;">VILLA2026</div>
                <div class="coupon-footer" style="font-size: 0.75rem; color: var(--text-gray); margin-top: 10px;">¡Úsalo en tu próxima reserva!</div>
            </div>
        </aside>
    </section>
</main>

<?php include_once "../layouts/footer.php"; ?>
