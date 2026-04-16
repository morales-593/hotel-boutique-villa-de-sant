<?php
require_once "config/config.php";
require_once "config/database.php";

$db = (new Database())->getConnection();

$success_json = null;

// Actualizar información del tipo de habitación
if (isset($_POST['update_room_info'])) {
    $tipo = $_POST['tipo'];
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = floatval($_POST['precio']);
    $imagen_actual = $_POST['imagen_actual'];
    $nueva_imagen = $imagen_actual;

    // 1. Manejo de Características (Texto a JSON)
    $features_raw = $_POST['caracteristicas'] ?? '';
    $features_array = array_filter(array_map('trim', explode("\n", $features_raw)));
    $caracteristicas_json = json_encode(array_values($features_array));

    // 2. Manejo de Imagen Principal
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['imagen']['tmp_name'];
        $file_name = $_FILES['imagen']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed)) {
            $upload_dir = 'assets/img/uploads/rooms/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $final_name = 'room_' . $tipo . '_' . time() . '.' . $ext;
            $destination = $upload_dir . $final_name;

            if (move_uploaded_file($file_tmp, $destination)) {
                // Borrar imagen anterior si existe y no es remota
                if ($imagen_actual && strpos($imagen_actual, 'http') === false && file_exists($imagen_actual)) {
                    unlink($imagen_actual);
                }
                $nueva_imagen = $destination;
            }
        }
    }

    // 3. Manejo de Galería (3 fotos)
    $roomTypeFolder = "assets/img/" . str_replace('_', ' ', $tipo);
    if (!is_dir($roomTypeFolder)) mkdir($roomTypeFolder, 0777, true);

    for ($i = 1; $i <= 3; $i++) {
        $field = "gallery_$i";
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            // Borrar archivos anteriores de este slot
            $existingFiles = glob($roomTypeFolder . "/gallery{$i}_*.*");
            foreach ($existingFiles as $ef) unlink($ef);

            $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $gal_name = "gallery{$i}_" . time() . "." . $ext;
                move_uploaded_file($_FILES[$field]['tmp_name'], $roomTypeFolder . "/" . $gal_name);
            }
        }
    }
    
    $stmt = $db->prepare("UPDATE habitaciones SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, caracteristicas = ? WHERE tipo = ?");
    if ($stmt->execute([$nombre, $descripcion, $precio, $nueva_imagen, $caracteristicas_json, $tipo])) {
        $success_json = json_encode([
            'title' => '¡Actualización Exitosa!',
            'html' => "La categoría <span style='color:var(--primary);font-weight:700;'>$nombre</span> ha sido actualizada correctamente.",
            'icon' => 'success'
        ]);
    }
}

// Obtener categorías únicas con características
$stmt = $db->query("SELECT tipo, nombre, descripcion, precio, imagen, caracteristicas FROM habitaciones GROUP BY tipo, nombre, descripcion, precio, imagen, caracteristicas ORDER BY precio ASC");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once "views/layouts/admin_header.php";
?>

<style>
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
        gap: 25px;
        margin-top: 30px;
    }
    
    .info-card {
        background: var(--card-bg);
        border: 1px solid var(--gold-border);
        border-radius: 20px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
    }
    
    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--gold-glow);
    }
    
    .info-card-header {
        height: 200px;
        background-size: cover;
        background-position: center;
        position: relative;
        cursor: pointer;
        transition: opacity 0.3s;
    }
    
    .info-card-header:hover {
        opacity: 0.9;
    }
    
    .info-card-header::after {
        content: '\f03e  Cambiar Imagen';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .info-card-header:hover::after {
        opacity: 1;
    }
    
    .info-card-header .type-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: var(--primary);
        color: black;
        padding: 5px 12px;
        border-radius: 50px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        z-index: 2;
    }
    
    .info-card-body {
        padding: 25px;
        flex: 1;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        color: var(--primary);
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        font-weight: 600;
    }
    
    .form-group input, 
    .form-group textarea {
        width: 100%;
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(197, 160, 89, 0.2);
        color: white;
        padding: 12px;
        border-radius: 10px;
        font-size: 0.85rem;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus, 
    .form-group textarea:focus {
        border-color: var(--primary);
        outline: none;
    }
    
    .price-input-container {
        position: relative;
    }
    
    .price-input-container::before {
        content: '$';
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary);
        font-weight: 600;
    }
    
    .price-input-container input {
        padding-left: 25px;
    }

    /* Galería management */
    .gallery-preview-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 10px;
        margin-top: 15px;
    }
    .gallery-slot {
        aspect-ratio: 1;
        border-radius: 8px;
        background: rgba(0,0,0,0.3);
        border: 1px dashed rgba(212,175,55,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }
    .gallery-slot img { width: 100%; height: 100%; object-fit: cover; }
    .gallery-slot:hover { border-color: var(--primary); }
    .gallery-slot i { color: rgba(212,175,55,0.5); font-size: 1.2rem; }
    
    .file-input-hidden { display: none; }

    /* Modal Styles */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.85);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        padding: 20px;
        backdrop-filter: blur(8px);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .modal-overlay.active {
        opacity: 1;
    }
    .modal-content {
        background: linear-gradient(145deg, #151921, #1a1f2e);
        border: 1px solid var(--gold-border);
        border-radius: 20px;
        max-width: 720px;
        width: 100%;
        padding: 35px;
        position: relative;
        box-shadow: 0 0 60px rgba(197, 160, 89, 0.15), 0 25px 50px rgba(0,0,0,0.5);
        transform: scale(0.9) translateY(20px);
        transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        max-height: 90vh;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .modal-overlay.active .modal-content {
        transform: scale(1) translateY(0);
    }
    .modal-close {
        position: absolute;
        top: 18px;
        right: 18px;
        color: #666;
        cursor: pointer;
        font-size: 1.2rem;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
        z-index: 2;
    }
    .modal-close:hover {
        background: rgba(197, 160, 89, 0.15);
        color: var(--primary);
    }
    .modal-header {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
    }
    .modal-header-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, rgba(197,160,89,0.2), rgba(197,160,89,0.05));
        border: 1px solid rgba(197,160,89,0.3);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        font-size: 1.2rem;
    }
    .modal-header-text h3 {
        font-size: 1.4rem;
        margin: 0;
    }
    .modal-header-text p {
        font-size: 0.8rem;
        color: #666;
        margin: 3px 0 0;
    }

    /* Search */
    .icon-search-wrap {
        position: relative;
        margin-bottom: 20px;
    }
    .icon-search-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #555;
        font-size: 0.85rem;
    }
    .icon-search-wrap input {
        width: 100%;
        background: rgba(0,0,0,0.35);
        border: 1px solid rgba(197,160,89,0.15);
        color: white;
        padding: 11px 15px 11px 40px;
        border-radius: 12px;
        font-size: 0.85rem;
        transition: border-color 0.3s;
    }
    .icon-search-wrap input::placeholder { color: #555; }
    .icon-search-wrap input:focus {
        outline: none;
        border-color: var(--primary);
    }

    /* Scrollable body */
    .icon-modal-body {
        overflow-y: auto;
        flex: 1;
        padding-right: 8px;
    }
    .icon-modal-body::-webkit-scrollbar { width: 4px; }
    .icon-modal-body::-webkit-scrollbar-track { background: transparent; }
    .icon-modal-body::-webkit-scrollbar-thumb {
        background: rgba(197,160,89,0.3);
        border-radius: 10px;
    }

    /* Category sections */
    .icon-category {
        margin-bottom: 22px;
    }
    .icon-category-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: var(--primary);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .icon-category-title::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(to right, rgba(197,160,89,0.3), transparent);
    }

    /* Icon grid */
    .icon-grid-modal {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }
    .icon-item-helper {
        background: rgba(255,255,255,0.02);
        border: 1px solid rgba(255,255,255,0.05);
        padding: 10px 12px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    .icon-item-helper::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(197,160,89,0.1), transparent);
        opacity: 0;
        transition: opacity 0.2s;
    }
    .icon-item-helper:hover {
        border-color: rgba(197,160,89,0.4);
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    }
    .icon-item-helper:hover::before { opacity: 1; }
    .icon-item-helper.copied {
        border-color: #4ade80 !important;
        background: rgba(74,222,128,0.08) !important;
    }
    .icon-item-helper.copied i.fas,
    .icon-item-helper.copied i.fab { color: #4ade80 !important; }
    .icon-item-helper i.fas,
    .icon-item-helper i.fab {
        color: var(--primary);
        width: 22px;
        text-align: center;
        font-size: 1rem;
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }
    .icon-item-helper .icon-name {
        font-size: 0.78rem;
        color: #ccc;
        position: relative;
        z-index: 1;
        white-space: nowrap;
    }
    .icon-item-helper .icon-code {
        font-family: 'Courier New', monospace;
        font-size: 0.65rem;
        color: #666;
        background: rgba(0,0,0,0.4);
        padding: 2px 7px;
        border-radius: 4px;
        margin-left: auto;
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }

    /* Footer */
    .modal-footer {
        margin-top: 20px;
        padding-top: 18px;
        border-top: 1px solid rgba(197,160,89,0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-footer-hint {
        font-size: 0.75rem;
        color: #555;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .modal-footer-hint i { color: var(--primary); }

    /* No results */
    .icon-no-results {
        display: none;
        text-align: center;
        padding: 40px 20px;
        color: #555;
    }
    .icon-no-results i {
        font-size: 2rem;
        margin-bottom: 10px;
        color: #444;
        display: block;
    }

    .icon-btn-help {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(212,175,55,0.1);
        border: 1px solid rgba(212,175,55,0.3);
        color: var(--primary);
        padding: 5px 12px;
        border-radius: 5px;
        font-size: 0.7rem;
        cursor: pointer;
        margin-top: 8px;
        font-weight: 700;
        transition: all 0.3s;
    }
    .icon-btn-help:hover {
        background: var(--primary);
        color: black;
    }

    @media (max-width: 600px) {
        .icon-grid-modal { grid-template-columns: repeat(2, 1fr); }
        .modal-content { padding: 20px; }
    }
</style>

<div class="admin-container" style="color: white; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px;">
        <div>
            <h2 class="serif gold-text" style="font-size: 2.2rem; margin: 0;">Información y Precios</h2>
            <p style="color: #888; margin: 5px 0 0;">Configura los detalles públicos, tarifas y fotografías de cada categoría.</p>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <div class="icon-btn-help" onclick="openIconModal()" style="margin-top: 0; padding: 10px 18px; display: flex;">
                <i class="fas fa-icons"></i> GUÍA DE ICONOS
            </div>
            <a href="index.php?action=admin-habitaciones" class="btn-gold" style="text-decoration: none;">
                <i class="fas fa-bed"></i> GESTIONAR UNIDADES
            </a>
        </div>
    </div>

    <div class="info-grid">
        <?php foreach ($categorias as $cat): ?>
        <div class="info-card">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="tipo" value="<?php echo $cat['tipo']; ?>">
                <input type="hidden" name="imagen_actual" value="<?php echo $cat['imagen']; ?>">
                <input type="hidden" name="update_room_info" value="1">
                
                <!-- Header acts as trigger for file input -->
                <div class="info-card-header" 
                     style="background-image: url('<?php echo $cat['imagen'] . '?v=' . time(); ?>');" 
                     onclick="this.nextElementSibling.click()">
                    <span class="type-badge"><?php echo $cat['tipo']; ?></span>
                </div>
                <input type="file" name="imagen" class="file-input-hidden" onchange="previewImage(this)">

                <div class="info-card-body">
                    <div class="form-group">
                        <label>Nombre Público</label>
                        <input type="text" name="nombre" value="<?php echo htmlspecialchars($cat['nombre']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción de la Vista</label>
                        <textarea name="descripcion" rows="3" style="resize: none;" required><?php echo htmlspecialchars($cat['descripcion']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Servicios Incluidos (icono:nombre por línea)</label>
                        <?php 
                            $feats = json_decode($cat['caracteristicas'] ?? '[]', true);
                            $feats_text = is_array($feats) ? implode("\n", $feats) : '';
                        ?>
                        <textarea name="caracteristicas" rows="4" style="resize: none; font-family: monospace; font-size: 0.75rem;" placeholder="fa-wifi:Wi-Fi Express&#10;fa-snowflake:Aire Acondicionado"><?php echo htmlspecialchars($feats_text); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Fotos de la Galería (3 imágenes)</label>
                        <div class="gallery-preview-grid">
                            <?php 
                            $roomTypeFolder = "assets/img/" . str_replace('_', ' ', $cat['tipo']);
                            for ($i = 1; $i <= 3; $i++): 
                                $files = glob($roomTypeFolder . "/gallery{$i}_*.*");
                                $imgSrc = !empty($files) ? $files[0] : '';
                            ?>
                            <div class="gallery-slot" onclick="this.nextElementSibling.click()">
                                <?php if ($imgSrc): ?>
                                    <img src="<?php echo $imgSrc . '?v=' . time(); ?>">
                                <?php else: ?>
                                    <i class="fas fa-plus"></i>
                                <?php endif; ?>
                            </div>
                            <input type="file" name="gallery_<?php echo $i; ?>" class="file-input-hidden" onchange="previewGalleryImage(this)">
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Precio por Noche</label>
                            <div class="price-input-container">
                                <input type="number" name="precio" value="<?php echo $cat['precio']; ?>" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="form-group" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn-gold" style="width: 100%; justify-content: center; height: 42px;">
                                <i class="fas fa-save"></i> GUARDAR
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ICON HELPER MODAL -->
<div class="modal-overlay" id="iconModal">
    <div class="modal-content">
        <div class="modal-close" onclick="closeIconModal()"><i class="fas fa-times"></i></div>
        
        <div class="modal-header">
            <div class="modal-header-icon"><i class="fas fa-icons"></i></div>
            <div class="modal-header-text">
                <h3 class="serif gold-text">Guía de Iconos</h3>
                <p>Haz clic en un icono para copiar su código</p>
            </div>
        </div>

        <div class="icon-search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="iconSearchInput" placeholder="Buscar icono... (ej: wifi, cama, tv)" oninput="filterIcons(this.value)">
        </div>

        <div class="icon-modal-body" id="iconModalBody">
            <!-- Habitación -->
            <div class="icon-category" data-category="habitacion">
                <div class="icon-category-title"><i class="fas fa-bed"></i> Habitación</div>
                <div class="icon-grid-modal">
                    <div class="icon-item-helper" data-search="cama king bed doble" onclick="copyIconCode(this, 'fa-bed')"><i class="fas fa-bed"></i> <span class="icon-name">Cama King</span> <span class="icon-code">fa-bed</span></div>
                    <div class="icon-item-helper" data-search="almohada pillow" onclick="copyIconCode(this, 'fa-mattress-pillow')"><i class="fas fa-mattress-pillow"></i> <span class="icon-name">Almohada</span> <span class="icon-code">fa-mattress-pillow</span></div>
                    <div class="icon-item-helper" data-search="puerta door llave" onclick="copyIconCode(this, 'fa-door-closed')"><i class="fas fa-door-closed"></i> <span class="icon-name">Habitación Privada</span> <span class="icon-code">fa-door-closed</span></div>
                    <div class="icon-item-helper" data-search="llave key tarjeta" onclick="copyIconCode(this, 'fa-key')"><i class="fas fa-key"></i> <span class="icon-name">Acceso con Llave</span> <span class="icon-code">fa-key</span></div>
                    <div class="icon-item-helper" data-search="caja fuerte seguridad safe" onclick="copyIconCode(this, 'fa-shield-halved')"><i class="fas fa-shield-halved"></i> <span class="icon-name">Caja Fuerte</span> <span class="icon-code">fa-shield-halved</span></div>
                    <div class="icon-item-helper" data-search="reloj despertador clock" onclick="copyIconCode(this, 'fa-clock')"><i class="fas fa-clock"></i> <span class="icon-name">Reloj / Despertador</span> <span class="icon-code">fa-clock</span></div>
                </div>
            </div>

            <!-- Baño y Bienestar -->
            <div class="icon-category" data-category="bano">
                <div class="icon-category-title"><i class="fas fa-bath"></i> Baño y Bienestar</div>
                <div class="icon-grid-modal">
                    <div class="icon-item-helper" data-search="bañera tina bath" onclick="copyIconCode(this, 'fa-bath')"><i class="fas fa-bath"></i> <span class="icon-name">Bañera</span> <span class="icon-code">fa-bath</span></div>
                    <div class="icon-item-helper" data-search="ducha shower regadera" onclick="copyIconCode(this, 'fa-shower')"><i class="fas fa-shower"></i> <span class="icon-name">Ducha</span> <span class="icon-code">fa-shower</span></div>
                    <div class="icon-item-helper" data-search="jacuzzi hot tub spa" onclick="copyIconCode(this, 'fa-hot-tub-person')"><i class="fas fa-hot-tub-person"></i> <span class="icon-name">Jacuzzi</span> <span class="icon-code">fa-hot-tub-person</span></div>
                    <div class="icon-item-helper" data-search="amenidades sink lavabo" onclick="copyIconCode(this, 'fa-sink')"><i class="fas fa-sink"></i> <span class="icon-name">Amenidades</span> <span class="icon-code">fa-sink</span></div>
                    <div class="icon-item-helper" data-search="secador pelo wind dryer" onclick="copyIconCode(this, 'fa-wind')"><i class="fas fa-wind"></i> <span class="icon-name">Secador de Pelo</span> <span class="icon-code">fa-wind</span></div>
                    <div class="icon-item-helper" data-search="toalla pump soap jabon" onclick="copyIconCode(this, 'fa-pump-soap')"><i class="fas fa-pump-soap"></i> <span class="icon-name">Jabón Premium</span> <span class="icon-code">fa-pump-soap</span></div>
                    <div class="icon-item-helper" data-search="spa relax masaje" onclick="copyIconCode(this, 'fa-spa')"><i class="fas fa-spa"></i> <span class="icon-name">Spa</span> <span class="icon-code">fa-spa</span></div>
                </div>
            </div>

            <!-- Tecnología -->
            <div class="icon-category" data-category="tecnologia">
                <div class="icon-category-title"><i class="fas fa-tv"></i> Tecnología y Conectividad</div>
                <div class="icon-grid-modal">
                    <div class="icon-item-helper" data-search="wifi internet red wireless" onclick="copyIconCode(this, 'fa-wifi')"><i class="fas fa-wifi"></i> <span class="icon-name">Wi-Fi</span> <span class="icon-code">fa-wifi</span></div>
                    <div class="icon-item-helper" data-search="tv television smart pantalla" onclick="copyIconCode(this, 'fa-tv')"><i class="fas fa-tv"></i> <span class="icon-name">Smart TV</span> <span class="icon-code">fa-tv</span></div>
                    <div class="icon-item-helper" data-search="telefono phone llamar" onclick="copyIconCode(this, 'fa-phone')"><i class="fas fa-phone"></i> <span class="icon-name">Teléfono</span> <span class="icon-code">fa-phone</span></div>
                    <div class="icon-item-helper" data-search="enchufe plug cargador usb" onclick="copyIconCode(this, 'fa-plug')"><i class="fas fa-plug"></i> <span class="icon-name">Enchufe USB</span> <span class="icon-code">fa-plug</span></div>
                    <div class="icon-item-helper" data-search="bluetooth signal señal" onclick="copyIconCode(this, 'fa-bluetooth-b')"><i class="fab fa-bluetooth-b"></i> <span class="icon-name">Bluetooth</span> <span class="icon-code">fa-bluetooth-b</span></div>
                </div>
            </div>

            <!-- Alimentos y Bebidas -->
            <div class="icon-category" data-category="alimentos">
                <div class="icon-category-title"><i class="fas fa-utensils"></i> Alimentos y Bebidas</div>
                <div class="icon-grid-modal">
                    <div class="icon-item-helper" data-search="desayuno breakfast mug" onclick="copyIconCode(this, 'fa-mug-hot')"><i class="fas fa-mug-hot"></i> <span class="icon-name">Desayuno</span> <span class="icon-code">fa-mug-hot</span></div>
                    <div class="icon-item-helper" data-search="cafetera cafe coffee" onclick="copyIconCode(this, 'fa-coffee')"><i class="fas fa-coffee"></i> <span class="icon-name">Cafetera</span> <span class="icon-code">fa-coffee</span></div>
                    <div class="icon-item-helper" data-search="restaurante comida cena cubiertos" onclick="copyIconCode(this, 'fa-utensils')"><i class="fas fa-utensils"></i> <span class="icon-name">Restaurante</span> <span class="icon-code">fa-utensils</span></div>
                    <div class="icon-item-helper" data-search="minibar bar drink cocktail copa" onclick="copyIconCode(this, 'fa-martini-glass-citrus')"><i class="fas fa-martini-glass-citrus"></i> <span class="icon-name">Mini Bar</span> <span class="icon-code">fa-martini-glass-citrus</span></div>
                    <div class="icon-item-helper" data-search="vino wine champagne" onclick="copyIconCode(this, 'fa-wine-glass')"><i class="fas fa-wine-glass"></i> <span class="icon-name">Vino de Cortesía</span> <span class="icon-code">fa-wine-glass</span></div>
                    <div class="icon-item-helper" data-search="botella agua water bottle" onclick="copyIconCode(this, 'fa-bottle-water')"><i class="fas fa-bottle-water"></i> <span class="icon-name">Agua Embotellada</span> <span class="icon-code">fa-bottle-water</span></div>
                </div>
            </div>

            <!-- Clima y Vista -->
            <div class="icon-category" data-category="clima">
                <div class="icon-category-title"><i class="fas fa-snowflake"></i> Clima y Entorno</div>
                <div class="icon-grid-modal">
                    <div class="icon-item-helper" data-search="aire acondicionado frio snowflake" onclick="copyIconCode(this, 'fa-snowflake')"><i class="fas fa-snowflake"></i> <span class="icon-name">Aire Acondicionado</span> <span class="icon-code">fa-snowflake</span></div>
                    <div class="icon-item-helper" data-search="calefaccion caliente fire" onclick="copyIconCode(this, 'fa-fire')"><i class="fas fa-fire"></i> <span class="icon-name">Calefacción</span> <span class="icon-code">fa-fire</span></div>
                    <div class="icon-item-helper" data-search="ventilador fan" onclick="copyIconCode(this, 'fa-fan')"><i class="fas fa-fan"></i> <span class="icon-name">Ventilador</span> <span class="icon-code">fa-fan</span></div>
                    <div class="icon-item-helper" data-search="vista montaña mountain paisaje view" onclick="copyIconCode(this, 'fa-mountain-sun')"><i class="fas fa-mountain-sun"></i> <span class="icon-name">Vista a Montaña</span> <span class="icon-code">fa-mountain-sun</span></div>
                    <div class="icon-item-helper" data-search="playa mar ocean water" onclick="copyIconCode(this, 'fa-water')"><i class="fas fa-water"></i> <span class="icon-name">Vista al Mar</span> <span class="icon-code">fa-water</span></div>
                    <div class="icon-item-helper" data-search="jardin garden tree arbol naturaleza" onclick="copyIconCode(this, 'fa-tree')"><i class="fas fa-tree"></i> <span class="icon-name">Jardín</span> <span class="icon-code">fa-tree</span></div>
                </div>
            </div>

            <!-- Servicios del Hotel -->
            <div class="icon-category" data-category="servicios">
                <div class="icon-category-title"><i class="fas fa-concierge-bell"></i> Servicios del Hotel</div>
                <div class="icon-grid-modal">
                    <div class="icon-item-helper" data-search="concierge campana bell recepcion" onclick="copyIconCode(this, 'fa-concierge-bell')"><i class="fas fa-concierge-bell"></i> <span class="icon-name">Concierge 24h</span> <span class="icon-code">fa-concierge-bell</span></div>
                    <div class="icon-item-helper" data-search="parking estacionamiento carro car" onclick="copyIconCode(this, 'fa-square-parking')"><i class="fas fa-square-parking"></i> <span class="icon-name">Estacionamiento</span> <span class="icon-code">fa-square-parking</span></div>
                    <div class="icon-item-helper" data-search="alberca piscina pool swim nadar" onclick="copyIconCode(this, 'fa-person-swimming')"><i class="fas fa-person-swimming"></i> <span class="icon-name">Alberca</span> <span class="icon-code">fa-person-swimming</span></div>
                    <div class="icon-item-helper" data-search="gym gimnasio pesas exercise" onclick="copyIconCode(this, 'fa-dumbbell')"><i class="fas fa-dumbbell"></i> <span class="icon-name">Gimnasio</span> <span class="icon-code">fa-dumbbell</span></div>
                    <div class="icon-item-helper" data-search="lavanderia laundry lavar ropa" onclick="copyIconCode(this, 'fa-shirt')"><i class="fas fa-shirt"></i> <span class="icon-name">Lavandería</span> <span class="icon-code">fa-shirt</span></div>
                    <div class="icon-item-helper" data-search="equipaje maleta luggage suitcase" onclick="copyIconCode(this, 'fa-suitcase-rolling')"><i class="fas fa-suitcase-rolling"></i> <span class="icon-name">Guardaequipaje</span> <span class="icon-code">fa-suitcase-rolling</span></div>
                    <div class="icon-item-helper" data-search="taxi transporte shuttle transfer" onclick="copyIconCode(this, 'fa-taxi')"><i class="fas fa-taxi"></i> <span class="icon-name">Transporte</span> <span class="icon-code">fa-taxi</span></div>
                    <div class="icon-item-helper" data-search="personas capacidad grupo users" onclick="copyIconCode(this, 'fa-users')"><i class="fas fa-users"></i> <span class="icon-name">Capacidad</span> <span class="icon-code">fa-users</span></div>
                    <div class="icon-item-helper" data-search="accesible wheelchair silla ruedas discapacidad" onclick="copyIconCode(this, 'fa-wheelchair')"><i class="fas fa-wheelchair"></i> <span class="icon-name">Accesible</span> <span class="icon-code">fa-wheelchair</span></div>
                </div>
            </div>

            <!-- Políticas -->
            <div class="icon-category" data-category="politicas">
                <div class="icon-category-title"><i class="fas fa-ban-smoking"></i> Políticas</div>
                <div class="icon-grid-modal">
                    <div class="icon-item-helper" data-search="no fumar smoke smoking prohibido" onclick="copyIconCode(this, 'fa-ban-smoking')"><i class="fas fa-ban-smoking"></i> <span class="icon-name">No Fumar</span> <span class="icon-code">fa-ban-smoking</span></div>
                    <div class="icon-item-helper" data-search="mascota pet dog perro animal" onclick="copyIconCode(this, 'fa-paw')"><i class="fas fa-paw"></i> <span class="icon-name">Pet Friendly</span> <span class="icon-code">fa-paw</span></div>
                    <div class="icon-item-helper" data-search="niños kids children familia family" onclick="copyIconCode(this, 'fa-children')"><i class="fas fa-children"></i> <span class="icon-name">Niños Bienvenidos</span> <span class="icon-code">fa-children</span></div>
                    <div class="icon-item-helper" data-search="seguridad camara vigilancia" onclick="copyIconCode(this, 'fa-shield')"><i class="fas fa-shield"></i> <span class="icon-name">Seguridad 24h</span> <span class="icon-code">fa-shield</span></div>
                </div>
            </div>

            <div class="icon-no-results" id="iconNoResults">
                <i class="fas fa-search"></i>
                No se encontraron iconos para tu búsqueda
            </div>
        </div>

        <div class="modal-footer">
            <div class="modal-footer-hint">
                <i class="fas fa-lightbulb"></i> Formato: <strong style="color:#ccc; margin-left: 3px;">fa-wifi:Wi-Fi Express</strong>
            </div>
            <a href="https://fontawesome.com/v6/search?o=r&m=free" target="_blank" class="btn-gold" style="font-size: 0.7rem; padding: 8px 16px; text-decoration: none;">
                <i class="fab fa-font-awesome"></i> MÁS ICONOS
            </a>
        </div>
    </div>
</div>

<script>
    function openIconModal() {
        const modal = document.getElementById('iconModal');
        if (modal) {
            modal.style.display = 'flex';
            // Trigger animation on next frame
            requestAnimationFrame(() => modal.classList.add('active'));
            document.getElementById('iconSearchInput').value = '';
            filterIcons('');
        }
    }

    function closeIconModal() {
        const modal = document.getElementById('iconModal');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => modal.style.display = 'none', 300);
        }
    }

    function copyIconCode(el, code) {
        navigator.clipboard.writeText(code).then(() => {
            // Visual feedback on the clicked item
            el.classList.add('copied');
            setTimeout(() => el.classList.remove('copied'), 1200);

            Swal.fire({
                title: '¡Copiado!',
                html: `<span style="font-family:monospace;background:rgba(0,0,0,0.3);padding:3px 10px;border-radius:4px;">${code}</span> copiado al portapapeles`,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
                background: '#151921',
                color: '#fff',
                position: 'top-end',
                toast: true
            });
        });
    }

    function filterIcons(query) {
        const q = query.toLowerCase().trim();
        const categories = document.querySelectorAll('.icon-category');
        const noResults = document.getElementById('iconNoResults');
        let totalVisible = 0;

        categories.forEach(cat => {
            const items = cat.querySelectorAll('.icon-item-helper');
            let catVisible = 0;

            items.forEach(item => {
                const searchText = (item.getAttribute('data-search') || '') + ' ' + item.textContent.toLowerCase();
                const match = !q || searchText.includes(q);
                item.style.display = match ? 'flex' : 'none';
                if (match) catVisible++;
            });

            cat.style.display = catVisible > 0 ? 'block' : 'none';
            totalVisible += catVisible;
        });

        noResults.style.display = totalVisible === 0 ? 'block' : 'none';
    }

    // Close modal on click outside
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('iconModal');
        if (event.target === modal) {
            closeIconModal();
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') closeIconModal();
    });

    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                input.previousElementSibling.style.backgroundImage = "url('" + e.target.result + "')";
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function previewGalleryImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                const slot = input.previousElementSibling;
                slot.innerHTML = '<img src="' + e.target.result + '">';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php if ($success_json): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            ...<?php echo $success_json; ?>,
            background: '#151921',
            color: '#fff',
            confirmButtonColor: '#c5a059',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    });
</script>
<?php endif; ?>

<?php include_once "views/layouts/admin_footer.php"; ?>
