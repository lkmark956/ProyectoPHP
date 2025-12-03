<?php
/**
 * Vista de posts por Categoria
 */

// Cargar configuración
require_once '../config/config.php';

use App\Models\Post;
use App\Models\Category;

// Verificar que se pasó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$categoryId = intval($_GET['id']);

// Instanciar modelos
$postModel = new Post();
$categoryModel = new Category();

// Obtener la Categoria
$currentCategory = $categoryModel->getCategoryById($categoryId);

// Si no existe la Categoria, redirigir
if (!$currentCategory) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Obtener posts de esta Categoria
$posts = $postModel->getPostsByCategory($categoryId);

// Obtener todas las Categorias para el sidebar
$categories = $categoryModel->getCategoriesWithPostCount();

// Título de la página
$pageTitle = 'Categoria: ' . htmlspecialchars($currentCategory['name']) . ' - ' . SITE_NAME;

// Incluir vistas
include VIEWS_PATH . '/header.php';
?>

<main class="main-container">
    <div class="content-wrapper">
        <!-- Contenido principal: Posts de la Categoria -->
        <section class="posts-section">
            <div class="category-header">
                <h1 class="category-title">
                    <?= getCategoryHeaderEmoji($currentCategory) ?> <?= htmlspecialchars($currentCategory['name']) ?>
                </h1>
                <?php if (!empty($currentCategory['description'])): ?>
                    <p class="category-description"><?= htmlspecialchars($currentCategory['description']) ?></p>
                <?php endif; ?>
                <div class="category-stats">
                    <span class="posts-count"><?= count($posts) ?> publicaciones</span>
                    <a href="<?= BASE_URL ?>/index.php" class="btn-back-small">← Volver al inicio</a>
                </div>
            </div>
            
            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <div class="no-posts-icon">📭</div>
                    <h2>No hay publicaciones en esta Categoría</h2>
                    <p>Aun no se han publicado posts en esta Categoria.</p>
                    <a href="<?= BASE_URL ?>/index.php" class="btn-primary">Ver todas las publicaciones</a>
                </div>
            <?php else: ?>
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <?php if (isset($post['image']) && $post['image']): ?>
                                <img src="<?= BASE_URL ?>/uploads/posts/<?= htmlspecialchars($post['image']) ?>" 
                                     alt="<?= htmlspecialchars($post['title']) ?>"
                                     class="post-thumbnail">
                            <?php endif; ?>
                            
                            <div class="post-content">
                                <div class="post-header">
                                    <h2 class="post-title">
                                        <a href="<?= BASE_URL ?>/post.php?id=<?= htmlspecialchars($post['id']) ?>">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </h2>
                                    <div class="post-meta">
                                        <span class="post-date">
                                            <i class="icon-clock"></i>
                                            <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                                        </span>
                                        <?php if (isset($post['views']) && $post['views'] > 0): ?>
                                            <span class="post-views">
                                                <i class="icon-eye"></i>
                                                <?= number_format($post['views']) ?> vistas
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="post-description">
                                    <p><?= htmlspecialchars($post['description']) ?></p>
                                </div>
                                <div class="post-footer">
                                    <div class="post-author">
                                        <?php if (isset($post['author_avatar']) && $post['author_avatar']): ?>
                                            <img src="<?= BASE_URL ?>/uploads/users/<?= htmlspecialchars($post['author_avatar']) ?>" 
                                                 alt="<?= htmlspecialchars($post['author_name']) ?>"
                                                 class="author-avatar-small">
                                        <?php else: ?>
                                            <span class="author-avatar-placeholder-small">
                                                <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="author-name"><?= htmlspecialchars($post['author_name']) ?></span>
                                    </div>
                                    <a href="<?= BASE_URL ?>/post.php?id=<?= htmlspecialchars($post['id']) ?>" class="btn-read-more">
                                        Leer mas →
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        
        <!-- Sidebar -->
        <?php include VIEWS_PATH . '/sidebar.php'; ?>
    </div>
</main>

<?php include VIEWS_PATH . '/footer.php'; ?>
