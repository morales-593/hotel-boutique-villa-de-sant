<?php
require_once "config/config.php";
require_once "config/database.php";
require_once "models/User.php";

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

// Restricción: Solo administradores pueden ver esta vista
if ($_SESSION['role'] !== 'admin') {
    header("Location: index.php?action=dashboard");
    exit();
}

$success_json = null;

// Lógica para crear nuevo usuario
if (isset($_POST['create_user'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'admin';

    if (!empty($nombre) && !empty($email) && !empty($password)) {
        $result = $userModel->create($nombre, $email, $password, $role);
        
        if ($result === true) {
            $success_json = json_encode([
                'title' => '¡Usuario Creado!',
                'text' => "Se ha generado el nuevo acceso para $nombre correctamente.",
                'icon' => 'success'
            ]);
        } else if ($result === "duplicate") {
            $success_json = json_encode([
                'title' => 'Error de Registro',
                'text' => "El correo electrónico $email ya está registrado en el sistema.",
                'icon' => 'warning'
            ]);
        }
    }
}

// Lógica para actualizar usuario
if (isset($_POST['update_user'])) {
    $id = intval($_POST['user_id']);
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = !empty($_POST['password']) ? $_POST['password'] : null;

    $result = $userModel->update($id, $nombre, $email, $role, $password);
    
    if ($result === true) {
        $success_json = json_encode([
            'title' => '¡Actualizado!',
            'text' => "Los datos de $nombre han sido actualizados.",
            'icon' => 'success'
        ]);
    } else if ($result === "duplicate") {
        $success_json = json_encode([
            'title' => 'Error al Actualizar',
            'text' => "El correo $email ya está en uso por otro administrador.",
            'icon' => 'error'
        ]);
    }
}

// Lógica para eliminar usuario
if (isset($_POST['delete_user'])) {
    $id = intval($_POST['user_id']);
    if ($id == $_SESSION['user_id']) {
        $success_json = json_encode(['title' => 'Acceso Denegado', 'text' => 'No puedes eliminar tu propia cuenta activa.', 'icon' => 'error']);
    } else {
        if ($userModel->delete($id)) {
            $success_json = json_encode(['title' => 'Usuario Eliminado', 'text' => 'El acceso ha sido removido del sistema.', 'icon' => 'info']);
        }
    }
}

// Lógica para resetear contraseña
if (isset($_POST['reset_password'])) {
    $id = intval($_POST['user_id']);
    $new_pass = $_POST['new_password'];
    $result = $userModel->update($id, $_POST['nombre'], $_POST['email'], $_POST['role'], $new_pass);
    if ($result === true) {
        $success_json = json_encode(['title' => 'Clave Actualizada', 'text' => 'La nueva contraseña se ha guardado correctamente.', 'icon' => 'success']);
    }
}

// Obtener todos los usuarios
$usuarios = $userModel->getAll();

include_once "views/layouts/admin_header.php";
?>

<div class="admin-container" style="color: white; padding: 20px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px;">
        <h2 class="serif gold-text" style="font-size: clamp(1.4rem, 4vw, 2rem); margin: 0;">Gestión de Usuarios Admin</h2>
        <div style="display: flex; gap: 10px;">
            <button onclick="showCreateForm()" class="btn-gold">
                <i class="fas fa-plus"></i> CREAR USUARIO
            </button>
            <a href="index.php?action=dashboard" class="btn-gold" style="padding: 10px 20px; font-size: 0.75rem; border-radius: 50px; background: rgba(255,255,255,0.05);">
                <i class="fas fa-arrow-left"></i> DASHBOARD
            </a>
        </div>
    </div>

    <?php if ($success_json): ?>
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

    <div class="glass-card">
        <h3 class="serif gold-text" style="margin-bottom: 1.5rem;">Administradores Registrados</h3>
        <div class="admin-table-container" style="margin-top: 0; background: transparent; border: none;">
            <table style="width: 100%;">
                <thead style="background: rgba(255,255,255,0.02);">
                    <tr>
                        <th style="padding: 15px;">Nombre Completo</th>
                        <th style="padding: 15px;">Acceso (Email)</th>
                        <th style="padding: 15px;">Rol</th>
                        <th style="padding: 15px; text-align: right;">Acciones de Seguridad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 15px; font-weight: 600;"><?php echo htmlspecialchars($u['nombre']); ?></td>
                        <td style="padding: 15px; color: #888;"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td style="padding: 15px;">
                            <span class="status-badge <?php echo $u['role'] == 'admin' ? 'status-available' : 'status-occupied'; ?>">
                                <?php echo strtoupper($u['role']); ?>
                            </span>
                        </td>
                        <td style="padding: 15px; text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <button onclick='editUser(<?php echo json_encode($u); ?>)' class="btn-gold" style="padding: 8px 12px; font-size: 0.7rem;" title="Editar Datos">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="promptResetPass(<?php echo htmlspecialchars(json_encode($u)); ?>)" class="btn-gold" style="padding: 8px 12px; font-size: 0.7rem; background: rgba(241, 196, 15, 0.1); border: 1px solid rgba(241, 196, 15, 0.2); color: #f1c40f;" title="Resetear Clave">
                                    <i class="fas fa-key"></i>
                                </button>
                                <button onclick="confirmDelete(<?php echo $u['id']; ?>)" class="btn-gold" style="padding: 8px 12px; font-size: 0.7rem; background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.2); color: #e74c3c;" title="Eliminar Acceso">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Usuario -->
<div id="userModal" class="modal-overlay">
    <div class="glass-card modal-content" style="position: relative;">
        <button onclick="hideModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: #888; font-size: 1.2rem; cursor: pointer;">&times;</button>
        <h3 id="formTitle" class="serif gold-text" style="margin-bottom: 20px;">Generar Nuevo Acceso</h3>
        <form id="userForm" method="POST">
            <input type="hidden" name="create_user" id="formAction" value="1">
            <input type="hidden" name="user_id" id="userIdField">
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.7rem; color: #888; margin-bottom: 5px;">NOMBRE COMPLETO</label>
                <input type="text" name="nombre" id="userName" placeholder="Ej: Juan Perez" required style="width: 100%;">
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.7rem; color: #888; margin-bottom: 5px;">CORREO ELECTRÓNICO</label>
                <input type="email" name="email" id="userEmail" placeholder="admin@villadesant.com" required style="width: 100%;">
            </div>

            <div id="passField" style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.7rem; color: #888; margin-bottom: 5px;">CONTRASEÑA</label>
                <input type="password" name="password" id="userPass" placeholder="********" required style="width: 100%;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 0.7rem; color: #888; margin-bottom: 5px;">ROL DEL USUARIO</label>
                <select name="role" id="userRole" style="width: 100%;">
                    <option value="admin">Administrador Total</option>
                    <option value="staff">Personal de Recepción</option>
                </select>
            </div>

            <button type="submit" id="submitBtn" class="btn-gold" style="width: 100%; justify-content: center;">
                <i class="fas fa-user-plus"></i> CREAR USUARIO
            </button>
        </form>
    </div>
</div>

<form id="hiddenActionForm" method="POST" style="display:none;">
    <input type="hidden" name="user_id" id="hiddenUserId">
    <input type="hidden" name="nombre" id="hiddenUserName">
    <input type="hidden" name="email" id="hiddenUserEmail">
    <input type="hidden" name="role" id="hiddenUserRole">
    <input type="hidden" name="new_password" id="hiddenUserNewPass">
    <input type="hidden" name="reset_password" value="1">
</form>

<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="user_id" id="deleteId">
    <input type="hidden" name="delete_user" value="1">
</form>

<script>
    function showCreateForm() {
        document.getElementById('formTitle').innerText = 'Generar Nuevo Acceso';
        document.getElementById('formAction').name = 'create_user';
        document.getElementById('userForm').reset();
        document.getElementById('passField').style.display = 'block';
        document.getElementById('userPass').required = true;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-user-plus"></i> CREAR USUARIO';
        document.getElementById('userModal').classList.add('active');
    }

    function editUser(user) {
        document.getElementById('formTitle').innerText = 'Editar Datos de Acceso';
        document.getElementById('formAction').name = 'update_user';
        document.getElementById('userIdField').value = user.id;
        document.getElementById('userName').value = user.nombre;
        document.getElementById('userEmail').value = user.email;
        document.getElementById('userRole').value = user.role;
        document.getElementById('passField').style.display = 'none'; // Password is separate now
        document.getElementById('userPass').required = false;
        document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> GUARDAR CAMBIOS';
        document.getElementById('userModal').classList.add('active');
    }

    function hideModal() {
        document.getElementById('userModal').classList.remove('active');
    }

    async function promptResetPass(user) {
        const { value: password } = await Swal.fire({
            title: 'Resetear Contraseña',
            text: `Ingresa la nueva clave para ${user.nombre}`,
            input: 'password',
            inputPlaceholder: 'Ingresa la nueva contraseña',
            showCancelButton: true,
            confirmButtonColor: '#c5a059',
            background: '#151921',
            color: '#fff',
            inputAttributes: {
                autocapitalize: 'off',
                autocorrect: 'off'
            }
        });

        if (password) {
            document.getElementById('hiddenUserId').value = user.id;
            document.getElementById('hiddenUserName').value = user.nombre;
            document.getElementById('hiddenUserEmail').value = user.email;
            document.getElementById('hiddenUserRole').value = user.role;
            document.getElementById('hiddenUserNewPass').value = password;
            document.getElementById('hiddenActionForm').submit();
        }
    }

    function confirmDelete(id) {
        Swal.fire({
            title: '¿Eliminar Acceso?',
            text: "Esta acción restringirá permanentemente este acceso al sistema.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74c3c',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: '#151921',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteId').value = id;
                document.getElementById('deleteForm').submit();
            }
        })
    }
    
    // Close modal on click overlay
    document.getElementById('userModal').addEventListener('click', function(e) {
        if (e.target === this) hideModal();
    });
</script>

<?php include_once "views/layouts/admin_footer.php"; ?>
