<?php
/**
 * Dashboard del Panel de Administración
 */

require_once 'auth.php';

use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Models\Database;

$postModel = new Post();
$categoryModel = new Category();
$userModel = new User();
$db = Database::getInstance()->getConnection();

// Obtener estadísticas
$totalPosts = $postModel->getTotalPosts();

$stmt = $db->query("SELECT COUNT(*) as total FROM categories");
$totalCategories = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM users");
$totalUsers = $stmt->fetch()['total'];

// Posts recientes
$recentPosts = $postModel->getAllPosts(1, 5);

// Categorias con conteo
$categories = $categoryModel->getCategoriesWithPostCount();

$pageTitle = 'Dashboard';
include '../../app/Views/admin/header.php';
?>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1>Dashboard</h1>
        <p>Bienvenido, <strong><?= htmlspecialchars($currentUser['full_name'] ?: $currentUser['username']) ?></strong></p>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="stats-grid">
        <div class="stat-card stat-posts">
            <div class="stat-icon">📝</div>
            <div class="stat-content">
                <div class="stat-number"><?= $totalPosts ?></div>
                <div class="stat-label">Posts Publicados</div>
            </div>
        </div>

        <div class="stat-card stat-categories">
            <div class="stat-icon">📁</div>
            <div class="stat-content">
                <div class="stat-number"><?= $totalCategories ?></div>
                <div class="stat-label">Categorias</div>
            </div>
        </div>

        <div class="stat-card stat-users">
            <div class="stat-icon">👥</div>
            <div class="stat-content">
                <div class="stat-number"><?= $totalUsers ?></div>
                <div class="stat-label">Usuarios</div>
            </div>
        </div>

        <div class="stat-card stat-views">
            <div class="stat-icon">👁️</div>
            <div class="stat-content">
                <?php
                $stmt = $db->query("SELECT SUM(views) as total FROM posts");
                $totalViews = $stmt->fetch()['total'] ?? 0;
                ?>
                <div class="stat-number"><?= number_format($totalViews) ?></div>
                <div class="stat-label">Vistas Totales</div>
            </div>
        </div>
    </div>

    <!-- Acciones rápidas -->
    <div class="quick-actions">
        <h2>Acciones Rápidas</h2>
        <div class="action-buttons">
            <?php if (canCreateContent()): ?>
            <a href="posts/create.php" class="action-btn btn-primary">
                <span class="btn-icon">➕</span>
                Crear Nuevo Post
            </a>
            <?php endif; ?>
            
            <a href="categories/create.php" class="action-btn btn-secondary">
                <span class="btn-icon">📁</span>
                Nueva Categoria
            </a>
            
            <?php if (isAdmin()): ?>
            <a href="users/create.php" class="action-btn btn-accent">
                <span class="btn-icon">👤</span>
                Nuevo Usuario
            </a>
            <?php endif; ?>
            
            <a href="<?= BASE_URL ?>/index.php" class="action-btn btn-outline" target="_blank">
                <span class="btn-icon">🌐</span>
                Ver Sitio Web
            </a>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Posts recientes -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Posts Recientes</h2>
                <a href="posts/index.php" class="section-link">Ver todos →</a>
            </div>
            
            <?php if (!empty($recentPosts)): ?>
            <div class="recent-posts-list">
                <?php foreach ($recentPosts as $post): ?>
                <div class="recent-post-item">
                    <div class="post-info">
                        <h3><?= htmlspecialchars($post['title']) ?></h3>
                        <div class="post-meta-info">
                            <span class="meta-category"><?= htmlspecialchars($post['category_name'] ?? 'Sin Categoria') ?></span>
                            <span class="meta-date"><?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
                        </div>
                    </div>
                    <div class="post-actions">
                        <a href="posts/edit.php?id=<?= $post['id'] ?>" class="btn-icon-action" title="Editar">✏️</a>
                        <a href="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>" class="btn-icon-action" title="Ver" target="_blank">👁️</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="empty-message">No hay posts aún. <a href="posts/create.php">Crea el primero</a></p>
            <?php endif; ?>
        </div>

        <!-- Categorias -->
        <div class="dashboard-section">
            <div class="section-header">
                <h2>Categorias</h2>
                <a href="categories/index.php" class="section-link">Ver todas →</a>
            </div>
            
            <?php if (!empty($categories)): ?>
            <div class="categories-list">
                <?php foreach (array_slice($categories, 0, 5) as $category): ?>
                <div class="category-item-dash">
                    <div class="category-info">
                        <span class="category-name-dash"><?= htmlspecialchars($category['name']) ?></span>
                        <span class="category-count-dash"><?= $category['post_count'] ?> posts</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="empty-message">No hay Categorias. <a href="categories/create.php">Crea una</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../app/Views/admin/footer.php'; ?>
