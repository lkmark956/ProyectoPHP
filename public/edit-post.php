<?php
/**
 * Editar post del usuario
 */

require_once '../config/config.php';

use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\ImageUpload;
use App\Models\Database;

$userModel = new User();

// Verificar que el usuario esté autenticado
if (!$userModel->isLoggedIn()) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$currentUser = $userModel->getCurrentUser();
$postModel = new Post();
$categoryModel = new Category();

// Verificar que se pasó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . '/my-posts.php');
    exit;
}

$postId = intval($_GET['id']);

// Obtener el post
$db = Database::getInstance()->getConnection();
$sql = "SELECT * FROM posts WHERE id = :id";
$stmt = $db->prepare($sql);
$stmt->bindValue(':id', $postId, PDO::PARAM_INT);
$stmt->execute();
$post = $stmt->fetch();

// Verificar que el post existe
if (!$post) {
    header('Location: ' . BASE_URL . '/my-posts.php');
    exit;
}

// Verificar que el usuario es el autor del post (o es admin)
if ($post['author_id'] != $currentUser['id'] && !$userModel->hasRole('admin')) {
    die('No tienes permisos para editar este post');
}

$error = '';
$success = '';

// Obtener Categorias
$categories = $categoryModel->getAllCategories();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'content' => trim($_POST['content'] ?? ''),
        'category_id' => intval($_POST['category_id'] ?? 0),
        'published' => isset($_POST['published']) ? 1 : 0
    ];
    
    // Validación básica
    if (empty($data['title'])) {
        $error = 'El título es requerido';
    } elseif (strlen($data['title']) < 5) {
        $error = 'El título debe tener al menos 5 caracteres';
    } elseif (empty($data['description'])) {
        $error = 'La descripción es requerida';
    } elseif (empty($data['content'])) {
        $error = 'El contenido es requerido';
    } elseif (strlen($data['content']) < 50) {
        $error = 'El contenido debe tener al menos 50 caracteres';
    } else {
        try {
            // Procesar nueva imagen si se subió
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageUpload = new ImageUpload('uploads/posts/');
                $uploadResult = $imageUpload->upload($_FILES['image'], 'post_');
                
                if ($uploadResult['success']) {
                    // Eliminar imagen anterior si existe
                    if ($post['image']) {
                        $imageUpload->delete($post['image']);
                    }
                    $data['image'] = $uploadResult['filename'];
                } else {
                    $error = 'Error al subir imagen: ' . $uploadResult['error'];
                }
            }
            
            // Si se marcó para eliminar la imagen actual
            if (isset($_POST['remove_image']) && $post['image']) {
                $imageUpload = new ImageUpload('uploads/posts/');
                $imageUpload->delete($post['image']);
                $data['image'] = null;
            }
            
            // Si no hay error, actualizar el post
            if (empty($error)) {
                $postModel->updatePost($postId, $data);
                $success = '¡Post actualizado exitosamente!';
                
                // Actualizar datos del post para mostrar cambios
                $stmt->execute();
                $post = $stmt->fetch();
            }
        } catch (\Exception $e) {
            $error = 'Error al actualizar el post: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Editar publicacion - ' . SITE_NAME;
include VIEWS_PATH . '/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/profile.css">

<main class="main-container">
    <div class="create-post-container">
        <div class="page-header-user">
            <h1>✏️ Editar publicacion</h1>
            <p>Actualiza tu publicacion</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <span class="alert-icon">⚠️</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <span class="alert-icon">✓</span>
                <span><?= htmlspecialchars($success) ?></span>
                <div class="success-actions">
                    <a href="<?= BASE_URL ?>/post.php?id=<?= $postId ?>" class="btn-link">Ver post</a>
                    <a href="<?= BASE_URL ?>/my-posts.php" class="btn-link">Volver a mis publicaciones</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="create-post-card">
            <form method="POST" action="" enctype="multipart/form-data" class="post-form">
                <div class="form-section">
                    <label for="title" class="form-label required">
                        Título del Post
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        class="form-input" 
                        required
                        maxlength="200"
                        placeholder="Escribe un título atractivo para tu post"
                        value="<?= htmlspecialchars($post['title']) ?>"
                    >
                </div>

                <div class="form-section">
                    <label for="category_id" class="form-label required">
                        Categoria
                    </label>
                    <select id="category_id" name="category_id" class="form-input" required>
                        <option value="">Selecciona una Categoria</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" 
                                    <?= ($post['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-section">
                    <label for="description" class="form-label required">
                        Descripción Corta
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-textarea" 
                        rows="3"
                        required
                        maxlength="500"
                        placeholder="Escribe una breve descripción"
                    ><?= htmlspecialchars($post['description']) ?></textarea>
                </div>

                <div class="form-section">
                    <label for="content" class="form-label required">
                        Contenido del Post
                    </label>
                    <textarea 
                        id="content" 
                        name="content" 
                        class="form-textarea content-editor" 
                        rows="15"
                        required
                        placeholder="Escribe el contenido completo de tu post"
                    ><?= htmlspecialchars($post['content']) ?></textarea>
                </div>

                <div class="form-section">
                    <label for="image" class="form-label">
                        Imagen Destacada
                    </label>
                    
                    <?php if ($post['image']): ?>
                        <div class="current-image">
                            <img src="<?= BASE_URL ?>/uploads/posts/<?= htmlspecialchars($post['image']) ?>" alt="Imagen actual">
                            <label class="remove-current-image">
                                <input type="checkbox" name="remove_image" value="1">
                                <span>Eliminar imagen actual</span>
                            </label>
                        </div>
                    <?php endif; ?>
                    
                    <div class="file-upload-area">
                        <input 
                            type="file" 
                            id="image" 
                            name="image" 
                            accept="image/jpeg,image/png,image/gif,image/webp"
                            class="file-input"
                            onchange="previewImage(this)"
                        >
                        <label for="image" class="file-label">
                            <span class="file-icon">📷</span>
                            <span class="file-text"><?= $post['image'] ? 'Cambiar imagen' : 'Seleccionar imagen' ?></span>
                            <span class="file-info">JPG, PNG, GIF o WEBP (máx. 5MB)</span>
                        </label>
                    </div>
                    <div id="image-preview" class="image-preview" style="display: none;">
                        <img id="preview-img" src="" alt="Vista previa">
                        <button type="button" class="remove-image" onclick="removeImage()">✕</button>
                    </div>
                </div>

                <div class="form-section">
                    <label class="checkbox-label">
                        <input type="checkbox" name="published" value="1" <?= $post['published'] ? 'checked' : '' ?>>
                        <span>Publicar este post (visible para todos)</span>
                    </label>
                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/my-posts.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        <span>✓</span> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const input = document.getElementById('image');
    const preview = document.getElementById('image-preview');
    
    input.value = '';
    preview.style.display = 'none';
}
</script>



<?php include VIEWS_PATH . '/footer.php'; ?>
