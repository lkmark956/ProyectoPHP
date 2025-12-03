<?php
/**
 * Crear post - Usuario normal
 * Los usuarios pueden crear sus propios posts para que sean revisados
 */

require_once '../config/config.php';

use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\ImageUpload;

$userModel = new User();

// Verificar que el usuario esté autenticado
if (!$userModel->isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$currentUser = $userModel->getCurrentUser();
$postModel = new Post();
$categoryModel = new Category();

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
        'author_id' => $currentUser['id'],
        'published' => 1 // Los posts de usuarios normales se publican directamente
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
    } elseif ($data['category_id'] <= 0) {
        $error = 'Debes seleccionar una Categoria';
    } else {
        try {
            // Procesar imagen si se subió
            if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageUpload = new ImageUpload('uploads/posts/');
                $uploadResult = $imageUpload->upload($_FILES['image'], 'post_');
                
                if ($uploadResult['success']) {
                    $data['image'] = $uploadResult['filename'];
                } else {
                    $error = 'Error al subir imagen: ' . $uploadResult['error'];
                }
            }
            
            // Si no hay error, crear el post
            if (empty($error)) {
                $postId = $postModel->createPost($data);
                $success = '¡Post creado exitosamente! Tu publicacion ya está visible.';
                
                // Limpiar formulario
                $_POST = [];
            }
        } catch (\Exception $e) {
            $error = 'Error al crear el post: ' . $e->getMessage();
        }
    }
}

$pageTitle = 'Escribir Nueva publicacion - ' . SITE_NAME;
include VIEWS_PATH . '/header.php';
?>

<main class="main-container">
    <div class="create-post-container">
        <div class="page-header-user">
            <h1>Escribir Nueva publicacion</h1>
            <p>Comparte tus ideas y conocimientos con la comunidad</p>
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
                    <a href="<?= BASE_URL ?>/index.php" class="btn-link">Ver en el blog</a>
                    <a href="<?= BASE_URL ?>/my-posts.php" class="btn-link">Ver mis publicaciones</a>
                    <a href="<?= BASE_URL ?>/create-post.php" class="btn-link">Crear otro post</a>
                </div>
            </div>
        <?php endif; ?>

        <div class="create-post-card">
            <form method="POST" action="" enctype="multipart/form-data" class="post-form">
                <div class="form-section">
                    <label for="title" class="form-label required">
                        Título de la publicacion
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        class="form-input" 
                        required
                        maxlength="200"
                        placeholder="Escribe un título atractivo para tu publicacion"
                        value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                    >
                    <small class="form-help">Mínimo 5 caracteres, máximo 200</small>
                </div>

                <div class="form-section">
                    <label for="category_id" class="form-label required">
                        Categoria
                    </label>
                    <select id="category_id" name="category_id" class="form-input" required>
                        <option value="">Selecciona una Categoria</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" 
                                    <?= (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : '' ?>>
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
                        placeholder="Escribe una breve descripción que aparecerá en la lista de publicaciones"
                    ><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    <small class="form-help">Resumen breve para atraer lectores (máximo 500 caracteres)</small>
                </div>

                <div class="form-section">
                    <label for="content" class="form-label required">
                        Contenido
                    </label>
                    <textarea 
                        id="content" 
                        name="content" 
                        class="form-textarea content-editor" 
                        rows="15"
                        required
                        placeholder="Escribe el contenido completo de tu publicacion. Puedes usar HTML básico para dar formato."
                    ><?= htmlspecialchars($_POST['content'] ?? '') ?></textarea>
                    <small class="form-help">
                        💡 Puedes usar HTML: &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;a&gt;
                    </small>
                </div>

                <div class="form-section">
                    <label for="image" class="form-label">
                        Imagen Destacada (opcional)
                    </label>
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
                            <span class="file-text">Haz clic para seleccionar una imagen</span>
                            <span class="file-info">JPG, PNG, GIF o WEBP (máx. 5MB)</span>
                        </label>
                    </div>
                    <div id="image-preview" class="image-preview" style="display: none;">
                        <img id="preview-img" src="" alt="Vista previa">
                        <button type="button" class="remove-image" onclick="removeImage()">✕</button>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/index.php" class="btn-secondary">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        <span>✓</span> Publicar Post
                    </button>
                </div>

                <div class="form-tips">
                    <h4>💡 Consejos para un buen post:</h4>
                    <ul>
                        <li>Usa un título claro y descriptivo</li>
                        <li>Incluye una imagen llamativa relacionada con el tema</li>
                        <li>Estructura tu contenido con párrafos cortos</li>
                        <li>Revisa la ortografía antes de publicar</li>
                        <li>Sé original y aporta valor a los lectores</li>
                    </ul>
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

// Contador de caracteres para textarea
document.addEventListener('DOMContentLoaded', function() {
    const textareas = document.querySelectorAll('textarea[maxlength]');
    
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength');
        const helpText = textarea.nextElementSibling;
        
        if (helpText && helpText.classList.contains('form-help')) {
            const counter = document.createElement('span');
            counter.className = 'char-counter';
            counter.textContent = `0/${maxLength}`;
            helpText.appendChild(counter);
            
            textarea.addEventListener('input', function() {
                const currentLength = this.value.length;
                counter.textContent = `${currentLength}/${maxLength}`;
                
                if (currentLength > maxLength * 0.9) {
                    counter.style.color = '#e74c3c';
                } else {
                    counter.style.color = '#95a5a6';
                }
            });
        }
    });
});
</script>

<style>
.create-post-container {
    max-width: 900px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.page-header-user {
    text-align: center;
    margin-bottom: 2rem;
}

.page-header-user h1 {
    font-size: 2.5rem;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.page-header-user p {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

.create-post-card {
    background: white;
    border-radius: var(--radius-lg);
    padding: 2.5rem;
    box-shadow: var(--shadow-md);
}

.post-form .form-section {
    margin-bottom: 2rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
    font-size: 1rem;
}

.form-label.required::after {
    content: ' *';
    color: #e74c3c;
}

.form-input,
.form-textarea {
    width: 100%;
    padding: 0.875rem;
    border: 2px solid #e0e0e0;
    border-radius: var(--radius-md);
    font-size: 1rem;
    font-family: inherit;
    transition: all 0.3s ease;
}

.form-input:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-textarea.content-editor {
    font-family: 'Courier New', monospace;
    line-height: 1.6;
}

.form-help {
    display: block;
    margin-top: 0.5rem;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.char-counter {
    float: right;
    font-weight: 600;
    color: #95a5a6;
}

.file-upload-area {
    position: relative;
}

.file-input {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.file-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    border: 2px dashed #cbd5e0;
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.file-label:hover {
    border-color: var(--primary);
    background: #f0f4ff;
}

.file-icon {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.file-text {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.file-info {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.image-preview {
    position: relative;
    margin-top: 1rem;
    border-radius: var(--radius-md);
    overflow: hidden;
    max-width: 100%;
}

.image-preview img {
    width: 100%;
    height: auto;
    display: block;
}

.remove-image {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(231, 76, 60, 0.9);
    color: white;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.remove-image:hover {
    background: #c0392b;
    transform: scale(1.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 2px solid #f0f0f0;
}

.success-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1rem;
}

.btn-link {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-link:hover {
    text-decoration: underline;
}

.form-tips {
    margin-top: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: var(--radius-md);
    color: white;
}

.form-tips h4 {
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.form-tips ul {
    list-style: none;
    padding: 0;
}

.form-tips li {
    padding: 0.5rem 0;
    padding-left: 1.5rem;
    position: relative;
}

.form-tips li::before {
    content: '✓';
    position: absolute;
    left: 0;
    font-weight: bold;
}

@media (max-width: 768px) {
    .create-post-card {
        padding: 1.5rem;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .form-actions button,
    .form-actions a {
        width: 100%;
        text-align: center;
    }
}
</style>

<?php include VIEWS_PATH . '/footer.php'; ?>
