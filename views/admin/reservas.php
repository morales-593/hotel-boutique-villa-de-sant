<?php
require_once "config/config.php";
require_once "config/database.php";

$db = (new Database())->getConnection();

// Lógica de actualización de estado
if (isset($_POST['update_status'])) {
    $reserva_id = intval($_POST['reserva_id']);
    $nuevo_estado = $_POST['estado'];
    
    // Si se cancela, liberar la habitación
    if ($nuevo_estado == 'cancelada') {
        $stmt_room = $db->prepare("UPDATE habitaciones SET estado = 'disponible' WHERE id = (SELECT habitacion_id FROM reservas WHERE id = ?)");
        $stmt_room->execute([$reserva_id]);
    }
    
    $stmt = $db->prepare("UPDATE reservas SET estado = ? WHERE id = ?");
    if ($stmt->execute([$nuevo_estado, $reserva_id])) {
        $success_json = json_encode([
            'title' => '¡Actualizado!',
            'text' => "Estado de reserva #$reserva_id actualizado a $nuevo_estado.",
            'icon' => 'success'
        ]);
    }
}

// Obtener reservas con info de habitación
$query = "
    SELECT r.*, h.nombre AS room_name, h.numero AS room_num
    FROM reservas r
    JOIN habitaciones h ON r.habitacion_id = h.id
    ORDER BY r.created_at DESC
";
$reservas = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

include_once "views/layouts/admin_header.php";
?>

<div class="admin-container" style="max-width: 1400px; margin: 0 auto; color: white; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h2 class="serif gold-text" style="font-size: clamp(1.4rem, 4vw, 2rem); margin: 0;">Gestión de Reservas</h2>
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

    <div class="admin-table-container">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead style="background: rgba(212, 175, 55, 0.1); color: var(--primary-gold); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px;">
                <tr>
                    <th style="padding: 15px;">ID</th>
                    <th style="padding: 15px;">Cliente</th>
                    <th style="padding: 15px;">Teléfono</th>
                    <th style="padding: 15px;">Estancia (IN/OUT)</th>
                    <th style="padding: 15px;">Habitación</th>
                    <th style="padding: 15px;">Total</th>
                    <th style="padding: 15px;">Estado</th>
                    <th style="padding: 15px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $res): ?>
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05); font-size: 0.85rem;">
                    <td style="padding: 15px;">#<?php echo str_pad($res['id'], 4, '0', STR_PAD_LEFT); ?></td>
                    <td style="padding: 15px;">
                        <strong><?php echo htmlspecialchars($res['nombre_cliente']); ?></strong><br>
                        <span style="font-size: 0.75rem; color: #888;"><?php echo htmlspecialchars($res['email_cliente']); ?></span>
                    </td>
                    <td style="padding: 15px;"><?php echo htmlspecialchars($res['telefono_cliente']); ?></td>
                    <td style="padding: 15px;">
                        <?php echo date('d/m', strtotime($res['fecha_entrada'])); ?> - <?php echo date('d/m', strtotime($res['fecha_salida'])); ?><br>
                        <span style="font-size: 0.75rem; color: #888;"><?php echo $res['num_huespedes']; ?> Huéspedes</span>
                    </td>
                    <td style="padding: 15px;">
                        <?php echo htmlspecialchars($res['room_name']); ?><br>
                        <span style="color: var(--primary-gold);">Unidad: <?php echo $res['room_num']; ?></span>
                    </td>
                    <td style="padding: 15px; font-weight: 700;">$<?php echo number_format($res['total'], 2); ?></td>
                    <td style="padding: 15px;">
                        <?php 
                        $colors = ['pendiente' => '#f1c40f', 'confirmada' => '#2ecc71', 'cancelada' => '#e74c3c'];
                        $color = $colors[$res['estado']] ?? '#888';
                        ?>
                        <span style="background: <?php echo $color; ?>; color: black; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">
                            <?php echo $res['estado']; ?>
                        </span>
                    </td>
                    <td style="padding: 15px;">
                        <form method="POST" style="display: flex; gap: 5px;">
                            <input type="hidden" name="reserva_id" value="<?php echo $res['id']; ?>">
                            <input type="hidden" name="update_status" value="1">
                            <select name="estado" onchange="this.form.submit()" style="background: #222; color: #ccc; border: 1px solid #444; padding: 5px; border-radius: 4px; font-size: 0.75rem;">
                                <option value="pendiente" <?php echo $res['estado'] == 'pendiente' ? 'selected' : ''; ?>>Marcar Pendiente</option>
                                <option value="confirmada" <?php echo $res['estado'] == 'confirmada' ? 'selected' : ''; ?>>Confirmar</option>
                                <option value="cancelada" <?php echo $res['estado'] == 'cancelada' ? 'selected' : ''; ?>>Cancelar</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once "views/layouts/admin_footer.php"; ?>
