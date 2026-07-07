<?php
require_once "config/config.php";
require_once "config/database.php";
require_once "models/Room.php";

$database = new Database();
$db = $database->getConnection();

// Cargar tipos de habitación agrupados
$stmtTypes = $db->query("
    SELECT tipo,
           nombre,
           imagen,
           COUNT(*) AS total,
           SUM(estado = 'disponible') AS disponibles,
           MIN(CASE WHEN estado = 'disponible' THEN id END) AS id_disponible
    FROM habitaciones
    GROUP BY tipo, nombre, imagen
    ORDER BY nombre ASC
");
$roomTypes = $stmtTypes->fetchAll(PDO::FETCH_ASSOC);

// Preselected room from ?room=
$preselected = intval($_GET['room'] ?? 0);

// Precios por persona por noche
$PRICE_ADULT = 10;
$PRICE_CHILD = 5;

$pageTitle = "Reserva tu Estancia | Hotel Centro";
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

    .price-info-box {
        background: rgba(212,175,55,0.07); border: 1px solid rgba(212,175,55,0.3);
        border-radius: 10px; padding: 14px 18px; margin-bottom: 20px;
        display: flex; align-items: center; gap: 12px;
        font-size: 0.85rem; color: var(--primary-gold);
    }
    .price-info-box i { font-size: 1.1rem; }

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
    .bs-total { margin-top:20px; padding-top:20px; border-top:2px solid var(--primary-gold); display:flex; justify-content:space-between; font-size:1.2rem; font-weight:700; color:var(--primary-gold); }
    .bs-note { font-size:0.75rem; color:var(--text-gray); margin-top:16px; display:flex; align-items:center; gap:8px; }
    .bs-note i { color:var(--primary-gold); }

    /* RESPONSIVE */
    @media (max-width: 768px) {
        .booking-layout { padding: 30px 15px 120px; gap: 30px; }
        .booking-form-card { padding: 25px 20px; border-radius: 12px; }
        .bf-row { grid-template-columns: 1fr; gap: 15px; }
        .room-type-grid { grid-template-columns: 1fr 1fr; }
        .booking-summary { position: static; padding: 25px 20px; }
        .btn-confirm { padding: 15px; font-size: 0.85rem; }
    }
    @media (max-width: 480px) {
        .room-type-grid { grid-template-columns: 1fr; }
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <label>Cédula o DNI</label>
                        <input type="text" name="dni" id="f-dni" placeholder="0000000000" required>
                    </div>
                    <div class="bf-group">
                        <label>Teléfono / WhatsApp</label>
                        <input type="tel" name="telefono" id="f-telefono" placeholder="+593 99 000 0000" required>
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
                    <div class="bf-group">
                        <label>Notas adicionales (Opcional)</label>
                        <input type="text" name="notas" id="f-notas" placeholder="Alguna petición especial...">
                    </div>
                </div>

                <!-- ROOM SELECTION -->
                <div class="section-label" style="margin-top:10px;"><i class="fa-solid fa-bed"></i> Selecciona tu Habitación</div>
                <div class="price-info-box">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Tarifa: <strong>$10 USD por persona / por noche</strong> — aplica a todos los tipos de habitación.</span>
                </div>
                <div class="room-type-grid" id="room-type-grid">
                    <?php foreach ($roomTypes as $i => $rt):
                        $availClass = $rt['disponibles'] > 0 ? 'ok' : 'no';
                        $availText  = $rt['disponibles'] > 0 ? $rt['disponibles'] . ' disponibles' : 'Sin disponibilidad';
                        $isDisabled = $rt['disponibles'] == 0 ? 'disabled' : '';
                        $isFirst    = ($i === 0);
                    ?>
                    <label class="room-type-card <?php echo $isFirst ? 'selected' : ''; ?>" data-tipo="<?php echo $rt['tipo']; ?>" data-id="<?php echo $rt['id_disponible']; ?>">
                        <input type="radio" name="habitacion_tipo" value="<?php echo $rt['tipo']; ?>" <?php echo $isFirst ? 'checked' : ''; ?> <?php echo $isDisabled; ?>>
                        <div class="rtc-name"><?php echo htmlspecialchars($rt['nombre']); ?></div>
                        <div class="rtc-price">$10 Adulto / $5 Niño por noche</div>
                        <div class="rtc-avail <?php echo $availClass; ?>">
                            <div class="rtc-dot"></div> <?php echo $availText; ?>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="habitacion_id" id="f-habitacion-id" value="">

                <!-- DATES & GUESTS -->
                <div class="section-label" style="margin-top:10px;"><i class="fa-solid fa-calendar-days"></i> Fechas y Huéspedes</div>
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
                        <label>Adultos ($10/noche)</label>
                        <input type="number" name="adultos" id="f-adultos" value="1" min="1" max="10" onchange="updateSummary()">
                    </div>
                    <div class="bf-group">
                        <label>Niños ($5/noche)</label>
                        <input type="number" name="ninos" id="f-ninos" value="0" min="0" max="10" onchange="updateSummary()">
                    </div>
                    <div class="bf-group">
                        <label>Notas adicionales (Opcional)</label>
                        <input type="text" name="notas" id="f-notas" placeholder="Alguna petición especial...">
                    </div>
                </div>

                <!-- EXTRAS: PARQUEADERO -->
                <div class="section-label" style="margin-top:10px;"><i class="fa-solid fa-car"></i> Servicios Adicionales</div>
                <div style="background: rgba(212,175,55,0.05); border: 1px solid rgba(212,175,55,0.2); border-radius: 12px; padding: 18px; margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 14px; cursor: pointer;">
                        <input type="checkbox" id="f-parking" name="parqueadero" value="1" onchange="updateSummary()" style="width: 20px; height: 20px; accent-color: var(--primary-gold); cursor: pointer; flex-shrink: 0;">
                        <div>
                            <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-white);"><i class="fa-solid fa-parking" style="color: var(--primary-gold); margin-right: 6px;"></i> Parqueadero Privado</div>
                            <div style="font-size: 0.78rem; color: var(--text-gray); margin-top: 3px;">Estacionamiento seguro dentro del hotel &mdash; <strong style="color: var(--primary-gold);">+ $10.00 USD</strong> por estancia</div>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn-confirm" id="btn-submit">
                    <i class="fa-solid fa-calendar-check"></i>
                    CONFIRMAR RESERVA
                </button>
            </form>
        </div>

        <!-- SUMMARY SIDEBAR -->
        <aside class="booking-summary scroll-anim scroll-right">
            <div class="bs-title">Resumen</div>

            <div class="bs-row"><span class="label">Tipo de habitación</span><span class="val" id="s-room">-</span></div>
            <div class="bs-row"><span class="label">Personas</span><span class="val" id="s-guests">1 Adulto</span></div>
            <div class="bs-row"><span class="label">Camas Asignadas</span><span class="val" id="s-beds">1</span></div>
            <div class="bs-row"><span class="label">Noches</span><span class="val" id="s-nights">0</span></div>
            <div class="bs-row"><span class="label">Subtotal habitación</span><span class="val" id="s-subtotal">$0.00</span></div>
            <div class="bs-row" id="s-parking-row" style="display:none;"><span class="label"><i class="fa-solid fa-car" style="font-size:0.75rem;"></i> Parqueadero</span><span class="val">$10.00 USD</span></div>
            <div class="bs-total">
                <span>Total Estancia</span>
                <span id="s-total">$0.00 <small>USD</small></span>
            </div>
            <div class="bs-note"><i class="fa-solid fa-shield-halved"></i> Sin cargos ocultos.</div>
            <div class="bs-note"><i class="fa-brands fa-whatsapp"></i> Confirmaremos por WhatsApp.</div>
        </aside>
    </div>
</main>

<script>
const PRICE_ADULT = <?php echo $PRICE_ADULT; ?>;
const PRICE_CHILD = <?php echo $PRICE_CHILD; ?>;
let selectedLabel = '-';
let selectedTipo  = '';

// Room type card selection
document.querySelectorAll('.room-type-card').forEach(card => {
    card.addEventListener('click', function () {
        if (this.querySelector('input').disabled) return;
        document.querySelectorAll('.room-type-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        this.querySelector('input').checked = true;
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
        selectedLabel = sel.querySelector('.rtc-name').innerText;
        selectedTipo  = sel.dataset.tipo;
        document.getElementById('f-habitacion-id').value = sel.dataset.id;
    }
    const today    = new Date();
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

function updateSummary() {
    const ci     = new Date(document.getElementById('f-checkin').value);
    const co     = new Date(document.getElementById('f-checkout').value);
    const nights = (!isNaN(ci) && !isNaN(co) && co > ci)
        ? Math.ceil((co - ci) / 86400000) : 0;
    const adultos = parseInt(document.getElementById('f-adultos').value) || 1;
    const ninos   = parseInt(document.getElementById('f-ninos').value) || 0;
    const parking = document.getElementById('f-parking').checked ? 10 : 0;
    const subHab  = ((adultos * PRICE_ADULT) + (ninos * PRICE_CHILD)) * (nights || 1);
    const total   = subHab + parking;

    document.getElementById('s-room').innerText     = selectedLabel || '-';
    
    let strGuests = adultos + ' Adulto' + (adultos !== 1 ? 's' : '');
    if (ninos > 0) strGuests += ', ' + ninos + ' Niño' + (ninos !== 1 ? 's' : '');
    document.getElementById('s-guests').innerText   = strGuests;
    document.getElementById('s-beds').innerText     = (adultos + ninos) + ' Cama' + ((adultos + ninos) !== 1 ? 's' : '');
    
    document.getElementById('s-nights').innerText   = nights + ' noche' + (nights !== 1 ? 's' : '');
    document.getElementById('s-subtotal').innerText = '$' + subHab.toFixed(2) + ' USD';
    const parkRow = document.getElementById('s-parking-row');
    if (parkRow) parkRow.style.display = parking > 0 ? 'flex' : 'none';
    document.getElementById('s-total').innerHTML    = '$' + total.toFixed(2) + ' <small>USD</small>';
}

document.getElementById('booking-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const btn = document.getElementById('btn-submit');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';

    const ci     = new Date(document.getElementById('f-checkin').value);
    const co     = new Date(document.getElementById('f-checkout').value);
    const nights  = Math.ceil((co - ci) / 86400000) || 1;
    const adultos = parseInt(document.getElementById('f-adultos').value) || 1;
    const ninos   = parseInt(document.getElementById('f-ninos').value) || 0;
    const parking = document.getElementById('f-parking').checked ? 10 : 0;
    const total   = (((adultos * PRICE_ADULT) + (ninos * PRICE_CHILD)) * nights + parking).toFixed(2);

    const payload = {
        nombre:          document.getElementById('f-nombre').value,
        dni:             document.getElementById('f-dni').value,
        email:           '',
        telefono:        document.getElementById('f-telefono').value,
        habitacion_id:   document.getElementById('f-habitacion-id').value,
        habitacion_tipo: selectedTipo,
        checkin:         document.getElementById('f-checkin').value,
        checkout:        document.getElementById('f-checkout').value,
        huespedes:       adultos + ninos,
        adultos:         adultos,
        ninos:           ninos,
        idioma:          document.getElementById('f-idioma').value,
        notas:           document.getElementById('f-notas').value,
        total:           total,
        nights:          nights,
        room_label:      selectedLabel,
        parqueadero:     parking > 0 ? 1 : 0
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
                title: '¡Reserva Confirmada!',
                html: `<p>Hemos registrado tu reserva <strong>#${data.reserva_id}</strong>.</p>
                       <p style="margin-top:10px;">Nos pondremos en contacto contigo por <strong>WhatsApp</strong> para confirmar los detalles.</p>`,
                icon: 'success',
                background: '#0c100c',
                color: '#fff',
                confirmButtonColor: '#c5a059',
                confirmButtonText: 'Entendido'
            }).then(() => {
                window.location.href = '<?php echo BASE_URL; ?>';
            });
        } else {
            throw new Error(data.message || 'Error desconocido');
        }
    } catch (error) {
        console.error(error);
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-calendar-check"></i> CONFIRMAR RESERVA';
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
