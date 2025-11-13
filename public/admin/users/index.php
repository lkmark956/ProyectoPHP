<?php
/**
 * Listar usuarios - Admin
 */

require_once '../auth.php';

use App\User;

// Solo admins pueden gestionar usuarios
requireRole('admin');

$userModel = new User();

// Obtener página actual
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;

// Obtener usuarios
$users = $userModel->getAllUsers($currentPage, $perPage);
$totalUsers = $userModel->getTotalUsers();
$totalPages = ceil($totalUsers / $perPage);

// Mensajes
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

$pageTitle = 'Gestión de Usuarios';
include '../../../views/admin/header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>👥 Gestión de Usuarios</h1>
        <a href="create.php" class="btn btn-primary">➕ Nuevo Usuario</a>
    </div>

    <?php if ($success === 'created'): ?>
        <div class="alert alert-success">✓ Usuario creado exitosamente</div>
    <?php elseif ($success === 'updated'): ?>
        <div class="alert alert-success">✓ Usuario actualizado exitosamente</div>
    <?php elseif ($success === 'deleted'): ?>
        <div class="alert alert-success">✓ Usuario eliminado exitosamente</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($users)): ?>
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <h2>No hay usuarios registrados</h2>
            <p>Crea el primer usuario para comenzar</p>
            <a href="create.php" class="btn btn-primary">➕ Crear Usuario</a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Último Acceso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($user['username']) ?></strong>
                            </td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['full_name'] ?? '-') ?></td>
                            <td>
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
                            </td>
                            <td>
                                <?php if ($user['active']): ?>
                                    <span class="badge badge-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?>
                            </td>
                            <td class="actions-cell">
                                <a href="edit.php?id=<?= $user['id'] ?>" class="btn-action" title="Editar">✏️</a>
                                <?php if ($user['id'] != $currentUser['id']): ?>
                                    <a href="delete.php?id=<?= $user['id'] ?>" 
                                       class="btn-action" 
                                       title="Eliminar"
                                       onclick="return confirm('¿Estás seguro de eliminar este usuario?')">🗑️</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="admin-pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>" class="pagination-btn">← Anterior</a>
                <?php endif; ?>

                <span class="pagination-info">
                    Página <?= $currentPage ?> de <?= $totalPages ?>
                </span>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>" class="pagination-btn">Siguiente →</a>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../../../views/admin/footer.php'; ?>
