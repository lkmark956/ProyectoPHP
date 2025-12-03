<?php
/**
 * Vista individual de un post
 */

// Cargar configuración
require_once '../config/config.php';

use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;

// Verificar que se pasó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$postId = intval($_GET['id']);

// Instanciar modelos
$postModel = new Post();
$categoryModel = new Category();
$commentModel = new Comment();

// Obtener el post
$post = $postModel->getPostById($postId);

// Obtener comentarios
$comments = $commentModel->getCommentsByPost($postId);
$commentCount = count($comments);

// Si no existe el post, redirigir
if (!$post) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Incrementar contador de vistas
$postModel->incrementViews($postId);

// Obtener Categorias para el sidebar
$categories = $categoryModel->getCategoriesWithPostCount();

// Título de la página
$pageTitle = htmlspecialchars($post['title']) . ' - ' . SITE_NAME;

// Incluir header
include VIEWS_PATH . '/header.php';
?>

<main class="main-container">
    <div class="post-single-layout">
        <!-- Contenido del post -->
        <article class="post-full">
            <div class="post-full-header">
                <h1 class="post-full-title"><?= htmlspecialchars($post['title']) ?></h1>
                
                <div class="post-full-meta">
                    <div class="post-author-info">
                        <?php if (isset($post['author_avatar']) && $post['author_avatar']): ?>
                            <img src="<?= BASE_URL ?>/uploads/users/<?= htmlspecialchars($post['author_avatar']) ?>" 
                                 alt="<?= htmlspecialchars($post['author_name']) ?>"
                                 class="author-avatar">
                        <?php else: ?>
                            <span class="author-avatar-placeholder">
                                <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
                            </span>
                        <?php endif; ?>
                        <div class="author-details">
                            <span class="author-by">Escrito por</span>
                            <span class="author-name-full"><?= htmlspecialchars($post['author_name']) ?></span>
                        </div>
                    </div>
                    
                    <div class="post-metadata">
                        <span class="post-category-badge">
                            <i class="icon-folder"></i>
                            <?= htmlspecialchars($post['category_name'] ?? 'Sin categoria') ?>
                        </span>
                        <span class="post-date-badge">
                            <i class="icon-clock"></i>
                            <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                        </span>
                        <?php if (isset($post['views']) && $post['views'] > 0): ?>
                            <span class="post-views-badge">
                                <i class="icon-eye"></i>
                                <?= number_format($post['views']) ?> vistas
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (isset($post['image']) && $post['image']): ?>
                <div class="post-featured-image">
                    <img src="<?= BASE_URL ?>/uploads/posts/<?= htmlspecialchars($post['image']) ?>" 
                         alt="<?= htmlspecialchars($post['title']) ?>">
                </div>
            <?php endif; ?>

            <div class="post-full-content">
                <?= $post['content'] ?>
            </div>

            <div class="post-full-footer">
                <a href="<?= BASE_URL ?>/index.php" class="btn-back">
                    ← Volver al inicio
                </a>
            </div>
        </article>

        <!-- Sección de Comentarios -->
        <div class="comments-section" id="comments">
            <h2 class="comments-title">💬 Comentarios (<?= $commentCount ?>)</h2>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    ✓ <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error">
                    ⚠️ <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Formulario para crear comentario -->
                <div class="comment-form-container">
                    <h3>✍️ Deja tu comentario</h3>
                    <form action="comment_create.php" method="POST" class="comment-form">
                        <input type="hidden" name="post_id" value="<?= $postId ?>">
                        <textarea name="content" rows="4" placeholder="Escribe tu comentario..." required></textarea>
                        <button type="submit" class="btn btn-primary">Publicar Comentario</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="comment-login-message">
                    <p>👤 <a href="<?= BASE_URL ?>/login.php?redirect=<?= urlencode($_SERVER['REQUEST_URI']) ?>">Inicia sesión</a> para dejar un comentario.</p>
                </div>
            <?php endif; ?>

            <!-- Lista de comentarios -->
            <?php if ($commentCount > 0): ?>
                <div class="comments-list">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-item" id="comment-<?= $comment['id'] ?>">
                            <div class="comment-header">
                                <div class="comment-author-info">
                                    <?php if ($comment['avatar']): ?>
                                        <img src="<?= BASE_URL ?>/uploads/users/<?= htmlspecialchars($comment['avatar']) ?>" 
                                             alt="<?= htmlspecialchars($comment['username']) ?>"
                                             class="comment-avatar">
                                    <?php else: ?>
                                        <div class="comment-avatar-placeholder">
                                            <?= strtoupper(substr($comment['username'], 0, 2)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="comment-author-details">
                                        <span class="comment-author-name">
                                            <?= htmlspecialchars($comment['username']) ?>
                                            <?php if ($comment['role'] === 'admin'): ?>
                                                <span class="badge-admin">Admin</span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="comment-date">
                                            <?php
                                            $createdDate = new DateTime($comment['created_at']);
                                            $now = new DateTime();
                                            $diff = $now->diff($createdDate);
                                            
                                            if ($diff->days == 0) {
                                                if ($diff->h > 0) {
                                                    echo "Hace " . $diff->h . " hora" . ($diff->h > 1 ? 's' : '');
                                                } else {
                                                    echo "Hace " . max(1, $diff->i) . " minuto" . ($diff->i > 1 ? 's' : '');
                                                }
                                            } elseif ($diff->days == 1) {
                                                echo "Ayer";
                                            } elseif ($diff->days < 7) {
                                                echo "Hace " . $diff->days . " días";
                                            } else {
                                                echo date('d/m/Y', strtotime($comment['created_at']));
                                            }
                                            ?>
                                            <?php if ($comment['created_at'] != $comment['updated_at']): ?>
                                                <span class="comment-edited">(editado)</span>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                </div>

                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <?php
                                    $canEdit = $commentModel->canEditComment($comment['id'], $_SESSION['user_id'], $_SESSION['role']);
                                    $canDelete = $commentModel->canDeleteComment($comment['id'], $_SESSION['user_id'], $_SESSION['role'], $post['author_id']);
                                    ?>
                                    <?php if ($canEdit || $canDelete): ?>
                                        <div class="comment-actions">
                                            <?php if ($canEdit): ?>
                                                <button onclick="toggleEditComment(<?= $comment['id'] ?>)" class="btn-comment-action">✏️ Editar</button>
                                            <?php endif; ?>
                                            <?php if ($canDelete): ?>
                                                <a href="comment_delete.php?id=<?= $comment['id'] ?>&post_id=<?= $postId ?>" 
                                                   class="btn-comment-action btn-delete"
                                                   onclick="return confirm('¿Eliminar este comentario?')">🗑️ Eliminar</a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div class="comment-content">
                                <div class="comment-text" id="comment-text-<?= $comment['id'] ?>">
                                    <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                </div>
                                
                                <?php if ($canEdit ?? false): ?>
                                    <form action="comment_edit.php" method="POST" class="comment-edit-form" id="comment-edit-<?= $comment['id'] ?>" style="display: none;">
                                        <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                        <input type="hidden" name="post_id" value="<?= $postId ?>">
                                        <textarea name="content" rows="3" required><?= htmlspecialchars($comment['content']) ?></textarea>
                                        <div class="edit-form-actions">
                                            <button type="submit" class="btn btn-primary btn-sm">💾 Guardar</button>
                                            <button type="button" onclick="toggleEditComment(<?= $comment['id'] ?>)" class="btn btn-secondary btn-sm">Cancelar</button>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-comments">
                    <p>No hay comentarios todavía. ¡Sé el primero en comentar!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
function toggleEditComment(commentId) {
    const textDiv = document.getElementById('comment-text-' + commentId);
    const editForm = document.getElementById('comment-edit-' + commentId);
    
    if (textDiv.style.display === 'none') {
        textDiv.style.display = 'block';
        editForm.style.display = 'none';
    } else {
        textDiv.style.display = 'none';
        editForm.style.display = 'block';
        editForm.querySelector('textarea').focus();
    }
}
</script>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/comments.css">

<?php include VIEWS_PATH . '/footer.php'; ?>
