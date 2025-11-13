<?php
/**
 * Cerrar sesión
 */

require_once '../config/config.php';

use App\User;

$userModel = new User();
$userModel->logout();

header('Location: index.php');
exit;
