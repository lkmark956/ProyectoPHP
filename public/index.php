<?php
/**
 * Punto de entrada principal de la aplicación
 */

// Cargar configuración (ya incluye session_start())
require_once '../config/config.php';

use App\Post;
use App\Category;

// Instanciar clases
$postModel = new Post();
$categoryModel = new Category();

// Obtener página actual de la URL (por defecto 1)
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Obtener posts con paginación
$posts = $postModel->getAllPosts($currentPage, POSTS_PER_PAGE);
$totalPosts = $postModel->getTotalPosts();
$totalPages = ceil($totalPosts / POSTS_PER_PAGE);

// Obtener categorías para el sidebar
$categories = $categoryModel->getCategoriesWithPostCount();

// Título de la página
$pageTitle = SITE_NAME;

// Incluir vistas
include VIEWS_PATH . '/header.php';
?>

<main class="main-container">
    <div class="content-wrapper">
        <!-- Contenido principal: Posts -->
        <section class="posts-section">
            <h1 class="section-title">Últimas Publicaciones</h1>
            
            <?php if (empty($posts)): ?>
                <div class="no-posts">
                    <p>No hay publicaciones disponibles en este momento.</p>
                </div>
            <?php else: ?>
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <article class="post-card">
                            <?php if (isset($post['image']) && $post['image']): ?>
                                <div class="post-image">
                                    <a href="post.php?id=<?= htmlspecialchars($post['id']) ?>">
                                        <img src="uploads/posts/<?= htmlspecialchars($post['image']) ?>" 
                                             alt="<?= htmlspecialchars($post['title']) ?>">
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="post-content">
                                <div class="post-header">
                                    <h2 class="post-title">
                                        <a href="post.php?id=<?= htmlspecialchars($post['id']) ?>">
                                            <?= htmlspecialchars($post['title']) ?>
                                        </a>
                                    </h2>
                                    <div class="post-meta">
                                        <span class="post-category">
                                            <i class="icon-folder"></i>
                                            <?= htmlspecialchars($post['category_name'] ?? 'Sin categoría') ?>
                                        </span>
                                        <span class="post-date">
                                            <i class="icon-clock"></i>
                                            <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="post-description">
                                    <p><?= htmlspecialchars($post['description']) ?></p>
                                </div>
                                <div class="post-footer">
                                    <div class="post-author">
                                        <?php if (isset($post['author_avatar']) && $post['author_avatar']): ?>
                                            <img src="uploads/users/<?= htmlspecialchars($post['author_avatar']) ?>" 
                                                 alt="<?= htmlspecialchars($post['author_name']) ?>"
                                                 class="author-avatar-small">
                                        <?php else: ?>
                                            <span class="author-avatar-placeholder-small">
                                                <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="author-name"><?= htmlspecialchars($post['author_name']) ?></span>
                                    </div>
                                    <a href="post.php?id=<?= htmlspecialchars($post['id']) ?>" class="btn-read-more">
                                        Leer más →
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
                
                <!-- Paginación -->
                <?php if ($totalPages > 1): ?>
                    <nav class="pagination">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=1" class="pagination-link">« Primera</a>
                            <a href="?page=<?= $currentPage - 1 ?>" class="pagination-link">‹ Anterior</a>
                        <?php endif; ?>
                        
                        <?php
                        // Mostrar páginas cercanas a la actual
                        $start = max(1, $currentPage - 2);
                        $end = min($totalPages, $currentPage + 2);
                        
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <a href="?page=<?= $i ?>" 
                               class="pagination-link <?= $i === $currentPage ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?= $currentPage + 1 ?>" class="pagination-link">Siguiente ›</a>
                            <a href="?page=<?= $totalPages ?>" class="pagination-link">Última »</a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </section>
        
        <!-- Sidebar -->
        <?php include VIEWS_PATH . '/sidebar.php'; ?>
    </div>
</main>

<?php include VIEWS_PATH . '/footer.php'; ?>
