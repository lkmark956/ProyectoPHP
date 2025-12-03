<?php
/**
 * Eliminar usuario - Admin
 */

require_once '../auth.php';

use App\Models\User;

requireRole('admin');

$userId = $_GET['id'] ?? null;

if (!$userId) {
    header('Location: index.php?error=Usuario no especificado');
    exit;
}

// No permitir que un admin se elimine a sí mismo
if ($userId == $currentUser['id']) {
    header('Location: index.php?error=No puedes eliminar tu propia cuenta');
    exit;
}

$userModel = new User();
$result = $userModel->deleteUser($userId);

if ($result) {
    header('Location: index.php?success=deleted');
} else {
    header('Location: index.php?error=Error al eliminar el usuario');
}
exit;
