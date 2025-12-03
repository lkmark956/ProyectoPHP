<?php
require_once '../auth.php';
use App\Models\Category;

$categoryModel = new Category();
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
            $categoryModel->createCategory($data);
            header('Location: index.php?success=created');
            exit;
        } catch (Exception $e) {
            $error = 'Error al crear la Categoria';
        }
    }
}

$pageTitle = 'Nueva Categoria';
include '../../../app/Views/admin/header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>➕ Nueva Categoria</h1>
        <a href="index.php" class="btn btn-secondary">← Volver</a>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="category-form-container">
        <form method="POST" class="admin-form elegant-form">
            <div class="form-main-content">
                <div class="form-group">
                    <label for="name" class="form-label required">Nombre de la Categoría</label>
                    <input type="text" id="name" name="name" class="form-control" required 
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                           placeholder="Ej: Tecnología, Viajes, Comida...">
                    <small class="form-help">🎯 El nombre aparecerá en el sidebar y las tarjetas de posts</small>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea id="description" name="description" class="form-control" rows="5"
                              placeholder="Escribe una breve descripción de esta categoría (opcional)"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <small class="form-help">📝 Esta descripción aparecerá en la página de la categoría</small>
                </div>

                <div class="form-actions-sticky">
                    <button type="submit" class="btn btn-primary btn-block">
                        <span>💾</span> Guardar Categoría
                    </button>
                    <a href="index.php" class="btn btn-outline btn-block">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include '../../../app/Views/admin/footer.php'; ?>
