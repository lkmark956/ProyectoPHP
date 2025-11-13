<?php
/**
 * Página de inicio de sesión
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
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = $userModel->login($username, $password);
    
    if ($result['success']) {
        // Redirigir al inicio
        header('Location: index.php');
        exit;
    } else {
        $error = $result['message'];
    }
}

$pageTitle = 'Iniciar Sesión - ' . SITE_NAME;
include VIEWS_PATH . '/header.php';
?>

<main class="main-container">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1 class="auth-title">Iniciar Sesión</h1>
                <p class="auth-subtitle">Accede a tu cuenta de <?= SITE_NAME ?></p>
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
                        placeholder="Ingresa tu usuario"
                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
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
                        placeholder="Ingresa tu contraseña"
                    >
                </div>

                <button type="submit" class="btn-submit">
                    Iniciar Sesión
                </button>
            </form>

            <div class="auth-footer">
                <p>¿No tienes cuenta? <a href="register.php" class="auth-link">Regístrate aquí</a></p>
                <p><a href="index.php" class="auth-link-secondary">← Volver al inicio</a></p>
            </div>
        </div>
    </div>
</main>

<?php include VIEWS_PATH . '/footer.php'; ?>
