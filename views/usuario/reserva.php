<?php
require_once "config/config.php";
require_once "config/database.php";
require_once "models/Room.php";

$database = new Database();
$db = $database->getConnection();

// Load room types grouped (distinct type + first available unit)
$stmtTypes = $db->query("
    SELECT tipo,
           nombre,
           MIN(precio) AS precio,
           imagen,
           COUNT(*) AS total,
           SUM(estado = 'disponible') AS disponibles,
           MIN(CASE WHEN estado = 'disponible' THEN id END) AS id_disponible
    FROM habitaciones
    GROUP BY tipo, nombre, imagen
    ORDER BY precio ASC
");
$roomTypes = $stmtTypes->fetchAll(PDO::FETCH_ASSOC);

// Preselected room from ?room=
$preselected = intval($_GET['room'] ?? 0);

$pageTitle = "Reserva tu Estancia | Hotel Boutique Villa de Sant";
$extraCSS = '
<style>

    .booking-layout { max-width:1200px; margin:0 auto; padding:50px 30px 80px; display:grid; grid-template-columns:1fr 360px; gap:50px; align-items:start; }
    @media(max-width:1024px){ .booking-layout{grid-template-columns:1fr;} }

    /* FORM CARD */
    .booking-form-card { background: rgba(12,16,12,0.85); border:1px solid rgba(212,175,55,0.2); border-radius:16px; padding:40px; }
    body.light-mode .booking-form-card { background:rgba(245,240,232,0.9); border-color:rgba(160,120,20,0.25); }

    .section-label { font-size:0.7rem; text-transform:uppercase; letter-spacing:3px; color:var(--primary-gold); margin-bottom:20px; display:flex; align-items:center; gap:10px; }
    .section-label::after { content:""; flex:1; height:1px; background:rgba(212,175,55,0.2); }

    .bf-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
    .bf-full { grid-column:1/-1; }
    .bf-group label { display:block; font-size:0.75rem; color:var(--text-gray); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px; }
    body.light-mode .bf-group label { color:#7a5a30; }
    .bf-group input, .bf-group select, .bf-group textarea {
        width:100%; padding:13px 16px; background:rgba(255,255,255,0.04); border:1px solid rgba(212,175,55,0.2);
        color:var(--text-white); border-radius:8px; font-family:var(--font-sans); font-size:0.9rem; transition:border-color 0.3s;
    }
    body.light-mode .bf-group input, body.light-mode .bf-group select, body.light-mode .bf-group textarea {
        background:rgba(255,250,240,0.8); color:#1a110a; border-color:rgba(160,120,20,0.25);
    }
    .bf-group input:focus, .bf-group select:focus { outline:none; border-color:var(--primary-gold); }
    .bf-group select option { background:#111; }

    .room-type-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:12px; margin-bottom:20px; }
    .room-type-card {
        border:1px solid rgba(212,175,55,0.2); border-radius:10px; padding:14px; cursor:pointer;
        transition:all 0.3s ease; background:rgba(255,255,255,0.02); position:relative;
    }
    .room-type-card:hover { border-color:rgba(212,175,55,0.6); background:rgba(212,175,55,0.05); }
    .room-type-card.selected { border-color:var(--primary-gold); background:rgba(212,175,55,0.1); }
    .room-type-card input[type="radio"] { position:absolute; opacity:0; width:0; height:0; }
    .rtc-name { font-size:0.82rem; font-weight:600; color:var(--text-white); margin-bottom:4px; }
    body.light-mode .rtc-name { color:#1a110a; }
    .rtc-price { font-size:0.75rem; color:var(--primary-gold); }
    .rtc-avail { font-size:0.68rem; margin-top:6px; display:flex; align-items:center; gap:5px; }
    .rtc-avail.ok { color:#2ecc71; }
    .rtc-avail.no { color:#e74c3c; }
    .rtc-dot { width:6px; height:6px; border-radius:50%; background:currentColor; }
    .rtc-badge {
        position:absolute; top:8px; right:8px;
        background:var(--primary-gold); color:#000; font-size:0.58rem;
        font-weight:800; padding:2px 6px; border-radius:50px; letter-spacing:1px;
    }

    .coupon-row { display:flex; gap:10px; }
    .coupon-row input { flex:1; }
    .btn-validate {
        padding:12px 18px; background:transparent; border:1px solid var(--primary-gold);
        color:var(--primary-gold); border-radius:8px; cursor:pointer; font-size:0.78rem;
        font-weight:700; letter-spacing:1px; text-transform:uppercase; white-space:nowrap; transition:all 0.3s;
    }
    .btn-validate:hover { background:var(--primary-gold); color:#000; }
    #coupon-status { font-size:0.8rem; margin-top:8px; }

    .btn-confirm {
        width:100%; padding:18px; background:var(--primary-gold); color:#000;
        font-weight:800; font-size:0.9rem; letter-spacing:2px; text-transform:uppercase;
        border:none; border-radius:8px; cursor:pointer; margin-top:30px;
        display:flex; align-items:center; justify-content:center; gap:12px;
        transition:all 0.3s ease;
    }
    .btn-confirm:hover { background:#c09b2a; transform:translateY(-2px); box-shadow:0 8px 20px rgba(212,175,55,0.4); }
    .btn-confirm:disabled { opacity:0.5; cursor:not-allowed; transform:none; }

    /* SUMMARY SIDEBAR */
    .booking-summary {
        background:rgba(12,16,12,0.92); border:1px solid rgba(212,175,55,0.3);
        border-radius:16px; padding:30px 28px; position:sticky; top:100px;
    }
    body.light-mode .booking-summary { background:rgba(240,230,200,0.92); border-color:rgba(160,120,20,0.35); }
    .bs-title { font-size:1.3rem; color:var(--primary-gold); font-family:var(--font-serif); margin-bottom:20px; }
    .bs-row { display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid rgba(212,175,55,0.08); font-size:0.88rem; }
    .bs-row .label { color:var(--text-gray); }
    body.light-mode .bs-row .label { color:#7a5a30; }
    .bs-row .val { color:var(--text-white); font-weight:600; }
    body.light-mode .bs-row .val { color:#1a110a; }
    .bs-discount { color:#2ecc71 !important; }
    .bs-total { margin-top:20px; padding-top:20px; border-top:2px solid var(--primary-gold); display:flex; justify-content:space-between; font-size:1.2rem; font-weight:700; color:var(--primary-gold); }
    .bs-note { font-size:0.75rem; color:var(--text-gray); margin-top:16px; display:flex; align-items:center; gap:8px; }
    .bs-note i { color:var(--primary-gold); }

    /* RESPONSIVE ADJUSTMENTS for Reserva */
    @media (max-width: 768px) {
        .booking-layout { padding: 30px 15px 120px; gap: 30px; }
        .booking-form-card { padding: 25px 20px; border-radius: 12px; }
        .bf-row { grid-template-columns: 1fr; gap: 15px; }
        .section-label { font-size: 0.65rem; letter-spacing: 2px; }
        .room-type-grid { grid-template-columns: 1fr 1fr; }
        .booking-summary { position: static; padding: 25px 20px; }
        .btn-confirm { padding: 15px; font-size: 0.85rem; }
    }

    @media (max-width: 480px) {
        .room-type-grid { grid-template-columns: 1fr; }
        .page-hero-sub { font-size: 0.85rem; }
        .bf-group input, .bf-group select { padding: 12px; font-size: 0.85rem; }
    }

    /* ADDITIONAL SERVICES BOXES */
    .extra-services-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 10px; }
    .extra-service-card {
        background: rgba(255,255,255,0.03); border: 1px solid rgba(212,175,55,0.2);
        padding: 15px; border-radius: 10px; cursor: pointer; transition: all 0.3s;
        display: flex; align-items: center; gap: 12px; position: relative;
    }
    .extra-service-card:hover { border-color: rgba(212,175,55,0.5); background: rgba(212,175,55,0.05); }
    .extra-service-card.active { border-color: var(--primary-gold); background: rgba(212,175,55,0.12); }
    .extra-service-card i { font-size: 1.2rem; color: var(--primary-gold); width: 25px; text-align: center; }
    .extra-service-info { flex: 1; }
    .esc-title { font-size: 0.82rem; font-weight: 700; color: var(--text-white); margin-bottom: 2px; }
    body.light-mode .esc-title { color: #1a110a; }
    .esc-sub { font-size: 0.68rem; color: var(--text-gray); }
    .extra-service-card input { position: absolute; opacity: 0; }
    
    @media (max-width: 600px) { .extra-services-grid { grid-template-columns: 1fr; } }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://sdk.mercadopago.com/js/v2"></script>
';

include_once "views/layouts/header.php";
?>

<main>
    <!-- HERO -->
    <section class="page-hero">
        <div class="page-hero-bg" style="background-image: url('<?php echo BASE_URL; ?>assets/img/home/hero_home.jpg')"></div>
        <div class="page-hero-overlay"></div>
        <div class="page-hero-content">
             <img src="<?php echo BASE_URL; ?>assets/img/logo.png" alt="Logo" class="hero-logo-glow" style="max-width: 70px;">
             <div class="page-hero-divider"></div>
             <h1 class="page-hero-title serif gold-text">Reserva tu Estancia</h1>
             <p class="page-hero-sub">Completa el formulario y nos contactaremos contigo de inmediato para confirmar tu visita.</p>
        </div>
    </section>

    <div class="booking-layout">

        <!-- FORM -->
        <div class="booking-form-card scroll-anim scroll-left">
            <form id="booking-form" novalidate>

                <!-- PERSONAL INFO -->
                <div class="section-label"><i class="fa-solid fa-user"></i> Información Personal</div>
                <div class="bf-row">
                    <div class="bf-group">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre" id="f-nombre" placeholder="Juan Pérez" required>
                    </div>
                    <div class="bf-group">
                        <label>Teléfono / WhatsApp</label>
                        <input type="tel" name="telefono" id="f-telefono" placeholder="+593 99 000 0000" required>
                    </div>
                    <div class="bf-group">
                        <label>Correo electrónico</label>
                        <input type="email" name="email" id="f-email" placeholder="correo@dominio.com" required>
                    </div>
                    <div class="bf-group">
                        <label>Idioma Preferido</label>
                        <select name="idioma" id="f-idioma">
                            <option value="es" selected>Español</option>
                            <option value="en">Inglés (English)</option>
                            <option value="fr">Francés (Français)</option>
                            <option value="de">Alemán (Deutsch)</option>
                        </select>
                    </div>
                </div>

                <!-- ROOM SELECTION -->
                <div class="section-label" style="margin-top:10px;"><i class="fa-solid fa-bed"></i> Selecciona tu Habitación</div>
                <div class="room-type-grid" id="room-type-grid">
                    <?php foreach ($roomTypes as $rt):
                        $availClass = $rt['disponibles'] > 0 ? 'ok' : 'no';
                        $availText  = $rt['disponibles'] > 0 ? $rt['disponibles'] . ' disponibles' : 'Sin disponibilidad';
                        $isDisabled = $rt['disponibles'] == 0 ? 'disabled' : '';
                        $typeLabels = [
                            'single'     => 'Single',
                            'queen'      => 'Queen',
                            'two_beds'   => 'Two Beds',
                            'three_beds' => 'Three Beds',
                            'suite'      => 'Suite',
                        ];
                        $shortLabel = $typeLabels[$rt['tipo']] ?? ucfirst($rt['tipo']);
                    ?>
                    <label class="room-type-card <?php echo ($rt['tipo'] == 'suite') ? 'selected' : ''; ?>" data-tipo="<?php echo $rt['tipo']; ?>" data-price="<?php echo $rt['precio']; ?>" data-id="<?php echo $rt['id_disponible']; ?>">
                        <input type="radio" name="habitacion_tipo" value="<?php echo $rt['tipo']; ?>" <?php echo ($rt['tipo'] == 'suite') ? 'checked' : ''; ?> <?php echo $isDisabled; ?>>
                        <div class="rtc-name"><?php echo htmlspecialchars($rt['nombre']); ?></div>
                        <div class="rtc-price">$<?php echo number_format($rt['precio'], 0); ?>/noche</div>
                        <div class="rtc-avail <?php echo $availClass; ?>">
                            <div class="rtc-dot"></div> <?php echo $availText; ?>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="habitacion_id" id="f-habitacion-id" value="">

                <!-- DATES -->
                <div class="section-label" style="margin-top:10px;"><i class="fa-solid fa-calendar-days"></i> Fechas de Estancia</div>
                <div class="bf-row">
                    <div class="bf-group">
                        <label>Check-in</label>
                        <input type="date" name="checkin" id="f-checkin" required>
                    </div>
                    <div class="bf-group">
                        <label>Check-out</label>
                        <input type="date" name="checkout" id="f-checkout" required>
                    </div>
                    <div class="bf-group">
                        <label>Número de huéspedes</label>
                        <input type="number" name="huespedes" id="f-huespedes" value="1" min="1" max="6">
                    </div>
                    <div class="bf-group">
                        <label>Notas adicionales (Opcional)</label>
                        <input type="text" name="notas" id="f-notas" placeholder="Alguna petición especial...">
                    </div>
                    <!-- COUPON -->
                    <div class="bf-group bf-full">
                        <label>Código de Descuento (Opcional)</label>
                        <div class="coupon-row">
                            <input type="text" id="f-cupon" placeholder="Ej. SUITE30SAN" style="text-transform:uppercase;">
                            <button type="button" class="btn-validate" onclick="verifyCoupon()">
                                <i class="fa-solid fa-tag"></i> Validar
                            </button>
                        </div>
                        <p id="coupon-status"></p>
                    </div>
                </div>

                <!-- ADDITIONAL SERVICES -->
                <div class="section-label" style="margin-top:10px;"><i class="fa-solid fa-wand-magic-sparkles"></i> Servicios Adicionales (Opcional)</div>
                <div class="extra-services-grid">
                    <label class="extra-service-card" onclick="toggleExtra(this)">
                        <input type="checkbox" name="extra_transporte" id="f-extra-transporte" onchange="updateSummary()">
                        <i class="fa-solid fa-van-shuttle"></i>
                        <div class="extra-service-info">
                            <div class="esc-title">Transporte de Lujo</div>
                            <div class="esc-sub">Traslado privado al Aeropuerto</div>
                        </div>
                    </label>
                    <label class="extra-service-card" onclick="toggleExtra(this)">
                        <input type="checkbox" name="extra_tour" id="f-extra-tour" onchange="updateSummary()">
                        <i class="fa-solid fa-map-location-dot"></i>
                        <div class="extra-service-info">
                            <div class="esc-title">Tours y Aventuras</div>
                            <div class="esc-sub">Explora Quito y sus alrededores</div>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn-confirm" id="btn-submit">
                    <i class="fa-solid fa-credit-card"></i>
                    PAGAR CON VISA / MASTERCARD (USD)
                </button>
            </form>
        </div>

        <!-- SUMMARY SIDEBAR -->
        <aside class="booking-summary scroll-anim scroll-right">
            <div class="bs-title">Resumen</div>
            
            <div class="bf-group" style="margin-bottom: 20px;">
                <label style="color: var(--primary-gold); font-size: 0.65rem;">MONEDA DE PAGO / CURRENCY</label>
                <select id="f-currency" onchange="updateSummary()" style="background: rgba(212,175,55,0.1); border-color: var(--primary-gold); font-weight: bold;">
                    <option value="USD">USD - Dólar Estadounidense</option>
                    <option value="EUR">EUR - Euro</option>
                    <option value="COP">COP - Peso Colombiano</option>
                    <option value="MXN">MXN - Peso Mexicano</option>
                    <option value="PEN">PEN - Sol Peruano</option>
                </select>
            </div>

            <div class="bs-row"><span class="label">Tipo de habitación</span><span class="val" id="s-room">-</span></div>
            <div class="bs-row"><span class="label">Precio por noche</span><span class="val" id="s-price">$0</span></div>
            <div class="bs-row"><span class="label">Noches</span><span class="val" id="s-nights">0</span></div>
            <div class="bs-row"><span class="label">Subtotal</span><span class="val" id="s-subtotal">$0.00</span></div>
            <div class="bs-row" id="s-disc-row" style="display:none;">
                <span class="label bs-discount">Descuento cupón</span>
                <span class="val bs-discount" id="s-disc">-$0.00</span>
            </div>
            <div class="bs-total">
                <span>Total Estancia</span>
                <span id="s-total">$0.00 <small>USD</small></span>
            </div>
            <div id="s-extras-box" style="margin-top: 15px; display: none;">
                <div style="font-size: 0.7rem; color: var(--primary-gold); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;">EXTRAS SOLICITADOS:</div>
                <div id="s-extras-list" style="font-size: 0.8rem; color: var(--text-white);"></div>
            </div>
            <div class="bs-note"><i class="fa-solid fa-shield-halved"></i> Sin cargos hasta 48h antes.</div>
            <div class="bs-note"><i class="fa-brands fa-whatsapp"></i> Confirmaremos por WhatsApp.</div>
        </aside>
    </div>
</main>

<script>
let currentDiscount = 0;
let selectedPrice   = 0;
let selectedLabel   = '-';
let selectedTipo    = '';

// Room type card selection
document.querySelectorAll('.room-type-card').forEach(card => {
    card.addEventListener('click', function () {
        if (this.querySelector('input').disabled) return;
        document.querySelectorAll('.room-type-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input').checked = true;
        selectedPrice = parseFloat(this.dataset.price);
        selectedLabel = this.querySelector('.rtc-name').innerText;
        selectedTipo  = this.dataset.tipo;
        document.getElementById('f-habitacion-id').value = this.dataset.id;
        updateSummary();
    });
});

// Init with first selected
(function () {
    const sel = document.querySelector('.room-type-card.selected');
    if (sel) {
        selectedPrice = parseFloat(sel.dataset.price);
        selectedLabel = sel.querySelector('.rtc-name').innerText;
        selectedTipo  = sel.dataset.tipo;
        document.getElementById('f-habitacion-id').value = sel.dataset.id;
    }
    const today = new Date();
    const tomorrow = new Date(today); tomorrow.setDate(today.getDate() + 1);
    const fmt = d => d.toISOString().split('T')[0];
    document.getElementById('f-checkin').value  = fmt(today);
    document.getElementById('f-checkout').value = fmt(tomorrow);
    document.getElementById('f-checkin').min  = fmt(today);
    document.getElementById('f-checkout').min = fmt(tomorrow);
    updateSummary();
})();

document.getElementById('f-checkin').addEventListener('change', function(){
    const next = new Date(this.value); next.setDate(next.getDate() + 1);
    document.getElementById('f-checkout').min = next.toISOString().split('T')[0];
    updateSummary();
});
document.getElementById('f-checkout').addEventListener('change', updateSummary);

function toggleExtra(card) {
    const cb = card.querySelector('input');
    cb.checked = !cb.checked;
    card.classList.toggle('active', cb.checked);
    updateSummary();
}

function updateSummary() {
    const ci = new Date(document.getElementById('f-checkin').value);
    const co = new Date(document.getElementById('f-checkout').value);
    const nights = (!isNaN(ci) && !isNaN(co) && co > ci)
        ? Math.ceil((co - ci) / 86400000) : 0;

    const currency = document.getElementById('f-currency').value;
    const rates = { 'USD': 1, 'EUR': 0.94, 'COP': 3900, 'MXN': 17.5, 'PEN': 3.75 };
    const rate = rates[currency] || 1;

    const base    = (selectedPrice * (nights || 1)) * rate;
    const discAmt = base * (currentDiscount / 100);
    const total   = base - discAmt;

    document.getElementById('s-room').innerText    = selectedLabel || '-';
    document.getElementById('s-price').innerText   = (selectedPrice * rate).toFixed(2) + ' ' + currency;
    document.getElementById('s-nights').innerText  = nights + ' noche' + (nights !== 1 ? 's' : '');
    document.getElementById('s-subtotal').innerText = base.toFixed(2) + ' ' + currency;
    document.getElementById('s-total').innerText   = total.toFixed(2) + ' ' + currency;

    const discRow = document.getElementById('s-disc-row');
    if (currentDiscount > 0) {
        discRow.style.display = 'flex';
        document.getElementById('s-disc').innerText = '-' + discAmt.toFixed(2) + ' ' + currency + ' (' + currentDiscount + '%)';
    } else {
        discRow.style.display = 'none';
    }

    // Update Extras in summary
    const extraTrans = document.getElementById('f-extra-transporte').checked;
    const extraTour = document.getElementById('f-extra-tour').checked;
    const extrasBox = document.getElementById('s-extras-box');
    const extrasList = document.getElementById('s-extras-list');
    
    let html = '';
    if (extraTrans) html += '• Servicio de Transporte<br>';
    if (extraTour) html += '• Tours y Aventuras<br>';
    
    if (html) {
        extrasBox.style.display = 'block';
        extrasList.innerHTML = html;
    } else {
        extrasBox.style.display = 'none';
    }
}

function verifyCoupon() {
    const code = document.getElementById('f-cupon').value.trim().toUpperCase();
    const status = document.getElementById('coupon-status');
    if (!code) { status.innerText = 'Ingresa un código.'; status.style.color = '#e74c3c'; return; }

    fetch(`<?php echo BASE_URL; ?>api/validate_coupon.php?code=${code}&tipo=${selectedTipo}`)
        .then(r => r.json())
        .then(d => {
            if (d.valid) {
                currentDiscount = d.discount;
                status.innerText = `✓ Cupón válido: ${d.discount}% de descuento aplicado.`;
                status.style.color = '#2ecc71';
            } else {
                currentDiscount = 0;
                status.innerText = d.message || 'Cupón no válido o expirado.';
                status.style.color = '#e74c3c';
            }
            updateSummary();
        });
}

document.getElementById('booking-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';

    const ci = new Date(document.getElementById('f-checkin').value);
    const co = new Date(document.getElementById('f-checkout').value);
    const nights = Math.ceil((co - ci) / 86400000) || 1;
    const base   = selectedPrice * nights;
    const total  = base - (base * currentDiscount / 100);

    const payload = {
        nombre:       document.getElementById('f-nombre').value,
        email:        document.getElementById('f-email').value,
        telefono:     document.getElementById('f-telefono').value,
        habitacion_id: document.getElementById('f-habitacion-id').value,
        habitacion_tipo: selectedTipo,
        checkin:      document.getElementById('f-checkin').value,
        checkout:     document.getElementById('f-checkout').value,
        huespedes:    document.getElementById('f-huespedes').value,
        idioma:       document.getElementById('f-idioma').value,
        notas:        document.getElementById('f-notas').value,
        cupon:        document.getElementById('f-cupon').value.toUpperCase(),
        descuento:    currentDiscount,
        total:        total.toFixed(2),
        currency:     currency,
        nights:       nights,
        room_label:   selectedLabel,
        extra_transporte: document.getElementById('f-extra-transporte').checked,
        extra_tour:       document.getElementById('f-extra-tour').checked
    };

    try {
        const res  = await fetch('<?php echo BASE_URL; ?>api/reservar.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                title: '¡Reserva Registrada!',
                text: 'Ahora te redirigiremos a la pasarela de pago segura para completar tu reserva con Visa o cualquier tarjeta.',
                icon: 'info',
                background: '#0c100c',
                color: '#fff',
                confirmButtonColor: '#c5a059',
                confirmButtonText: 'Ir a Pagar'
            }).then(() => {
                // Redirigir a Mercado Pago
                if (data.init_point) {
                    window.location.href = data.init_point;
                } else {
                    Swal.fire('Error', 'No se pudo generar el enlace de pago. Por favor intente más tarde.', 'error');
                }
            });
        } else {
            throw new Error(data.message || 'Error desconocido');
        }
    } catch (error) {
        console.error(error);
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-brands fa-whatsapp"></i> CONFIRMAR RESERVA';
        Swal.fire({
            title: 'Error',
            text: 'No pudimos procesar tu reserva: ' + error.message,
            icon: 'error',
            background: '#0c100c',
            color: '#fff',
            confirmButtonColor: '#e74c3c'
        });
    }
});
</script>

<?php include_once "views/layouts/footer.php"; ?>
