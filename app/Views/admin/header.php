<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin' ?> - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style-clean.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-forms.css">
    <!-- ANIMACIONES Y COLORES PROFESIONALES -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/animations.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/colors-professional.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-professional.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
    <!-- Loading Bar -->
    <div class="progress-bar" id="progressBar" style="display: none;"></div>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-header-container">
            <div class="admin-logo">
                <h1>⚙️ Panel de administracion</h1>
            </div>
            <nav class="admin-nav">
                <div class="admin-user-info">
                    <span class="admin-user-icon">👤</span>
                    <span class="admin-username"><?= htmlspecialchars($currentUser['username']) ?></span>
                    <span class="admin-role">(<?= htmlspecialchars($currentUser['role']) ?>)</span>
                </div>
            </nav>
        </div>
    </header>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <nav class="admin-menu">
                <a href="<?= BASE_URL ?>/admin/index.php" class="admin-menu-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                    <span class="menu-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                
                <?php if (canCreateContent()): ?>
                <a href="<?= BASE_URL ?>/admin/posts/index.php" class="admin-menu-item <?= strpos($_SERVER['PHP_SELF'], '/posts/') !== false ? 'active' : '' ?>">
                    <span class="menu-icon">📝</span>
                    <span>Posts</span>
                </a>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>/admin/categories/index.php" class="admin-menu-item <?= strpos($_SERVER['PHP_SELF'], '/categories/') !== false ? 'active' : '' ?>">
                    <span class="menu-icon">📁</span>
                    <span>Categorias</span>
                </a>
                
                <?php if (isAdmin()): ?>
                <a href="<?= BASE_URL ?>/admin/users/index.php" class="admin-menu-item <?= strpos($_SERVER['PHP_SELF'], '/users/') !== false ? 'active' : '' ?>">
                    <span class="menu-icon">👥</span>
                    <span>Usuarios</span>
                </a>
                <?php endif; ?>
                
                <hr class="admin-menu-divider">
                
                <a href="<?= BASE_URL ?>/index.php" class="admin-menu-item" target="_blank">
                    <span class="menu-icon">🌐</span>
                    <span>Ver Sitio</span>
                </a>
                
                <a href="<?= BASE_URL ?>/logout.php" class="admin-menu-item logout">
                    <span class="menu-icon">🚪</span>
                    <span>Cerrar Sesión</span>
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-content">
