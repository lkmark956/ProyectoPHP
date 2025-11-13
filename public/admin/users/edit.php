<?php
/**
 * Editar usuario - Admin
 */

require_once '../auth.php';

use App\User;
use App\ImageUpload;

requireRole('admin');

$userModel = new User();
$error = '';

$userId = $_GET['id'] ?? null;
if (!$userId) {
    header('Location: index.php?error=Usuario no especificado');
    exit;
}

$user = $userModel->getUserById($userId);
if (!$user) {
    header('Location: index.php?error=Usuario no encontrado');
    exit;
}

// No permitir que un admin se edite a sí mismo (debe usar perfil)
if ($user['id'] == $currentUser['id']) {
    header('Location: ../../profile.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update';
    
    if ($action === 'update') {
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'role' => $_POST['role'] ?? 'user'
        ];
        
        // Si hay contraseña nueva
        if (!empty($_POST['password'])) {
            if ($_POST['password'] !== $_POST['password_confirm']) {
                $error = 'Las contraseñas no coinciden';
            } else if (strlen($_POST['password']) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres';
            } else {
                $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
        }
        
        if (!$error) {
            $result = $userModel->updateUserByAdmin($userId, $data);
            if ($result) {
                header('Location: index.php?success=updated');
                exit;
            } else {
                $error = 'Error al actualizar el usuario';
            }
        }
    }
}

$pageTitle = 'Editar Usuario';
include '../../../views/admin/header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>✏️ Editar Usuario: <?= htmlspecialchars($user['username']) ?></h1>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="admin-form">
        <input type="hidden" name="action" value="update">
        
        <div class="form-grid">
            <div class="form-main">
                <div class="form-group">
                    <label for="username" class="form-label required">Usuario</label>
                    <input type="text" id="username" name="username" class="form-control" required
                           value="<?= htmlspecialchars($user['username']) ?>"
                           pattern="[a-zA-Z0-9_]+" title="Solo letras, números y guiones bajos">
                </div>

                <div class="form-group">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?= htmlspecialchars($user['email']) ?>">
                </div>

                <div class="form-group">
                    <label for="full_name" class="form-label">Nombre Completo</label>
                    <input type="text" id="full_name" name="full_name" class="form-control"
                           value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                </div>

                <hr>
                <h3>Cambiar Contraseña (opcional)</h3>

                <div class="form-group">
                    <label for="password" class="form-label">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" minlength="6">
                    <small class="form-help">Dejar en blanco para no cambiarla</small>
                </div>

                <div class="form-group">
                    <label for="password_confirm" class="form-label">Confirmar Nueva Contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" minlength="6">
                </div>
            </div>

            <div class="form-sidebar">
                <div class="sidebar-section">
                    <h3>Información</h3>
                    <div class="info-box">
                        <p><strong>ID:</strong> <?= $user['id'] ?></p>
                        <p><strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></p>
                        <p><strong>Último Acceso:</strong><br>
                            <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?>
                        </p>
                    </div>
                </div>

                <div class="sidebar-section">
                    <h3>Rol y Permisos</h3>
                    
                    <div class="form-group">
                        <label for="role" class="form-label">Rol</label>
                        <select id="role" name="role" class="form-control">
                            <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>Usuario</option>
                            <option value="author" <?= $user['role'] === 'author' ? 'selected' : '' ?>>Autor</option>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions-sticky">
                    <button type="submit" class="btn btn-primary btn-block">
                        💾 Guardar Cambios
                    </button>
                    <a href="index.php" class="btn btn-outline btn-block">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include '../../../views/admin/footer.php'; ?>
