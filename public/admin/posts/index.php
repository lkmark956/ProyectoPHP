<?php
/**
 * Listar todos los posts - Admin
 */

require_once '../auth.php';

use App\Post;

if (!canCreateContent()) {
    die('No tienes permisos para acceder a esta página');
}

$postModel = new Post();

// Paginación
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;

// Obtener todos los posts (incluyendo no publicados)
$db = App\Database::getInstance()->getConnection();
$offset = ($page - 1) * $perPage;

$sql = "SELECT p.*, c.name as category_name, u.username as author_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN users u ON p.author_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($sql);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

// Total de posts
$totalPosts = $db->query("SELECT COUNT(*) as total FROM posts")->fetch()['total'];
$totalPages = ceil($totalPosts / $perPage);

$pageTitle = 'Gestión de Posts';
include '../../../views/admin/header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>📝 Gestión de Posts</h1>
        <a href="create.php" class="btn btn-primary">
            <span>➕</span> Crear Nuevo Post
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        $messages = [
            'created' => '✓ Post creado exitosamente',
            'updated' => '✓ Post actualizado exitosamente',
            'deleted' => '✓ Post eliminado exitosamente'
        ];
        echo $messages[$_GET['success']] ?? '✓ Operación exitosa';
        ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($posts)): ?>
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Categoría</th>
                    <th>Autor</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Vistas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td><?= $post['id'] ?></td>
                    <td class="post-title-cell">
                        <strong><?= htmlspecialchars($post['title']) ?></strong>
                    </td>
                    <td>
                        <span class="badge badge-category">
                            <?= htmlspecialchars($post['category_name'] ?? 'Sin categoría') ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($post['author_name']) ?></td>
                    <td>
                        <?php if ($post['published']): ?>
                            <span class="badge badge-success">Publicado</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Borrador</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($post['created_at'])) ?></td>
                    <td><?= number_format($post['views']) ?></td>
                    <td class="actions-cell">
                        <a href="../../post.php?id=<?= $post['id'] ?>" class="btn-action btn-view" title="Ver" target="_blank">👁️</a>
                        <a href="edit.php?id=<?= $post['id'] ?>" class="btn-action btn-edit" title="Editar">✏️</a>
                        <a href="delete.php?id=<?= $post['id'] ?>" class="btn-action btn-delete" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar este post?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php if ($totalPages > 1): ?>
    <nav class="admin-pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>" class="pagination-btn">← Anterior</a>
        <?php endif; ?>
        
        <span class="pagination-info">Página <?= $page ?> de <?= $totalPages ?></span>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>" class="pagination-btn">Siguiente →</a>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📝</div>
        <h2>No hay posts aún</h2>
        <p>Comienza creando tu primer post</p>
        <a href="create.php" class="btn btn-primary">Crear Nuevo Post</a>
    </div>
    <?php endif; ?>
</div>

<?php include '../../../views/admin/footer.php'; ?>
