<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : SITE_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="index.php">
                    <h1><?= SITE_NAME ?></h1>
                </a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="nav-link">Inicio</a></li>
                    <?php
                    use App\User;
                    $userModel = new User();
                    if ($userModel->isLoggedIn()):
                        $currentUser = $userModel->getCurrentUser();
                    ?>
                        <li class="user-menu">
                            <div class="user-dropdown">
                                <?php if (isset($currentUser['avatar']) && $currentUser['avatar']): ?>
                                    <img src="uploads/users/<?= htmlspecialchars($currentUser['avatar']) ?>" 
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
                        <li><a href="profile.php" class="nav-link">Mi Perfil</a></li>
                        <?php if ($userModel->hasRole('admin') || $userModel->hasRole('author')): ?>
                            <li><a href="admin/" class="nav-link">Panel Admin</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php" class="nav-link btn-login">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="register.php" class="nav-link">Registrarse</a></li>
                        <li><a href="login.php" class="nav-link btn-login">Iniciar Sesión</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
