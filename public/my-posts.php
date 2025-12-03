<?php
/**
 * Mis Posts - Gestión de posts del usuario
 */

require_once '../config/config.php';

use App\Models\User;
use App\Models\Post;
use App\Models\Database;

$userModel = new User();

// Verificar que el usuario esté autenticado
if (!$userModel->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$currentUser = $userModel->getCurrentUser();
$postModel = new Post();
$db = Database::getInstance()->getConnection();

// Obtener posts del usuario
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.author_id = :author_id
        ORDER BY p.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->bindValue(':author_id', $currentUser['id'], PDO::PARAM_INT);
$stmt->execute();
$myPosts = $stmt->fetchAll();

// Contar estadísticas
$totalPosts = count($myPosts);
$totalViews = array_sum(array_column($myPosts, 'views'));

$pageTitle = 'Mis Publicaciones - ' . SITE_NAME;
include VIEWS_PATH . '/header.php';
?>

<main class="main-container">
    <div class="my-posts-container">
        <div class="page-header-user">
            <h1>Mis Publicaciones</h1>
            <p>Gestiona y visualiza todas tus publicaciones</p>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message_type'] ?? 'info' ?>">
                <span class="alert-icon"><?= $_SESSION['message_type'] === 'success' ? '✓' : '⚠️' ?></span>
                <span><?= htmlspecialchars($_SESSION['message']) ?></span>
            </div>
            <?php 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <!-- Estadísticas del usuario -->
        <div class="user-stats">
            <div class="stat-box">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <div class="stat-number"><?= $totalPosts ?></div>
                    <div class="stat-label">Publicaciones</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">👁️</div>
                <div class="stat-info">
                    <div class="stat-number"><?= number_format($totalViews) ?></div>
                    <div class="stat-label">Vistas Totales</div>
                </div>
            </div>
            <div class="stat-box">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <div class="stat-number"><?= $totalPosts > 0 ? number_format($totalViews / $totalPosts, 1) : 0 ?></div>
                    <div class="stat-label">Visitas/publicacion</div>
                </div>
            </div>
        </div>

        <!-- Botón para crear nuevo post -->
        <div class="action-bar">
            <a href="<?= BASE_URL ?>/create-post.php" class="btn-primary">
                <span>✍️</span> Escribir publicacion
            </a>
        </div>

        <!-- Lista de posts -->
        <?php if (empty($myPosts)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h2>Aun no has escrito nada</h2>
                <p>Comparte tus ideas y conocimientos con la comunidad</p>
                <a href="<?= BASE_URL ?>/create-post.php" class="btn-primary">✍️ Escribir Mi Primera publicacion</a>
            </div>
        <?php else: ?>
            <div class="posts-list-user">
                <?php foreach ($myPosts as $post): ?>
                    <div class="post-item-user">
                        <?php if ($post['image']): ?>
                            <div class="post-thumbnail">
                                <img src="<?= BASE_URL ?>/uploads/posts/<?= htmlspecialchars($post['image']) ?>" 
                                     alt="<?= htmlspecialchars($post['title']) ?>">
                            </div>
                        <?php else: ?>
                            <div class="post-thumbnail no-image">
                                <span class="no-image-icon">📄</span>
                            </div>
                        <?php endif; ?>
                        
                        <div class="post-details">
                            <h3 class="post-title-user">
                                <?= htmlspecialchars($post['title']) ?>
                            </h3>
                            <div class="post-meta-user">
                                <span class="meta-item">
                                    <i class="icon">📁</i>
                                    <?= htmlspecialchars($post['category_name'] ?? 'Sin categoria') ?>
                                </span>
                                <span class="meta-item">
                                    <i class="icon">📅</i>
                                    <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                                </span>
                                <span class="meta-item">
                                    <i class="icon">👁️</i>
                                    <?= number_format($post['views']) ?> vistas
                                </span>
                                <span class="meta-item status-badge <?= $post['published'] ? 'published' : 'draft' ?>">
                                    <?= $post['published'] ? '✓ Publicado' : '⏸ Borrador' ?>
                                </span>
                            </div>
                            <p class="post-description-user">
                                <?= htmlspecialchars(substr($post['description'], 0, 150)) ?>
                                <?= strlen($post['description']) > 150 ? '...' : '' ?>
                            </p>
                        </div>
                        
                        <div class="post-actions-user">
                            <a href="<?= BASE_URL ?>/post.php?id=<?= $post['id'] ?>" 
                               class="btn-action-user btn-view" 
                               title="Ver post"
                               target="_blank">
                                👁️ Ver
                            </a>
                            <a href="<?= BASE_URL ?>/edit-post.php?id=<?= $post['id'] ?>" 
                               class="btn-action-user btn-edit" 
                               title="Editar">
                                ✏️ Editar
                            </a>
                            <a href="<?= BASE_URL ?>/delete-post.php?id=<?= $post['id'] ?>" 
                               class="btn-action-user btn-delete" 
                               title="Eliminar"
                               onclick="return confirm('¿Estás seguro de que quieres eliminar este post? Esta acción no se puede deshacer.')">
                                🗑️ Eliminar
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<style>
.my-posts-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.user-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-box {
    background: white;
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.3s ease;
}

.stat-box:hover {
    transform: translateY(-5px);
}

.stat-box:nth-child(1) {
    border-left: 4px solid #667eea;
}

.stat-box:nth-child(2) {
    border-left: 4px solid #f093fb;
}

.stat-box:nth-child(3) {
    border-left: 4px solid #4facfe;
}

.stat-icon {
    font-size: 2.5rem;
}

.stat-info {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-top: 0.25rem;
}

.action-bar {
    margin-bottom: 2rem;
    display: flex;
    justify-content: flex-end;
}

.posts-list-user {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.post-item-user {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    padding: 1.5rem;
    display: grid;
    grid-template-columns: 150px 1fr auto;
    gap: 1.5rem;
    align-items: center;
    transition: all 0.3s ease;
}

.post-item-user:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.post-thumbnail {
    width: 150px;
    height: 100px;
    border-radius: var(--radius-md);
    overflow: hidden;
}

.post-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-thumbnail.no-image {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.no-image-icon {
    font-size: 3rem;
}

.post-details {
    flex: 1;
}

.post-title-user {
    font-size: 1.25rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.post-meta-user {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: var(--text-secondary);
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-sm);
    font-weight: 600;
    font-size: 0.75rem;
}

.status-badge.published {
    background: #d4edda;
    color: #155724;
}

.status-badge.draft {
    background: #fff3cd;
    color: #856404;
}

.post-description-user {
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.5;
}

.post-actions-user {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.btn-action-user {
    padding: 0.5rem 1rem;
    border-radius: var(--radius-md);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    text-align: center;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-view {
    background: #667eea;
    color: white;
}

.btn-view:hover {
    background: #5568d3;
}

.btn-edit {
    background: #f093fb;
    color: white;
}

.btn-edit:hover {
    background: #d97ce6;
}

.btn-delete {
    background: #e74c3c;
    color: white;
}

.btn-delete:hover {
    background: #c0392b;
}

.empty-state {
    background: white;
    border-radius: var(--radius-lg);
    padding: 4rem 2rem;
    text-align: center;
    box-shadow: var(--shadow-md);
}

.empty-icon {
    font-size: 5rem;
    margin-bottom: 1rem;
}

.empty-state h2 {
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .post-item-user {
        grid-template-columns: 1fr;
        text-align: center;
    }
    
    .post-thumbnail {
        width: 100%;
        height: 200px;
    }
    
    .post-actions-user {
        flex-direction: row;
        justify-content: center;
    }
    
    .user-stats {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include VIEWS_PATH . '/footer.php'; ?>
