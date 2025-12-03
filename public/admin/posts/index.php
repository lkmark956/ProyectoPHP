<?php
/**
 * Listar todos los posts - Admin
 */

require_once '../auth.php';

use App\Models\Post;
use App\Models\Database;

if (!canCreateContent()) {
    die('No tienes permisos para acceder a esta página');
}

$postModel = new Post();

// Paginación
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;

// Filtro por categoría
$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;

// Obtener todos los posts (incluyendo no publicados)
$db = Database::getInstance()->getConnection();
$offset = ($page - 1) * $perPage;

// Construir query con filtro opcional
$whereClauses = [];
$params = [':limit' => $perPage, ':offset' => $offset];

if ($categoryId) {
    $whereClauses[] = "p.category_id = :category_id";
    $params[':category_id'] = $categoryId;
}

$whereSQL = !empty($whereClauses) ? "WHERE " . implode(" AND ", $whereClauses) : "";

$sql = "SELECT p.*, c.name as category_name, u.username as author_name 
        FROM posts p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN users u ON p.author_id = u.id 
        $whereSQL
        ORDER BY p.created_at DESC 
        LIMIT :limit OFFSET :offset";

$stmt = $db->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$posts = $stmt->fetchAll();

// Total de posts (con filtro)
$countSQL = "SELECT COUNT(*) as total FROM posts p " . $whereSQL;
$countStmt = $db->prepare($countSQL);
if ($categoryId) {
    $countStmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
}
$countStmt->execute();
$totalPosts = $countStmt->fetch()['total'];

// Obtener nombre de categoría si hay filtro
$categoryName = '';
if ($categoryId) {
    $catStmt = $db->prepare("SELECT name FROM categories WHERE id = :id");
    $catStmt->bindValue(':id', $categoryId, PDO::PARAM_INT);
    $catStmt->execute();
    $catResult = $catStmt->fetch();
    $categoryName = $catResult ? $catResult['name'] : '';
}
$totalPages = ceil($totalPosts / $perPage);

$pageTitle = 'Gestion de Posts';
include '../../../app/Views/admin/header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>📝 Gestión de Posts<?php if ($categoryName): ?> - <?= htmlspecialchars($categoryName) ?><?php endif; ?></h1>
        <div>
            <?php if ($categoryId): ?>
                <a href="index.php" class="btn btn-secondary" style="margin-right: 10px;">← Ver todos</a>
            <?php endif; ?>
            <a href="create.php" class="btn btn-primary">
                <span>➕</span> Crear Nuevo Post
            </a>
        </div>
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
                    <th>Titulo</th>
                    <th>Categoria</th>
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
                            <?= htmlspecialchars($post['category_name'] ?? 'Sin Categoria') ?>
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
        <h2>No hay posts Aun</h2>
        <p>Comienza creando tu primer post</p>
        <a href="create.php" class="btn btn-primary">Crear Nuevo Post</a>
    </div>
    <?php endif; ?>
</div>

<?php include '../../../app/Views/admin/footer.php'; ?>
