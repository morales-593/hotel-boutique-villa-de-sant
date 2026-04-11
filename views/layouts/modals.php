<!-- Reserva Modal -->
<div id="booking-modal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close" id="modal-close">&times;</button>
        <div class="modal-header">
            <div class="lottie-hummingbird modal-lottie"></div>
            <h2>Reserva tu Estancia</h2>
            <p>Reserva rápida via Email o WhatsApp</p>
        </div>
        <form id="quick-booking-form">
            <div class="modal-grid">
                <div class="form-group full">
                    <label>Nombre Completo</label>
                    <input type="text" id="quick-name" placeholder="Tu nombre" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="quick-email" placeholder="tu@email.com" required>
                </div>
                <div class="form-group">
                    <label>WhatsApp (Opcional)</label>
                    <input type="tel" id="quick-tel" placeholder="+593 9..." required>
                </div>
            </div>
            <button type="submit" class="btn btn-gold btn-full"><span class="btn-text">RESERVAR AHORA</span></button>
        </form>
    </div>
</div>

<!-- Tour Modal -->
<div id="tour-modal" class="modal-overlay" style="display: none; align-items: center; justify-content: center;">
    <div class="modal-content" style="max-width: 450px; background: rgba(17, 17, 17, 0.95); backdrop-filter: blur(10px);">
        <button class="modal-close" id="tour-modal-close" style="top: 15px; right: 20px;">&times;</button>
        <div class="text-center" style="margin-bottom: 25px;">
            <h2 class="gold-text serif" style="font-size: 1.6rem;">Información del Tour</h2>
            <p style="color: var(--text-gray); font-size: 0.9rem;" id="tour-name-display">Reserva de Actividad</p>
        </div>
        
        <form id="tour-wa-form">
            <input type="hidden" id="tour-hidden-name" value="">
            <div class="form-group" style="margin-bottom: 15px;">
                <label style="color:var(--text-white); font-size:0.85rem; margin-bottom:5px; display:block;">Huésped / Referencia</label>
                <input type="text" id="tour-guest-name" placeholder="Ej. Juan Pérez" required style="width: 100%; padding: 12px; background: rgba(0,0,0,0.5); border: 1px solid #333; color: white;">
            </div>
            
            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                <div class="form-group">
                    <label style="color:var(--text-white); font-size:0.85rem; margin-bottom:5px; display:block;">Habitación</label>
                    <input type="text" id="tour-room" placeholder="Ej. Suite Real" required style="width: 100%; padding: 12px; background: rgba(0,0,0,0.5); border: 1px solid #333; color: white;">
                </div>
                <div class="form-group">
                    <label style="color:var(--text-white); font-size:0.85rem; margin-bottom:5px; display:block;">País</label>
                    <input type="text" id="tour-country" placeholder="Ej. Colombia" required style="width: 100%; padding: 12px; background: rgba(0,0,0,0.5); border: 1px solid #333; color: white;">
                </div>
            </div>
            
            <button type="submit" class="btn btn-gold btn-full pulse-anim" style="display: flex; justify-content: center; align-items: center; gap: 10px;">
                Consultar por WhatsApp <i class="fa-brands fa-whatsapp" style="font-size: 1.2rem;"></i>
            </button>
        </form>
    </div>
</div>

<div id="toast" class="toast">Reserva enviada.</div>
