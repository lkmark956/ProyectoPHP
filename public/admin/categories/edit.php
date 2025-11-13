<?php
require_once '../auth.php';
use App\Category;

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$categoryModel = new Category();
$categoryId = intval($_GET['id']);
$category = $categoryModel->getCategoryById($categoryId);

if (!$category) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? '')
    ];
    
    if (empty($data['name'])) {
        $error = 'El nombre es requerido';
    } else {
        try {
            $categoryModel->updateCategory($categoryId, $data);
            header('Location: index.php?success=updated');
            exit;
        } catch (Exception $e) {
            $error = 'Error al actualizar';
        }
    }
} else {
    $_POST = $category;
}

$pageTitle = 'Editar Categoría';
include '../../../views/admin/header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>✏️ Editar Categoría</h1>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="admin-form" style="max-width: 600px;">
        <div class="form-group">
            <label for="name" class="form-label required">Nombre</label>
            <input type="text" id="name" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name']) ?>">
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Descripción</label>
            <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Actualizar</button>
            <a href="index.php" class="btn btn-outline">Cancelar</a>
            <a href="delete.php?id=<?= $categoryId ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar esta categoría?')">🗑️ Eliminar</a>
        </div>
    </form>
</div>

<?php include '../../../views/admin/footer.php'; ?>
