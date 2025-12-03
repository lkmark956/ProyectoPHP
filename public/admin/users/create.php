<?php
/**
 * Crear usuario - Admin
 */

require_once '../auth.php';

use App\Models\User;

requireRole('admin');

$userModel = new User();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? '',
        'full_name' => trim($_POST['full_name'] ?? ''),
        'role' => $_POST['role'] ?? 'user'
    ];
    
    $result = $userModel->register($data);
    
    if ($result['success']) {
        header('Location: index.php?success=created');
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Crear Usuario';
include '../../../app/Views/admin/header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>➕ Crear Nuevo Usuario</h1>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="admin-form">
        <div class="form-grid">
            <div class="form-main">
                <div class="form-group">
                    <label for="username" class="form-label required">Usuario</label>
                    <input type="text" id="username" name="username" class="form-control" required
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           pattern="[a-zA-Z0-9_]+" title="Solo letras, números y guiones bajos">
                    <small class="form-help">Solo letras, números y guiones bajos (sin espacios)</small>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label required">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="full_name" class="form-label">Nombre Completo</label>
                    <input type="text" id="full_name" name="full_name" class="form-control"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label required">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required minlength="6">
                    <small class="form-help">Mínimo 6 caracteres</small>
                </div>

                <div class="form-group">
                    <label for="password_confirm" class="form-label required">Confirmar Contraseña</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" required minlength="6">
                </div>
            </div>

            <div class="form-sidebar">
                <div class="sidebar-section">
                    <h3>Rol del Usuario</h3>
                    
                    <div class="form-group">
                        <label for="role" class="form-label">Rol</label>
                        <select id="role" name="role" class="form-control">
                            <option value="user" <?= (($_POST['role'] ?? '') === 'user') ? 'selected' : '' ?>>Usuario</option>
                            <option value="author" <?= (($_POST['role'] ?? '') === 'author') ? 'selected' : '' ?>>Autor</option>
                            <option value="admin" <?= (($_POST['role'] ?? '') === 'admin') ? 'selected' : '' ?>>Administrador</option>
                        </select>
                        <small class="form-help">
                            <strong>Usuario:</strong> Solo lectura<br>
                            <strong>Autor:</strong> Puede crear posts<br>
                            <strong>Admin:</strong> Control total
                        </small>
                    </div>

                    <div class="info-box">
                        <p><strong>💡 Nota:</strong></p>
                        <p>El usuario recibirá acceso inmediato al sistema con las credenciales configuradas.</p>
                    </div>
                </div>

                <div class="form-actions-sticky">
                    <button type="submit" class="btn btn-primary btn-block">
                        💾 Crear Usuario
                    </button>
                    <a href="index.php" class="btn btn-outline btn-block">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include '../../../app/Views/admin/footer.php'; ?>
