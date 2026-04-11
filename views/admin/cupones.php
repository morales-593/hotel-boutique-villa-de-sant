<?php
require_once "config/config.php";
require_once "config/database.php";

$db = (new Database())->getConnection();

// Lógica de edición/actualización
if (isset($_POST['save_coupon'])) {
    $id = intval($_POST['coupon_id']);
    $descuento = intval($_POST['descuento']);
    $fecha_ini = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    $stmt = $db->prepare("UPDATE cupones SET descuento = ?, fecha_inicio = ?, fecha_fin = ?, activo = ? WHERE id = ?");
    if ($stmt->execute([$descuento, $fecha_ini, $fecha_fin, $activo, $id])) {
        $success_json = json_encode([
            'title' => '¡Cupón Guardado!',
            'text' => 'Los cambios en el cupón se han aplicado correctamente.',
            'icon' => 'success'
        ]);
    }
}

// Obtener todos los cupones
$cupones = $db->query("SELECT * FROM cupones")->fetchAll(PDO::FETCH_ASSOC);

include_once "views/layouts/admin_header.php";
?>

<div class="admin-container" style="max-width: 1200px; margin: 0 auto; color: white; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h2 class="serif gold-text" style="font-size: clamp(1.4rem, 4vw, 2rem); margin: 0;">Gestión de Cupones</h2>
        <a href="index.php?action=dashboard" class="btn-gold" style="padding: 10px 20px; font-size: 0.75rem; border-radius: 50px;">
            <i class="fas fa-arrow-left"></i> VOLVER AL DASHBOARD
        </a>
    </div>

    <?php if (isset($success_json)): ?>
    <script>
        Swal.fire({
            ...<?php echo $success_json; ?>,
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

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
        <?php foreach ($cupones as $cup): 
            $is_expired = strtotime($cup['fecha_fin']) < time();
            $status_class = ($cup['activo'] && !$is_expired) ? 'color: #2ecc71;' : 'color: #e74c3c;';
            $status_text = $cup['activo'] ? ($is_expired ? 'EXPIRADO' : 'ACTIVO') : 'DESACTIVADO';
        ?>
        <div style="background: rgba(17, 17, 17, 0.8); border: 1px solid rgba(212, 175, 55, 0.3); border-radius: 12px; padding: 25px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <span style="font-family: monospace; font-size: 1.4rem; color: var(--primary-gold); font-weight: 800;"><?php echo $cup['codigo']; ?></span>
                <span style="font-size: 0.7rem; font-weight: 800; <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
            </div>
            
            <p style="font-size: 0.75rem; color: #888; margin-bottom: 20px;">Habitación: <span style="color: white;"><?php echo strtoupper($cup['habitacion_tipo'] ?: 'GLOBAL'); ?></span></p>

            <form method="POST">
                <input type="hidden" name="coupon_id" value="<?php echo $cup['id']; ?>">
                
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 0.7rem; color: #666; margin-bottom: 4px;">DESCUENTO (%)</label>
                    <input type="number" name="descuento" value="<?php echo $cup['descuento']; ?>" style="width: 100%; background: #222; border: 1px solid #444; color: white; padding: 10px; border-radius: 6px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: #666; margin-bottom: 4px;">INICIO</label>
                        <input type="date" name="fecha_inicio" value="<?php echo $cup['fecha_inicio']; ?>" style="width: 100%; background: #222; border: 1px solid #444; color: white; padding: 8px; border-radius: 6px; font-size: 0.8rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.7rem; color: #666; margin-bottom: 4px;">FIN</label>
                        <input type="date" name="fecha_fin" value="<?php echo $cup['fecha_fin']; ?>" style="width: 100%; background: #222; border: 1px solid #444; color: white; padding: 8px; border-radius: 6px; font-size: 0.8rem;">
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <input type="checkbox" name="activo" id="act-<?php echo $cup['id']; ?>" <?php echo $cup['activo'] ? 'checked' : ''; ?>>
                    <label for="act-<?php echo $cup['id']; ?>" style="font-size: 0.8rem;">Cupón Activo</label>
                </div>

                <button type="submit" name="save_coupon" class="btn btn-gold" style="width: 100%; padding: 10px; font-size: 0.8rem;">GARDAR CAMBIOS</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include_once "views/layouts/admin_footer.php"; ?>
