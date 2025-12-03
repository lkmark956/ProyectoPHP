<?php
/**
 * Eliminar post del usuario
 */

require_once '../config/config.php';

use App\Models\User;
use App\Models\Post;
use App\Models\ImageUpload;
use App\Models\Database;

$userModel = new User();

// Verificar que el usuario esté autenticado
if (!$userModel->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$currentUser = $userModel->getCurrentUser();
$postModel = new Post();

// Verificar que se pasó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . '/my-posts.php');
    exit;
}

$postId = intval($_GET['id']);

// Obtener el post
$db = Database::getInstance()->getConnection();
$sql = "SELECT * FROM posts WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindValue(':id', $postId, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch();

// Verificar que el post existe
if (!$post) {
    header('Location: ' . BASE_URL . '/my-posts.php');
    exit;
}

// Verificar que el usuario es el autor del post (o es admin)
if ($post['author_id'] != $currentUser['id'] && !$userModel->hasRole('admin')) {
    die('No tienes permisos para eliminar este post');
}

// Eliminar la imagen si existe
if ($post['image']) {
    $imageUpload = new ImageUpload('uploads/posts/');
    $imageUpload->delete($post['image']);
}

// Eliminar el post
$postModel->deletePost($postId);

// Redirigir con mensaje de éxito
$_SESSION['message'] = 'Post eliminado exitosamente';
$_SESSION['message_type'] = 'success';
header('Location: ' . BASE_URL . '/my-posts.php');
exit;
