<?php
/**
 * Eliminar post - Admin
 */

require_once '../auth.php';

use App\Models\Post;

if (!canCreateContent()) {
    die('No tienes permisos para acceder a esta página');
}

// Verificar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$postId = intval($_GET['id']);
$postModel = new Post();
$post = $postModel->getPostById($postId);

if (!$post) {
    header('Location: index.php');
    exit;
}

// Eliminar el post
$postModel->deletePost($postId);
header('Location: index.php?success=deleted');
exit;
?>
