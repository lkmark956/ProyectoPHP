<?php
/**
 * Página de registro de usuarios
 */

// Cargar configuración
require_once '../config/config.php';

use App\User;

$userModel = new User();

// Si ya está logueado, redirigir
if ($userModel->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'password_confirm' => $_POST['password_confirm'] ?? '',
        'full_name' => trim($_POST['full_name'] ?? ''),
        'role' => 'user' // Por defecto todos son usuarios normales
    ];
    
    $result = $userModel->register($data);
    
    if ($result['success']) {
        $success = $result['message'] . ' Ya puedes iniciar sesión.';
        // Limpiar formulario
        $_POST = [];
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Registrarse - ' . SITE_NAME;
include VIEWS_PATH . '/header.php';
?>

<main class="main-container">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Crear Cuenta</h1>
                <p class="auth-subtitle">Únete a <?= SITE_NAME ?> y empieza a compartir</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">⚠️</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✓</span>
                    <span><?= htmlspecialchars($success) ?></span>
                    <a href="login.php" class="alert-link">Ir a iniciar sesión →</a>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="auth-form">
                <div class="form-group">
                    <label for="username" class="form-label">
                        <span class="label-icon">👤</span>
                        Nombre de usuario
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input" 
                        required 
                        autofocus
                        placeholder="Elige un nombre de usuario"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                        pattern="[a-zA-Z0-9_]{3,50}"
                        title="Solo letras, números y guiones bajos (3-50 caracteres)"
                    >
                    <small class="form-help">Solo letras, números y guiones bajos (mínimo 3 caracteres)</small>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        <span class="label-icon">📧</span>
                        Correo electrónico
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        required
                        placeholder="tu@email.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="full_name" class="form-label">
                        <span class="label-icon">✍️</span>
                        Nombre completo (opcional)
                    </label>
                    <input 
                        type="text" 
                        id="full_name" 
                        name="full_name" 
                        class="form-input" 
                        placeholder="Tu nombre completo"
                        value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <span class="label-icon">🔒</span>
                        Contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-input" 
                        required
                        placeholder="Mínimo 6 caracteres"
                        minlength="6"
                    >
                </div>

                <div class="form-group">
                    <label for="password_confirm" class="form-label">
                        <span class="label-icon">🔒</span>
                        Confirmar contraseña
                    </label>
                    <input 
                        type="password" 
                        id="password_confirm" 
                        name="password_confirm" 
                        class="form-input" 
                        required
                        placeholder="Repite tu contraseña"
                        minlength="6"
                    >
                </div>

                <button type="submit" class="btn-submit">
                    Crear Cuenta
                </button>
            </form>

            <div class="auth-footer">
                <p>¿Ya tienes cuenta? <a href="login.php" class="auth-link">Inicia sesión aquí</a></p>
                <p><a href="index.php" class="auth-link-secondary">← Volver al inicio</a></p>
            </div>
        </div>
    </div>
</main>

<?php include VIEWS_PATH . '/footer.php'; ?>
