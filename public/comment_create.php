<?php
/**
 * Crear comentario
 */

require_once '../config/config.php';
// No es necesario session_start() aquí porque config.php ya lo hace

use App\Models\Comment;

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL);
    exit;
}

$postId = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$content = $_POST['content'] ?? '';

if (!$postId || empty($content)) {
    $_SESSION['error'] = 'Datos inválidos';
    header('Location: ' . BASE_URL . '/post.php?id=' . $postId);
    exit;
}

$commentModel = new Comment();
$result = $commentModel->createComment($postId, $_SESSION['user_id'], $content);

if ($result['success']) {
    $_SESSION['success'] = $result['message'];
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: ' . BASE_URL . '/post.php?id=' . $postId . '#comments');
exit;
