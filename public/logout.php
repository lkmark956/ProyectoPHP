<?php
/**
 * Cerrar sesión
 */

require_once '../config/config.php';

use App\Models\User;

$userModel = new User();
$userModel->logout();

header('Location: ' . BASE_URL . '/index.php');
exit;
