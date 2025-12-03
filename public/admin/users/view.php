<?php
/**
 * Ver detalles de usuario - Admin
 */

require_once '../auth.php';

use App\Models\User;

// Solo admins pueden gestionar usuarios
requireRole('admin');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$userModel = new User();
$userId = intval($_GET['id']);
$user = $userModel->getUserById($userId);

if (!$user) {
    header('Location: index.php?error=Usuario no encontrado');
    exit;
}

// Obtener estadísticas
$stats = $userModel->getUserStats($userId);

// Obtener posts del usuario
$userPosts = $userModel->getUserPosts($userId, 20);

$pageTitle = 'Detalles de Usuario';
include '../../../app/Views/admin/header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>👤 Perfil de Usuario</h1>
        <div>
            <a href="index.php" class="btn btn-secondary" style="margin-right: 10px;">← Volver</a>
            <a href="edit.php?id=<?= $userId ?>" class="btn btn-primary">✏️ Editar</a>
        </div>
    </div>

    <!-- Información del Usuario -->
    <div class="user-details-container">
        <div class="user-info-card">
            <div class="user-avatar-section">
                <?php if ($user['avatar']): ?>
                    <img src="<?= BASE_URL ?>/uploads/users/<?= htmlspecialchars($user['avatar']) ?>" 
                         alt="Avatar" class="user-avatar-large">
                <?php else: ?>
                    <div class="user-avatar-placeholder-large">
                        <?= strtoupper(substr($user['username'], 0, 2)) ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="user-info-details">
                <h2><?= htmlspecialchars($user['username']) ?></h2>
                
                <div class="user-info-grid">
                    <div class="info-item">
                        <span class="info-label">📧 Email:</span>
                        <span class="info-value"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">👤 Nombre completo:</span>
                        <span class="info-value"><?= htmlspecialchars($user['full_name'] ?: '-') ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">🎭 Rol:</span>
                        <span class="info-value">
                            <?php
                            $roleColors = [
                                'admin' => 'badge-category',
                                'author' => 'badge-info',
                                'user' => 'badge-warning'
                            ];
                            $roleColor = $roleColors[$user['role']] ?? 'badge-warning';
                            ?>
                            <span class="badge <?= $roleColor ?>">
                                <?= htmlspecialchars($user['role']) ?>
                            </span>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">📅 Registro:</span>
                        <span class="info-value"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">🕐 Último acceso:</span>
                        <span class="info-value">
                            <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?>
                        </span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">✅ Estado:</span>
                        <span class="info-value">
                            <?php if ($user['active']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inactivo</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <div class="stats-card">
                <div class="stats-icon">📝</div>
                <div class="stats-number"><?= number_format($stats['posts_created']) ?></div>
                <div class="stats-label">Posts Creados</div>
            </div>
            
            <div class="stats-card">
                <div class="stats-icon">👁️</div>
                <div class="stats-number"><?= number_format($stats['total_views']) ?></div>
                <div class="stats-label">Vistas Totales</div>
            </div>
            
            <div class="stats-card">
                <div class="stats-icon">📊</div>
                <div class="stats-number">
                    <?= $stats['posts_created'] > 0 ? number_format($stats['total_views'] / $stats['posts_created'], 1) : '0' ?>
                </div>
                <div class="stats-label">Vistas por Post</div>
            </div>
        </div>

        <!-- Posts del Usuario -->
        <div class="user-posts-section">
            <h3>📚 Posts Creados (<?= count($userPosts) ?>)</h3>
            
            <?php if (!empty($userPosts)): ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Vistas</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($userPosts as $post): ?>
                                <tr>
                                    <td><?= $post['id'] ?></td>
                                    <td class="post-title-cell">
                                        <strong><?= htmlspecialchars($post['title']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-category">
                                            <?= htmlspecialchars($post['category_name'] ?? 'Sin Categoría') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($post['published']): ?>
                                            <span class="badge badge-success">Publicado</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Borrador</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($post['views']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($post['created_at'])) ?></td>
                                    <td class="actions-cell">
                                        <a href="../../post.php?id=<?= $post['id'] ?>" class="btn-action" title="Ver" target="_blank">👁️</a>
                                        <a href="../posts/edit.php?id=<?= $post['id'] ?>" class="btn-action" title="Editar">✏️</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state-small">
                    <p>Este usuario no ha creado ningún post todavía.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.user-details-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.user-info-card {
    background: white;
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    display: flex;
    gap: 2rem;
    align-items: start;
}

.user-avatar-section {
    flex-shrink: 0;
}

.user-avatar-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #8b7355;
    box-shadow: 0 4px 12px rgba(139, 115, 85, 0.3);
}

.user-avatar-placeholder-large {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1a2332 0%, #8b7355 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    font-weight: 800;
    border: 4px solid #8b7355;
    box-shadow: 0 4px 12px rgba(139, 115, 85, 0.3);
}

.user-info-details {
    flex: 1;
}

.user-info-details h2 {
    margin: 0 0 1.5rem 0;
    color: #1a2332;
    font-size: 2rem;
    font-weight: 800;
}

.user-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.info-label {
    font-size: 0.9rem;
    color: #5a6c7d;
    font-weight: 600;
}

.info-value {
    font-size: 1.1rem;
    color: #1a2332;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.stats-card {
    position: relative;
    overflow: hidden;
    background: white !important;
}

.stats-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 120px;
    height: 120px;
    background: radial-gradient(circle, rgba(139, 115, 85, 0.1) 0%, transparent 70%);
    border-radius: 50%;
    transform: translate(30%, -30%);
}

.stats-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.8;
    position: relative;
    z-index: 1;
}

.stats-number {
    position: relative;
    z-index: 1;
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, #1a2332 0%, #8b7355 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0.5rem 0;
}

.stats-label {
    position: relative;
    z-index: 1;
    color: #5a6c7d;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.user-posts-section {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.user-posts-section h3 {
    margin: 0 0 1.5rem 0;
    color: #1a2332;
    font-size: 1.5rem;
    font-weight: 700;
}

.empty-state-small {
    text-align: center;
    padding: 3rem;
    color: #5a6c7d;
}

@media (max-width: 768px) {
    .user-info-card {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .user-info-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include '../../../app/Views/admin/footer.php'; ?>
