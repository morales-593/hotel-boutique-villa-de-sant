<?php
require_once "config/config.php";
require_once "config/database.php";

$db = (new Database())->getConnection();

// Lógica de edición/actualización
if (isset($_POST['save_coupon'])) {
    $id = intval($_POST['coupon_id']);
    $codigo = strtoupper(trim($_POST['codigo']));
    $habitacion_tipo = !empty($_POST['habitacion_tipo']) ? $_POST['habitacion_tipo'] : null;
    $descuento = intval($_POST['descuento']);
    $fecha_ini = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    $stmt = $db->prepare("UPDATE cupones SET habitacion_tipo = ?, codigo = ?, descuento = ?, fecha_inicio = ?, fecha_fin = ?, activo = ? WHERE id = ?");
    if ($stmt->execute([$habitacion_tipo, $codigo, $descuento, $fecha_ini, $fecha_fin, $activo, $id])) {
        $success_json = json_encode(['title' => '¡Actualizado!', 'text' => 'Los cambios se han aplicado.', 'icon' => 'success']);
    }
}

// Obtener todos los cupones y categorías de habitación
$cupones = $db->query("SELECT * FROM cupones ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $db->query("SELECT DISTINCT tipo, nombre FROM habitaciones ORDER BY precio ASC")->fetchAll(PDO::FETCH_ASSOC);

include_once "views/layouts/admin_header.php";
?>

<div class="admin-container" style="color: white; padding: 20px;">
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
            timer: 3000
        });
    </script>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px;">
        <?php foreach ($cupones as $cup): 
            $today = date('Y-m-d');
            $is_expired = ($cup['fecha_fin'] < $today);
            $status_color = ($cup['activo'] && !$is_expired) ? '#2ecc71' : '#e74c3c';
            $status_label = $cup['activo'] ? ($is_expired ? 'EXPIRADO (INACTIVO)' : 'ACTIVO') : 'DESACTIVADO';
        ?>
        <div class="glass-card" style="padding: 22px; position: relative; border-color: <?php echo $status_color; ?>44;">
            <div style="margin-bottom: 20px;">
                <div style="font-family: 'Space Mono', monospace; font-size: 1.5rem; color: var(--primary-gold); font-weight: 800; letter-spacing: 2px;">
                    <?php echo htmlspecialchars($cup['codigo']); ?>
                </div>
                <div style="font-size: 0.65rem; color: <?php echo $status_color; ?>; font-weight: 900; letter-spacing: 1px; margin-top: 5px;">
                    ● <?php echo $status_label; ?>
                    <?php if($cup['habitacion_tipo']): ?> | APLICA A: <?php echo strtoupper($cup['habitacion_tipo']); ?><?php else: ?> | GLOBAL<?php endif; ?>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="coupon_id" value="<?php echo $cup['id']; ?>">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; font-size: 0.65rem; color: #888; margin-bottom: 5px;">CÓDIGO DE CUPÓN</label>
                        <input type="text" name="codigo" value="<?php echo htmlspecialchars($cup['codigo']); ?>" style="width: 100%; text-transform: uppercase;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.65rem; color: #888; margin-bottom: 5px;">HABITACIÓN APLICABLE</label>
                        <select name="habitacion_tipo" style="width: 100%; padding: 8px; border-radius: 5px; border: 1px solid rgba(212,175,55,0.3); background: #111; color: white;">
                            <option value="">Todas (Global)</option>
                            <?php foreach($categorias as $cat): ?>
                                <option value="<?php echo $cat['tipo']; ?>" <?php echo $cup['habitacion_tipo'] === $cat['tipo'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div>
                        <label style="display: block; font-size: 0.65rem; color: #888; margin-bottom: 5px;">DESCUENTO</label>
                        <div style="position: relative;">
                            <input type="number" name="descuento" value="<?php echo $cup['descuento']; ?>" style="width: 100%; padding-right: 25px;">
                            <span style="position: absolute; right: 10px; top: 10px; color: #555;">%</span>
                        </div>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.65rem; color: #888; margin-bottom: 5px;">ESTADO MANUAL</label>
                        <div style="display: flex; align-items: center; gap: 8px; height: 38px;">
                            <input type="checkbox" name="activo" id="act-<?php echo $cup['id']; ?>" <?php echo $cup['activo'] ? 'checked' : ''; ?> style="width: auto;">
                            <label for="act-<?php echo $cup['id']; ?>" style="font-size: 0.75rem;">Habilitar</label>
                        </div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 25px;">
                    <div>
                        <label style="display: block; font-size: 0.65rem; color: #888; margin-bottom: 5px;">FECHA INICIO</label>
                        <input type="date" name="fecha_inicio" value="<?php echo $cup['fecha_inicio']; ?>" style="width: 100%; font-size: 0.8rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.65rem; color: #888; margin-bottom: 5px;">FECHA EXPIRACIÓN</label>
                        <input type="date" name="fecha_fin" value="<?php echo $cup['fecha_fin']; ?>" style="width: 100%; font-size: 0.8rem; border-color: <?php echo $is_expired ? '#e74c3c' : 'rgba(197, 160, 89, 0.3)'; ?>;">
                    </div>
                </div>

                <button type="submit" name="save_coupon" class="btn-gold" style="width: 100%; justify-content: center; font-size: 0.75rem;">
                    ACTUALIZAR CUPÓN
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include_once "views/layouts/admin_header.php"; ?>
