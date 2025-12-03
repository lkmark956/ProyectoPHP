<?php
/**
 * Middleware de autenticación
 * Verifica que el usuario esté logueado antes de acceder al admin
 */

use App\Models\User;

// Cargar configuración si no está cargada
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../../config/config.php';
}

$userModel = new User();

// Verificar si está logueado
if (!$userModel->isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ../login.php');
    exit;
}

// Obtener usuario actual
$currentUser = $userModel->getCurrentUser();

/**
 * Función para verificar si el usuario tiene un rol específico
 * @param string $role
 * @return bool
 */
function requireRole($role) {
    global $currentUser;
    if ($currentUser['role'] !== $role) {
        die('<h1>Acceso Denegado</h1><p>No tienes permisos para acceder a esta página.</p><a href="<?= BASE_URL ?>/index.php">Volver al inicio</a>');
    }
}

/**
 * Función para verificar si el usuario es admin
 * @return bool
 */
function isAdmin() {
    global $currentUser;
    return $currentUser['role'] === 'admin';
}

/**
 * Función para verificar si el usuario es autor o admin
 * @return bool
 */
function canCreateContent() {
    global $currentUser;
    return in_array($currentUser['role'], ['admin', 'author']);
}
