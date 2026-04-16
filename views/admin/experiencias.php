<?php
require_once "config/config.php";
require_once "config/database.php";
require_once "models/Experience.php";

$db = (new Database())->getConnection();
$experience = new Experience($db);

$secciones = $experience->getAllSections();
$galeria = $experience->getGalleryPhotos();

// Indexar secciones por tipo para acceso rápido
$seccionesMap = [];
foreach ($secciones as $s) {
    $seccionesMap[$s['seccion']] = $s;
}

$transporte = $seccionesMap['transporte'] ?? null;
$tours = $seccionesMap['tours'] ?? null;

include_once "views/layouts/admin_header.php";
?>

<style>
    /* ===================== EXPERIENCE ADMIN STYLES ===================== */
    .exp-admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 35px;
    }
    .exp-admin-header h2 {
        font-size: 2.2rem;
        margin: 0;
    }
    .exp-admin-header p {
        color: #888;
        margin: 5px 0 0;
    }

    /* Section cards */
    .exp-section-card {
        background: var(--card-bg);
        border: 1px solid var(--gold-border);
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 35px;
        transition: transform 0.3s ease;
    }
    .exp-section-card:hover {
        box-shadow: var(--gold-glow);
    }

    .exp-section-header {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 0;
        min-height: 320px;
    }

    .exp-section-img-wrap {
        position: relative;
        cursor: pointer;
        overflow: hidden;
        background: #000;
    }
    .exp-section-img-wrap img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease, opacity 0.3s;
    }
    .exp-section-img-wrap:hover img {
        transform: scale(1.05);
        opacity: 0.8;
    }
    .exp-section-img-wrap::after {
        content: '\f03e  Cambiar Imagen';
        font-family: 'Font Awesome 6 Free', sans-serif;
        font-weight: 900;
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
        opacity: 0;
        transition: opacity 0.3s;
        letter-spacing: 1px;
    }
    .exp-section-img-wrap:hover::after {
        opacity: 1;
    }
    .exp-section-img-wrap .section-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: var(--primary);
        color: black;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        z-index: 2;
        letter-spacing: 1px;
    }

    .exp-section-body {
        padding: 30px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .exp-form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
    }

    .exp-form-group {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .exp-form-group label {
        color: var(--primary);
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    .exp-form-group input,
    .exp-form-group textarea {
        width: 100%;
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(197, 160, 89, 0.2);
        color: white;
        padding: 11px 14px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-family: inherit;
        transition: border-color 0.3s;
        resize: none;
    }
    .exp-form-group input:focus,
    .exp-form-group textarea:focus {
        border-color: var(--primary);
        outline: none;
    }

    /* Feature list builder */
    .feature-builder {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .feat-row {
        display: grid;
        grid-template-columns: 180px 1fr 40px;
        gap: 10px;
        align-items: center;
    }
    .feat-row input {
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(197, 160, 89, 0.2);
        color: white;
        padding: 9px 12px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-family: monospace;
    }
    .feat-row input:focus {
        border-color: var(--primary);
        outline: none;
    }
    .feat-remove {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1px solid rgba(231,76,60,0.3);
        background: rgba(231,76,60,0.1);
        color: #e74c3c;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .feat-remove:hover {
        background: rgba(231,76,60,0.3);
    }
    .feat-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: var(--primary);
        background: rgba(212,175,55,0.1);
        border: 1px dashed rgba(212,175,55,0.3);
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.75rem;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.2s;
        width: fit-content;
    }
    .feat-add-btn:hover {
        background: rgba(212,175,55,0.2);
        border-color: var(--primary);
    }

    .exp-section-actions {
        padding: 0 30px 25px;
        display: flex;
        justify-content: flex-end;
    }
    .exp-save-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--primary), #a88a3d);
        color: black;
        border: none;
        padding: 12px 30px;
        border-radius: 10px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .exp-save-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(197,160,89,0.3);
    }
    .exp-save-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* ===================== GALLERY GRID ===================== */
    .gallery-admin-title {
        margin-top: 50px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .gallery-admin-title h3 {
        font-size: 1.6rem;
        margin: 0;
    }
    .gallery-admin-title .badge-count {
        background: rgba(212,175,55,0.15);
        color: var(--primary);
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.7rem;
        font-weight: 700;
    }

    .gallery-admin-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 40px;
    }

    .gallery-admin-item {
        aspect-ratio: 4/3;
        border-radius: 14px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(197,160,89,0.15);
        transition: all 0.3s ease;
    }
    .gallery-admin-item:hover {
        border-color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    }
    .gallery-admin-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease, opacity 0.3s;
    }
    .gallery-admin-item:hover img {
        transform: scale(1.08);
        opacity: 0.7;
    }
    .gallery-admin-item .gal-pos {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(0,0,0,0.7);
        color: var(--primary);
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 700;
        z-index: 2;
        border: 1px solid rgba(197,160,89,0.3);
    }
    .gallery-admin-item .gal-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.6);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        opacity: 0;
        transition: opacity 0.3s;
        color: white;
    }
    .gallery-admin-item:hover .gal-overlay {
        opacity: 1;
    }
    .gal-overlay i {
        font-size: 1.4rem;
        color: var(--primary);
    }
    .gal-overlay span {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .gallery-admin-item.empty-slot {
        border-style: dashed;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 8px;
    }
    .gallery-admin-item.empty-slot i {
        font-size: 1.5rem;
        color: rgba(197,160,89,0.4);
    }
    .gallery-admin-item.empty-slot span {
        font-size: 0.7rem;
        color: #666;
    }

    /* Upload progress */
    .upload-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: rgba(0,0,0,0.5);
        z-index: 5;
        display: none;
    }
    .upload-progress-bar {
        height: 100%;
        background: var(--primary);
        width: 0%;
        transition: width 0.3s;
        border-radius: 2px;
    }

    /* Responsive */
    @media (max-width: 1100px) {
        .exp-section-header {
            grid-template-columns: 1fr;
        }
        .exp-section-img-wrap {
            height: 220px;
        }
    }
    @media (max-width: 768px) {
        .exp-form-row {
            grid-template-columns: 1fr;
        }
        .gallery-admin-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    @media (max-width: 500px) {
        .gallery-admin-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .feat-row {
            grid-template-columns: 1fr;
        }
        .feat-row input:first-child {
            font-size: 0.7rem;
        }
    }
</style>

<div class="admin-container" style="color: white; padding: 20px;">
    <!-- HEADER -->
    <div class="exp-admin-header">
        <div>
            <h2 class="serif gold-text">Gestión de Experiencias</h2>
            <p>Edita el contenido, imágenes y galería de la página de experiencias.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>?action=experiencias" target="_blank" class="btn-gold" style="text-decoration: none;">
            <i class="fas fa-external-link-alt"></i> VER PÁGINA
        </a>
    </div>

    <!-- ==================== SECCIÓN TRANSPORTE ==================== -->
    <?php if ($transporte): ?>
    <div class="exp-section-card" id="card-transporte">
        <div class="exp-section-header">
            <div class="exp-section-img-wrap" onclick="document.getElementById('img-transporte').click()">
                <span class="section-badge"><i class="fas fa-plane-arrival"></i> Transporte</span>
                <img src="<?php echo BASE_URL . $transporte['imagen'] . '?v=' . time(); ?>" alt="Transporte" id="preview-transporte">
            </div>
            <input type="file" id="img-transporte" accept="image/*" style="display:none" onchange="previewSectionImage(this, 'transporte')">

            <div class="exp-section-body">
                <div class="exp-form-row">
                    <div class="exp-form-group">
                        <label>Subtítulo Dorado</label>
                        <input type="text" id="transporte-subtitulo" value="<?php echo e($transporte['subtitulo']); ?>">
                    </div>
                    <div class="exp-form-group">
                        <label>Título Principal</label>
                        <input type="text" id="transporte-titulo" value="<?php echo e($transporte['titulo']); ?>">
                    </div>
                </div>

                <div class="exp-form-group">
                    <label>Descripción</label>
                    <textarea id="transporte-descripcion" rows="3"><?php echo e($transporte['descripcion']); ?></textarea>
                </div>

                <div class="exp-form-group">
                    <label>Tags (separados por coma)</label>
                    <?php $tags_t = json_decode($transporte['tags'] ?? '[]', true); ?>
                    <input type="text" id="transporte-tags" value="<?php echo e(implode(', ', $tags_t ?: [])); ?>" placeholder="#TrasladoPrivado, #Seguridad, #Confort">
                </div>

                <div class="exp-form-group">
                    <label>Lista de Características</label>
                    <?php $lista_t = json_decode($transporte['lista'] ?? '[]', true); ?>
                    <div class="feature-builder" id="features-transporte">
                        <?php if ($lista_t): foreach ($lista_t as $item): ?>
                        <div class="feat-row">
                            <input type="text" class="feat-icon" value="<?php echo e($item['icono']); ?>" placeholder="fa-solid fa-check">
                            <input type="text" class="feat-text" value="<?php echo e($item['texto']); ?>" placeholder="Descripción del punto">
                            <button type="button" class="feat-remove" onclick="this.closest('.feat-row').remove()"><i class="fas fa-trash-alt"></i></button>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <div class="feat-add-btn" onclick="addFeatureRow('transporte')">
                        <i class="fas fa-plus"></i> Agregar Punto
                    </div>
                </div>
            </div>
        </div>
        <div class="exp-section-actions">
            <button class="exp-save-btn" onclick="saveSection('transporte')">
                <i class="fas fa-save"></i> Guardar Transporte
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== SECCIÓN TOURS ==================== -->
    <?php if ($tours): ?>
    <div class="exp-section-card" id="card-tours">
        <div class="exp-section-header">
            <div class="exp-section-img-wrap" onclick="document.getElementById('img-tours').click()">
                <span class="section-badge"><i class="fas fa-map-marked-alt"></i> Tours</span>
                <img src="<?php echo BASE_URL . $tours['imagen'] . '?v=' . time(); ?>" alt="Tours" id="preview-tours">
            </div>
            <input type="file" id="img-tours" accept="image/*" style="display:none" onchange="previewSectionImage(this, 'tours')">

            <div class="exp-section-body">
                <div class="exp-form-row">
                    <div class="exp-form-group">
                        <label>Subtítulo Dorado</label>
                        <input type="text" id="tours-subtitulo" value="<?php echo e($tours['subtitulo']); ?>">
                    </div>
                    <div class="exp-form-group">
                        <label>Título Principal</label>
                        <input type="text" id="tours-titulo" value="<?php echo e($tours['titulo']); ?>">
                    </div>
                </div>

                <div class="exp-form-group">
                    <label>Descripción</label>
                    <textarea id="tours-descripcion" rows="3"><?php echo e($tours['descripcion']); ?></textarea>
                </div>

                <div class="exp-form-group">
                    <label>Tags (separados por coma)</label>
                    <?php $tags_to = json_decode($tours['tags'] ?? '[]', true); ?>
                    <input type="text" id="tours-tags" value="<?php echo e(implode(', ', $tags_to ?: [])); ?>" placeholder="#QuitoPatrimonial, #Cultura">
                </div>

                <div class="exp-form-group">
                    <label>Lista de Características</label>
                    <?php $lista_to = json_decode($tours['lista'] ?? '[]', true); ?>
                    <div class="feature-builder" id="features-tours">
                        <?php if ($lista_to): foreach ($lista_to as $item): ?>
                        <div class="feat-row">
                            <input type="text" class="feat-icon" value="<?php echo e($item['icono']); ?>" placeholder="fa-solid fa-check">
                            <input type="text" class="feat-text" value="<?php echo e($item['texto']); ?>" placeholder="Descripción del punto">
                            <button type="button" class="feat-remove" onclick="this.closest('.feat-row').remove()"><i class="fas fa-trash-alt"></i></button>
                        </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <div class="feat-add-btn" onclick="addFeatureRow('tours')">
                        <i class="fas fa-plus"></i> Agregar Punto
                    </div>
                </div>
            </div>
        </div>
        <div class="exp-section-actions">
            <button class="exp-save-btn" onclick="saveSection('tours')">
                <i class="fas fa-save"></i> Guardar Tours
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- ==================== GALERÍA 12 FOTOS ==================== -->
    <div class="gallery-admin-title">
        <h3 class="serif gold-text">Galería de Fotos</h3>
        <span class="badge-count">12 imágenes</span>
    </div>
    <p style="color: #888; margin: -15px 0 25px; font-size: 0.85rem;">Haz clic en cualquier foto para reemplazarla. La imagen anterior se eliminará automáticamente.</p>

    <div class="gallery-admin-grid">
        <?php for ($i = 1; $i <= 12; $i++):
            $foto = null;
            foreach ($galeria as $g) {
                if (intval($g['posicion']) === $i) { $foto = $g; break; }
            }
        ?>
        <div class="gallery-admin-item <?php echo !$foto ? 'empty-slot' : ''; ?>" onclick="document.getElementById('gal-input-<?php echo $i; ?>').click()">
            <span class="gal-pos"><?php echo $i; ?></span>
            <?php if ($foto): ?>
                <img src="<?php echo BASE_URL . $foto['imagen'] . '?v=' . time(); ?>" alt="<?php echo e($foto['alt_text']); ?>" id="gal-preview-<?php echo $i; ?>">
                <div class="gal-overlay">
                    <i class="fas fa-camera"></i>
                    <span>Reemplazar</span>
                </div>
            <?php else: ?>
                <i class="fas fa-plus"></i>
                <span>Agregar foto</span>
            <?php endif; ?>
            <div class="upload-progress" id="gal-progress-<?php echo $i; ?>">
                <div class="upload-progress-bar"></div>
            </div>
        </div>
        <input type="file" id="gal-input-<?php echo $i; ?>" accept="image/*" style="display:none" onchange="uploadGalleryPhoto(<?php echo $i; ?>, this)">
        <?php endfor; ?>
    </div>
</div>

<script>
const BASE = '<?php echo BASE_URL; ?>';

// ==================== PREVIEW IMAGE ====================
function previewSectionImage(input, seccion) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-' + seccion).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ==================== ADD FEATURE ROW ====================
function addFeatureRow(seccion) {
    const builder = document.getElementById('features-' + seccion);
    const row = document.createElement('div');
    row.className = 'feat-row';
    row.innerHTML = `
        <input type="text" class="feat-icon" value="fa-solid fa-check" placeholder="fa-solid fa-check">
        <input type="text" class="feat-text" value="" placeholder="Descripción del punto">
        <button type="button" class="feat-remove" onclick="this.closest('.feat-row').remove()"><i class="fas fa-trash-alt"></i></button>
    `;
    builder.appendChild(row);
    row.querySelector('.feat-text').focus();
}

// ==================== SAVE SECTION ====================
async function saveSection(seccion) {
    const btn = document.querySelector(`#card-${seccion} .exp-save-btn`);
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    btn.disabled = true;

    const formData = new FormData();
    formData.append('action', 'update_section');
    formData.append('seccion', seccion);
    formData.append('subtitulo', document.getElementById(seccion + '-subtitulo').value);
    formData.append('titulo', document.getElementById(seccion + '-titulo').value);
    formData.append('descripcion', document.getElementById(seccion + '-descripcion').value);
    formData.append('tags', document.getElementById(seccion + '-tags').value);

    // Feature list
    const rows = document.querySelectorAll(`#features-${seccion} .feat-row`);
    rows.forEach(row => {
        formData.append('lista_iconos[]', row.querySelector('.feat-icon').value);
        formData.append('lista_textos[]', row.querySelector('.feat-text').value);
    });

    // Image file
    const fileInput = document.getElementById('img-' + seccion);
    if (fileInput.files.length > 0) {
        formData.append('imagen', fileInput.files[0]);
    }

    try {
        const res = await fetch(BASE + 'api/update_experience.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                title: '¡Actualizado!',
                html: `La sección <strong style="color:var(--primary);">${seccion}</strong> fue guardada correctamente.`,
                icon: 'success',
                background: '#151921',
                color: '#fff',
                confirmButtonColor: '#c5a059',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            // Clear file input after successful upload
            fileInput.value = '';
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message || 'No se pudo guardar',
                icon: 'error',
                background: '#151921',
                color: '#fff',
                confirmButtonColor: '#c5a059'
            });
        }
    } catch (err) {
        Swal.fire({
            title: 'Error de Conexión',
            text: 'No se pudo conectar con el servidor.',
            icon: 'error',
            background: '#151921',
            color: '#fff',
            confirmButtonColor: '#c5a059'
        });
    }

    btn.innerHTML = originalHTML;
    btn.disabled = false;
}

// ==================== UPLOAD GALLERY PHOTO ====================
async function uploadGalleryPhoto(posicion, input) {
    if (!input.files || !input.files[0]) return;

    const file = input.files[0];
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowed.includes(file.type)) {
        Swal.fire({
            title: 'Formato no válido',
            text: 'Solo se permiten imágenes JPG, PNG o WebP.',
            icon: 'warning',
            background: '#151921',
            color: '#fff',
            confirmButtonColor: '#c5a059'
        });
        return;
    }

    const item = input.previousElementSibling;
    const progressWrap = document.getElementById('gal-progress-' + posicion);
    const progressBar = progressWrap.querySelector('.upload-progress-bar');
    progressWrap.style.display = 'block';
    progressBar.style.width = '30%';

    const formData = new FormData();
    formData.append('action', 'update_gallery');
    formData.append('posicion', posicion);
    formData.append('imagen', file);

    try {
        progressBar.style.width = '60%';
        const res = await fetch(BASE + 'api/update_experience.php', {
            method: 'POST',
            body: formData
        });
        progressBar.style.width = '90%';
        const data = await res.json();

        if (data.success) {
            progressBar.style.width = '100%';
            // Update preview
            const reader = new FileReader();
            reader.onload = function(e) {
                // Rebuild the item content
                item.classList.remove('empty-slot');
                item.innerHTML = `
                    <span class="gal-pos">${posicion}</span>
                    <img src="${e.target.result}" alt="Experiencia ${posicion}" id="gal-preview-${posicion}">
                    <div class="gal-overlay">
                        <i class="fas fa-camera"></i>
                        <span>Reemplazar</span>
                    </div>
                    <div class="upload-progress" id="gal-progress-${posicion}">
                        <div class="upload-progress-bar"></div>
                    </div>
                `;
            };
            reader.readAsDataURL(file);

            Swal.fire({
                title: '¡Foto Actualizada!',
                html: `Posición <strong style="color:var(--primary);">#${posicion}</strong> reemplazada.`,
                icon: 'success',
                background: '#151921',
                color: '#fff',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        } else {
            Swal.fire({
                title: 'Error',
                text: data.message || 'No se pudo actualizar la foto',
                icon: 'error',
                background: '#151921',
                color: '#fff',
                confirmButtonColor: '#c5a059'
            });
        }
    } catch (err) {
        Swal.fire({
            title: 'Error de Conexión',
            text: 'No se pudo conectar con el servidor.',
            icon: 'error',
            background: '#151921',
            color: '#fff',
            confirmButtonColor: '#c5a059'
        });
    }

    setTimeout(() => {
        progressWrap.style.display = 'none';
        progressBar.style.width = '0%';
    }, 500);

    input.value = '';
}
</script>

<?php include_once "views/layouts/admin_footer.php"; ?>
