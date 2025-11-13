<?php
/**
 * Gestión de Categorías - Admin
 */

require_once '../auth.php';

use App\Category;

$categoryModel = new Category();
$categories = $categoryModel->getCategoriesWithPostCount();

$pageTitle = 'Gestión de Categorías';
include '../../../views/admin/header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>📁 Gestión de Categorías</h1>
        <a href="create.php" class="btn btn-primary">
            <span>➕</span> Nueva Categoría
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">
        <?php
        $messages = [
            'created' => '✓ Categoría creada exitosamente',
            'updated' => '✓ Categoría actualizada exitosamente',
            'deleted' => '✓ Categoría eliminada exitosamente'
        ];
        echo $messages[$_GET['success']] ?? '✓ Operación exitosa';
        ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($categories)): ?>
    <div class="table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Slug</th>
                    <th>Descripción</th>
                    <th>Posts</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= $category['id'] ?></td>
                    <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                    <td><code><?= htmlspecialchars($category['slug']) ?></code></td>
                    <td><?= htmlspecialchars(substr($category['description'] ?? '', 0, 50)) ?><?= strlen($category['description'] ?? '') > 50 ? '...' : '' ?></td>
                    <td><span class="badge badge-info"><?= $category['post_count'] ?></span></td>
                    <td class="actions-cell">
                        <a href="edit.php?id=<?= $category['id'] ?>" class="btn-action btn-edit" title="Editar">✏️</a>
                        <?php if ($category['post_count'] == 0): ?>
                        <a href="delete.php?id=<?= $category['id'] ?>" class="btn-action btn-delete" title="Eliminar" onclick="return confirm('¿Eliminar esta categoría?')">🗑️</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="empty-state">
        <div class="empty-icon">📁</div>
        <h2>No hay categorías</h2>
        <p>Crea tu primera categoría para organizar los posts</p>
        <a href="create.php" class="btn btn-primary">Nueva Categoría</a>
    </div>
    <?php endif; ?>
</div>

<?php include '../../../views/admin/footer.php'; ?>
