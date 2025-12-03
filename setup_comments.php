<?php
/**
 * Script para configurar la tabla de comentarios
 * Ejecuta este archivo una sola vez para crear la tabla
 */

require_once 'config/config.php';

use App\Models\Database;

try {
    $db = Database::getInstance()->getConnection();
    
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Configurar Comentarios - CMS Blog</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: linear-gradient(135deg, #f5f7fa 0%, #eff2f5 100%);
            }
            .container {
                background: white;
                border-radius: 16px;
                padding: 40px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            }
            h1 {
                color: #1a2332;
                border-bottom: 3px solid #8b7355;
                padding-bottom: 15px;
                margin-top: 0;
            }
            .success {
                background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
                border-left: 4px solid #10b981;
                color: #065f46;
                padding: 15px 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
            .error {
                background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
                border-left: 4px solid #ef4444;
                color: #991b1b;
                padding: 15px 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
            .code {
                background: #f8f9fa;
                padding: 15px;
                border-radius: 8px;
                font-family: 'Courier New', monospace;
                margin: 20px 0;
                border: 1px solid #e1e4e8;
            }
            .btn {
                background: linear-gradient(135deg, #1a2332 0%, #2d3e50 100%);
                color: white;
                padding: 12px 30px;
                border-radius: 8px;
                text-decoration: none;
                display: inline-block;
                margin-top: 20px;
                font-weight: 600;
            }
            .btn:hover {
                opacity: 0.9;
            }
            .step {
                margin: 15px 0;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>⚙️ Configuración de Sistema de Comentarios</h1>";
    
    // Verificar si la tabla ya existe
    $stmt = $db->query("SHOW TABLES LIKE 'comments'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<div class='success'>
                ✅ La tabla 'comments' ya existe en la base de datos.
              </div>";
        
        // Contar comentarios existentes
        $count = $db->query("SELECT COUNT(*) as total FROM comments")->fetch();
        echo "<div class='step'>
                📊 Comentarios actuales en la base de datos: <strong>{$count['total']}</strong>
              </div>";
    } else {
        // Crear la tabla
        $sql = "CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            post_id INT NOT NULL,
            user_id INT NOT NULL,
            content TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX idx_post_id (post_id),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db->exec($sql);
        
        echo "<div class='success'>
                ✅ Tabla 'comments' creada exitosamente!
              </div>";
    }
    
    // Verificar estructura
    echo "<h2>📋 Estructura de la Tabla</h2>";
    $columns = $db->query("DESCRIBE comments")->fetchAll();
    echo "<div class='code'>";
    echo "<table style='width:100%; border-collapse: collapse;'>";
    echo "<tr style='background:#e1e4e8;'><th style='padding:8px; text-align:left;'>Campo</th><th style='padding:8px; text-align:left;'>Tipo</th><th style='padding:8px; text-align:left;'>Null</th><th style='padding:8px; text-align:left;'>Key</th></tr>";
    foreach ($columns as $col) {
        echo "<tr style='border-bottom:1px solid #e1e4e8;'>
                <td style='padding:8px;'>{$col['Field']}</td>
                <td style='padding:8px;'>{$col['Type']}</td>
                <td style='padding:8px;'>{$col['Null']}</td>
                <td style='padding:8px;'>{$col['Key']}</td>
              </tr>";
    }
    echo "</table></div>";
    
    echo "<div class='success'>
            <h3>✨ ¡Sistema de comentarios listo!</h3>
            <p><strong>Funcionalidades disponibles:</strong></p>
            <ul>
                <li>✅ Crear comentarios (usuarios logueados)</li>
                <li>✅ Editar comentarios (autor o admin)</li>
                <li>✅ Eliminar comentarios (autor, creador del post, o admin)</li>
                <li>✅ Vista de comentarios con avatares y badges</li>
                <li>✅ Timestamps relativos (hace X horas)</li>
                <li>✅ Indicador de edición</li>
            </ul>
          </div>";
    
    echo "<a href='public/index.php' class='btn'>🏠 Ir al Inicio</a>";
    echo "<a href='public/admin/' class='btn' style='margin-left:10px;'>⚙️ Panel Admin</a>";
    
    echo "</div></body></html>";
    
} catch (Exception $e) {
    echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Error - Configuración</title>
        <style>
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: linear-gradient(135deg, #f5f7fa 0%, #eff2f5 100%);
            }
            .container {
                background: white;
                border-radius: 16px;
                padding: 40px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            }
            .error {
                background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
                border-left: 4px solid #ef4444;
                color: #991b1b;
                padding: 15px 20px;
                border-radius: 8px;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>❌ Error de Configuración</h1>
            <div class='error'>
                <strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "
            </div>
            <p>Por favor verifica:</p>
            <ul>
                <li>La conexión a la base de datos en config/config.php</li>
                <li>Que existan las tablas: users, posts, categories</li>
                <li>Los permisos de usuario de MySQL</li>
            </ul>
        </div>
    </body>
    </html>";
}
