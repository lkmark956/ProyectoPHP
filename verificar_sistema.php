<?php
/**
 * Script de verificación del sistema
 * Ejecutar SOLO DURANTE EL DESARROLLO para verificar configuración
 * ELIMINAR EN PRODUCCIÓN por seguridad
 */

// Cargar configuración
require_once __DIR__ . '/config/config.php';

use App\Database;
use App\User;
use App\Post;
use App\Category;
use App\ImageUpload;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - CMS Blog</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f2f5; padding: 2rem; }
        .container { max-width: 1000px; margin: 0 auto; }
        h1 { color: #1e293b; margin-bottom: 2rem; text-align: center; }
        .section { background: white; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .section h2 { color: #4f46e5; margin-bottom: 1rem; font-size: 1.3rem; border-bottom: 2px solid #4f46e5; padding-bottom: 0.5rem; }
        .check-item { padding: 0.8rem; margin: 0.5rem 0; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; }
        .check-item.success { background: #d1fae5; border-left: 4px solid #10b981; }
        .check-item.error { background: #fee2e2; border-left: 4px solid #ef4444; }
        .check-item.warning { background: #fef3c7; border-left: 4px solid #f59e0b; }
        .status { font-weight: bold; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.9rem; }
        .status.ok { background: #10b981; color: white; }
        .status.fail { background: #ef4444; color: white; }
        .status.warn { background: #f59e0b; color: white; }
        .info { color: #64748b; font-size: 0.9rem; margin-top: 0.5rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        table th, table td { padding: 0.8rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        table th { background: #f8fafc; color: #1e293b; font-weight: 600; }
        .btn { display: inline-block; padding: 0.8rem 1.5rem; background: #4f46e5; color: white; text-decoration: none; border-radius: 6px; margin-top: 1rem; }
        .btn:hover { background: #4338ca; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación del Sistema CMS Blog</h1>
        
        <!-- Configuración PHP -->
        <div class="section">
            <h2>⚙️ Configuración PHP</h2>
            <?php
            $phpVersion = phpversion();
            $phpOk = version_compare($phpVersion, '7.4.0', '>=');
            ?>
            <div class="check-item <?= $phpOk ? 'success' : 'error' ?>">
                <span>Versión de PHP: <?= $phpVersion ?></span>
                <span class="status <?= $phpOk ? 'ok' : 'fail' ?>"><?= $phpOk ? '✓ OK' : '✗ Requiere 7.4+' ?></span>
            </div>
            
            <?php
            $extensions = ['pdo', 'pdo_mysql', 'mbstring', 'gd', 'fileinfo'];
            foreach ($extensions as $ext):
                $loaded = extension_loaded($ext);
            ?>
            <div class="check-item <?= $loaded ? 'success' : 'error' ?>">
                <span>Extensión: <?= $ext ?></span>
                <span class="status <?= $loaded ? 'ok' : 'fail' ?>"><?= $loaded ? '✓ Cargada' : '✗ No disponible' ?></span>
            </div>
            <?php endforeach; ?>
            
            <?php
            $uploadMax = ini_get('upload_max_filesize');
            $postMax = ini_get('post_max_size');
            ?>
            <div class="check-item success">
                <span>Límite de subida: <?= $uploadMax ?> | Post max: <?= $postMax ?></span>
                <span class="status ok">✓ OK</span>
            </div>
        </div>
        
        <!-- Conexión a Base de Datos -->
        <div class="section">
            <h2>💾 Conexión a Base de Datos</h2>
            <?php
            try {
                $db = Database::getInstance()->getConnection();
                $dbConnected = true;
                
                // Verificar tablas
                $tables = ['users', 'posts', 'categories'];
                $tablesExist = [];
                
                foreach ($tables as $table) {
                    $stmt = $db->query("SHOW TABLES LIKE '$table'");
                    $tablesExist[$table] = $stmt->rowCount() > 0;
                }
                
                // Contar registros
                $counts = [];
                foreach ($tables as $table) {
                    if ($tablesExist[$table]) {
                        $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $counts[$table] = $result['count'];
                    }
                }
                
            } catch (Exception $e) {
                $dbConnected = false;
                $dbError = $e->getMessage();
            }
            ?>
            
            <div class="check-item <?= $dbConnected ? 'success' : 'error' ?>">
                <span>Conexión MySQL</span>
                <span class="status <?= $dbConnected ? 'ok' : 'fail' ?>"><?= $dbConnected ? '✓ Conectado' : '✗ Error' ?></span>
            </div>
            
            <?php if ($dbConnected): ?>
                <?php foreach ($tables as $table): ?>
                <div class="check-item <?= $tablesExist[$table] ? 'success' : 'error' ?>">
                    <span>Tabla: <?= $table ?> <?= $tablesExist[$table] ? "({$counts[$table]} registros)" : '' ?></span>
                    <span class="status <?= $tablesExist[$table] ? 'ok' : 'fail' ?>"><?= $tablesExist[$table] ? '✓ Existe' : '✗ No existe' ?></span>
                </div>
                <?php endforeach; ?>
                
                <?php
                // Verificar columnas de imágenes
                $stmt = $db->query("DESCRIBE users");
                $userColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $hasAvatar = in_array('avatar', $userColumns);
                
                $stmt = $db->query("DESCRIBE posts");
                $postColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $hasImage = in_array('image', $postColumns);
                ?>
                
                <div class="check-item <?= $hasAvatar ? 'success' : 'warning' ?>">
                    <span>Columna 'avatar' en tabla users</span>
                    <span class="status <?= $hasAvatar ? 'ok' : 'warn' ?>"><?= $hasAvatar ? '✓ Existe' : '⚠ Falta' ?></span>
                </div>
                
                <div class="check-item <?= $hasImage ? 'success' : 'warning' ?>">
                    <span>Columna 'image' en tabla posts</span>
                    <span class="status <?= $hasImage ? 'ok' : 'warn' ?>"><?= $hasImage ? '✓ Existe' : '⚠ Falta' ?></span>
                </div>
                
                <?php if (!$hasAvatar || !$hasImage): ?>
                <div class="info">
                    ⚠️ Ejecuta el archivo <strong>update_database.sql</strong> para agregar las columnas de imágenes.
                </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="info">
                    ❌ Error de conexión: <?= htmlspecialchars($dbError) ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Archivos y Clases -->
        <div class="section">
            <h2>📁 Archivos y Clases</h2>
            <?php
            $classes = [
                'Database' => ROOT_PATH . '/src/Database.php',
                'User' => ROOT_PATH . '/src/User.php',
                'Post' => ROOT_PATH . '/src/Post.php',
                'Category' => ROOT_PATH . '/src/Category.php',
                'ImageUpload' => ROOT_PATH . '/src/ImageUpload.php'
            ];
            
            foreach ($classes as $className => $file):
                $exists = file_exists($file);
            ?>
            <div class="check-item <?= $exists ? 'success' : 'error' ?>">
                <span>Clase: <?= $className ?></span>
                <span class="status <?= $exists ? 'ok' : 'fail' ?>"><?= $exists ? '✓ Existe' : '✗ No existe' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Directorios y Permisos -->
        <div class="section">
            <h2>📂 Directorios y Permisos</h2>
            <?php
            $directories = [
                'Uploads' => PUBLIC_PATH . '/uploads',
                'Uploads/Users' => PUBLIC_PATH . '/uploads/users',
                'Uploads/Posts' => PUBLIC_PATH . '/uploads/posts',
                'Views' => VIEWS_PATH,
                'CSS' => PUBLIC_PATH . '/css'
            ];
            
            foreach ($directories as $name => $dir):
                $exists = is_dir($dir);
                $writable = $exists ? is_writable($dir) : false;
            ?>
            <div class="check-item <?= ($exists && $writable) ? 'success' : ($exists ? 'warning' : 'error') ?>">
                <span><?= $name ?>: <?= $dir ?></span>
                <span class="status <?= ($exists && $writable) ? 'ok' : ($exists ? 'warn' : 'fail') ?>">
                    <?= $exists ? ($writable ? '✓ OK' : '⚠ Solo lectura') : '✗ No existe' ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Archivos de Configuración -->
        <div class="section">
            <h2>🔧 Configuración del Sistema</h2>
            <table>
                <tr>
                    <th>Constante</th>
                    <th>Valor</th>
                </tr>
                <tr><td>DB_HOST</td><td><?= DB_HOST ?></td></tr>
                <tr><td>DB_NAME</td><td><?= DB_NAME ?></td></tr>
                <tr><td>DB_USER</td><td><?= DB_USER ?></td></tr>
                <tr><td>SITE_NAME</td><td><?= SITE_NAME ?></td></tr>
                <tr><td>POSTS_PER_PAGE</td><td><?= POSTS_PER_PAGE ?></td></tr>
                <tr><td>ROOT_PATH</td><td><?= ROOT_PATH ?></td></tr>
                <tr><td>PUBLIC_PATH</td><td><?= PUBLIC_PATH ?></td></tr>
            </table>
        </div>
        
        <!-- URLs del Sistema -->
        <div class="section">
            <h2>🌐 Enlaces del Sistema</h2>
            <div class="check-item success">
                <span>Página Principal</span>
                <a href="public/index.php" class="status ok" target="_blank">Abrir →</a>
            </div>
            <div class="check-item success">
                <span>Login</span>
                <a href="public/login.php" class="status ok" target="_blank">Abrir →</a>
            </div>
            <div class="check-item success">
                <span>Registro</span>
                <a href="public/register.php" class="status ok" target="_blank">Abrir →</a>
            </div>
            <div class="check-item success">
                <span>Panel Admin</span>
                <a href="public/admin/" class="status ok" target="_blank">Abrir →</a>
            </div>
        </div>
        
        <!-- Resumen Final -->
        <div class="section">
            <h2>✅ Resumen</h2>
            <?php
            $allOk = $phpOk && $dbConnected && 
                     array_reduce($tablesExist, function($carry, $item) { return $carry && $item; }, true) &&
                     array_reduce($classes, function($carry, $file) { return $carry && file_exists($file); }, true);
            ?>
            <?php if ($allOk): ?>
                <div class="check-item success">
                    <span><strong>🎉 Sistema listo para usar</strong></span>
                    <span class="status ok">✓ TODO OK</span>
                </div>
                <div class="info">
                    El sistema está correctamente configurado. Puedes empezar a usar el CMS.
                </div>
                <a href="public/index.php" class="btn">Ir al Blog →</a>
            <?php else: ?>
                <div class="check-item error">
                    <span><strong>⚠️ Requiere atención</strong></span>
                    <span class="status fail">Errores detectados</span>
                </div>
                <div class="info">
                    Revisa los errores marcados arriba y corrígelos antes de continuar.
                </div>
            <?php endif; ?>
            
            <div class="info" style="margin-top: 1.5rem; padding: 1rem; background: #fef3c7; border-radius: 6px;">
                <strong>⚠️ IMPORTANTE:</strong> Este archivo es solo para desarrollo. 
                <strong>ELIMÍNALO</strong> antes de subir el proyecto a producción por razones de seguridad.
            </div>
        </div>
    </div>
</body>
</html>
