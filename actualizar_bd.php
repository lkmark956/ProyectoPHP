<?php
/**
 * Script para actualizar la base de datos
 * Agrega las columnas avatar e image
 */

require_once __DIR__ . '/config/config.php';

use App\Database;

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Actualizar Base de Datos</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #4f46e5; margin-bottom: 20px; }
        .success { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .error { background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .info { background: #dbeafe; border-left: 4px solid #3b82f6; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .btn { display: inline-block; padding: 10px 20px; background: #4f46e5; color: white; text-decoration: none; border-radius: 6px; margin-top: 20px; }
        .btn:hover { background: #4338ca; }
        pre { background: #1e293b; color: #f1f5f9; padding: 15px; border-radius: 6px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🔄 Actualización de Base de Datos</h1>";

try {
    $db = Database::getInstance()->getConnection();
    echo "<div class='success'>✓ Conexión a la base de datos exitosa</div>";
    
    // Verificar si ya existen las columnas
    $stmt = $db->query("DESCRIBE users");
    $userColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $hasAvatar = in_array('avatar', $userColumns);
    
    $stmt = $db->query("DESCRIBE posts");
    $postColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $hasImage = in_array('image', $postColumns);
    
    echo "<div class='info'><strong>Estado actual:</strong><br>";
    echo "- Columna 'avatar' en users: " . ($hasAvatar ? "✓ Ya existe" : "✗ No existe") . "<br>";
    echo "- Columna 'image' en posts: " . ($hasImage ? "✓ Ya existe" : "✗ No existe") . "</div>";
    
    $updated = false;
    
    // Agregar columna avatar si no existe
    if (!$hasAvatar) {
        try {
            $db->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER full_name");
            echo "<div class='success'>✓ Columna 'avatar' agregada a la tabla users</div>";
            $updated = true;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "<div class='info'>ℹ Columna 'avatar' ya existe en users</div>";
            } else {
                throw $e;
            }
        }
    } else {
        echo "<div class='info'>ℹ Columna 'avatar' ya existe en users (sin cambios)</div>";
    }
    
    // Agregar columna image si no existe
    if (!$hasImage) {
        try {
            $db->exec("ALTER TABLE posts ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER content");
            echo "<div class='success'>✓ Columna 'image' agregada a la tabla posts</div>";
            $updated = true;
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "<div class='info'>ℹ Columna 'image' ya existe en posts</div>";
            } else {
                throw $e;
            }
        }
    } else {
        echo "<div class='info'>ℹ Columna 'image' ya existe en posts (sin cambios)</div>";
    }
    
    if ($updated) {
        echo "<div class='success'><strong>🎉 ¡Base de datos actualizada correctamente!</strong></div>";
    } else {
        echo "<div class='info'><strong>ℹ Base de datos ya estaba actualizada</strong></div>";
    }
    
    // Mostrar estructura final
    echo "<h3>📋 Estructura final de las tablas:</h3>";
    
    echo "<h4>Tabla: users</h4>";
    echo "<pre>";
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo sprintf("%-20s %-20s %s\n", $col['Field'], $col['Type'], $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL');
    }
    echo "</pre>";
    
    echo "<h4>Tabla: posts</h4>";
    echo "<pre>";
    $stmt = $db->query("DESCRIBE posts");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo sprintf("%-20s %-20s %s\n", $col['Field'], $col['Type'], $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL');
    }
    echo "</pre>";
    
    echo "<a href='verificar_sistema.php' class='btn'>Verificar Sistema Completo</a>";
    echo "<a href='public/index.php' class='btn'>Ir al Blog</a>";
    
} catch (Exception $e) {
    echo "<div class='error'><strong>❌ Error:</strong><br>" . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='info'>Verifica que:<br>
    1. MySQL esté corriendo<br>
    2. Las credenciales en config/config.php sean correctas<br>
    3. La base de datos 'cms_blog' exista</div>";
}

echo "</div></body></html>";
