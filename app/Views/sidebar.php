<aside class="sidebar">
    <div class="sidebar-widget">
        <h3 class="widget-title">Categorias</h3>
        <ul class="category-list">
            <?php if (!empty($categories)): ?>
                <?php foreach ($categories as $category): ?>
                    <li class="category-item">
                        <a href="<?= BASE_URL ?>/category.php?id=<?= htmlspecialchars($category['id']) ?>" class="category-link">
                            <span class="category-name"><?= htmlspecialchars($category['name']) ?></span>
                            <span class="post-count"><?= $category['post_count'] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="no-categories">No hay Categorias disponibles</li>
            <?php endif; ?>
        </ul>
    </div>
</aside>
