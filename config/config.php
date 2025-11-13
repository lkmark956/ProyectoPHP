<?php

/**
 * Archivo de configuración del proyecto
 * Define constantes para la conexión a la base de datos y rutas del proyecto
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '3306'); // Puerto MySQL por defecto
define('DB_NAME', 'cms_blog');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de rutas
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// URL base del proyecto (sin /public porque index.php ya está en public)
define('BASE_URL', 'http://localhost/ProyectoPHP/public');
define('SITE_URL', 'http://localhost/ProyectoPHP/public'); // URL completa

// Configuración de la aplicación
define('SITE_NAME', 'CMS Blog Personal');
define('POSTS_PER_PAGE', 6);

// Configuración de sesiones (ANTES de session_start())
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Iniciar sesión DESPUÉS de configurar las opciones
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Autoload de clases
spl_autoload_register(function ($class) {
    // Convertir namespace a ruta de archivo
    $class = str_replace('App\\', '', $class);
    $file = ROOT_PATH . '/src/' . $class . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});
