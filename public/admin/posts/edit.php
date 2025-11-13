<?php
/**
 * Editar post - Admin
 */

require_once '../auth.php';

use App\Post;
use App\Category;
use App\ImageUpload;

if (!canCreateContent()) {
    die('No tienes permisos para acceder a esta página');
}

$postModel = new Post();
$categoryModel = new Category();

// Verificar que existe el ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$postId = intval($_GET['id']);
$post = $postModel->getPostById($postId);

if (!$post) {
    header('Location: index.php');
    exit;
}

$error = '';
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
    
    // Eliminar imagen si se solicitó
    if (isset($_POST['delete_image']) && $post['image']) {
        $imageUpload = new ImageUpload('../../uploads/posts/');
        $imageUpload->delete($post['image']);
        $postModel->deletePostImage($postId);
        $post['image'] = null;
    }
    
    // Procesar nueva imagen si se subió
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imageUpload = new ImageUpload('../../uploads/posts/');
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
    
    if (empty($data['title'])) {
        $error = 'El título es requerido';
    } elseif (empty($data['content'])) {
        $error = 'El contenido es requerido';
    } elseif (empty($error)) {
        try {
            $postModel->updatePost($postId, $data);
            header('Location: index.php?success=updated');
            exit;
        } catch (Exception $e) {
            $error = 'Error al actualizar el post: ' . $e->getMessage();
        }
    }
    
    // Refrescar datos del post
    if (!headers_sent()) {
        $post = $postModel->getPostById($postId);
    }
} else {
    // Prellenar formulario con datos actuales
    $_POST = $post;
}

$pageTitle = 'Editar Post';
include '../../../views/admin/header.php';
?>

<div class="admin-page">
    <div class="page-header">
        <h1>✏️ Editar Post</h1>
        <div class="header-actions">
            <a href="../.. /post.php?id=<?= $postId ?>" class="btn btn-outline" target="_blank">👁️ Ver Post</a>
            <a href="index.php" class="btn btn-secondary">← Volver</a>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">
        ⚠️ <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
        <div class="form-grid">
            <div class="form-main">
                <div class="form-group">
                    <label for="title" class="form-label required">Título del Post</label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        class="form-control" 
                        required
                        value="<?= htmlspecialchars($_POST['title']) ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="description" class="form-label required">Descripción Breve</label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control" 
                        rows="3"
                        required
                        maxlength="200"
                    ><?= htmlspecialchars($_POST['description']) ?></textarea>
                    <small class="form-help">Máximo 200 caracteres</small>
                </div>

                <div class="form-group">
                    <label for="content" class="form-label required">Contenido del Post</label>
                    <div class="editor-toolbar">
                        <button type="button" class="editor-btn" onclick="formatText('bold')" title="Negrita"><strong>B</strong></button>
                        <button type="button" class="editor-btn" onclick="formatText('italic')" title="Cursiva"><em>I</em></button>
                        <button type="button" class="editor-btn" onclick="formatText('underline')" title="Subrayado"><u>U</u></button>
                        <span class="editor-separator">|</span>
                        <button type="button" class="editor-btn" onclick="insertHeading(2)" title="Título">H2</button>
                        <button type="button" class="editor-btn" onclick="insertHeading(3)" title="Subtítulo">H3</button>
                        <span class="editor-separator">|</span>
                        <button type="button" class="editor-btn" onclick="insertList('ul')" title="Lista">• Lista</button>
                        <button type="button" class="editor-btn" onclick="insertList('ol')" title="Lista numerada">1. Lista</button>
                        <span class="editor-separator">|</span>
                        <button type="button" class="editor-btn" onclick="insertLink()" title="Enlace">🔗 Link</button>
                    </div>
                    <textarea 
                        id="content" 
                        name="content" 
                        class="form-control editor-content" 
                        rows="15"
                        required
                    ><?= htmlspecialchars($_POST['content']) ?></textarea>
                </div>
            </div>

            <div class="form-sidebar">
                <div class="sidebar-section">
                    <h3>Publicación</h3>
                    
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input 
                                type="checkbox" 
                                name="published" 
                                value="1"
                                <?= $_POST['published'] ? 'checked' : '' ?>
                            >
                            <span>Publicado</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="category_id" class="form-label">Categoría</label>
                        <select id="category_id" name="category_id" class="form-control">
                            <option value="0">Sin categoría</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" 
                                    <?= ($_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="info-box">
                        <p><strong>Creado:</strong><br><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></p>
                        <?php if ($post['updated_at']): ?>
                        <p><strong>Actualizado:</strong><br><?= date('d/m/Y H:i', strtotime($post['updated_at'])) ?></p>
                        <?php endif; ?>
                        <p><strong>Vistas:</strong> <?= number_format($post['views']) ?></p>
                    </div>
                </div>
                
                <div class="sidebar-section">
                    <h3>Imagen Destacada</h3>
                    
                    <?php if ($post['image']): ?>
                        <div style="margin-bottom: 1rem;">
                            <img src="../../uploads/posts/<?= htmlspecialchars($post['image']) ?>" 
                                 alt="Imagen actual" 
                                 style="max-width: 100%; border-radius: 8px;">
                        </div>
                        <button type="submit" name="delete_image" class="btn btn-danger btn-block" 
                                onclick="return confirm('¿Eliminar esta imagen?')">
                            🗑️ Eliminar Imagen
                        </button>
                        <hr style="margin: 1rem 0;">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="image" class="form-label">
                            <?= $post['image'] ? 'Cambiar' : 'Subir' ?> Imagen
                        </label>
                        <input 
                            type="file" 
                            id="image" 
                            name="image" 
                            accept="image/*"
                            class="form-control"
                            onchange="previewImage(this)"
                        >
                        <small class="form-help">JPG, PNG, GIF, WEBP (máx. 5MB)</small>
                    </div>
                    
                    <div id="image-preview" style="display: none; margin-top: 1rem;">
                        <img id="preview-img" src="" alt="Preview" style="max-width: 100%; border-radius: 8px;">
                    </div>
                </div>

                <div class="form-actions-sticky">
                    <button type="submit" class="btn btn-primary btn-block">
                        💾 Actualizar Post
                    </button>
                    <a href="index.php" class="btn btn-outline btn-block">
                        Cancelar
                    </a>
                    <a href="delete.php?id=<?= $postId ?>" class="btn btn-danger btn-block" 
                       onclick="return confirm('¿Estás seguro de eliminar este post?')">
                        🗑️ Eliminar Post
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Preview de imagen
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

function formatText(command) {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    
    let formattedText = '';
    switch(command) {
        case 'bold': formattedText = '<strong>' + selectedText + '</strong>'; break;
        case 'italic': formattedText = '<em>' + selectedText + '</em>'; break;
        case 'underline': formattedText = '<u>' + selectedText + '</u>'; break;
    }
    
    textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
    textarea.focus();
}

function insertHeading(level) {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end) || 'Título aquí';
    const heading = '<h' + level + '>' + selectedText + '</h' + level + '>\n';
    textarea.value = textarea.value.substring(0, start) + heading + textarea.value.substring(end);
    textarea.focus();
}

function insertList(type) {
    const textarea = document.getElementById('content');
    const start = textarea.selectionStart;
    const list = type === 'ul' 
        ? '<ul>\n    <li>Elemento 1</li>\n    <li>Elemento 2</li>\n</ul>\n'
        : '<ol>\n    <li>Elemento 1</li>\n    <li>Elemento 2</li>\n</ol>\n';
    textarea.value = textarea.value.substring(0, start) + list + textarea.value.substring(start);
    textarea.focus();
}

function insertLink() {
    const url = prompt('URL del enlace:');
    if (url) {
        const text = prompt('Texto del enlace:', url);
        const textarea = document.getElementById('content');
        const start = textarea.selectionStart;
        const link = '<a href="' + url + '">' + text + '</a>';
        textarea.value = textarea.value.substring(0, start) + link + textarea.value.substring(start);
        textarea.focus();
    }
}
</script>

<?php include '../../../views/admin/footer.php'; ?>
