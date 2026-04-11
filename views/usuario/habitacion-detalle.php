<?php
require_once "config/config.php";
require_once "config/database.php";
require_once "models/Room.php";

$roomId = intval($_GET['room'] ?? 0);
if (!$roomId) {
    header("Location: index.php?action=habitaciones");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$roomModel = new Room($db);
$room = $roomModel->readOne($roomId);

if (!$room) {
    header("Location: index.php?action=habitaciones");
    exit();
}

$isAvailable = $room['estado'] == 'disponible';
$features    = Room::parseFeatures($room['caracteristicas'] ?? null);

// Global hotel amenities for the sidebar
$amenities = [
    ['icon' => 'fa-wifi',            'label' => 'Wi-Fi gratis'],
    ['icon' => 'fa-mug-hot',         'label' => 'Desayuno incluido'],
    ['icon' => 'fa-square-parking',  'label' => 'Estacionamiento pago'],
    ['icon' => 'fa-ban-smoking',     'label' => 'Libre de humo'],
    ['icon' => 'fa-snowflake',       'label' => 'Aire acondicionado'],
    ['icon' => 'fa-shirt',           'label' => 'Servicio de lavandería'],
    ['icon' => 'fa-paw',             'label' => 'Se permiten mascotas'],
    ['icon' => 'fa-utensils',        'label' => 'Restaurante'],
    ['icon' => 'fa-van-shuttle',     'label' => 'Transporte aeropuerto'],
    ['icon' => 'fa-champagne-glass', 'label' => 'Bar'],
];

// Coupon specific to this room type
$coupon = null;
try {
    $stmtC = $db->prepare(
        "SELECT * FROM cupones WHERE habitacion_tipo = ? AND activo = 1 AND fecha_fin >= CURDATE() ORDER BY fecha_fin DESC LIMIT 1"
    );
    $stmtC->execute([$room['tipo']]);
    $coupon = $stmtC->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* silent */ }

$pageTitle = htmlspecialchars($room['nombre']) . " | Hotel Boutique Villa de Sant";
$extraCSS  = '
<style>
    /* ========= HERO MINIMAL ========= */
    .room-detail-hero {
        position: relative;
        height: clamp(280px, 40vh, 400px);
        background: url("' . ($room['imagen'] ?: 'https://lh3.googleusercontent.com/p/AF1QipMmnjV0M4xUEjDw-0RaGFiNhANIB6XM-I_a6War=s4000') . '") no-repeat center 35%;
        background-size: cover;
        display: flex;
        align-items: flex-end;
        overflow: hidden;
    }
    .room-detail-hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(5,10,5,0.96) 100%);
    }
    body.light-mode .room-detail-hero::before {
        background: linear-gradient(to bottom, rgba(210,190,140,0.2) 0%, rgba(238,228,200,0.97) 100%);
    }
    .room-detail-hero-content {
        position: relative;
        z-index: 2;
        padding: 30px 40px;
    }
    .room-detail-hero-content .back-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8rem;
        color: rgba(255,255,255,0.65);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
        transition: color 0.3s;
    }
    body.light-mode .room-detail-hero-content .back-link { color: rgba(60,40,10,0.65); }
    .room-detail-hero-content .back-link:hover { color: var(--primary-gold); }
    .room-detail-hero-content h1 {
        font-size: clamp(2rem, 4vw, 3rem);
        color: var(--primary-gold);
        line-height: 1.1;
        margin: 0;
    }

    /* ========= MAIN LAYOUT ========= */
    .room-detail-layout {
        max-width: 1280px;
        margin: 0 auto;
        padding: 50px 30px 80px;
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 50px;
        align-items: start;
    }
    @media (max-width: 1024px) { .room-detail-layout { grid-template-columns: 1fr; } }

    /* ========= LEFT: CONTENT ========= */
    .room-main-content {}

    .room-description {
        color: var(--text-gray);
        font-size: 1rem;
        line-height: 1.8;
        margin-bottom: 35px;
        max-width: 700px;
    }
    body.light-mode .room-description { color: #5a4030; }

    /* Unique room features chips */
    .room-unique-features {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 35px;
    }
    .feature-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(212,175,55,0.1);
        border: 1px solid rgba(212,175,55,0.3);
        color: var(--primary-gold);
        font-size: 0.8rem;
        padding: 6px 14px;
        border-radius: 50px;
        font-weight: 500;
    }
    body.light-mode .feature-chip {
        background: rgba(180,140,30,0.08);
        border-color: rgba(160,120,20,0.3);
        color: #9a7020;
    }

    /* Photo Grid - 1 big + 2 small */
    .room-photo-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 360px 240px;
        gap: 12px;
    }
    .room-photo-grid .photo-main {
        grid-column: 1 / -1;
        border-radius: 10px;
        overflow: hidden;
    }
    .room-photo-grid .photo-sm {
        border-radius: 10px;
        overflow: hidden;
    }
    .room-photo-grid img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
        display: block;
    }
    .room-photo-grid .photo-main:hover img,
    .room-photo-grid .photo-sm:hover img { transform: scale(1.03); }

    /* Status badge */
    .room-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin-bottom: 30px;
    }
    .badge-dot { width: 8px; height: 8px; border-radius: 50%; }
    .badge-available .badge-dot { background: #2ecc71; box-shadow: 0 0 8px #2ecc71; }
    .badge-available { color: #2ecc71; }
    .badge-occupied .badge-dot  { background: #e74c3c; box-shadow: 0 0 8px #e74c3c; }
    .badge-occupied  { color: #e74c3c; }

    /* ========= RIGHT: SIDEBAR ========= */
    .booking-sidebar {
        background: rgba(15, 20, 15, 0.92);
        border: 1px solid rgba(212,175,55,0.3);
        border-radius: 16px;
        padding: 30px 28px;
        position: sticky;
        top: 100px;
    }
    body.light-mode .booking-sidebar {
        background: rgba(240,230,200,0.9);
        border-color: rgba(160,120,20,0.35);
    }

    .sidebar-title {
        font-size: 1.4rem;
        color: var(--primary-gold);
        font-family: var(--font-serif);
        margin-bottom: 12px;
    }
    .sidebar-sub {
        color: var(--text-gray);
        font-size: 0.88rem;
        line-height: 1.7;
        margin-bottom: 24px;
    }
    body.light-mode .sidebar-sub { color: #5a4030; }

    .sidebar-amenities { list-style: none; margin-bottom: 28px; }
    .sidebar-amenities li {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 7px 0;
        border-bottom: 1px solid rgba(212,175,55,0.08);
        font-size: 0.88rem;
        color: var(--text-white);
    }
    body.light-mode .sidebar-amenities li { color: #2a1a08; border-bottom-color: rgba(160,120,20,0.12); }
    .sidebar-amenities li i { color: var(--primary-gold); width: 18px; text-align: center; font-size: 0.85rem; }

    .btn-reservar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
        padding: 18px;
        background: var(--primary-gold);
        color: #000;
        font-weight: 800;
        font-size: 0.85rem;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        border: none;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.3s ease;
        text-decoration: none;
        margin-bottom: 20px;
    }
    .btn-reservar:hover { background: #c09b2a; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(212,175,55,0.4); }

    /* Coupon Box */
    .coupon-box {
        background: rgba(10,14,10,0.7);
        border: 1.5px dashed rgba(212,175,55,0.55);
        border-radius: 12px;
        padding: 18px 20px;
        text-align: center;
        margin-bottom: 16px;
        position: relative;
    }
    body.light-mode .coupon-box {
        background: rgba(240,230,200,0.5);
        border-color: rgba(160,120,20,0.55);
    }
    .coupon-label {
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 2.5px;
        color: var(--text-gray);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .coupon-code {
        font-family: monospace;
        font-size: 1.55rem;
        color: var(--primary-gold);
        font-weight: 800;
        letter-spacing: 4px;
        text-shadow: 0 0 12px rgba(212,175,55,0.35);
    }
    .coupon-discount {
        display: inline-block;
        background: rgba(212,175,55,0.15);
        color: var(--primary-gold);
        font-size: 0.72rem;
        font-weight: 600;
        padding: 2px 10px;
        border-radius: 50px;
        margin-top: 6px;
        margin-bottom: 4px;
    }
    .coupon-validity {
        font-size: 0.7rem;
        color: var(--text-gray);
        margin-top: 4px;
    }

    .sidebar-disclaimer {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.75rem;
        color: var(--text-gray);
    }
    body.light-mode .sidebar-disclaimer { color: #7a5a30; }
    .sidebar-disclaimer i { color: var(--primary-gold); }
</style>';

include_once "views/layouts/header.php";
?>

<main>
    <!-- HERO -->
    <div class="room-detail-hero">
        <div class="room-detail-hero-content">
            <a href="index.php?action=habitaciones" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Volver a Habitaciones
            </a>
            <h1 class="serif"><?php echo htmlspecialchars($room['nombre']); ?></h1>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="room-detail-layout">
        <!-- LEFT: MAIN CONTENT -->
        <div class="room-main-content scroll-anim scroll-left">
            <h2 class="serif gold-text" style="font-size:1.8rem; margin-bottom:16px;">Elegancia y Confort</h2>

            <!-- Status -->
            <div class="room-status-badge <?php echo $isAvailable ? 'badge-available' : 'badge-occupied'; ?>">
                <div class="badge-dot"></div>
                <span><?php echo $isAvailable ? 'DISPONIBLE' : 'NO DISPONIBLE'; ?></span>
            </div>

            <!-- Description -->
            <p class="room-description"><?php echo nl2br(htmlspecialchars($room['descripcion'] ?? '')); ?></p>

            <!-- Unique room features (chips) -->
            <?php if (!empty($features)): ?>
            <div class="room-unique-features">
                <?php foreach ($features as $f): ?>
                <div class="feature-chip">
                    <i class="fa-solid <?php echo htmlspecialchars($f['icon']); ?>"></i>
                    <?php echo htmlspecialchars($f['label']); ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Photo Grid: 1 large + 2 small -->
            <div class="room-photo-grid">
                <div class="photo-main">
                    <img src="<?php echo htmlspecialchars($room['imagen'] ?: 'https://lh3.googleusercontent.com/p/AF1QipMIQr7TRGo_LxBf6Y6uQFMdudqUH53FkN0YOmQy=s4000'); ?>" alt="<?php echo htmlspecialchars($room['nombre']); ?>">
                </div>
                <div class="photo-sm">
                    <img src="<?php echo htmlspecialchars($room['imagen'] ?: 'https://lh3.googleusercontent.com/p/AF1QipOUY0uL1F_v_8dGPVe0ynf_3RW2WLzdV7YcKVbK=s4000'); ?>" alt="Vista 2">
                </div>
                <div class="photo-sm">
                    <img src="<?php echo htmlspecialchars($room['imagen'] ?: 'https://lh3.googleusercontent.com/p/AF1QipNszXIbjjvNsY0MnxFayR8jadWS4bkzKXzM4S-V=s4000'); ?>" alt="Vista 3">
                </div>
            </div>
        </div>

        <!-- RIGHT: BOOKING SIDEBAR -->
        <div class="booking-sidebar scroll-anim scroll-right">
            <h3 class="sidebar-title">Reserva tu Estancia</h3>
            <p class="sidebar-sub">Selecciona tus fechas y vive la experiencia del colibrí con los servicios incluidos. Consulta disponibilidad sin compromiso.</p>

            <ul class="sidebar-amenities">
                <?php foreach ($amenities as $a): ?>
                <li>
                    <i class="fa-solid <?php echo $a['icon']; ?>"></i>
                    <?php echo $a['label']; ?>
                </li>
                <?php endforeach; ?>
            </ul>

            <a href="index.php?action=reserva&room=<?php echo $room['id']; ?>" class="btn-reservar">
                <i class="fa-solid fa-calendar-days"></i>
                CONSULTAR DISPONIBILIDAD
            </a>

            <?php if ($coupon): ?>
            <div class="coupon-box">
                <div class="coupon-label">
                    <i class="fa-solid fa-ticket"></i> Cupón de descuento
                </div>
                <div class="coupon-code"><?php echo htmlspecialchars($coupon['codigo']); ?></div>
                <div class="coupon-discount"><?php echo $coupon['descuento']; ?>% de descuento</div>
                <div class="coupon-validity">
                    Válido: <?php
                        $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                        $fi = $meses[intval(date('n', strtotime($coupon['fecha_inicio'])))-1] . ' ' . date('d', strtotime($coupon['fecha_inicio']));
                        $ff = $meses[intval(date('n', strtotime($coupon['fecha_fin'])))-1] . ' ' . date('d, Y', strtotime($coupon['fecha_fin']));
                        echo "$fi - $ff";
                    ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="sidebar-disclaimer">
                <i class="fa-solid fa-shield-halved"></i>
                Sin cargos por cancelación hasta 48h antes.
            </div>
        </div>
    </div>
</main>

<?php include_once "views/layouts/footer.php"; ?>
