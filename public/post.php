<?php
/**
 * Vista individual de un post
 */

// Cargar configuración
require_once '../config/config.php';

use App\Post;
use App\Category;

// Verificar que se pasó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$postId = intval($_GET['id']);

// Instanciar modelos
$postModel = new Post();
$categoryModel = new Category();

// Obtener el post
$post = $postModel->getPostById($postId);

// Si no existe el post, redirigir
if (!$post) {
    header('Location: index.php');
    exit;
}

// Incrementar contador de vistas
$postModel->incrementViews($postId);

// Obtener categorías para el sidebar
$categories = $categoryModel->getCategoriesWithPostCount();

// Título de la página
$pageTitle = htmlspecialchars($post['title']) . ' - ' . SITE_NAME;

// Incluir header
include VIEWS_PATH . '/header.php';
?>

<main class="main-container">
    <div class="content-wrapper">
        <!-- Contenido del post -->
        <article class="post-full">
            <div class="post-full-header">
                <h1 class="post-full-title"><?= htmlspecialchars($post['title']) ?></h1>
                
                <div class="post-full-meta">
                    <div class="post-author-info">
                        <?php if (isset($post['author_avatar']) && $post['author_avatar']): ?>
                            <img src="uploads/users/<?= htmlspecialchars($post['author_avatar']) ?>" 
                                 alt="<?= htmlspecialchars($post['author_name']) ?>"
                                 class="author-avatar">
                        <?php else: ?>
                            <span class="author-avatar-placeholder">
                                <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                        <div class="author-details">
                            <span class="author-by">Escrito por</span>
                            <span class="author-name-full"><?= htmlspecialchars($post['author_name']) ?></span>
                        </div>
                    </div>
                    
                    <div class="post-metadata">
                        <span class="post-category-badge">
                            <i class="icon-folder"></i>
                            <?= htmlspecialchars($post['category_name'] ?? 'Sin categoría') ?>
                        </span>
                        <span class="post-date-badge">
                            <i class="icon-clock"></i>
                            <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                        </span>
                        <?php if (isset($post['views']) && $post['views'] > 0): ?>
                            <span class="post-views-badge">
                                <i class="icon-eye"></i>
                                <?= number_format($post['views']) ?> vistas
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (isset($post['image']) && $post['image']): ?>
                <div class="post-featured-image">
                    <img src="uploads/posts/<?= htmlspecialchars($post['image']) ?>" 
                         alt="<?= htmlspecialchars($post['title']) ?>">
                </div>
            <?php endif; ?>

            <div class="post-full-content">
                <?= $post['content'] ?>
            </div>

            <div class="post-full-footer">
                <a href="index.php" class="btn-back">
                    ← Volver al inicio
                </a>
            </div>
        </article>
        
        <!-- Sidebar -->
        <?php include VIEWS_PATH . '/sidebar.php'; ?>
    </div>
</main>

<?php include VIEWS_PATH . '/footer.php'; ?>
