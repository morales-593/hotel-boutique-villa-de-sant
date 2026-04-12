<?php
require_once "config/config.php";
$pageTitle = "Experiencias en Quito | Villa de Sant";
$extraCSS = '
<style>
    .double-collage-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 40px; }
    .photo-collage-grid { display: grid; grid-template-columns: repeat(3, 1fr); grid-gap: 15px; grid-auto-rows: 200px; }
    .pcg-item { border-radius: 8px; overflow: hidden; position: relative; box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5); background: var(--bg-card); }
    .pcg-item img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1); }
    .pcg-item:hover img { transform: scale(1.05); }
    .pcg-1 { grid-column: span 2; }
    .pcg-3 { grid-column: span 1; grid-row: span 2; }
    .pcg-4 { grid-column: span 2; grid-row: span 2; }
    @media (max-width: 1200px) { .photo-collage-grid { grid-auto-rows: 150px; } }
    @media (max-width: 900px) { .double-collage-wrapper { grid-template-columns: 1fr; } .photo-collage-grid { grid-auto-rows: 200px; } }
    
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
            style="background-image: url('https://lh3.googleusercontent.com/gps-cs-s/AHVAwerfdPRz75mHQrQNhr6FN-495Ez88UUW2bu96-q-fw80MPoEs3h8sq1LClWXUI4B1dBEgmXaQq-Gx0bjV2JQXw1tgIZiTJtrfYbCaD2SD-LLQ-VXsYMDcSUUuHgoYBu7yo9vXMWlAA=s4000')">
        </div>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Villa de Sant" class="hero-logo-glow" style="max-width: 60px;">
            <div class="page-hero-divider"></div>
            <h1 class="page-hero-title serif gold-text">Experiencias de Usuario</h1>
            <p class="page-hero-sub">Vive la exclusividad y el confort supremo dentro de nuestro santuario.</p>
        </div>
    </section>

    <div class="section-padding">
        <div class="container">
            <div class="double-collage-wrapper">
                <!-- LEFT COLLAGE -->
                <div class="photo-collage-grid">
                    <div class="pcg-item pcg-1 scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipMIQr7TRGo_LxBf6Y6uQFMdudqUH53FkN0YOmQy=s4000" alt="Spa"></div>
                    <div class="pcg-item pcg-2 scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipOUY0uL1F_v_8dGPVe0ynf_3RW2WLzdV7YcKVbK=s4000" alt="Breakfast"></div>
                    <div class="pcg-item pcg-3 scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipNszXIbjjvNsY0MnxFayR8jadWS4bkzKXzM4S-V=s4000" alt="Suite"></div>
                    <div class="pcg-item pcg-4 scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipPsTeTQJsqrOLbm2XuJkVvUDWZAzqKeFBKRCCzV=s4000" alt="Dinner"></div>
                </div>
                <!-- RIGHT COLLAGE -->
                <div class="photo-collage-grid">
                    <div class="pcg-item pcg-1 scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipMmnjV0M4xUEjDw-0RaGFiNhANIB6XM-I_a6War=s4000" alt="Lobby"></div>
                    <div class="pcg-item pcg-2 scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipPEkKnDj6Keln7IrPTru8JI35soB7ZoTxarN4TX=s4000" alt="Garden"></div>
                    <div class="pcg-item pcg-3 scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipPfiPxrqUxywypEypkcJmtUCocUPEF5O8J0fWpr=s4000" alt="Patio"></div>
                    <div class="pcg-item pcg-4 scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipM4zBSQkctyMdsOZkeLBrD9lZCFWnuMjly3xqM-=s4000" alt="Bar"></div>
                </div>
            </div>
            
            <!-- Additional Masonry Row as per user image -->
            <div class="photo-collage-grid" style="grid-template-columns: repeat(4, 1fr); grid-auto-rows: 180px; margin-top: 15px;">
                <div class="pcg-item scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipNyCSzQQwHRrwSijTJjGdxpD9KUzSzTSoS-UaBk=s4000" alt="G1"></div>
                
                <div class="pcg-item scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipOdcJEEhb35hoq33uFBCeTcjLoNeIKDjisGpll4=s4000" alt="G2"></div>
                <div class="pcg-item scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipNnats45klmlfIf3e_kutlz84n5yk2gfvtanysnc=s4000" alt="G3"></div>
                <div class="pcg-item scroll-anim"><img src="https://lh3.googleusercontent.com/p/AF1QipP-v6_n_6_n_6_n_6_n_6_n_6_n_6_n" alt="G4"></div>
            </div>
        </div>

        <section class="section-padding section-360-bg">
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
    </div>
</main>

<?php include_once "views/layouts/footer.php"; ?>
