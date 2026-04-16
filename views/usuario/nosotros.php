<?php
require_once "config/config.php";
$pageTitle = "Sobre Nosotros | Hotel Boutique Villa de Sant";
$extraCSS = '
<style>
    /* STORY SECTION */
    .nos-about { padding: 120px 0; }
    .nos-about-grid { display: grid; grid-template-columns: 1.2fr 1.5fr; gap: 80px; align-items: center; }
    .nos-about-img { position: relative; border-radius: 20px; overflow: hidden; height: 550px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
    .nos-about-img img { width: 100%; height: 100%; object-fit: cover; }
    .nos-about-badge { position: absolute; bottom: 30px; left: 30px; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(10px); border: 1.5px solid var(--primary-gold); padding: 18px 25px; border-radius: 12px; z-index: 10; }
    .nos-about-badge span { font-size: 2.2rem; font-weight: 700; color: var(--primary-gold); display: block; font-family: var(--font-serif); line-height: 1; }
    .nos-about-badge p { font-size: 0.75rem; color: var(--text-white); text-transform: uppercase; letter-spacing: 2px; margin-top: 5px; opacity: 0.9; }
    
    .nos-label { color: var(--primary-gold); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 4px; display: flex; align-items: center; gap: 10px; margin-bottom: 25px; font-weight: 600; }
    .nos-text h2 { font-size: 2.2rem; line-height: 1.2; margin-bottom: 25px; color: var(--primary-gold); }
    .nos-text p { color: var(--text-gray); font-size: 1.05rem; line-height: 1.8; margin-bottom: 25px; }
    .nos-values-list { list-style: none; padding: 0; margin-top: 40px; }
    .nos-value-item { display: flex; align-items: center; gap: 15px; margin-bottom: 18px; font-size: 1rem; color: var(--text-white); font-weight: 500; }
    .nos-value-item i { color: var(--primary-gold); font-size: 1.1rem; }

    /* LOCATION SECTION */
    .nos-ubicacion { background: var(--bg-black); padding: 120px 0; }
    .plus-code-box { display: inline-flex; align-items: center; gap: 10px; border: 1px solid var(--primary-gold); padding: 8px 15px; border-radius: 4px; background: rgba(212, 175, 55, 0.05); color: var(--primary-gold); font-family: monospace; font-size: 0.95rem; margin: 30px 0; }
    .location-card { height: 450px; border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); }

    /* CTA SECTION */
    .nos-cta { padding: 150px 0; text-align: center; }
    .nos-cta h2 { font-size: 3rem; font-style: italic; color: var(--primary-gold); margin-bottom: 40px; }
    .nos-cta p { color: var(--text-gray); max-width: 650px; margin: 0 auto 50px; font-size: 1.2rem; }
    .nos-cta-btns { display: flex; gap: 20px; justify-content: center; }
    .btn-rounded { border-radius: 50px; padding: 18px 45px; font-size: 0.95rem; font-weight: 700; }
    .btn-outline { border: 1.5px solid var(--primary-gold); background: transparent; color: var(--primary-gold); }
    .btn-outline:hover { background: var(--primary-gold); color: #000; }

    @media (max-width: 1000px) { .nos-about-grid { grid-template-columns: 1fr; gap: 50px; } .nos-about-img { height: 400px; } .nos-cta h2 { font-size: 3rem; } }
    @media (max-width: 768px) {
        .nos-about { padding: 60px 0; }
        .nos-about-img { height: 300px; border-radius: 12px; }
        .nos-about-badge { padding: 12px 18px; bottom: 15px; left: 15px; }
        .nos-about-badge span { font-size: 1.6rem; }
        .nos-text h2 { font-size: 1.8rem; }
        .nos-text p { font-size: 0.95rem; line-height: 1.7; }
        .nos-ubicacion { padding: 60px 0; }
        .location-card { height: 300px; }
        .nos-cta { padding: 80px 0; }
        .nos-cta h2 { font-size: 2rem; }
        .nos-cta p { font-size: 1rem; }
        .nos-cta-btns { flex-direction: column; align-items: center; gap: 15px; }
        .btn-rounded { padding: 14px 30px; font-size: 0.88rem; width: 100%; max-width: 280px; text-align: center; }
    }
</style>';
include_once "views/layouts/header.php";
?>

<main>
    <section class="page-hero">
        <div class="page-hero-bg" id="nos-hero-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/img/home/hero_home.jpg')"></div>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Logo" class="hero-logo-glow" style="max-width: 70px;">
            <div class="page-hero-divider"></div>
            <h1 class="page-hero-title serif gold-text">Quiénes Somos</h1>
        </div>
    </section>

    <!-- SECTION: NUESTRA HISTORIA -->
    <section class="nos-about section-padding">
        <div class="container">
            <div class="nos-about-grid">
                <div class="nos-about-img scroll-anim scroll-left">
                    <img src="<?php echo BASE_URL; ?>assets/img/home/home2.jpg" alt="Hotel Boutique Villa de Sant">
                    <div class="nos-about-badge">
                        <span>1924</span>
                        <p>Año de Fundación</p>
                    </div>
                </div>
                <div class="nos-text scroll-anim scroll-right">
                    <div class="nos-label"><i class="fa-solid fa-leaf"></i> NUESTRA HISTORIA</div>
                    <h2 class="serif gold-text">Un legado de hospitalidad alada</h2>
                    <p>Villa de Sant nació como una casona colonial en el centro histórico de Quito, testigo silencioso de generaciones de viajeros que encontraron aquí su hogar lejos de casa. Cada piedra tallada, cada ventanal arqueado y cada patio florido cuenta una historia de amor por la hospitalidad.</p>
                    <p>Hoy, décadas después, mantenemos intacta esa esencia: un hotel boutique donde el lujo no se mide en metros cuadrados, sino en la profundidad de las experiencias y la calidez de cada encuentro.</p>
                    <ul class="nos-values-list">
                        <li class="nos-value-item"><i class="fa-solid fa-circle-check"></i> Hospitalidad auténtica y personalizada</li>
                        <li class="nos-value-item"><i class="fa-solid fa-circle-check"></i> Diseño colonial restaurado con esmero</li>
                        <li class="nos-value-item"><i class="fa-solid fa-circle-check"></i> Gastronomía de autor con sabores locales</li>
                        <li class="nos-value-item"><i class="fa-solid fa-circle-check"></i> Compromiso con el turismo sostenible</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION: UBICACIÓN -->
    <section class="nos-ubicacion">
        <div class="container">
            <div class="nos-about-grid">
                <div class="nos-text scroll-anim scroll-left">
                    <div class="nos-label"><i class="fa-solid fa-location-dot"></i> DÓNDE ESTAMOS</div>
                    <h2 class="serif gold-text">Nuestra Ubicación</h2>
                    <p style="color: var(--text-white); font-weight: 700; font-size: 1.3rem;">
                        <i class="fa-solid fa-map" style="color: var(--primary-gold); margin-right: 15px;"></i> Robles E y Reina Victoria 5-62 y<br>
                        <span style="padding-left: 38px;">170526 Quito, Ecuador</span>
                    </p>
                    <div class="plus-code-box">
                        <i class="fa-solid fa-qrcode"></i> Plus Code: QGV4+C5 Quito
                    </div>
                    <p>Ubicados en el corazón vibrante y elegante de la capital. Nuestro santuario campestre te ofrece una escapada perfecta cerca de los puntos más emblemáticos de la ciudad.</p>
                    <a href="https://www.google.com/maps/dir//Hotel+Boutique+Villa+de+Sant/@-0.2063638,-78.497076,17z" target="_blank" class="btn btn-gold btn-rounded" style="width: auto; margin-top: 30px;">
                        ABRIR EN MAPS <i class="fa-solid fa-location-arrow" style="margin-left:8px;"></i>
                    </a>
                </div>
                <div class="location-card scroll-anim scroll-right">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.792556214589!2d-78.497076!3d-0.2063638!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91d59b188d65eb01%3A0xaef63a839146e029!2sHotel%20Boutique%20Villa%20de%20Sant!5e0!3m2!1ses!2sec!4v1712781234567!5m2!1ses!2sec" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </section>

    <!-- SECTION: CTA -->
    <section class="nos-cta section-padding">
        <div class="container text-center">
            <div class="page-hero-divider" style="width: 100px; margin: 0 auto 50px;"></div>
            <h2 class="serif">&ldquo;El vuelo del colibrí te espera&rdquo;</h2>
            <p>Ven a ser parte de nuestra historia. Cada estancia en Villa de Sant es el comienzo de una memoria que llevarás contigo para siempre.</p>
            <div class="nos-cta-btns scroll-anim scroll-fade">
                <a href="<?php echo BASE_URL; ?>?action=reserva" class="btn btn-gold btn-rounded"><i class="fa-solid fa-calendar-check" style="margin-right:10px;"></i> RESERVAR AHORA</a>
                <a href="<?php echo BASE_URL; ?>?action=habitaciones" class="btn btn-outline btn-rounded"><i class="fa-solid fa-bed" style="margin-right:10px;"></i> VER HABITACIONES</a>
            </div>
        </div>
    </section>
</main>

<?php include_once "views/layouts/footer.php"; ?>
