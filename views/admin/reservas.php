<?php
require_once "config/config.php";
require_once "config/database.php";
require_once "models/Reservation.php";
require_once "models/Room.php";

$db = (new Database())->getConnection();
$resModel = new Reservation($db);
$roomModel = new Room($db);

// Obtener solo una habitación por categoría para la selección manual
$stmt_rooms = $db->query("SELECT * FROM habitaciones GROUP BY nombre");
$room_categories = $stmt_rooms->fetchAll(PDO::FETCH_ASSOC);

// Lógica de actualización de estado
if (isset($_POST['update_status'])) {
    $reserva_id = intval($_POST['reserva_id']);
    $nuevo_estado = $_POST['estado'];
    
    // Si se cancela, liberar la habitación
    if ($nuevo_estado == 'cancelada') {
        $stmt_room = $db->prepare("UPDATE habitaciones SET estado = 'disponible' WHERE id = (SELECT habitacion_id FROM reservas WHERE id = ?)");
        $stmt_room->execute([$reserva_id]);
    }
    
    if ($resModel->updateStatus($reserva_id, $nuevo_estado)) {
        $success_json = json_encode(['title' => '¡Actualizado!', 'text' => "Estado de reserva #$reserva_id actualizado.", 'icon' => 'success']);
    }
}

// Lógica de Reserva Manual
if (isset($_POST['manual_reserva'])) {
    $data = [
        'nombre_cliente' => $_POST['nombre'],
        'email_cliente' => $_POST['email'],
        'telefono_cliente' => $_POST['telefono'],
        'habitacion_id' => $_POST['habitacion_id'],
        'num_huespedes' => $_POST['huespedes'],
        'fecha_entrada' => $_POST['entrada'],
        'fecha_salida' => $_POST['salida'],
        'total' => $_POST['total'],
        'estado' => 'confirmada'
    ];
    if ($resModel->create($data)) {
        $success_json = json_encode(['title' => '¡Reserva Creada!', 'text' => "La reserva manual ha sido registrada.", 'icon' => 'success']);
    }
}

// Lógica de Eliminación
if (isset($_POST['delete_reserva'])) {
    $id = intval($_POST['reserva_id']);
    if ($resModel->delete($id)) {
        $success_json = json_encode(['title' => 'Reserva Eliminada', 'text' => 'El registro ha sido removido y los IDs reordenados.', 'icon' => 'info']);
    }
}

// --- Lógica de Búsqueda y Paginación ---
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if (!in_array($limit, [5, 10, 15])) $limit = 10;

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (r.nombre_cliente LIKE ? OR r.email_cliente LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($date_filter) {
    $where .= " AND (r.fecha_entrada = ? OR r.fecha_salida = ?)";
    $params[] = $date_filter;
    $params[] = $date_filter;
}

// Contador total para paginación
$count_query = "SELECT COUNT(*) FROM reservas r $where";
$stmt_count = $db->prepare($count_query);
$stmt_count->execute($params);
$total_rows = $stmt_count->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Obtener reservas con info de habitación y filtros
$query = "
    SELECT r.*, h.nombre AS room_name, h.numero AS room_num
    FROM reservas r
    JOIN habitaciones h ON r.habitacion_id = h.id
    $where
    ORDER BY r.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once "views/layouts/admin_header.php";
?>

<div class="admin-container" style="color: white; padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h2 class="serif gold-text" style="font-size: clamp(1.4rem, 4vw, 2rem); margin: 0;">Gestión de Reservas</h2>
        <div style="display: flex; gap: 10px;">
            <button onclick="showReservaModal()" class="btn-gold">
                <i class="fas fa-calendar-plus"></i> NUEVA RESERVA
            </button>
            <a href="index.php?action=dashboard" class="btn-gold" style="padding: 10px 20px; font-size: 0.75rem; border-radius: 50px; background: rgba(255,255,255,0.05);">
                <i class="fas fa-arrow-left"></i> DASHBOARD
            </a>
        </div>
    </div>

    <!-- Barra de Filtros y Búsqueda -->
    <div class="glass-card" style="padding: 1.5rem; margin-bottom: 2rem;">
        <form method="GET" action="index.php" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <input type="hidden" name="action" value="admin-reservas">
            
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; font-size: 0.7rem; color: var(--primary); margin-bottom: 5px; text-transform: uppercase;">Buscar Cliente</label>
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 12px; color: #555;"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nombre o email..." style="width: 100%; padding-left: 35px;">
                </div>
            </div>

            <div style="width: 150px;">
                <label style="display: block; font-size: 0.7rem; color: var(--primary); margin-bottom: 5px; text-transform: uppercase;">Fecha</label>
                <input type="date" name="date" value="<?php echo $date_filter; ?>" style="width: 100%;">
            </div>

            <div style="width: 80px;">
                <label style="display: block; font-size: 0.7rem; color: var(--primary); margin-bottom: 5px; text-transform: uppercase;">Ver</label>
                <select name="limit" style="width: 100%;" onchange="this.form.submit()">
                    <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
                </select>
            </div>

            <button type="submit" class="btn-gold" style="padding: 10px 20px;">
                <i class="fas fa-filter"></i>
            </button>
            
            <?php if($search || $date_filter): ?>
            <a href="index.php?action=admin-reservas" class="nav-link" style="margin: 0; padding: 10px; color: #e74c3c;">
                <i class="fas fa-times-circle"></i>
            </a>
            <?php endif; ?>
        </form>
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
            timer: 3500,
            timerProgressBar: true
        });
    </script>
    <?php endif; ?>

    <div class="admin-table-container">
        <table style="width: 100%;">
            <thead style="background: rgba(255, 255, 255, 0.02);">
                <tr>
                    <th style="padding: 15px;">ID</th>
                    <th style="padding: 15px;">Cliente</th>
                    <th style="padding: 15px;">Estancia</th>
                    <th style="padding: 15px;">Habitación</th>
                    <th style="padding: 15px;">Total</th>
                    <th style="padding: 15px;">Estado</th>
                    <th style="padding: 15px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $res): ?>
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
                    <td style="padding: 15px;">#<?php echo str_pad($res['id'], 3, '0', STR_PAD_LEFT); ?></td>
                    <td style="padding: 15px;">
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($res['nombre_cliente']); ?></div>
                        <div style="font-size: 0.75rem; color: #888;"><?php echo htmlspecialchars($res['email_cliente']); ?></div>
                    </td>
                    <td style="padding: 15px;">
                        <div style="font-size: 0.85rem;"><?php echo date('d MIL', strtotime($res['fecha_entrada'])); ?> - <?php echo date('d MIL', strtotime($res['fecha_salida'])); ?></div>
                        <div style="font-size: 0.75rem; color: #666;"><?php echo $res['num_huespedes']; ?> Huéspedes</div>
                    </td>
                    <td style="padding: 15px;">
                        <div style="font-size: 0.85rem;"><?php echo htmlspecialchars($res['room_name']); ?></div>
                        <div style="font-size: 0.75rem; color: var(--primary);">Unidad <?php echo $res['room_num']; ?></div>
                    </td>
                    <td style="padding: 15px; font-weight: 700;">$<?php echo number_format($res['total'], 2); ?></td>
                    <td style="padding: 15px;">
                        <form method="POST">
                            <input type="hidden" name="reserva_id" value="<?php echo $res['id']; ?>">
                            <input type="hidden" name="update_status" value="1">
                            <?php 
                                $status_colors = ['pendiente' => '#f1c40f', 'confirmada' => '#2ecc71', 'cancelada' => '#e74c3c'];
                                $current_color = $status_colors[$res['estado']] ?? '#fff';
                            ?>
                            <select name="estado" onchange="this.form.submit()" style="border-color: <?php echo $current_color; ?>; color: <?php echo $current_color; ?>; padding: 4px; font-size: 0.75rem; background: transparent;">
                                <option value="pendiente" <?php echo $res['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="confirmada" <?php echo $res['estado'] == 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                                <option value="cancelada" <?php echo $res['estado'] == 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                            </select>
                        </form>
                    </td>
                    <td style="padding: 15px; text-align: right;">
                        <button onclick="confirmDeleteReserva(<?php echo $res['id']; ?>)" class="btn-gold" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; border: 1px solid rgba(231, 76, 60, 0.2); padding: 5px 10px;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if($total_pages > 1): ?>
    <div style="display: flex; justify-content: center; gap: 10px; margin-top: 30px;">
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?action=admin-reservas&page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&date=<?php echo $date_filter; ?>" 
               class="btn-gold" style="padding: 5px 12px; background: <?php echo $page == $i ? 'var(--primary)' : 'transparent'; ?>; color: <?php echo $page == $i ? 'black' : 'white'; ?>;">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Modal Reserva Manual -->
<div id="reservaModal" class="modal-overlay">
    <div class="glass-card modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 class="serif gold-text">Nueva Reserva Manual</h3>
            <button onclick="hideReservaModal()" style="background:none; border:none; color:#888; cursor:pointer; font-size:1.5rem;">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="manual_reserva" value="1">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="grid-column: span 2;">
                    <label style="font-size: 0.7rem; color: #888;">NOMBRE DEL CLIENTE</label>
                    <input type="text" name="nombre" required style="width: 100%;">
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #888;">EMAIL</label>
                    <input type="email" name="email" required style="width: 100%;">
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #888;">TELÉFONO</label>
                    <input type="text" name="telefono" required style="width: 100%;">
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #888;">CATEGORÍA DE HABITACIÓN</label>
                    <select name="habitacion_id" required style="width: 100%;">
                        <?php foreach($room_categories as $room): ?>
                            <option value="<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #888;">HUÉSPEDES</label>
                    <input type="number" name="huespedes" min="1" max="10" value="2" required style="width: 100%;">
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #888;">LLEGADA</label>
                    <input type="date" name="entrada" required style="width: 100%;">
                </div>
                <div>
                    <label style="font-size: 0.7rem; color: #888;">SALIDA</label>
                    <input type="date" name="salida" required style="width: 100%;">
                </div>
                <div style="grid-column: span 2;">
                    <label style="font-size: 0.7rem; color: #888;">TOTAL A PAGAR ($)</label>
                    <input type="number" step="0.01" name="total" required style="width: 100%;">
                </div>
            </div>
            <button type="submit" class="btn-gold" style="width: 100%; margin-top: 20px; justify-content: center;">
                <i class="fas fa-save"></i> GUARDAR RESERVA
            </button>
        </form>
    </div>
</div>

<form id="reservaDeleteForm" method="POST" style="display:none;">
    <input type="hidden" name="reserva_id" id="reservaDeleteId">
    <input type="hidden" name="delete_reserva" value="1">
</form>

<script>
    function showReservaModal() { document.getElementById('reservaModal').classList.add('active'); }
    function hideReservaModal() { document.getElementById('reservaModal').classList.remove('active'); }
    
    function confirmDeleteReserva(id) {
        Swal.fire({
            title: '¿Eliminar Reserva?',
            text: "Esta acción es permanente y reordenará los números de reserva.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: '#151921',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('reservaDeleteId').value = id;
                document.getElementById('reservaDeleteForm').submit();
            }
        })
    }

    // Cerrar modal al hacer clic fuera
    document.getElementById('reservaModal').addEventListener('click', function(e) {
        if (e.target === this) hideReservaModal();
    });
</script>

<?php include_once "views/layouts/admin_footer.php"; ?>
