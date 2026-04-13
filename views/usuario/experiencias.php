<?php
require_once "config/config.php";
$pageTitle = "Experiencias en Quito | Villa de Sant";
$extraCSS = '
<style>
    .double-collage-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .photo-collage-grid { display: grid; grid-template-columns: repeat(3, 1fr); grid-gap: 12px; grid-auto-rows: 180px; }
    .pcg-item { border-radius: 8px; overflow: hidden; position: relative; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5); background: var(--bg-card); }
    .pcg-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
    .pcg-item:hover img { transform: scale(1.05); }
    .pcg-1 { grid-column: span 2; }
    .pcg-3 { grid-column: span 1; grid-row: span 2; }
    .pcg-4 { grid-column: span 2; grid-row: span 2; }
    @media (max-width: 1200px) { .photo-collage-grid { grid-auto-rows: 140px; } }
    @media (max-width: 900px) {
        .double-collage-wrapper { grid-template-columns: 1fr; gap: 12px; }
        .photo-collage-grid { grid-auto-rows: 160px; }
        .pcg-3 { grid-row: span 1; }
        .pcg-4 { grid-row: span 1; }
    }
    @media (max-width: 580px) {
        .photo-collage-grid { grid-template-columns: 1fr 1fr; grid-auto-rows: 120px; }
        .pcg-1 { grid-column: span 2; }
        .pcg-3, .pcg-4 { grid-column: span 1; grid-row: span 1; }
    }
    
    .section-360-bg {
        margin-top: 60px;
        border-top: 1px solid rgba(212, 175, 55, 0.2);
        border-bottom: 1px solid rgba(212, 175, 55, 0.2);
        background: linear-gradient(135deg, #0a110a 0%, #000000 100%);
    }
    .tour-360-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px; width: 100%; max-width: 1000px; margin: 0 auto; }
    .tour-360-item { height: 350px; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6); border: 1px solid rgba(212, 175, 55, 0.3); background: #000; }
    @media (max-width: 900px) { .tour-360-grid { grid-template-columns: 1fr; } .tour-360-item { height: 300px; } }
</style>';
include_once "views/layouts/header.php";
?>

<main>
    <section class="page-hero">
        <div class="page-hero-bg" id="hero-bg"
            style="background-image: url('<?php echo BASE_URL; ?>assets/img/home/hero_home.jpg')">
        </div>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Villa de Sant" class="hero-logo-glow" style="max-width: 70px;">
            <div class="page-hero-divider"></div>
            <h1 class="page-hero-title serif gold-text">Experiencias de Usuario</h1>
        </div>
    </section>

    <div class="section-padding">
        <div class="container">
            <div class="double-collage-wrapper">
                <!-- LEFT COLLAGE -->
                <div class="photo-collage-grid">
                    <div class="pcg-item pcg-1 scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia1.jpg" alt="Experiencia 1"></div>
                    <div class="pcg-item pcg-2 scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia9.jpg" alt="Experiencia 2"></div>
                    <div class="pcg-item pcg-3 scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia3.jpg" alt="Experiencia 3"></div>
                    <div class="pcg-item pcg-4 scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia4.jpg" alt="Experiencia 4"></div>
                </div>
                <!-- RIGHT COLLAGE -->
                <div class="photo-collage-grid">
                    <div class="pcg-item pcg-1 scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia5.jpg" alt="Experiencia 5"></div>
                    <div class="pcg-item pcg-2 scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia6.jpg" alt="Experiencia 6"></div>
                    <div class="pcg-item pcg-3 scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia10.jpg" alt="Experiencia 7"></div>
                    <div class="pcg-item pcg-4 scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia11.jpg" alt="Experiencia 8"></div>
                </div>
            </div>

            <!-- Fila inferior - 12 imágenes en total -->
            <div class="photo-collage-grid" style="grid-template-columns: repeat(4, 1fr); grid-auto-rows: 160px; margin-top: 12px;">
                <div class="pcg-item scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia2.jpg" alt="Experiencia 2"></div>
                <div class="pcg-item scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia7.jpg" alt="Experiencia 7"></div>
                <div class="pcg-item scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia8.jpg" alt="Experiencia 8"></div>
                <div class="pcg-item scroll-anim"><img src="<?php echo BASE_URL; ?>assets/img/experiencia/experiencia12.jpg" alt="Experiencia 12"></div>
            </div>
        </div>
    </div>

    <section class="section-360-bg" style="padding: 60px 0;">
        <div class="container text-center scroll-anim scroll-fade">
            <p style="color: var(--primary-gold); text-transform: uppercase; letter-spacing: 2px; font-weight: 600; font-size: 0.85rem; margin-bottom: 10px;">
                <i class="fa-solid fa-vr-cardboard" style="margin-right: 8px;"></i>Exploración Virtual
            </p>
            <h2 class="serif gold-text" style="font-size: 2.5rem; margin-bottom: 15px;">Recorridos 360°</h2>
            <div class="tour-360-grid scroll-anim scroll-fade">
                <div class="tour-360-item"><iframe src="https://www.google.com/maps/embed?pb=!4v1680000000000!6m8!1m7!1sCIHM0ogKEICAgIDssrHyaA!2m2!1d-0.2064062!2d-78.4945526!3f120!4f100!5f0.7820865974627469" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe></div>
                <div class="tour-360-item"><iframe src="https://www.google.com/maps/embed?pb=!4v1775444151019!6m8!1m7!1sCAoSFkNJSE0wb2dLRUlDQWdJRHNzdkd1SXc.!2m2!1d-0.2064062152678696!2d-78.4945525612083!3f175.13555631159855!4f-16.00812007753943!5f0.7820865974627469" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe></div>
            </div>
        </div>
    </section>
</main>

<?php include_once "views/layouts/footer.php"; ?>
