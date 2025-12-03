<?php
/**
 * Editar comentario
 */

require_once '../config/config.php';
session_start();

use App\Models\Comment;

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}

$commentId = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$content = $_POST['content'] ?? '';

if (!$commentId || !$postId || empty($content)) {
    $_SESSION['error'] = 'Datos inválidos';
    header('Location: post.php?id=' . $postId);
    exit;
}

$commentModel = new Comment();

// Verificar permisos
if (!$commentModel->canEditComment($commentId, $_SESSION['user_id'], $_SESSION['role'])) {
    $_SESSION['error'] = 'No tienes permiso para editar este comentario';
    header('Location: post.php?id=' . $postId);
    exit;
}

$result = $commentModel->updateComment($commentId, $content);

if ($result['success']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: post.php?id=' . $postId . '#comment-' . $commentId);
exit;
