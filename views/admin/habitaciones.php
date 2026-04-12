<?php
require_once "config/config.php";
require_once "config/database.php";

$db = (new Database())->getConnection();

// Lógica de actualización de estado de unidad
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

// Unidades agrupadas por tipo para una mejor interfaz
$stmt_units = $db->query("SELECT * FROM habitaciones ORDER BY tipo, numero");
$unidades_raw = $stmt_units->fetchAll(PDO::FETCH_ASSOC);

$unidades_por_tipo = [];
foreach ($unidades_raw as $u) {
    $unidades_por_tipo[$u['nombre']][] = $u;
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
        display: flex; justify-content: space-between; align-items: center;
    }
    .unit-card:hover { 
        border-color: rgba(212, 175, 55, 0.4); 
        background: rgba(212, 175, 55, 0.03);
        transform: translateY(-2px);
    }
    
    .unit-info .unit-num { font-size: 0.65rem; color: #888; font-weight: 800; letter-spacing: 1px; }
    .unit-info .unit-type { font-size: 0.95rem; color: #ddd; margin: 2px 0; }
    
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
    }
</style>

<div class="admin-container" style="color: white; padding: 20px;">
    
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 15px;">
        <h2 class="serif gold-text" style="font-size: clamp(1.4rem, 4vw, 2.2rem); margin: 0;">Gestión de Habitaciones</h2>
        <a href="index.php?action=dashboard" class="btn-gold" style="padding: 10px 22px; font-size: 0.75rem; border-radius: 50px;">
            <i class="fas fa-arrow-left"></i> VOLVER AL DASHBOARD
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
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px;">
            <?php foreach ($unidades as $u): ?>
            <div class="unit-card">
                <form method="POST" style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
                    <input type="hidden" name="unit_id" value="<?php echo $u['id']; ?>">
                    <input type="hidden" name="update_unit_status" value="1">
                    
                    <div class="unit-info">
                        <div class="unit-num">HABITACIÓN #</div>
                        <input type="text" name="numero_habitacion" value="<?php echo $u['numero']; ?>" 
                               style="background: transparent; border: 1px solid rgba(212,175,55,0.3); color: white; width: 60px; padding: 4px; border-radius: 4px; font-weight: 700; margin-bottom: 5px; font-size: 0.85rem;">
                        <br>
                        <div class="status-pill <?php echo $u['estado']; ?>">
                            <div style="width: 5px; height: 5px; border-radius: 50%; background: currentColor;"></div>
                            <?php echo $u['estado']; ?>
                        </div>
                    </div>
                    
                    <div class="unit-actions" style="display: flex; flex-direction: column; gap: 8px;">
                        <select name="estado" style="width: 100%;">
                            <option value="disponible" <?php echo $u['estado'] == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                            <option value="ocupado" <?php echo $u['estado'] == 'ocupado' ? 'selected' : ''; ?>>Ocupado</option>
                            <option value="mantenimiento" <?php echo $u['estado'] == 'mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                        </select>
                        <button type="submit" class="btn-gold" style="padding: 4px 8px; font-size: 0.65rem; border-radius: 4px; width: 100%;">GUARDAR</button>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>

</div>

<!-- SWEETALERT NOTIFICATION -->
<?php if ($success_json): ?>
<script>
    const data = <?php echo $success_json; ?>;
    Swal.fire({
        ...data,
        background: '#151921',
        color: '#fff',
        confirmButtonColor: '#c5a059',
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
</script>
<?php endif; ?>

<?php include_once "views/layouts/admin_footer.php"; ?>
