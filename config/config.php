<?php
// Configuracion UTF-8
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
ini_set('default_charset', 'UTF-8');

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
define('VIEWS_PATH', ROOT_PATH . '/app/Views');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// URL base del proyecto
// El servidor corre desde la raíz del workspace, no desde /public
define('BASE_URL', 'http://localhost:3000/ProyectoPHP/public');
define('SITE_URL', 'http://localhost:3000/ProyectoPHP/public');

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
    
    // Nuevo sistema MVC (app/)
    if (strpos($class, 'App\\') === 0) {
        $classPath = str_replace('App\\', '', $class);
        $classPath = str_replace('\\', '/', $classPath);
        
        $file = ROOT_PATH . '/app/' . $classPath . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Soporte legacy (src/) - para compatibilidad temporal
    $legacyClass = str_replace('App\\', '', $class);
    $legacyFile = ROOT_PATH . '/src/' . $legacyClass . '.php';
    
    if (file_exists($legacyFile)) {
        require_once $legacyFile;
    }
});

// Cargar funciones auxiliares
require_once ROOT_PATH . '/app/helpers.php';
