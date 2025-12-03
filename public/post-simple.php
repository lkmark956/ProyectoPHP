<?php
/**
 * Vista individual de un post - VERSION SIMPLE DE PRUEBA
 */

// Cargar configuración
require_once '../config/config.php';

echo "<!DOCTYPE html><html><head><title>Post Test</title></head><body>";
echo "<h1>✅ POST.PHP FUNCIONA</h1>";
echo "<p><strong>ID recibido:</strong> " . (isset($_GET['id']) ? htmlspecialchars($_GET['id']) : 'NO ID') . "</p>";
echo "<p><strong>BASE_URL:</strong> " . BASE_URL . "</p>";

// Verificar que se pasó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p style='color: red;'>❌ Error: ID no válido o no proporcionado</p>";
    echo "<p><a href='" . BASE_URL . "/index.php'>Volver al inicio</a></p>";
    echo "</body></html>";
    exit;
}

$postId = intval($_GET['id']);
echo "<p style='color: green;'>✅ ID válido: " . $postId . "</p>";

use App\Models\Post;

// Instanciar modelo
$postModel = new Post();
echo "<p style='color: green;'>✅ Modelo Post instanciado</p>";

// Obtener el post
try {
    $post = $postModel->getPostById($postId);
    
    if (!$post) {
        echo "<p style='color: red;'>❌ Post no encontrado en la base de datos</p>";
        echo "<p><a href='" . BASE_URL . "/index.php'>Volver al inicio</a></p>";
        echo "</body></html>";
        exit;
    }
    
    echo "<p style='color: green;'>✅ Post encontrado</p>";
    echo "<hr>";
    echo "<h2>" . htmlspecialchars($post['title']) . "</h2>";
    echo "<p>" . htmlspecialchars($post['description']) . "</p>";
    echo "<p><strong>Autor:</strong> " . htmlspecialchars($post['author_name']) . "</p>";
    echo "<p><strong>Fecha:</strong> " . date('d/m/Y', strtotime($post['created_at'])) . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><a href='" . BASE_URL . "/index.php'>← Volver al inicio</a></p>";
echo "</body></html>";
