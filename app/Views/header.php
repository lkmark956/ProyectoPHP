<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style-clean.css">
    <!-- ANIMACIONES: Para eliminar, borra esta línea y el archivo animations.css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/animations.css">
    <!-- COLORES PROFESIONALES: Para REVERTIR a colores originales, borra esta línea y el archivo colors-professional.css -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/colors-professional.css">
    <!-- ESTILOS DE AUTENTICACIÓN -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/auth.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Loading Bar -->
    <div class="progress-bar" id="progressBar" style="display: none;"></div>
    <!-- Header -->
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="<?= BASE_URL ?>/index.php" class="logo-link">
                    <h1>CMS Blog Personal</h1>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="<?= BASE_URL ?>/index.php" class="nav-link">Inicio</a></li>
                    <?php
                    use App\Models\User;
                    $userModel = new User();
                    if ($userModel->isLoggedIn()):
                        $currentUser = $userModel->getCurrentUser();
                    ?>
                        <li class="user-menu">
                            <div class="user-dropdown">
                                <?php if (isset($currentUser['avatar']) && $currentUser['avatar']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/users/<?= htmlspecialchars($currentUser['avatar']) ?>" 
                                         alt="<?= htmlspecialchars($currentUser['username']) ?>"
                                         class="user-avatar-header">
                                <?php else: ?>
                                    <span class="user-icon-placeholder">
                                        <?= strtoupper(substr($currentUser['username'], 0, 1)) ?>
                                    </span>
                                <?php endif; ?>
                                <span><?= htmlspecialchars($currentUser['username']) ?></span>
                            </div>
                        </li>
                        <li><a href="<?= BASE_URL ?>/my-posts.php" class="nav-link">Mis Publicaciones</a></li>
                        <li><a href="<?= BASE_URL ?>/create-post.php" class="nav-link">Escribir</a></li>
                        <li><a href="<?= BASE_URL ?>/profile.php" class="nav-link">Mi Perfil</a></li>
                        <?php if ($userModel->hasRole('admin') || $userModel->hasRole('author')): ?>
                            <li><a href="<?= BASE_URL ?>/admin/" class="nav-link">Panel Admin</a></li>
                        <?php endif; ?>
                        <li><a href="<?= BASE_URL ?>/logout.php" class="nav-link btn-login">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/register.php" class="nav-link">Registrarse</a></li>
                        <li><a href="<?= BASE_URL ?>/login.php" class="nav-link btn-login">Iniciar Sesión</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
