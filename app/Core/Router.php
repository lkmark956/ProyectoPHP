<?php

namespace App\Core;

/**
 * Router - Sistema de enrutamiento simple
 */
class Router {
    private $routes = [];
    
    /**
     * Registra una ruta GET
     */
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }
    
    /**
     * Registra una ruta POST
     */
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }
    
    /**
     * Ejecuta el router
     */
    public function run() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remover el prefijo /ProyectoPHP/public si existe
        $uri = str_replace('/ProyectoPHP/public', '', $uri);
        
        // Si la URI está vacía, usar /
        if (empty($uri) || $uri === '/') {
            $uri = '/index.php';
        }
        
        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $route => $callback) {
                $pattern = $this->convertToRegex($route);
                
                if (preg_match($pattern, $uri, $matches)) {
                    array_shift($matches); // Remover el match completo
                    
                    if (is_callable($callback)) {
                        call_user_func_array($callback, $matches);
                        return;
                    } elseif (is_string($callback) && strpos($callback, '@') !== false) {
                        [$controller, $action] = explode('@', $callback);
                        $this->callControllerAction($controller, $action, $matches);
                        return;
                    }
                }
            }
        }
        
        // 404 - Ruta no encontrada
        http_response_code(404);
        echo "404 - Página no encontrada";
    }
    
    /**
     * Convierte una ruta a expresión regular
     */
    private function convertToRegex($route) {
        $route = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([a-zA-Z0-9_]+)', $route);
        return '#^' . $route . '$#';
    }
    
    /**
     * Llama a una acción del controlador
     */
    private function callControllerAction($controller, $action, $params = []) {
        $controllerClass = "App\\Controllers\\$controller";
        
        if (!class_exists($controllerClass)) {
            die("Controlador no encontrado: $controllerClass");
        }
        
        $controllerInstance = new $controllerClass();
        
        if (!method_exists($controllerInstance, $action)) {
            die("Método no encontrado: $action en $controllerClass");
        }
        
        call_user_func_array([$controllerInstance, $action], $params);
    }
}
