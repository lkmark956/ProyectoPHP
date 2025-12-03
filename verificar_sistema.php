<?php
/**
 * Script de Verificación del Sistema
 * Comprueba que todos los componentes estén correctamente configurados
 */

// Configurar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar configuración
require_once 'config/config.php';

use App\Models\Database;
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use App\Models\Comment;

$checks = [];
$errors = [];
$warnings = [];

// =====================================================
// FUNCIONES AUXILIARES
// =====================================================

function checkPass($description) {
    global $checks;
    $checks[] = ['status' => 'pass', 'message' => $description];
}

function checkFail($description, $details = '') {
    global $checks, $errors;
    $checks[] = ['status' => 'fail', 'message' => $description, 'details' => $details];
    $errors[] = $description;
}

function checkWarning($description, $details = '') {
    global $checks, $warnings;
    $checks[] = ['status' => 'warning', 'message' => $description, 'details' => $details];
    $warnings[] = $description;
}

// =====================================================
// VERIFICACIONES
// =====================================================

// 1. Verificar configuración
if (defined('BASE_URL') && defined('DB_HOST') && defined('DB_NAME')) {
    checkPass('✅ Archivo de configuración cargado correctamente');
} else {
    checkFail('❌ Error en archivo de configuración', 'Faltan constantes importantes');
}

// 2. Verificar conexión a base de datos
try {
    $db = Database::getInstance()->getConnection();
    checkPass('✅ Conexión a base de datos exitosa');
} catch (Exception $e) {
    checkFail('❌ Error de conexión a base de datos', $e->getMessage());
}

// 3. Verificar tablas existentes
if (isset($db)) {
    $requiredTables = ['users', 'posts', 'categories', 'comments'];
    $stmt = $db->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($requiredTables as $table) {
        if (in_array($table, $existingTables)) {
            checkPass("✅ Tabla '$table' existe");
            
            // Contar registros
            $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            if ($table === 'comments' && $count === 0) {
                checkWarning("⚠️ Tabla '$table' está vacía (normal si es nueva)", "0 registros");
            } else {
                checkPass("   └─ $count registros en '$table'");
            }
        } else {
            checkFail("❌ Tabla '$table' NO existe", "Ejecuta cms_blog_COMPLETO.sql");
        }
    }
}

// 4. Verificar modelos
$models = [
    'User' => User::class,
    'Post' => Post::class,
    'Category' => Category::class,
    'Comment' => Comment::class
];

foreach ($models as $name => $class) {
    if (class_exists($class)) {
        checkPass("✅ Modelo $name disponible");
    } else {
        checkFail("❌ Modelo $name NO encontrado", "Verifica app/Models/$name.php");
    }
}

// 5. Verificar directorios y permisos
$directories = [
    'public/uploads/users' => 'Avatares de usuarios',
    'public/uploads/posts' => 'Imágenes de posts',
    'public/css' => 'Archivos CSS',
    'app/Views' => 'Vistas',
    'app/Models' => 'Modelos'
];

foreach ($directories as $dir => $description) {
    $fullPath = ROOT_PATH . '/' . $dir;
    if (is_dir($fullPath)) {
        if (is_writable($fullPath)) {
            checkPass("✅ Directorio '$description' existe y es escribible");
        } else {
            checkWarning("⚠️ Directorio '$description' existe pero NO es escribible", $fullPath);
        }
    } else {
        checkFail("❌ Directorio '$description' NO existe", $fullPath);
    }
}

// 6. Verificar archivos CSS críticos
$cssFiles = [
    'style-clean.css' => 'Estilos principales',
    'animations.css' => 'Animaciones',
    'colors-professional.css' => 'Colores profesionales',
    'auth.css' => 'Login y registro',
    'comments.css' => 'Sistema de comentarios',
    'admin.css' => 'Panel admin',
    'admin-professional.css' => 'Estilos admin elegantes'
];

foreach ($cssFiles as $file => $description) {
    $fullPath = PUBLIC_PATH . '/css/' . $file;
    if (file_exists($fullPath)) {
        checkPass("✅ CSS '$description' disponible");
    } else {
        checkWarning("⚠️ CSS '$description' NO encontrado", $file);
    }
}

// 7. Verificar páginas principales
$pages = [
    'index.php' => 'Página principal',
    'login.php' => 'Inicio de sesión',
    'register.php' => 'Registro',
    'post.php' => 'Vista de post',
    'profile.php' => 'Perfil de usuario',
    'admin/index.php' => 'Dashboard admin'
];

foreach ($pages as $file => $description) {
    $fullPath = PUBLIC_PATH . '/' . $file;
    if (file_exists($fullPath)) {
        checkPass("✅ Página '$description' existe");
    } else {
        checkFail("❌ Página '$description' NO existe", $file);
    }
}

// 8. Verificar archivos de comentarios
$commentFiles = [
    'comment_create.php' => 'Crear comentario',
    'comment_edit.php' => 'Editar comentario',
    'comment_delete.php' => 'Eliminar comentario'
];

foreach ($commentFiles as $file => $description) {
    $fullPath = PUBLIC_PATH . '/' . $file;
    if (file_exists($fullPath)) {
        checkPass("✅ Sistema de comentarios: '$description'");
    } else {
        checkFail("❌ Archivo de comentarios '$description' NO existe", $file);
    }
}

// 9. Verificar helpers.php
if (file_exists(ROOT_PATH . '/app/helpers.php')) {
    checkPass("✅ Archivo helpers.php existe");
    if (function_exists('getCategoryEmoji')) {
        checkPass("   └─ Función getCategoryEmoji() disponible");
    } else {
        checkWarning("⚠️ Función getCategoryEmoji() NO encontrada");
    }
} else {
    checkFail("❌ Archivo helpers.php NO existe");
}

// 10. Verificar estructura de admin
$adminPages = [
    'categories/index.php' => 'Gestión de categorías',
    'posts/index.php' => 'Gestión de posts',
    'users/index.php' => 'Gestión de usuarios',
    'users/view.php' => 'Vista de usuario'
];

foreach ($adminPages as $file => $description) {
    $fullPath = PUBLIC_PATH . '/admin/' . $file;
    if (file_exists($fullPath)) {
        checkPass("✅ Admin: '$description'");
    } else {
        checkWarning("⚠️ Página admin '$description' NO encontrada", $file);
    }
}

// =====================================================
// GENERAR REPORTE HTML
// =====================================================

$totalChecks = count($checks);
$passCount = count(array_filter($checks, fn($c) => $c['status'] === 'pass'));
$failCount = count($errors);
$warningCount = count($warnings);
$successRate = round(($passCount / $totalChecks) * 100, 1);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - CMS Blog</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eff2f5 100%);
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1a2332 0%, #2d3e50 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            margin: 10px 0;
        }
        .stat-label {
            color: #5a6c7d;
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .success { color: #10b981; }
        .warning { color: #f59e0b; }
        .error { color: #ef4444; }
        .checks {
            padding: 30px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .check-item.pass {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
            border-color: #10b981;
        }
        .check-item.fail {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
            border-color: #ef4444;
        }
        .check-item.warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.05) 100%);
            border-color: #f59e0b;
        }
        .details {
            margin-top: 5px;
            font-size: 0.9rem;
            color: #5a6c7d;
            font-family: 'Courier New', monospace;
        }
        .actions {
            padding: 30px;
            background: #f8f9fa;
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 5px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #1a2332 0%, #2d3e50 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26, 35, 50, 0.3);
        }
        .progress-bar {
            height: 8px;
            background: #e1e4e8;
            border-radius: 4px;
            overflow: hidden;
            margin: 20px 30px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
            transition: width 1s ease;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Verificación del Sistema</h1>
            <p>CMS Blog Personal - Estado del Proyecto</p>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number success"><?= $passCount ?></div>
                <div class="stat-label">Verificaciones Exitosas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number warning"><?= $warningCount ?></div>
                <div class="stat-label">Advertencias</div>
            </div>
            <div class="stat-card">
                <div class="stat-number error"><?= $failCount ?></div>
                <div class="stat-label">Errores Críticos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="background: linear-gradient(135deg, #1a2332 0%, #8b7355 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?= $successRate ?>%</div>
                <div class="stat-label">Tasa de Éxito</div>
            </div>
        </div>

        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= $successRate ?>%;"></div>
        </div>

        <div class="checks">
            <h2 style="margin-bottom: 20px; color: #1a2332;">📋 Resultados de Verificación</h2>
            <?php foreach ($checks as $check): ?>
                <div class="check-item <?= $check['status'] ?>">
                    <?= htmlspecialchars($check['message']) ?>
                    <?php if (isset($check['details']) && $check['details']): ?>
                        <div class="details">└─ <?= htmlspecialchars($check['details']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($failCount > 0): ?>
            <div style="padding: 30px; background: #fee; border-top: 2px solid #ef4444;">
                <h3 style="color: #991b1b; margin-bottom: 15px;">⚠️ Acción Requerida</h3>
                <p style="color: #991b1b;">Se encontraron <strong><?= $failCount ?></strong> errores críticos que deben ser corregidos:</p>
                <ul style="margin: 15px 0; padding-left: 30px; color: #991b1b;">
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php elseif ($warningCount > 0): ?>
            <div style="padding: 30px; background: #fef3c7; border-top: 2px solid #f59e0b;">
                <h3 style="color: #92400e; margin-bottom: 15px;">⚠️ Advertencias</h3>
                <p style="color: #92400e;">El sistema funciona pero hay <strong><?= $warningCount ?></strong> advertencias:</p>
                <ul style="margin: 15px 0; padding-left: 30px; color: #92400e;">
                    <?php foreach ($warnings as $warning): ?>
                        <li><?= htmlspecialchars($warning) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div style="padding: 30px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%); border-top: 2px solid #10b981;">
                <h3 style="color: #065f46; margin-bottom: 15px;">🎉 ¡Sistema Completamente Funcional!</h3>
                <p style="color: #065f46;">Todas las verificaciones pasaron exitosamente. El proyecto está listo para usarse.</p>
            </div>
        <?php endif; ?>

        <div class="actions">
            <a href="public/index.php" class="btn btn-primary">🏠 Ir al Sitio</a>
            <a href="public/admin/" class="btn btn-primary">⚙️ Panel Admin</a>
            <a href="setup_comments.php" class="btn btn-primary">💬 Configurar Comentarios</a>
        </div>
    </div>
</body>
</html>
