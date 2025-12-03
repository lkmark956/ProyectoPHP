<?php

namespace App\Core;

/**
 * Clase base Controller
 * Todos los controladores heredan de esta clase
 */
class Controller {
    
    /**
     * Renderiza una vista
     */
    protected function view($viewPath, $data = []) {
        extract($data);
        
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $viewPath) . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Vista no encontrada: $viewFile");
        }
    }
    
    /**
     * Redirige a una URL
     */
    protected function redirect($url) {
        header("Location: " . BASE_URL . $url);
        exit;
    }
    
    /**
     * Retorna JSON
     */
    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Verifica si el usuario está autenticado
     */
    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login.php');
        }
    }
    
    /**
     * Verifica si el usuario tiene un rol específico
     */
    protected function requireRole($role) {
        $this->requireAuth();
        
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
            $this->redirect('/index.php');
        }
    }
    
    /**
     * Obtiene el usuario actual
     */
    protected function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }
    
    /**
     * Obtiene un parámetro POST
     */
    protected function post($key, $default = null) {
        return $_POST[$key] ?? $default;
    }
    
    /**
     * Obtiene un parámetro GET
     */
    protected function get($key, $default = null) {
        return $_GET[$key] ?? $default;
    }
    
    /**
     * Obtiene un archivo subido
     */
    protected function file($key) {
        return $_FILES[$key] ?? null;
    }
    
    /**
     * Valida datos requeridos
     */
    protected function validate($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if ($rule === 'required' && empty($data[$field])) {
                $errors[$field] = "El campo $field es requerido";
            }
        }
        
        return $errors;
    }
    
    /**
     * Limpia datos de entrada
     */
    protected function sanitize($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
}
