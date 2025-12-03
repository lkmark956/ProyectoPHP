<?php
require_once '../config/config.php';

use App\Models\User;
use App\Models\ImageUpload;

$user = new User();

// Verificar que el usuario esté autenticado
if (!$user->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$currentUser = $user->getCurrentUser();
$userId = $_SESSION['user_id'];

$message = '';
$messageType = '';

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Actualizar información del perfil
        $result = $user->updateProfile($userId, [
            'full_name' => $_POST['full_name'],
            'email' => $_POST['email']
        ]);
        
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
        
        // Refrescar datos del usuario
        $currentUser = $user->getUserById($userId);
    }
    
    if (isset($_POST['change_password'])) {
        // Cambiar contraseña
        $result = $user->changePassword(
            $userId,
            $_POST['current_password'],
            $_POST['new_password']
        );
        
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }
    
    if (isset($_POST['upload_avatar'])) {
        // Subir avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageUpload = new ImageUpload('uploads/users/');
            $result = $imageUpload->upload($_FILES['avatar'], 'user_' . $userId . '_');
            
            if ($result['success']) {
                // Eliminar avatar anterior si existe
                $oldAvatar = $currentUser['avatar'];
                if ($oldAvatar) {
                    $imageUpload->delete($oldAvatar);
                }
                
                // Actualizar en la base de datos
                $updateResult = $user->updateAvatar($userId, $result['filename']);
                $message = $updateResult['message'];
                $messageType = $updateResult['success'] ? 'success' : 'error';
                
                // Refrescar datos del usuario
                $currentUser = $user->getUserById($userId);
            } else {
                $message = $result['error'];
                $messageType = 'error';
            }
        } else {
            $message = 'Por favor selecciona una imagen';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['delete_avatar'])) {
        // Eliminar avatar
        $oldAvatar = $currentUser['avatar'];
        if ($oldAvatar) {
            $imageUpload = new ImageUpload('uploads/users/');
            $imageUpload->delete($oldAvatar);
            
            $result = $user->deleteAvatar($userId);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            
            // Refrescar datos del usuario
            $currentUser = $user->getUserById($userId);
        }
    }
}

$pageTitle = 'Mi Perfil';
include VIEWS_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/profile.css">

<div class="profile-container">
    <div class="profile-header">
        <h1>Mi Perfil</h1>
        <p>Administra tu información personal y configuración de cuenta</p>
    </div>
    
    <?php if ($message): ?>
        <div class="message message-<?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>
    
    <div class="profile-grid">
        <!-- Avatar Section -->
        <div class="profile-card">
            <h2>Foto de Perfil</h2>
            <div class="avatar-section">
                <div class="avatar-display">
                    <?php if ($currentUser['avatar']): ?>
                        <img src="<?= BASE_URL ?>/uploads/users/<?= htmlspecialchars($currentUser['avatar']) ?>" alt="Avatar" class="avatar-large">
                    <?php else: ?>
                        <div class="avatar-placeholder">
                            <span class="avatar-initials">
                                <?= strtoupper(substr($currentUser['username'], 0, 2)) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="avatar-form">
                    <div class="form-group">
                        <label for="avatar" class="btn btn-secondary">
                            📷 Seleccionar imagen
                            <input type="file" id="avatar" name="avatar" accept="image/*" style="display: none;" onchange="showFileName(this)">
                        </label>
                        <span id="file-name" class="file-name-display"></span>
                    </div>
                    
                    <div class="avatar-actions">
                        <button type="submit" name="upload_avatar" class="btn btn-primary">
                            ⬆️ Subir
                        </button>
                        <?php if ($currentUser['avatar']): ?>
                            <button type="submit" name="delete_avatar" class="btn btn-danger" onclick="return confirm('¿Eliminar tu foto de perfil?')">
                                🗑️ Eliminar
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <p class="help-text">
                        Formatos permitidos: JPG, PNG, GIF, WEBP (máx. 5MB)
                    </p>
                </form>
            </div>
        </div>
        
        <!-- Profile Info Section -->
        <div class="profile-card">
            <h2>Información Personal</h2>
            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" value="<?= htmlspecialchars($currentUser['username']) ?>" disabled class="form-control">
                    <small class="form-help">El nombre de usuario no se puede cambiar</small>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Nombre Completo</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($currentUser['full_name'] ?? '') ?>" class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($currentUser['email']) ?>" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label>Rol</label>
                    <div class="badge badge-info">
                        <?= htmlspecialchars($currentUser['role']) ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Miembro desde</label>
                    <input type="text" value="<?= date('d/m/Y', strtotime($currentUser['created_at'])) ?>" disabled class="form-control">
                </div>
                
                <button type="submit" name="update_profile" class="btn btn-primary btn-block">
                    💾 Guardar Cambios
                </button>
            </form>
        </div>
        
        <!-- Password Change Section -->
        <div class="profile-card">
            <h2>Cambiar Contraseña</h2>
            <form method="POST" class="profile-form">
                <div class="form-group">
                    <label for="current_password">Contraseña Actual</label>
                    <input type="password" id="current_password" name="current_password" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" id="new_password" name="new_password" required minlength="6" class="form-control">
                    <small class="form-help">Mínimo 6 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nueva Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" class="form-control">
                </div>
                
                <button type="submit" name="change_password" class="btn btn-primary btn-block">
                    🔒 Cambiar Contraseña
                </button>
            </form>
        </div>
        
        <!-- Account Info -->
        <div class="profile-card">
            <h2>Información de Cuenta</h2>
            <div class="account-info">
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="badge badge-success">Activa</span>
                </div>
                
                <?php if ($currentUser['last_login']): ?>
                    <div class="info-row">
                        <span class="info-label">Último acceso:</span>
                        <span><?= date('d/m/Y H:i', strtotime($currentUser['last_login'])) ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($user->hasRole('admin') || $user->hasRole('author')): ?>
                    <div class="info-row">
                        <a href="admin/" class="btn btn-secondary">
                            ⚙️ Panel de Administración
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function showFileName(input) {
    const fileName = input.files[0]?.name;
    const display = document.getElementById('file-name');
    if (fileName) {
        display.textContent = fileName;
        display.style.display = 'inline';
    }
}

// Validar que las contraseñas coincidan
const passForm = document.querySelector('button[name="change_password"]');
if (passForm) {
    passForm.closest('form').addEventListener('submit', function(e) {
        const newPass = document.getElementById('new_password').value;
        const confirmPass = document.getElementById('confirm_password').value;
        
        if (newPass !== confirmPass) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
        }
    });
}
</script>

<?php require_once '../app/Views/footer.php'; ?>
