<?php
/**
 * Eliminar comentario
 */

require_once '../config/config.php';
// No es necesario session_start() aquí porque config.php ya lo hace

use App\Models\Comment;
use App\Models\Post;

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$commentId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$postId = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

if (!$commentId || !$postId) {
    $_SESSION['error'] = 'Datos inválidos';
    header('Location: ' . BASE_URL);
    exit;
}

$commentModel = new Comment();
$postModel = new Post();

// Obtener el post para saber quién es el autor
$post = $postModel->getPostById($postId);

// Verificar permisos
if (!$commentModel->canDeleteComment($commentId, $_SESSION['user_id'], $_SESSION['role'], $post['author_id'] ?? null)) {
    $_SESSION['error'] = 'No tienes permiso para eliminar este comentario';
    header('Location: ' . BASE_URL . '/post.php?id=' . $postId);
    exit;
}

$result = $commentModel->deleteComment($commentId);

if ($result['success']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ' . BASE_URL . '/post.php?id=' . $postId . '#comments');
exit;
