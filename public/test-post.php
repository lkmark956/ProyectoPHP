<?php
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Post View</title>
    <link rel="stylesheet" href="css/style-clean.css">
</head>
<body>
    <div style="max-width: 1200px; margin: 40px auto; padding: 20px;">
        <h1 style="color: #2c3e50; margin-bottom: 20px;">🧪 Test de Vista de Post</h1>
        
        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
            <h3>Información de Configuración:</h3>
            <p><strong>BASE_URL:</strong> <?= BASE_URL ?></p>
            <p><strong>Archivo actual:</strong> <?= __FILE__ ?></p>
            <p><strong>URL esperada de post.php:</strong> <?= BASE_URL ?>/post.php?id=1</p>
            <p><strong>Ruta relativa desde aquí:</strong> post.php?id=1</p>
        </div>

        <div style="background: white; border: 2px solid #e0e0e0; border-radius: 12px; overflow: hidden;">
            <div style="padding: 30px;">
                <h2 style="color: #2c3e50; margin-bottom: 15px;">Ejemplo de Post Card</h2>
                
                <article class="post-card" style="max-width: 400px; margin: 20px auto;">
                    <img src="https://picsum.photos/400/250?random=1" 
                         alt="Test Image"
                         class="post-thumbnail">
                    
                    <div class="post-content">
                        <div class="post-header">
                            <h2 class="post-title">
                                <a href="post.php?id=1">Título de Ejemplo</a>
                            </h2>
                            <div class="post-meta">
                                <span class="post-category">Tecnología</span>
                                <span class="post-date">03/12/2025</span>
                            </div>
                        </div>
                        <div class="post-description">
                            <p>Esta es una descripción de ejemplo para probar el enlace.</p>
                        </div>
                        <div class="post-footer">
                            <div class="post-author">
                                <span class="author-avatar-placeholder-small">A</span>
                                <span class="author-name">Autor Test</span>
                            </div>
                            <a href="post.php?id=1" class="btn-read-more">
                                Leer más →
                            </a>
                        </div>
                    </div>
                </article>

                <div style="margin-top: 30px; padding: 20px; background: #e8f5e9; border-radius: 8px;">
                    <h3 style="color: #2e7d32;">✅ Enlaces de prueba:</h3>
                    <ul style="list-style: none; padding: 0;">
                        <li style="margin: 10px 0;">
                            <a href="post.php?id=1" style="color: #1976d2; font-weight: 600;">
                                → Ruta relativa: post.php?id=1
                            </a>
                        </li>
                        <li style="margin: 10px 0;">
                            <a href="<?= BASE_URL ?>/post.php?id=1" style="color: #1976d2; font-weight: 600;">
                                → Con BASE_URL: <?= BASE_URL ?>/post.php?id=1
                            </a>
                        </li>
                        <li style="margin: 10px 0;">
                            <a href="./post.php?id=1" style="color: #1976d2; font-weight: 600;">
                                → Con ./: ./post.php?id=1
                            </a>
                        </li>
                    </ul>
                </div>

                <div style="margin-top: 20px; padding: 20px; background: #fff3e0; border-radius: 8px;">
                    <h3 style="color: #e65100;">📁 Verifica que existen estos archivos:</h3>
                    <ul style="font-family: monospace; font-size: 14px;">
                        <li style="margin: 8px 0;">✓ public/post.php <?= file_exists('post.php') ? '✅ EXISTE' : '❌ NO EXISTE' ?></li>
                        <li style="margin: 8px 0;">✓ public/index.php <?= file_exists('index.php') ? '✅ EXISTE' : '❌ NO EXISTE' ?></li>
                        <li style="margin: 8px 0;">✓ public/.htaccess <?= file_exists('.htaccess') ? '✅ EXISTE' : '❌ NO EXISTE' ?></li>
                        <li style="margin: 8px 0;">✓ config/config.php <?= file_exists('../config/config.php') ? '✅ EXISTE' : '❌ NO EXISTE' ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; text-align: center;">
            <a href="index.php" style="display: inline-block; padding: 12px 24px; background: #2c3e50; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                ← Volver al Index
            </a>
        </div>
    </div>
</body>
</html>
