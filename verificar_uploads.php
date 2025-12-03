<?php
/**
 * Script para verificar y arreglar permisos de directorios de uploads
 */

require_once 'config/config.php';

$directories = [
    PUBLIC_PATH . '/uploads',
    PUBLIC_PATH . '/uploads/users',
    PUBLIC_PATH . '/uploads/posts'
];

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificar Uploads</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 900px;
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
        }
        .check {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        .success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(5, 150, 105, 0.05) 100%);
            border-color: #10b981;
            color: #065f46;
        }
        .error {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.05) 100%);
            border-color: #ef4444;
            color: #991b1b;
        }
        .warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.05) 100%);
            border-color: #f59e0b;
            color: #92400e;
        }
        .code {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            margin: 5px 0;
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
    </style>
</head>
<body>
    <div class='container'>
        <h1>🗂️ Verificación de Directorios de Uploads</h1>";

$allOk = true;

foreach ($directories as $dir) {
    $dirName = str_replace(PUBLIC_PATH . '/', '', $dir);
    
    // Verificar si existe
    if (!is_dir($dir)) {
        echo "<div class='check error'>
                ❌ Directorio <code>$dirName</code> NO existe<br>
                <div class='code'>Intentando crear...</div>";
        
        if (mkdir($dir, 0777, true)) {
            chmod($dir, 0777);
            echo "<div class='code'>✓ Directorio creado exitosamente</div>";
        } else {
            echo "<div class='code'>✗ Error al crear directorio</div>";
            $allOk = false;
        }
        echo "</div>";
    } else {
        // Verificar si es escribible
        if (is_writable($dir)) {
            $perms = substr(sprintf('%o', fileperms($dir)), -4);
            echo "<div class='check success'>
                    ✅ Directorio <code>$dirName</code> existe y es escribible
                    <div class='code'>Permisos: $perms</div>
                  </div>";
        } else {
            echo "<div class='check warning'>
                    ⚠️ Directorio <code>$dirName</code> existe pero NO es escribible<br>
                    <div class='code'>Intentando cambiar permisos...</div>";
            
            if (chmod($dir, 0777)) {
                echo "<div class='code'>✓ Permisos cambiados a 0777</div>";
            } else {
                echo "<div class='code'>✗ No se pudieron cambiar los permisos. Hazlo manualmente.</div>";
                $allOk = false;
            }
            echo "</div>";
        }
        
        // Listar archivos
        $files = glob($dir . '/*');
        $fileCount = count(array_filter($files, 'is_file'));
        echo "<div class='check' style='background:#f8f9fa; border-color:#e1e4e8; color:#5a6c7d;'>
                📁 $fileCount archivos en <code>$dirName</code>
              </div>";
    }
}

// Verificar configuración de PHP
echo "<h2 style='margin-top: 40px; color: #1a2332;'>⚙️ Configuración de PHP</h2>";

$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');
$memory = ini_get('memory_limit');

echo "<div class='check' style='background:#f8f9fa; border-color:#e1e4e8; color:#5a6c7d;'>
        <strong>upload_max_filesize:</strong> $upload_max<br>
        <strong>post_max_size:</strong> $post_max<br>
        <strong>memory_limit:</strong> $memory
      </div>";

// Verificar extensiones GD
if (extension_loaded('gd')) {
    echo "<div class='check success'>
            ✅ Extensión GD habilitada (necesaria para procesar imágenes)
          </div>";
} else {
    echo "<div class='check error'>
            ❌ Extensión GD NO habilitada<br>
            <div class='code'>Necesitas habilitar GD en php.ini para que funcionen las imágenes</div>
          </div>";
    $allOk = false;
}

// Resultado final
if ($allOk) {
    echo "<div class='check success' style='margin-top: 30px; font-size: 1.1rem;'>
            <strong>🎉 ¡Todo listo!</strong><br>
            Los directorios están configurados correctamente. Ya puedes subir imágenes.
          </div>";
} else {
    echo "<div class='check error' style='margin-top: 30px; font-size: 1.1rem;'>
            <strong>⚠️ Requiere atención</strong><br>
            Algunos directorios necesitan configuración manual. Contacta al administrador del servidor.
          </div>";
}

echo "<div style='text-align: center;'>
        <a href='public/index.php' class='btn'>🏠 Ir al Sitio</a>
        <a href='public/profile.php' class='btn' style='margin-left:10px;'>👤 Probar Subida de Avatar</a>
      </div>";

echo "</div></body></html>";
