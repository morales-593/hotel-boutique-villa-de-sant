<?php
require_once "config/config.php";
require_once "config/database.php";
require_once "models/Room.php";

$database = new Database();
$db = $database->getConnection();

// Group by type to show only the 5 categories
$query = "
    SELECT tipo, nombre, imagen, caracteristicas,
           MIN(id) as id,
           MIN(precio) as precio,
           SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) as qty_disponible
    FROM habitaciones
    GROUP BY tipo, nombre, imagen, caracteristicas
    ORDER BY precio ASC
";
$rooms = $db->query($query);

$pageTitle = "Nuestras Estancias | Hotel Boutique Villa de Sant";
$extraCSS = '
<style>


    /* ========= ROOMS GRID ========= */
    .rooms-grid-section {
        background: var(--bg-black);
        padding: 60px 0;
    }
    body.light-mode .rooms-grid-section { background: #f5f0e8; }

    .rooms-container {
        width: 95%;
        max-width: 1500px;
        margin: 0 auto;
        padding: 0 10px;
    }

    .room-display-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 22px;
    }
    @media (max-width: 1400px) { .room-display-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 900px)  { .room-display-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 580px)  { .room-display-grid { grid-template-columns: 1fr; } }

    .room-item { display: flex; flex-direction: column; }

    .room-img-wrapper {
        border-radius: 6px;
        overflow: hidden;
        margin-bottom: 10px;
        height: 165px;
        flex-shrink: 0;
    }
    .room-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .room-item:hover .room-img-wrapper img { transform: scale(1.04); }

    .room-info { display: flex; flex-direction: column; flex: 1; }

    .room-info h2 {
        font-size: 0.9rem;
        color: var(--text-white);
        margin-bottom: 7px;
        line-height: 1.3;
    }
    body.light-mode .room-info h2 { color: #1a110a; }

    .room-features {
        display: flex;
        flex-wrap: wrap;
        gap: 3px 8px;
        margin-bottom: 8px;
    }
    .feature-item {
        display: flex;
        align-items: center;
        gap: 4px;
        color: var(--text-gray);
        font-size: 0.68rem;
    }
    body.light-mode .feature-item { color: #5a4030; }
    .feature-item i { color: var(--primary-gold); font-size: 0.62rem; }

    .status-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 700;
        font-size: 0.67rem;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 10px;
        margin-top: auto;
    }
    .status-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .status-available .status-dot { background: #2ecc71; box-shadow: 0 0 5px #2ecc71; }
    .status-available { color: #2ecc71; }
    .status-occupied .status-dot  { background: #e74c3c; box-shadow: 0 0 5px #e74c3c; }
    .status-occupied  { color: #e74c3c; }

    .btn-outline-gold {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border: 1px solid var(--primary-gold);
        background: transparent;
        color: var(--primary-gold);
        padding: 8px 12px;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1px;
        font-size: 0.68rem;
        transition: all 0.3s ease;
        width: 100%;
    }
    .btn-outline-gold:hover {
        background: var(--primary-gold);
        color: #000;
    }
    body.light-mode .btn-outline-gold { border-color: #b8922a; color: #b8922a; }
    body.light-mode .btn-outline-gold:hover { background: #b8922a; color: #fff; }

    @media (max-width: 768px) {
        .rooms-grid-section { padding-top: 40px; }
    }
</style>';
include_once "views/layouts/header.php";
?>

<main>
    <!-- HERO: NUESTRAS ESTANCIAS -->
    <section class="page-hero">
        <div class="page-hero-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/img/home/habitacion.jpg')"></div>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
            <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Logo" class="hero-logo-glow" style="max-width: 70px;">
            <div class="page-hero-divider"></div>
            <h1 class="page-hero-title serif gold-text">Nuestras Estancias</h1>
        </div>
    </section>

    <!-- ROOMS GRID -->
    <section class="rooms-grid-section">
        <div class="rooms-container">
            <div class="room-display-grid">
                <?php while ($row = $rooms->fetch(PDO::FETCH_ASSOC)):
                    $isAvailable = $row['qty_disponible'] > 0;
                    $features = Room::parseFeatures($row['caracteristicas'] ?? null);
                ?>
                <div class="room-item scroll-anim scroll-fade">
                    <div class="room-img-wrapper">
                        <?php 
                        // Try to find a local image first
                        $localImg = null;
                        $roomTypeFolder = "assets/img/" . str_replace('_', ' ', $row['tipo']);
                        if (is_dir($roomTypeFolder)) {
                            // If the DB already has a valid local path, use it, else pick the first from folder
                            if ($row['imagen'] && strpos($row['imagen'], 'assets/') === 0 && file_exists($row['imagen'])) {
                                $localImg = BASE_URL . $row['imagen'];
                            } else {
                                $files = scandir($roomTypeFolder);
                                foreach ($files as $file) {
                                    if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'])) {
                                        $localImg = BASE_URL . $roomTypeFolder . '/' . $file;
                                        break;
                                    }
                                }
                            }
                        }
                        
                        // Fallback to DB or placeholder
                        $finalImg = $localImg ?: ($row['imagen'] ?: BASE_URL . 'assets/img/home/hero_home.jpg');
                        // Ensure it's a full URL if it's from DB and not already full
                        if (strpos($finalImg, 'http') !== 0 && strpos($finalImg, BASE_URL) !== 0) {
                            $finalImg = BASE_URL . $finalImg;
                        }
                        ?>
                        <img src="<?php echo $finalImg; ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                    </div>
                    <div class="room-info">
                        <h2 class="serif"><?php echo htmlspecialchars($row['nombre']); ?></h2>
                        
                        <div style="font-size: 1.15rem; color: var(--primary-gold); font-weight: 800; margin-bottom: 12px; margin-top: -3px;">
                            $<?php echo number_format($row['precio'], 2); ?> <span style="font-size: 0.75rem; color: var(--text-gray); font-weight: normal;">/ noche</span>
                        </div>

                        <div class="room-features">
                            <?php if (!empty($features)): ?>
                                <?php foreach ($features as $f): ?>
                                    <div class="feature-item">
                                        <i class="fa-solid <?php echo htmlspecialchars($f['icon']); ?>"></i>
                                        <?php echo htmlspecialchars($f['label']); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="feature-item"><i class="fa-solid fa-wifi"></i> WiFi</div>
                                <div class="feature-item"><i class="fa-solid fa-snowflake"></i> A/C</div>
                            <?php endif; ?>
                        </div>

                        <div class="status-indicator <?php echo $isAvailable ? 'status-available' : 'status-occupied'; ?>">
                            <div class="status-dot"></div>
                            <span><?php echo $isAvailable ? 'DISPONIBLE' : 'OCUPADO'; ?></span>
                        </div>

                        <a href="index.php?action=habitacion&room=<?php echo $row['id']; ?>" class="btn-outline-gold">
                            VER DETALLES <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
</main>

<?php include_once "views/layouts/footer.php"; ?>
