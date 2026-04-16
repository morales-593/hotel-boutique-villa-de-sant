<?php
require_once "config/config.php";
require_once "config/database.php";
require_once "models/Reservation.php";
require_once "models/Room.php";

$db = (new Database())->getConnection();
$resModel = new Reservation($db);
$roomModel = new Room($db);

// Obtener habitaciones para selección manual
$stmt_rooms = $db->query("SELECT id, tipo, numero, nombre, precio FROM habitaciones WHERE estado = 'disponible' ORDER BY tipo, numero");
$available_rooms = $stmt_rooms->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías para filtro
$stmt_cats = $db->query("SELECT DISTINCT tipo, nombre FROM habitaciones ORDER BY tipo");
$room_categories = $stmt_cats->fetchAll(PDO::FETCH_ASSOC);

$success_json = null;

// ======== ACTUALIZAR ESTADO ========
if (isset($_POST['update_status'])) {
    $reserva_id = intval($_POST['reserva_id']);
    $nuevo_estado = $_POST['estado'];
    
    if ($nuevo_estado == 'cancelada') {
        $stmt_room = $db->prepare("UPDATE habitaciones SET estado = 'disponible' WHERE id = (SELECT habitacion_id FROM reservas WHERE id = ?)");
        $stmt_room->execute([$reserva_id]);
    }
    
    if ($resModel->updateStatus($reserva_id, $nuevo_estado)) {
        $success_data = [
            'title' => '¡Actualizado!', 
            'text' => "Estado de reserva #$reserva_id actualizado.", 
            'icon' => 'success'
        ];

        // Si se confirma, preparar link de WhatsApp con el idioma guardado
        if ($nuevo_estado == 'confirmada') {
            $stmt = $db->prepare("SELECT r.*, h.nombre as room_name FROM reservas r JOIN habitaciones h ON r.habitacion_id = h.id WHERE r.id = ?");
            $stmt->execute([$reserva_id]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($res && !empty($res['telefono_cliente'])) {
                $tel = preg_replace('/[^0-9]/', '', $res['telefono_cliente']);
                if (strlen($tel) == 9 && $tel[0] == '9') $tel = '593' . $tel;
                
                $idioma = $res['idioma'] ?? 'es';
                $name = $res['nombre_cliente'];
                $room = $res['room_name'];

                $msgs = [
                    'es' => "Hola $name, te saluda el *Hotel Boutique Villa de Sant*. 🏨\n\nTu reserva para la habitación *$room* ha sido *CONFIRMADA*. ✅\n\n¡Estamos ansiosos por recibirte!",
                    'en' => "Hi $name, greetings from *Hotel Boutique Villa de Sant*. 🏨\n\nYour reservation for the *$room* has been *CONFIRMED*. ✅\n\nWe look forward to welcoming you!",
                    'fr' => "Bonjour $name, salutations de l' *Hôtel Boutique Villa de Sant*. 🏨\n\nVotre réservation pour la chambre *$room* a été *CONFIRMÉE*. ✅\n\nNous avons hâte de vous accueillir !",
                    'de' => "Hallo $name, Grüße vom *Hotel Boutique Villa de Sant*. 🏨\n\nIhre Reservierung für das Zimmer *$room* wurde *BESTÄTIGT*. ✅\n\nWir freuen uns darauf, Sie begrüßen zu dürfen!"
                ];

                $msg = $msgs[$idioma] ?? $msgs['es'];
                $success_data['whatsapp_url'] = "https://wa.me/$tel?text=" . urlencode($msg);
            }
        }
        $success_json = json_encode($success_data);
    }
}

// ======== EDITAR RESERVA COMPLETA ========
if (isset($_POST['edit_reserva'])) {
    $id = intval($_POST['reserva_id']);
    $stmt = $db->prepare("UPDATE reservas SET 
        nombre_cliente = ?, 
        email_cliente = ?, 
        telefono_cliente = ?,
        habitacion_id = ?,
        num_huespedes = ?,
        fecha_entrada = ?,
        fecha_salida = ?,
        total = ?,
        estado = ?,
        cupon_codigo = ?,
        descuento_aplicado = ?,
        notas = ?,
        idioma = ?
        WHERE id = ?");
    
    $cupon = trim($_POST['cupon']) ?: null;
    $notas = trim($_POST['notas']) ?: null;
    
    if ($stmt->execute([
        $_POST['nombre'], $_POST['email'], $_POST['telefono'],
        intval($_POST['habitacion_id']), intval($_POST['huespedes']),
        $_POST['entrada'], $_POST['salida'], floatval($_POST['total']),
        $_POST['estado'], $cupon, intval($_POST['descuento']),
        $notas, $_POST['idioma'], $id
    ])) {
        $success_data = [
            'title' => '¡Reserva Editada!', 
            'text' => "La reserva #$id fue actualizada correctamente.", 
            'icon' => 'success'
        ];

        // Si se confirma en la edición, también preparar link con idioma
        if ($_POST['estado'] == 'confirmada') {
            $stmt_room = $db->prepare("SELECT nombre FROM habitaciones WHERE id = ?");
            $stmt_room->execute([intval($_POST['habitacion_id'])]);
            $room_name = $stmt_room->fetchColumn();

            if (!empty($_POST['telefono'])) {
                $tel = preg_replace('/[^0-9]/', '', $_POST['telefono']);
                if (strlen($tel) == 9 && $tel[0] == '9') $tel = '593' . $tel;
                
                $idioma = $_POST['idioma'] ?? 'es';
                $name = $_POST['nombre'];

                $msgs = [
                    'es' => "Hola $name, te saluda el *Hotel Boutique Villa de Sant*. 🏨\n\nTu reserva para la habitación *$room_name* ha sido *CONFIRMADA*. ✅\n\n¡Estamos ansiosos por recibirte!",
                    'en' => "Hi $name, greetings from *Hotel Boutique Villa de Sant*. 🏨\n\nYour reservation for the *$room_name* has been *CONFIRMED*. ✅\n\nWe look forward to welcoming you!",
                    'fr' => "Bonjour $name, salutations de l' *Hôtel Boutique Villa de Sant*. 🏨\n\nVotre réservation pour la chambre *$room_name* a été *CONFIRMÉE*. ✅\n\nNous avons hâte de vous accueillir !",
                    'de' => "Hallo $name, Grüße vom *Hotel Boutique Villa de Sant*. 🏨\n\nIhre Reservierung für das Zimmer *$room_name* wurde *BESTÄTIGT*. ✅\n\nWir freuen uns darauf, Sie begrüßen zu dürfen!"
                ];

                $msg = $msgs[$idioma] ?? $msgs['es'];
                $success_data['whatsapp_url'] = "https://wa.me/$tel?text=" . urlencode($msg);
            }
        }
        $success_json = json_encode($success_data);
    }
}

// ======== RESERVA MANUAL ========
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
        // También guardar notas y cupón si se proporcionaron
        $lastId = $db->lastInsertId();
        $notas = trim($_POST['notas'] ?? '');
        $cupon = trim($_POST['cupon'] ?? '');
        $descuento = intval($_POST['descuento'] ?? 0);
        $idioma = $_POST['idioma'] ?? 'es';
        if ($notas || $cupon || $idioma != 'es') {
            $db->prepare("UPDATE reservas SET notas = ?, cupon_codigo = ?, descuento_aplicado = ?, idioma = ? WHERE id = ?")
               ->execute([$notas ?: null, $cupon ?: null, $descuento, $idioma, $lastId]);
        }
        $success_json = json_encode(['title' => '¡Reserva Creada!', 'text' => "La reserva manual ha sido registrada.", 'icon' => 'success']);
    }
}

// ======== ELIMINACIÓN ========
if (isset($_POST['delete_reserva'])) {
    $id = intval($_POST['reserva_id']);
    // Liberar habitación antes de eliminar
    $stmt_room = $db->prepare("UPDATE habitaciones SET estado = 'disponible' WHERE id = (SELECT habitacion_id FROM reservas WHERE id = ?)");
    $stmt_room->execute([$id]);
    if ($resModel->delete($id)) {
        $success_json = json_encode(['title' => 'Reserva Eliminada', 'text' => 'El registro ha sido removido.', 'icon' => 'info']);
    }
}

// ======== BÚSQUEDA, FILTROS Y PAGINACIÓN ========
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
if (!in_array($limit, [5, 10, 15, 25])) $limit = 10;

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "WHERE 1=1";
$params = [];

if ($search) {
    $where .= " AND (r.nombre_cliente LIKE ? OR r.email_cliente LIKE ? OR r.telefono_cliente LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($date_filter) {
    $where .= " AND (r.fecha_entrada = ? OR r.fecha_salida = ?)";
    $params[] = $date_filter;
    $params[] = $date_filter;
}

if ($status_filter) {
    $where .= " AND r.estado = ?";
    $params[] = $status_filter;
}

// Contador total
$count_query = "SELECT COUNT(*) FROM reservas r $where";
$stmt_count = $db->prepare($count_query);
$stmt_count->execute($params);
$total_rows = $stmt_count->fetchColumn();
$total_pages = ceil($total_rows / $limit);

// Obtener reservas con info de habitación
$query = "
    SELECT r.*, h.nombre AS room_name, h.numero AS room_num, h.tipo AS room_tipo, h.precio AS room_price
    FROM reservas r
    JOIN habitaciones h ON r.habitacion_id = h.id
    $where
    ORDER BY r.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($query);
$stmt->execute($params);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats rápidos
$stats_total = $db->query("SELECT COUNT(*) FROM reservas")->fetchColumn();
$stats_pending = $db->query("SELECT COUNT(*) FROM reservas WHERE estado = 'pendiente'")->fetchColumn();
$stats_confirmed = $db->query("SELECT COUNT(*) FROM reservas WHERE estado = 'confirmada'")->fetchColumn();
$stats_revenue = $db->query("SELECT COALESCE(SUM(total), 0) FROM reservas WHERE estado != 'cancelada'")->fetchColumn();

// Meses en español para formateo
$meses_es = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

// Todas las habitaciones para modal de edición
$all_rooms = $db->query("SELECT id, tipo, numero, nombre, precio FROM habitaciones ORDER BY tipo, numero")->fetchAll(PDO::FETCH_ASSOC);

include_once "views/layouts/admin_header.php";
?>

<style>
    /* Stats Cards */
    .res-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
    .res-stat-card { background: var(--card-bg); border: 1px solid var(--gold-border); border-radius: 14px; padding: 18px 20px; transition: transform 0.3s; }
    .res-stat-card:hover { transform: translateY(-3px); box-shadow: var(--gold-glow); }
    .res-stat-label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 6px; }
    .res-stat-value { font-size: 1.8rem; font-weight: 700; }
    .res-stat-value.gold { color: var(--primary); }
    .res-stat-value.green { color: #2ecc71; }
    .res-stat-value.yellow { color: #f1c40f; }

    /* Detail row toggle */
    .detail-row { display: none; }
    .detail-row.active { display: table-row; }
    .detail-cell { background: rgba(197,160,89,0.03) !important; padding: 20px 30px !important; }
    .detail-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
    .detail-item label { display: block; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; color: var(--primary); margin-bottom: 4px; font-weight: 600; }
    .detail-item span { font-size: 0.85rem; color: var(--text); }
    .detail-notes { grid-column: 1 / -1; background: rgba(0,0,0,0.2); padding: 12px 16px; border-radius: 8px; border-left: 3px solid var(--primary); margin-top: 5px; }

    /* Action buttons */
    .action-group { display: flex; gap: 6px; justify-content: flex-end; }
    .btn-action { width: 34px; height: 34px; border-radius: 8px; border: 1px solid; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 0.8rem; transition: all 0.2s; background: transparent; }
    .btn-action.view { color: var(--primary); border-color: rgba(197,160,89,0.3); }
    .btn-action.view:hover { background: rgba(197,160,89,0.15); }
    .btn-action.edit { color: #3498db; border-color: rgba(52,152,219,0.3); }
    .btn-action.edit:hover { background: rgba(52,152,219,0.15); }
    .btn-action.delete { color: #e74c3c; border-color: rgba(231,76,60,0.3); }
    .btn-action.delete:hover { background: rgba(231,76,60,0.15); }

    /* Status select styling */
    .status-select { padding: 5px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; cursor: pointer; transition: all 0.3s; border-width: 1px; border-style: solid; }
    .status-pendiente { background: rgba(241,196,15,0.1); color: #f1c40f; border-color: rgba(241,196,15,0.3); }
    .status-confirmada { background: rgba(46,204,113,0.1); color: #2ecc71; border-color: rgba(46,204,113,0.3); }
    .status-cancelada { background: rgba(231,76,60,0.1); color: #e74c3c; border-color: rgba(231,76,60,0.3); }

    /* Client info */
    .client-name { font-weight: 600; font-size: 0.85rem; }
    .client-email { font-size: 0.72rem; color: #888; }
    .client-phone { font-size: 0.7rem; color: var(--primary); margin-top: 2px; }

    /* Date display */
    .date-badge { display: inline-flex; align-items: center; gap: 5px; background: rgba(0,0,0,0.2); padding: 4px 10px; border-radius: 6px; font-size: 0.78rem; }
    .date-arrow { color: var(--primary); font-size: 0.65rem; }
    .nights-badge { font-size: 0.68rem; color: var(--primary); margin-top: 4px; }

    /* Coupon badge */
    .coupon-badge { display: inline-flex; align-items: center; gap: 4px; background: rgba(46,204,113,0.1); color: #2ecc71; padding: 3px 8px; border-radius: 5px; font-size: 0.7rem; font-weight: 600; }

    /* Edit Modal - wider */
    #editModal .modal-content { max-width: 700px; }
    .edit-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .edit-full { grid-column: 1 / -1; }
    .edit-group { display: flex; flex-direction: column; gap: 5px; }
    .edit-group label { font-size: 0.68rem; text-transform: uppercase; letter-spacing: 1px; color: var(--primary); font-weight: 600; }
    .edit-group input, .edit-group select, .edit-group textarea { width: 100%; background: rgba(0,0,0,0.3); border: 1px solid rgba(197,160,89,0.2); color: white; padding: 10px 14px; border-radius: 8px; font-size: 0.82rem; font-family: inherit; }
    .edit-group input:focus, .edit-group select:focus, .edit-group textarea:focus { border-color: var(--primary); outline: none; }
    .edit-divider { grid-column: 1/-1; border: none; border-top: 1px solid rgba(197,160,89,0.15); margin: 5px 0; }

    /* Pagination */
    .pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 30px; }
    .page-btn { display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; border: 1px solid var(--gold-border); background: transparent; color: var(--text-muted); font-size: 0.8rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: all 0.2s; }
    .page-btn:hover { border-color: var(--primary); color: var(--primary); }
    .page-btn.active { background: var(--primary); color: black; border-color: var(--primary); }

    /* Empty state */
    .empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state i { font-size: 3rem; color: rgba(197,160,89,0.3); margin-bottom: 15px; display: block; }

    @media (max-width: 900px) {
        .res-stats { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 500px) {
        .res-stats { grid-template-columns: 1fr; }
        .edit-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="admin-container" style="color: white; padding: 20px;">
    <!-- HEADER -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
        <div>
            <h2 class="serif gold-text" style="font-size: clamp(1.4rem, 4vw, 2rem); margin: 0;">Gestión de Reservas</h2>
            <p style="color: #888; margin: 5px 0 0; font-size: 0.8rem;">Registros totales: <?php echo $total_rows; ?> de <?php echo $stats_total; ?></p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="showCreateModal()" class="btn-gold">
                <i class="fas fa-calendar-plus"></i> NUEVA RESERVA
            </button>
        </div>
    </div>

    <!-- STATS -->
    <div class="res-stats">
        <div class="res-stat-card">
            <div class="res-stat-label">Total Reservas</div>
            <div class="res-stat-value gold"><?php echo $stats_total; ?></div>
        </div>
        <div class="res-stat-card">
            <div class="res-stat-label">Pendientes</div>
            <div class="res-stat-value yellow"><?php echo $stats_pending; ?></div>
        </div>
        <div class="res-stat-card">
            <div class="res-stat-label">Confirmadas</div>
            <div class="res-stat-value green"><?php echo $stats_confirmed; ?></div>
        </div>
        <div class="res-stat-card">
            <div class="res-stat-label">Ingresos Estimados</div>
            <div class="res-stat-value gold">$<?php echo number_format($stats_revenue, 2); ?></div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="glass-card" style="padding: 1.2rem; margin-bottom: 1.5rem;">
        <form method="GET" action="index.php" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
            <input type="hidden" name="action" value="admin-reservas">
            
            <div style="flex: 1; min-width: 180px;">
                <label style="display: block; font-size: 0.65rem; color: var(--primary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px;">Buscar</label>
                <div style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 11px; color: #555; font-size: 0.75rem;"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nombre, email o teléfono..." style="width: 100%; padding-left: 32px;">
                </div>
            </div>

            <div style="width: 140px;">
                <label style="display: block; font-size: 0.65rem; color: var(--primary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px;">Fecha</label>
                <input type="date" name="date" value="<?php echo $date_filter; ?>" style="width: 100%;">
            </div>

            <div style="width: 130px;">
                <label style="display: block; font-size: 0.65rem; color: var(--primary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px;">Estado</label>
                <select name="status" style="width: 100%;">
                    <option value="">Todos</option>
                    <option value="pendiente" <?php echo $status_filter == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="confirmada" <?php echo $status_filter == 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                    <option value="cancelada" <?php echo $status_filter == 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                </select>
            </div>

            <div style="width: 70px;">
                <label style="display: block; font-size: 0.65rem; color: var(--primary); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px;">Ver</label>
                <select name="limit" style="width: 100%;" onchange="this.form.submit()">
                    <option value="5" <?php echo $limit == 5 ? 'selected' : ''; ?>>5</option>
                    <option value="10" <?php echo $limit == 10 ? 'selected' : ''; ?>>10</option>
                    <option value="15" <?php echo $limit == 15 ? 'selected' : ''; ?>>15</option>
                    <option value="25" <?php echo $limit == 25 ? 'selected' : ''; ?>>25</option>
                </select>
            </div>

            <button type="submit" class="btn-gold" style="padding: 9px 16px;"><i class="fas fa-filter"></i></button>
            
            <?php if($search || $date_filter || $status_filter): ?>
            <a href="index.php?action=admin-reservas" style="color: #e74c3c; padding: 9px; font-size: 1rem; text-decoration: none;">
                <i class="fas fa-times-circle"></i>
            </a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (isset($success_json)): ?>
    <script>
        const resData = <?php echo $success_json; ?>;
        if (resData.whatsapp_url) {
            window.open(resData.whatsapp_url, '_blank');
        }
        Swal.fire({
            ...resData,
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

    <!-- TABLE -->
    <?php if (empty($reservas)): ?>
    <div class="empty-state">
        <i class="fas fa-calendar-xmark"></i>
        <p style="font-size: 1rem; margin-bottom: 5px;">No se encontraron reservas</p>
        <p style="font-size: 0.8rem;">Intenta cambiar los filtros o crea una nueva reserva.</p>
    </div>
    <?php else: ?>
    <div class="admin-table-container">
        <table style="width: 100%;">
            <thead style="background: rgba(255, 255, 255, 0.02);">
                <tr>
                    <th style="padding: 14px;">ID</th>
                    <th style="padding: 14px;">Cliente</th>
                    <th style="padding: 14px;">Estancia</th>
                    <th style="padding: 14px;">Habitación</th>
                    <th style="padding: 14px;">Total</th>
                    <th style="padding: 14px;">Estado</th>
                    <th style="padding: 14px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservas as $res): 
                    $fi = date('d', strtotime($res['fecha_entrada'])) . ' ' . $meses_es[intval(date('n', strtotime($res['fecha_entrada'])))];
                    $fo = date('d', strtotime($res['fecha_salida'])) . ' ' . $meses_es[intval(date('n', strtotime($res['fecha_salida'])))];
                    $nights = max(1, (strtotime($res['fecha_salida']) - strtotime($res['fecha_entrada'])) / 86400);
                    $status_class = 'status-' . $res['estado'];
                ?>
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);" id="row-<?php echo $res['id']; ?>">
                    <td style="padding: 14px;">
                        <span style="font-weight: 700; color: var(--primary);">#<?php echo str_pad($res['id'], 3, '0', STR_PAD_LEFT); ?></span>
                    </td>
                    <td style="padding: 14px;">
                        <div class="client-name"><?php echo htmlspecialchars($res['nombre_cliente']); ?></div>
                        <div class="client-email"><?php echo htmlspecialchars($res['email_cliente']); ?></div>
                        <?php if ($res['telefono_cliente']): ?>
                        <div class="client-phone"><i class="fas fa-phone" style="font-size: 0.6rem;"></i> <?php echo htmlspecialchars($res['telefono_cliente']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 14px;">
                        <div class="date-badge">
                            <?php echo $fi; ?> <span class="date-arrow"><i class="fas fa-arrow-right"></i></span> <?php echo $fo; ?>
                        </div>
                        <div class="nights-badge">
                            <i class="fas fa-moon" style="font-size: 0.6rem;"></i> 
                            <?php echo intval($nights); ?> noche<?php echo intval($nights) != 1 ? 's' : ''; ?> · <?php echo $res['num_huespedes']; ?> huésped<?php echo $res['num_huespedes'] > 1 ? 'es' : ''; ?>
                        </div>
                    </td>
                    <td style="padding: 14px;">
                        <div style="font-size: 0.82rem; font-weight: 600;"><?php echo htmlspecialchars($res['room_name']); ?></div>
                        <div style="font-size: 0.72rem; color: var(--primary);">Unidad <?php echo $res['room_num']; ?></div>
                        <?php if ($res['cupon_codigo']): ?>
                        <div class="coupon-badge" style="margin-top: 4px;">
                            <i class="fas fa-tag"></i> <?php echo $res['cupon_codigo']; ?> (-<?php echo $res['descuento_aplicado']; ?>%)
                        </div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 14px;">
                        <div style="font-weight: 700; font-size: 1rem;">$<?php echo number_format($res['total'], 2); ?></div>
                        <?php if ($res['descuento_aplicado'] > 0): ?>
                        <div style="font-size: 0.68rem; color: #2ecc71;">-<?php echo $res['descuento_aplicado']; ?>% dto.</div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 14px;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="reserva_id" value="<?php echo $res['id']; ?>">
                            <input type="hidden" name="update_status" value="1">
                            <select name="estado" onchange="this.form.submit()" class="status-select <?php echo $status_class; ?>">
                                <option value="pendiente" <?php echo $res['estado'] == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="confirmada" <?php echo $res['estado'] == 'confirmada' ? 'selected' : ''; ?>>Confirmada</option>
                                <option value="cancelada" <?php echo $res['estado'] == 'cancelada' ? 'selected' : ''; ?>>Cancelada</option>
                            </select>
                        </form>
                    </td>
                    <td style="padding: 14px;">
                        <div class="action-group">
                            <button class="btn-action view" onclick="toggleDetail(<?php echo $res['id']; ?>)" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-action edit" onclick='openEditModal(<?php echo json_encode($res, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' title="Editar">
                                <i class="fas fa-pen"></i>
                            </button>
                            <button class="btn-action delete" onclick="confirmDeleteReserva(<?php echo $res['id']; ?>)" title="Eliminar">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <!-- DETAIL EXPANDABLE ROW -->
                <tr class="detail-row" id="detail-<?php echo $res['id']; ?>">
                    <td colspan="7" class="detail-cell">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label><i class="fas fa-user"></i> Nombre Completo</label>
                                <span><?php echo htmlspecialchars($res['nombre_cliente']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label><i class="fas fa-envelope"></i> Email</label>
                                <span><?php echo htmlspecialchars($res['email_cliente']); ?></span>
                            </div>
                            <div class="detail-item">
                                <label><i class="fas fa-phone"></i> Teléfono</label>
                                <span><?php echo htmlspecialchars($res['telefono_cliente'] ?? 'No proporcionado'); ?></span>
                            </div>
                            <div class="detail-item">
                                <label><i class="fas fa-bed"></i> Habitación</label>
                                <span><?php echo htmlspecialchars($res['room_name']); ?> — Unidad <?php echo $res['room_num']; ?></span>
                            </div>
                            <div class="detail-item">
                                <label><i class="fas fa-calendar-check"></i> Check-in</label>
                                <span><?php echo date('d/m/Y', strtotime($res['fecha_entrada'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <label><i class="fas fa-calendar-xmark"></i> Check-out</label>
                                <span><?php echo date('d/m/Y', strtotime($res['fecha_salida'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <label><i class="fas fa-users"></i> Huéspedes</label>
                                <span><?php echo $res['num_huespedes']; ?></span>
                            </div>
                            <div class="detail-item">
                                <label><i class="fas fa-dollar-sign"></i> Total</label>
                                <span style="font-weight: 700; color: var(--primary);">$<?php echo number_format($res['total'], 2); ?></span>
                            </div>
                            <?php if ($res['cupon_codigo']): ?>
                            <div class="detail-item">
                                <label><i class="fas fa-tag"></i> Cupón Aplicado</label>
                                <span><?php echo $res['cupon_codigo']; ?> (<?php echo $res['descuento_aplicado']; ?>% descuento)</span>
                            </div>
                            <?php endif; ?>
                            <div class="detail-item">
                                <label><i class="fas fa-clock"></i> Fecha de Registro</label>
                                <span><?php echo date('d/m/Y H:i', strtotime($res['created_at'])); ?></span>
                            </div>
                            <?php if ($res['notas']): ?>
                            <div class="detail-notes">
                                <label style="display: block; font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; color: var(--primary); margin-bottom: 6px; font-weight: 600;"><i class="fas fa-sticky-note"></i> Notas del Cliente</label>
                                <span style="font-size: 0.85rem; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($res['notas'])); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <?php if($total_pages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="index.php?action=admin-reservas&page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&date=<?php echo $date_filter; ?>&status=<?php echo $status_filter; ?>" class="page-btn">
            <i class="fas fa-chevron-left"></i>
        </a>
        <?php endif; ?>
        
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <a href="index.php?action=admin-reservas&page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&date=<?php echo $date_filter; ?>&status=<?php echo $status_filter; ?>" 
           class="page-btn <?php echo $page == $i ? 'active' : ''; ?>">
            <?php echo $i; ?>
        </a>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
        <a href="index.php?action=admin-reservas&page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&date=<?php echo $date_filter; ?>&status=<?php echo $status_filter; ?>" class="page-btn">
            <i class="fas fa-chevron-right"></i>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<!-- ==================== MODAL: CREAR RESERVA ==================== -->
<div id="createModal" class="modal-overlay">
    <div class="glass-card modal-content" style="max-width: 700px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 class="serif gold-text" style="margin: 0;"><i class="fas fa-calendar-plus" style="margin-right: 8px;"></i>Nueva Reserva Manual</h3>
            <button onclick="hideModal('createModal')" style="background:none; border:none; color:#888; cursor:pointer; font-size:1.5rem;">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="manual_reserva" value="1">
            <div class="edit-grid">
                <div class="edit-full edit-group">
                    <label>Nombre del Cliente</label>
                    <input type="text" name="nombre" required placeholder="Nombre completo">
                </div>
                <div class="edit-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="correo@ejemplo.com">
                </div>
                <div class="edit-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" required placeholder="+593 99 000 0000">
                </div>
                <hr class="edit-divider">
                <div class="edit-group">
                    <label>Habitación</label>
                    <select name="habitacion_id" required>
                        <option value="">— Seleccionar —</option>
                        <?php foreach($available_rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room['nombre']); ?> — #<?php echo $room['numero']; ?> ($<?php echo number_format($room['precio'], 2); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="edit-group">
                    <label>Huéspedes</label>
                    <input type="number" name="huespedes" min="1" max="10" value="2" required>
                </div>
                <div class="edit-group">
                    <label>Check-in</label>
                    <input type="date" name="entrada" required>
                </div>
                <div class="edit-group">
                    <label>Check-out</label>
                    <input type="date" name="salida" required>
                </div>
                <hr class="edit-divider">
                <div class="edit-group">
                    <label>Total a Pagar ($)</label>
                    <input type="number" step="0.01" name="total" required placeholder="0.00">
                </div>
                <div class="edit-group">
                    <label>Cupón (Opcional)</label>
                    <input type="text" name="cupon" placeholder="Ej: SUITE30SAN" style="text-transform: uppercase;">
                </div>
                <div class="edit-group">
                    <label>Descuento %</label>
                    <input type="number" name="descuento" min="0" max="100" value="0">
                </div>
                <div class="edit-group">
                    <label>Idioma del Huésped</label>
                    <select name="idioma">
                        <option value="es" selected>Español</option>
                        <option value="en">Inglés (English)</option>
                        <option value="fr">Francés (Français)</option>
                        <option value="de">Alemán (Deutsch)</option>
                    </select>
                </div>
                <div class="edit-full edit-group">
                    <label>Notas / Peticiones Especiales</label>
                    <textarea name="notas" rows="2" style="resize: none;" placeholder="Solicitud de cama extra, alergias, hora de llegada..."></textarea>
                </div>
            </div>
            <button type="submit" class="btn-gold" style="width: 100%; margin-top: 20px; justify-content: center; padding: 14px;">
                <i class="fas fa-save"></i> GUARDAR RESERVA
            </button>
        </form>
    </div>
</div>

<!-- ==================== MODAL: EDITAR RESERVA ==================== -->
<div id="editModal" class="modal-overlay">
    <div class="glass-card modal-content" style="max-width: 700px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 class="serif gold-text" style="margin: 0;"><i class="fas fa-pen" style="margin-right: 8px;"></i>Editar Reserva <span id="edit-id-display" style="color: #888;"></span></h3>
            <button onclick="hideModal('editModal')" style="background:none; border:none; color:#888; cursor:pointer; font-size:1.5rem;">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="edit_reserva" value="1">
            <input type="hidden" name="reserva_id" id="edit-id">
            <div class="edit-grid">
                <div class="edit-full edit-group">
                    <label>Nombre del Cliente</label>
                    <input type="text" name="nombre" id="edit-nombre" required>
                </div>
                <div class="edit-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit-email" required>
                </div>
                <div class="edit-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" id="edit-telefono">
                </div>
                <hr class="edit-divider">
                <div class="edit-group">
                    <label>Habitación</label>
                    <select name="habitacion_id" id="edit-habitacion" required>
                        <?php foreach($all_rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room['nombre']); ?> — #<?php echo $room['numero']; ?> ($<?php echo number_format($room['precio'], 2); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="edit-group">
                    <label>Huéspedes</label>
                    <input type="number" name="huespedes" id="edit-huespedes" min="1" max="10" required>
                </div>
                <div class="edit-group">
                    <label>Check-in</label>
                    <input type="date" name="entrada" id="edit-entrada" required>
                </div>
                <div class="edit-group">
                    <label>Check-out</label>
                    <input type="date" name="salida" id="edit-salida" required>
                </div>
                <hr class="edit-divider">
                <div class="edit-group">
                    <label>Total a Pagar ($)</label>
                    <input type="number" step="0.01" name="total" id="edit-total" required>
                </div>
                <div class="edit-group">
                    <label>Estado</label>
                    <select name="estado" id="edit-estado">
                        <option value="pendiente">Pendiente</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="edit-group">
                    <label>Cupón</label>
                    <input type="text" name="cupon" id="edit-cupon" style="text-transform: uppercase;">
                </div>
                <div class="edit-group">
                    <label>Descuento %</label>
                    <input type="number" name="descuento" id="edit-descuento" min="0" max="100">
                </div>
                <div class="edit-group">
                    <label>Idioma del Huésped</label>
                    <select name="idioma" id="edit-idioma">
                        <option value="es">Español</option>
                        <option value="en">Inglés (English)</option>
                        <option value="fr">Francés (Français)</option>
                        <option value="de">Alemán (Deutsch)</option>
                    </select>
                </div>
                <div class="edit-full edit-group">
                    <label>Notas / Peticiones Especiales</label>
                    <textarea name="notas" id="edit-notas" rows="3" style="resize: none;"></textarea>
                </div>
            </div>
            <button type="submit" class="btn-gold" style="width: 100%; margin-top: 20px; justify-content: center; padding: 14px;">
                <i class="fas fa-save"></i> ACTUALIZAR RESERVA
            </button>
        </form>
    </div>
</div>

<!-- DELETE FORM (hidden) -->
<form id="reservaDeleteForm" method="POST" style="display:none;">
    <input type="hidden" name="reserva_id" id="reservaDeleteId">
    <input type="hidden" name="delete_reserva" value="1">
</form>

<script>
    // ======== MODALS ========
    function showCreateModal() { document.getElementById('createModal').classList.add('active'); }
    function hideModal(id) { document.getElementById(id).classList.remove('active'); }
    
    // Close modals on click outside
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) hideModal(this.id);
        });
    });
    
    // Close on Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(m => hideModal(m.id));
        }
    });

    // ======== TOGGLE DETAIL ROW ========
    function toggleDetail(id) {
        const row = document.getElementById('detail-' + id);
        const btn = document.querySelector('#row-' + id + ' .btn-action.view i');
        if (row) {
            row.classList.toggle('active');
            btn.classList.toggle('fa-eye');
            btn.classList.toggle('fa-eye-slash');
        }
    }

    // ======== OPEN EDIT MODAL ========
    function openEditModal(res) {
        document.getElementById('edit-id').value = res.id;
        document.getElementById('edit-id-display').textContent = '#' + String(res.id).padStart(3, '0');
        document.getElementById('edit-nombre').value = res.nombre_cliente;
        document.getElementById('edit-email').value = res.email_cliente;
        document.getElementById('edit-telefono').value = res.telefono_cliente || '';
        document.getElementById('edit-habitacion').value = res.habitacion_id;
        document.getElementById('edit-huespedes').value = res.num_huespedes;
        document.getElementById('edit-entrada').value = res.fecha_entrada;
        document.getElementById('edit-salida').value = res.fecha_salida;
        document.getElementById('edit-total').value = res.total;
        document.getElementById('edit-estado').value = res.estado;
        document.getElementById('edit-cupon').value = res.cupon_codigo || '';
        document.getElementById('edit-descuento').value = res.descuento_aplicado || 0;
        document.getElementById('edit-idioma').value = res.idioma || 'es';
        document.getElementById('edit-notas').value = res.notas || '';
        
        document.getElementById('editModal').classList.add('active');
    }

    // ======== DELETE CONFIRMATION ========
    function confirmDeleteReserva(id) {
        Swal.fire({
            title: '¿Eliminar Reserva #' + String(id).padStart(3, '0') + '?',
            html: '<p style="color:#999;">Esta acción es permanente. La habitación será liberada automáticamente.</p>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            cancelButtonColor: '#333',
            confirmButtonText: '<i class="fas fa-trash-alt"></i> Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: '#151921',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('reservaDeleteId').value = id;
                document.getElementById('reservaDeleteForm').submit();
            }
        });
    }
</script>

<?php include_once "views/layouts/admin_footer.php"; ?>
