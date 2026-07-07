<?php
require_once "config/config.php";
require_once "config/database.php";

$db = (new Database())->getConnection();

// ======== API AJAX: Actualizar estado de unidad automáticamente ========
if (isset($_POST['ajax_update_unit']) && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: application/json');
    $unit_id = intval($_POST['unit_id']);
    $nuevo_estado = $_POST['estado'];
    $nuevo_numero = trim($_POST['numero_habitacion'] ?? '');

    $allowed_estados = ['disponible', 'ocupado', 'mantenimiento'];
    if (!in_array($nuevo_estado, $allowed_estados)) {
        echo json_encode(['success' => false, 'message' => 'Estado inválido']);
        exit;
    }

    if (!empty($nuevo_numero)) {
        $stmt = $db->prepare("UPDATE habitaciones SET estado = ?, numero = ? WHERE id = ?");
        $ok = $stmt->execute([$nuevo_estado, $nuevo_numero, $unit_id]);
    } else {
        $stmt = $db->prepare("UPDATE habitaciones SET estado = ? WHERE id = ?");
        $ok = $stmt->execute([$nuevo_estado, $unit_id]);
    }
    echo json_encode(['success' => $ok, 'message' => $ok ? 'Actualizado' : 'Error al actualizar']);
    exit;
}

// Lógica de actualización de estado de unidad (fallback sin AJAX)
$success_json = null;
if (isset($_POST['update_unit_status'])) {
    $unit_id = intval($_POST['unit_id']);
    $nuevo_estado = $_POST['estado'];
    $nuevo_numero = trim($_POST['numero_habitacion'] ?? '');
    
    if (!empty($nuevo_numero)) {
        $stmt = $db->prepare("UPDATE habitaciones SET estado = ?, numero = ? WHERE id = ?");
        if ($stmt->execute([$nuevo_estado, $nuevo_numero, $unit_id])) {
            $success_json = json_encode([
                'title' => '¡Actualizado!',
                'text' => "Habitación #$nuevo_numero actualizada correctamente.",
                'icon' => 'success'
            ]);
        }
    }
}

// Resumen por tipos para los indicadores
$stmt_summary = $db->query("
    SELECT tipo, nombre, 
           COUNT(*) as total, 
           SUM(CASE WHEN estado = 'disponible' THEN 1 ELSE 0 END) as disponibles
    FROM habitaciones 
    GROUP BY tipo, nombre
    ORDER BY total DESC
");
$resumen_tipos = $stmt_summary->fetchAll(PDO::FETCH_ASSOC);

// Unidades agrupadas por tipo + reserva activa confirmada con nombre del cliente
$stmt_units = $db->query("
    SELECT h.*, 
           r.nombre_cliente as cliente_activo,
           r.fecha_entrada,
           r.fecha_salida
    FROM habitaciones h
    LEFT JOIN reservas r ON r.habitacion_id = h.id 
        AND r.estado = 'confirmada'
        AND r.fecha_entrada <= CURDATE()
        AND r.fecha_salida >= CURDATE()
    ORDER BY h.tipo, h.numero
");
$unidades_raw = $stmt_units->fetchAll(PDO::FETCH_ASSOC);

$unidades_por_tipo = [];
foreach ($unidades_raw as $u) {
    $unidades_por_tipo[$u['nombre']][] = $u;
}

// También cargar reservas futuras confirmadas (próximos 30 días) por habitación
$stmt_futuras = $db->query("
    SELECT r.habitacion_id, r.nombre_cliente, r.fecha_entrada, r.fecha_salida
    FROM reservas r
    WHERE r.estado = 'confirmada'
      AND r.fecha_entrada > CURDATE()
      AND r.fecha_entrada <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY r.fecha_entrada ASC
");
$reservas_futuras = [];
foreach ($stmt_futuras->fetchAll(PDO::FETCH_ASSOC) as $rf) {
    $reservas_futuras[$rf['habitacion_id']][] = $rf;
}

include_once "views/layouts/admin_header.php";
?>

<!-- Estilos específicos para esta vista -->
<style>
    .admin-rooms-section { margin-bottom: 50px; }
    .section-header {
        display: flex; align-items: center; gap: 15px; margin-bottom: 25px;
        padding-bottom: 10px; border-bottom: 1px solid rgba(212,175,55,0.1);
    }
    .section-header h3 { font-size: 1.2rem; margin: 0; color: var(--primary-gold); }
    
    .unit-card {
        background: rgba(20, 25, 35, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 12px;
        padding: 16px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .unit-card:hover { 
        border-color: rgba(212, 175, 55, 0.4); 
        background: rgba(212, 175, 55, 0.03);
        transform: translateY(-2px);
    }
    .unit-card-inner {
        display: flex; justify-content: space-between; align-items: center;
    }
    
    .unit-info .unit-num { font-size: 0.65rem; color: #888; font-weight: 800; letter-spacing: 1px; }
    .unit-info .unit-type { font-size: 0.95rem; color: #ddd; margin: 2px 0; }
    
    /* Cliente activo - muestra a la derecha de la unidad */
    .unit-client-badge {
        display: flex; align-items: center; gap: 7px;
        background: rgba(46, 204, 113, 0.08);
        border: 1px solid rgba(46, 204, 113, 0.2);
        border-radius: 8px; padding: 6px 12px;
        margin-top: 8px;
        font-size: 0.72rem;
    }
    .unit-client-badge i { color: #2ecc71; font-size: 0.7rem; }
    .unit-client-badge .client-name-text { color: #2ecc71; font-weight: 700; }
    .unit-client-badge .client-dates { color: #888; font-size: 0.65rem; }

    /* Badge reserva futura */
    .unit-future-badge {
        display: flex; align-items: center; gap: 7px;
        background: rgba(241, 196, 15, 0.07);
        border: 1px solid rgba(241, 196, 15, 0.2);
        border-radius: 8px; padding: 5px 10px;
        margin-top: 5px;
        font-size: 0.68rem;
        color: #f1c40f;
    }
    .unit-future-badge i { font-size: 0.65rem; }

    .status-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 10px; border-radius: 50px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
    }
    .status-pill.disponible { background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.2); }
    .status-pill.ocupado { background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.2); }
    .status-pill.mantenimiento { background: rgba(241, 196, 15, 0.1); color: #f1c40f; border: 1px solid rgba(241, 196, 15, 0.2); }
    
    .unit-actions select {
        padding: 6px 12px; font-size: 0.75rem; border-radius: 8px;
        background: #0a0e14; border-color: rgba(212,175,55,0.2);
        cursor: pointer; transition: border-color 0.2s;
    }
    .unit-actions select:focus { border-color: var(--primary-gold); outline: none; }

    /* Indicador de guardado automático */
    .auto-save-indicator {
        font-size: 0.6rem; color: #555; margin-top: 4px; text-align: right;
        transition: color 0.3s;
    }
    .auto-save-indicator.saving { color: #f1c40f; }
    .auto-save-indicator.saved { color: #2ecc71; }
    .auto-save-indicator.error { color: #e74c3c; }

    /* Número de habitación inline edit */
    .num-edit-wrap { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; }
    .num-edit-wrap input {
        background: transparent; border: 1px solid rgba(212,175,55,0.3); 
        color: white; width: 60px; padding: 4px 6px; border-radius: 4px; 
        font-weight: 700; font-size: 0.85rem; text-align: center;
        transition: border-color 0.2s;
    }
    .num-edit-wrap input:focus { border-color: var(--primary-gold); outline: none; }
</style>

<div class="admin-container" style="color: white; padding: 20px;">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 class="serif gold-text" style="font-size: clamp(1.4rem, 4vw, 2.2rem); margin: 0;">Gestión de Unidades</h2>
            <p style="color: #888; margin-top: 5px;">Controla el estado operativo y numeración de cada habitación física. <span style="color: var(--primary-gold); font-size: 0.75rem;">✦ Los cambios de estado se guardan automáticamente</span></p>
        </div>
        <a href="index.php?action=admin-precios" class="btn-gold" style="padding: 10px 22px; font-size: 0.75rem; border-radius: 50px;">
            <i class="fas fa-tag"></i> EDITAR INFORMACIÓN Y PRECIOS
        </a>
    </div>

    <!-- 1. RESUMEN DE DISPONIBILIDAD -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 50px;">
        <?php foreach ($resumen_tipos as $tipo): ?>
        <div class="glass-card" style="padding: 20px; border-color: rgba(212, 175, 55, 0.15); text-align: center;">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; color: #888; margin-bottom: 10px;"><?php echo htmlspecialchars($tipo['nombre']); ?></div>
            <div style="font-size: 2.2rem; font-weight: 800; color: var(--primary-gold);">
                <?php echo $tipo['disponibles']; ?> <span style="font-size: 1rem; color: #555; font-weight: 400;">/ <?php echo $tipo['total']; ?></span>
            </div>
            <div style="font-size: 0.65rem; color: #2ecc71; font-weight: 700; margin-top: 5px; opacity: 0.8;">DISPONIBLES</div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- 2. CONTROL DE UNIDADES DIVIDIDO POR CATEGORÍA -->
    <?php foreach ($unidades_por_tipo as $nombre_cat => $unidades): ?>
    <section class="admin-rooms-section">
        <div class="section-header">
            <i class="fas fa-bed" style="color: var(--primary-gold);"></i>
            <h3><?php echo htmlspecialchars($nombre_cat); ?></h3>
            <span style="font-size: 0.7rem; color: #555;">(<?php echo count($unidades); ?> unidades físicas)</span>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 18px;">
            <?php foreach ($unidades as $u): 
                $has_client = !empty($u['cliente_activo']);
                $future_res = $reservas_futuras[$u['id']] ?? [];
            ?>
            <div class="unit-card" id="unit-card-<?php echo $u['id']; ?>">
                <div class="unit-card-inner">
                    <!-- Izquierda: info unidad -->
                    <div class="unit-info">
                        <div class="unit-num">HABITACIÓN</div>
                        <div class="num-edit-wrap">
                            <input type="text" 
                                   class="unit-number-input"
                                   data-unit-id="<?php echo $u['id']; ?>"
                                   value="<?php echo htmlspecialchars($u['numero']); ?>" 
                                   title="Número de habitación (editable)"
                                   maxlength="10">
                            <span style="font-size: 0.65rem; color: #555;">#</span>
                        </div>
                        <div class="status-pill <?php echo $u['estado']; ?>" id="pill-<?php echo $u['id']; ?>">
                            <div style="width: 5px; height: 5px; border-radius: 50%; background: currentColor;"></div>
                            <span id="pill-text-<?php echo $u['id']; ?>"><?php echo $u['estado']; ?></span>
                        </div>
                    </div>
                    
                    <!-- Derecha: selector de estado -->
                    <div class="unit-actions" style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
                        <select class="unit-status-select" 
                                data-unit-id="<?php echo $u['id']; ?>"
                                data-current="<?php echo $u['estado']; ?>">
                            <option value="disponible" <?php echo $u['estado'] == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                            <option value="ocupado" <?php echo $u['estado'] == 'ocupado' ? 'selected' : ''; ?>>Ocupado</option>
                            <option value="mantenimiento" <?php echo $u['estado'] == 'mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                        </select>
                        <div class="auto-save-indicator" id="save-ind-<?php echo $u['id']; ?>">
                            <i class="fas fa-circle-check" style="font-size: 0.55rem;"></i> automático
                        </div>
                    </div>
                </div>

                <?php if ($has_client): ?>
                <!-- Cliente actualmente hospedado -->
                <div class="unit-client-badge">
                    <i class="fas fa-user-check"></i>
                    <div>
                        <div class="client-name-text"><?php echo htmlspecialchars($u['cliente_activo']); ?></div>
                        <div class="client-dates">
                            <?php 
                                echo date('d/m', strtotime($u['fecha_entrada'])) . ' → ' . date('d/m/Y', strtotime($u['fecha_salida']));
                            ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($future_res)): ?>
                <!-- Próximas reservas confirmadas -->
                <?php foreach (array_slice($future_res, 0, 2) as $fres): ?>
                <div class="unit-future-badge">
                    <i class="fas fa-clock"></i>
                    <span><strong><?php echo htmlspecialchars($fres['nombre_cliente']); ?></strong> · 
                    <?php echo date('d/m', strtotime($fres['fecha_entrada'])); ?> → <?php echo date('d/m', strtotime($fres['fecha_salida'])); ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>

</div>

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

<script>
// ======== ACTUALIZACIÓN AUTOMÁTICA DE ESTADO ========
document.querySelectorAll('.unit-status-select').forEach(function(select) {
    select.addEventListener('change', function() {
        const unitId = this.dataset.unitId;
        const nuevoEstado = this.value;
        const numInput = document.querySelector('.unit-number-input[data-unit-id="' + unitId + '"]');
        const indicator = document.getElementById('save-ind-' + unitId);
        const pill = document.getElementById('pill-' + unitId);
        const pillText = document.getElementById('pill-text-' + unitId);

        indicator.className = 'auto-save-indicator saving';
        indicator.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:0.55rem;"></i> guardando...';

        const formData = new FormData();
        formData.append('ajax_update_unit', '1');
        formData.append('unit_id', unitId);
        formData.append('estado', nuevoEstado);
        if (numInput) formData.append('numero_habitacion', numInput.value);

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Actualizar pill
                pill.className = 'status-pill ' + nuevoEstado;
                pillText.textContent = nuevoEstado;

                indicator.className = 'auto-save-indicator saved';
                indicator.innerHTML = '<i class="fas fa-check" style="font-size:0.55rem;"></i> guardado';
                setTimeout(() => {
                    indicator.className = 'auto-save-indicator';
                    indicator.innerHTML = '<i class="fas fa-circle-check" style="font-size:0.55rem;"></i> automático';
                }, 2500);

                // Toast de confirmación suave
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Estado actualizado',
                        text: 'Unidad ' + (numInput ? numInput.value : unitId) + ': ' + nuevoEstado,
                        icon: 'success',
                        background: '#151921', color: '#fff',
                        confirmButtonColor: '#c5a059',
                        toast: true, position: 'bottom-end',
                        showConfirmButton: false, timer: 2000, timerProgressBar: true
                    });
                }
            } else {
                indicator.className = 'auto-save-indicator error';
                indicator.innerHTML = '<i class="fas fa-exclamation-triangle" style="font-size:0.55rem;"></i> error';
                // Revertir selección
                this.value = this.dataset.current;
            }
        })
        .catch(() => {
            indicator.className = 'auto-save-indicator error';
            indicator.innerHTML = '<i class="fas fa-exclamation-triangle" style="font-size:0.55rem;"></i> sin conexión';
            this.value = this.dataset.current;
        });
    });
});

// Guardar número de habitación al perder foco (blur)
document.querySelectorAll('.unit-number-input').forEach(function(input) {
    let originalValue = input.value;
    input.addEventListener('blur', function() {
        const newVal = this.value.trim();
        if (!newVal || newVal === originalValue) return;
        const unitId = this.dataset.unitId;
        const select = document.querySelector('.unit-status-select[data-unit-id="' + unitId + '"]');
        const indicator = document.getElementById('save-ind-' + unitId);

        indicator.className = 'auto-save-indicator saving';
        indicator.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:0.55rem;"></i> guardando #...';

        const formData = new FormData();
        formData.append('ajax_update_unit', '1');
        formData.append('unit_id', unitId);
        formData.append('estado', select ? select.value : 'disponible');
        formData.append('numero_habitacion', newVal);

        fetch(window.location.href, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                originalValue = newVal;
                indicator.className = 'auto-save-indicator saved';
                indicator.innerHTML = '<i class="fas fa-check" style="font-size:0.55rem;"></i> #' + newVal + ' guardado';
                setTimeout(() => {
                    indicator.className = 'auto-save-indicator';
                    indicator.innerHTML = '<i class="fas fa-circle-check" style="font-size:0.55rem;"></i> automático';
                }, 2500);
            } else {
                this.value = originalValue;
                indicator.className = 'auto-save-indicator error';
                indicator.innerHTML = '<i class="fas fa-times" style="font-size:0.55rem;"></i> error al guardar';
            }
        })
        .catch(() => { this.value = originalValue; });
    });
});
</script>

<?php include_once "views/layouts/admin_footer.php"; ?>
