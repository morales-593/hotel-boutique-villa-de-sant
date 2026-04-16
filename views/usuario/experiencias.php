<?php
require_once "config/config.php";
require_once "config/database.php";
require_once "models/Experience.php";

$db = (new Database())->getConnection();
$experience = new Experience($db);

$secciones = $experience->getAllSections();
$galeria = $experience->getGalleryPhotos();

// Indexar secciones
$seccionesMap = [];
foreach ($secciones as $s) {
    $seccionesMap[$s['seccion']] = $s;
}

$transporte = $seccionesMap['transporte'] ?? null;
$tours = $seccionesMap['tours'] ?? null;

// Indexar galería por posición
$galeriaMap = [];
foreach ($galeria as $g) {
    $galeriaMap[intval($g['posicion'])] = $g;
}

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

    /* ========= NEW FEATURE SECTIONS ========= */
    .experience-feature {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        align-items: center;
        padding: 80px 0;
    }
    .feature-img-box {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        aspect-ratio: 16/10;
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    }
    .feature-img-box img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.8s ease; }
    .feature-img-box:hover img { transform: scale(1.05); }
    
    .feature-content h3 { font-size: 2.2rem; margin-bottom: 20px; color: var(--primary-gold); }
    .feature-content p { color: var(--text-gray); line-height: 1.8; margin-bottom: 25px; font-size: 1.05rem; }
    .feature-list { list-style: none; padding: 0; }
    .feature-list li { display: flex; align-items: center; gap: 12px; color: var(--text-white); margin-bottom: 12px; font-size: 0.95rem; }
    .feature-list li i { color: var(--primary-gold); font-size: 0.85rem; }

    .keyword-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 25px;
    }
    .keyword-tag {
        font-size: 0.65rem;
        background: rgba(212,175,55,0.1);
        color: var(--primary-gold);
        padding: 5px 12px;
        border-radius: 50px;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        border: 1px solid rgba(212,175,55,0.2);
    }

    @media (max-width: 900px) {
        .experience-feature { grid-template-columns: 1fr; gap: 40px; padding: 50px 0; }
        .experience-feature.reverse { direction: initial; }
        .experience-feature.reverse .feature-img-box { order: -1; }
    }
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

    <!-- TRANSPORTATION SECTION -->
    <?php if ($transporte): 
        $tags_t = json_decode($transporte['tags'] ?? '[]', true) ?: [];
        $lista_t = json_decode($transporte['lista'] ?? '[]', true) ?: [];
    ?>
    <div class="container">
        <section class="experience-feature scroll-anim scroll-fade">
            <div class="feature-content">
                <span class="gold-text" style="text-transform: uppercase; letter-spacing: 3px; font-weight: 700; font-size: 0.75rem; display: block; margin-bottom: 10px;"><?php echo e($transporte['subtitulo']); ?></span>
                <h3 class="serif"><?php echo e($transporte['titulo']); ?></h3>
                
                <?php if (!empty($tags_t)): ?>
                <div class="keyword-tags">
                    <?php foreach ($tags_t as $tag): ?>
                    <span class="keyword-tag"><?php echo e($tag); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <p><?php echo e($transporte['descripcion']); ?></p>
                <?php if (!empty($lista_t)): ?>
                <ul class="feature-list">
                    <?php foreach ($lista_t as $item): ?>
                    <li><i class="<?php echo e($item['icono']); ?>"></i> <?php echo e($item['texto']); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
            <div class="feature-img-box">
                <img src="<?php echo BASE_URL . $transporte['imagen']; ?>" alt="<?php echo e($transporte['titulo']); ?>">
            </div>
        </section>
    </div>
    <?php endif; ?>

    <div class="section-padding">
        <div class="container">
            <div class="double-collage-wrapper">
                <!-- LEFT COLLAGE (posiciones 1-4) -->
                <div class="photo-collage-grid">
                    <?php 
                    $leftClasses = ['pcg-1', 'pcg-2', 'pcg-3', 'pcg-4'];
                    for ($i = 1; $i <= 4; $i++):
                        $foto = $galeriaMap[$i] ?? null;
                        $cls = $leftClasses[$i - 1];
                    ?>
                    <div class="pcg-item <?php echo $cls; ?> scroll-anim">
                        <?php if ($foto): ?>
                        <img src="<?php echo BASE_URL . $foto['imagen']; ?>" alt="<?php echo e($foto['alt_text']); ?>">
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
                <!-- RIGHT COLLAGE (posiciones 5-8) -->
                <div class="photo-collage-grid">
                    <?php 
                    $rightClasses = ['pcg-1', 'pcg-2', 'pcg-3', 'pcg-4'];
                    for ($i = 5; $i <= 8; $i++):
                        $foto = $galeriaMap[$i] ?? null;
                        $cls = $rightClasses[$i - 5];
                    ?>
                    <div class="pcg-item <?php echo $cls; ?> scroll-anim">
                        <?php if ($foto): ?>
                        <img src="<?php echo BASE_URL . $foto['imagen']; ?>" alt="<?php echo e($foto['alt_text']); ?>">
                        <?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Fila inferior - posiciones 9-12 -->
            <div class="photo-collage-grid" style="grid-template-columns: repeat(4, 1fr); grid-auto-rows: 160px; margin-top: 12px;">
                <?php for ($i = 9; $i <= 12; $i++):
                    $foto = $galeriaMap[$i] ?? null;
                ?>
                <div class="pcg-item scroll-anim">
                    <?php if ($foto): ?>
                    <img src="<?php echo BASE_URL . $foto['imagen']; ?>" alt="<?php echo e($foto['alt_text']); ?>">
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>

            <!-- TOURS SECTION -->
            <?php if ($tours): 
                $tags_to = json_decode($tours['tags'] ?? '[]', true) ?: [];
                $lista_to = json_decode($tours['lista'] ?? '[]', true) ?: [];
            ?>
            <section class="experience-feature reverse scroll-anim scroll-fade" style="border-top: 1px solid rgba(212,175,55,0.1); margin-top: 40px;">
                <div class="feature-img-box">
                    <img src="<?php echo BASE_URL . $tours['imagen']; ?>" alt="<?php echo e($tours['titulo']); ?>">
                </div>
                <div class="feature-content">
                    <span class="gold-text" style="text-transform: uppercase; letter-spacing: 3px; font-weight: 700; font-size: 0.75rem; display: block; margin-bottom: 10px;"><?php echo e($tours['subtitulo']); ?></span>
                    <h3 class="serif"><?php echo e($tours['titulo']); ?></h3>

                    <?php if (!empty($tags_to)): ?>
                    <div class="keyword-tags">
                        <?php foreach ($tags_to as $tag): ?>
                        <span class="keyword-tag"><?php echo e($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <p><?php echo e($tours['descripcion']); ?></p>
                    <?php if (!empty($lista_to)): ?>
                    <ul class="feature-list">
                        <?php foreach ($lista_to as $item): ?>
                        <li><i class="<?php echo e($item['icono']); ?>"></i> <?php echo e($item['texto']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>
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
