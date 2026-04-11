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
    /* ========= HERO ========= */
    .rooms-hero {
        position: relative;
        height: 85vh; /* Increased height */
        min-height: 550px;
        display: flex;
        align-items: center; /* Center vertically for a more balanced look */
        justify-content: center;
        text-align: center;
        background: url("https://lh3.googleusercontent.com/p/AF1QipNVb6baKMNn4xJZncYQr9f1z7mdaYdRtnXhkfiz=s4000") no-repeat center center;
        background-size: cover;
        overflow: hidden;
    }
    .rooms-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom,
            rgba(0,0,0,0.15) 0%,
            rgba(0,0,0,0.45) 40%,
            rgba(5,10,5,0.97) 100%);
    }
    body.light-mode .rooms-hero::before {
        background: linear-gradient(to bottom,
            rgba(210,190,140,0.3) 0%,
            rgba(200,175,110,0.7) 50%,
            rgba(238,228,200,0.98) 100%);
    }
    .rooms-hero-content {
        position: relative;
        z-index: 2;
        width: 100%;
        max-width: 900px;
        padding: 40px 20px; /* Adjusted padding */
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .rooms-hero-divider {
        width: 60px;
        height: 2px;
        background: linear-gradient(to right, transparent, var(--primary-gold), transparent);
        margin: 20px auto 25px;
    }
    .rooms-hero-title {
        font-size: clamp(2.2rem, 4.5vw, 3.6rem);
        color: var(--primary-gold);
        margin-bottom: 10px;
        line-height: 1.05;
        text-shadow: 0 2px 16px rgba(0,0,0,0.7);
    }
    body.light-mode .rooms-hero-title { text-shadow: 0 1px 6px rgba(120,80,0,0.2); }
    .rooms-hero-sub {
        font-size: 0.95rem;
        color: rgba(255,255,255,0.82);
        max-width: 500px;
        margin: 0 auto 24px;
        line-height: 1.65;
    }
    body.light-mode .rooms-hero-sub { color: rgba(50,30,5,0.8); }

    .schedule-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        max-width: 520px;
        width: 100%;
        margin: 0 auto;
    }
    .schedule-card {
        background: rgba(16, 28, 18, 0.72);
        border: 1px solid rgba(212, 175, 55, 0.35);
        border-radius: 12px;
        padding: 14px 16px;
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        transition: border-color 0.3s ease;
    }
    .schedule-card:hover { border-color: rgba(212,175,55,0.7); }
    body.light-mode .schedule-card {
        background: rgba(240, 230, 200, 0.72);
        border-color: rgba(160, 120, 20, 0.4);
    }
    .schedule-icon-circle {
        width: 34px;
        height: 34px;
        background: rgba(212, 175, 55, 0.18);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-gold);
        font-size: 0.9rem;
    }
    .schedule-card h4 {
        font-size: 0.78rem;
        color: rgba(255,255,255,0.75);
        font-weight: 400;
        font-family: var(--font-sans);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }
    body.light-mode .schedule-card h4 { color: rgba(60,40,10,0.75); }
    .schedule-card .time {
        font-size: 1.2rem;
        color: var(--primary-gold);
        font-weight: 700;
        font-family: var(--font-serif);
        margin: 0;
    }

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
        .schedule-grid { grid-template-columns: 1fr; }
        .rooms-hero { height: auto; min-height: 380px; padding-bottom: 30px; }
    }
</style>';
include_once "views/layouts/header.php";
?>

<main>
    <!-- HERO: NUESTRAS ESTANCIAS -->
    <section class="rooms-hero">
        <div class="rooms-hero-content">
            <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Logo" style="max-width: 90px; margin-bottom: 30px; border-radius:50%; filter: drop-shadow(0 0 15px rgba(212,175,55,0.7));">
            <div class="rooms-hero-divider"></div>
            <h1 class="rooms-hero-title serif">Nuestras Estancias</h1>
            <p class="rooms-hero-sub">Descubre el equilibrio perfecto entre la historia colonial y el confort contemporáneo.</p>

            <div class="schedule-grid">
                <div class="schedule-card">
                    <div class="schedule-icon-circle"><i class="fa-solid fa-bell-concierge"></i></div>
                    <h4>Hora de entrada</h4>
                    <div class="time">12:00 p. m.</div>
                </div>
                <div class="schedule-card">
                    <div class="schedule-icon-circle"><i class="fa-solid fa-key"></i></div>
                    <h4>Hora de salida</h4>
                    <div class="time">12:00 p. m.</div>
                </div>
            </div>
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
                        <img src="<?php echo $row['imagen'] ?: 'https://lh3.googleusercontent.com/gps-cs-s/AHVAweo7jA089iqm7VwQLpaAZq6Ljb2GYdxVe6eeQb91mnSuAezn2jbyLheGmWy1aF0bMwuNNsRPp6_-KFQQC-PiTfNn0V5vxLLrUTKXfOj6gVzw2Cm5siYhaS1ruBXiFmzPj_YcO4Yf=s4000'; ?>" alt="<?php echo htmlspecialchars($row['nombre']); ?>">
                    </div>
                    <div class="room-info">
                        <h2 class="serif"><?php echo htmlspecialchars($row['nombre']); ?></h2>

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
