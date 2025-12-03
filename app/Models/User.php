<?php

namespace App\Models;

use PDO;

/**
 * Modelo User - Gestiona la autenticación y usuarios
 */
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Registra un nuevo usuario
     */
    public function register($data) {
        $errors = $this->validateRegistration($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }
        
        if ($this->userExists($data['username'], $data['email'])) {
            return ['success' => false, 'message' => 'El usuario o email ya está registrado'];
        }
        
        try {
            $sql = "INSERT INTO users (username, email, password, full_name, role, created_at) 
                    VALUES (:username, :email, :password, :full_name, :role, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':username', trim($data['username']));
            $stmt->bindValue(':email', trim($data['email']));
            $stmt->bindValue(':password', password_hash($data['password'], PASSWORD_BCRYPT));
            $stmt->bindValue(':full_name', trim($data['full_name'] ?? ''));
            $stmt->bindValue(':role', $data['role'] ?? 'user');
            $stmt->execute();
            
            return [
                'success' => true, 
                'message' => 'Usuario registrado exitosamente',
                'user_id' => $this->db->lastInsertId()
            ];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error al registrar usuario: ' . $e->getMessage()];
        }
    }
    
    /**
     * Inicia sesión de un usuario
     */
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Usuario y contraseña son requeridos'];
        }
        
        $user = $this->getUserByUsername($username);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
        }
        
        if (!$user['active']) {
            return ['success' => false, 'message' => 'Tu cuenta está desactivada'];
        }
        
        $this->updateLastLogin($user['id']);
        
        unset($user['password']);
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        return [
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'user' => $user
        ];
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    /**
     * Verifica si un usuario está autenticado
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Obtiene el usuario actual de la sesión
     */
    public function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }
    
    /**
     * Verifica si el usuario tiene un rol específico
     */
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    /**
     * Obtiene un usuario por su username
     */
    private function getUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', trim($username));
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Obtiene un usuario por su ID
     */
    public function getUserById($id) {
        $sql = "SELECT id, username, email, full_name, avatar, role, created_at, last_login, active 
                FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Actualiza el avatar del usuario
     */
    public function updateAvatar($userId, $avatarFilename) {
        try {
            $user = $this->getUserById($userId);
            $oldAvatar = $user['avatar'] ?? null;
            
            $sql = "UPDATE users SET avatar = :avatar WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':avatar', $avatarFilename);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['avatar'] = $avatarFilename;
            }
            
            return [
                'success' => true,
                'message' => 'Avatar actualizado exitosamente',
                'old_avatar' => $oldAvatar
            ];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar avatar: ' . $e->getMessage()];
        }
    }
    
    /**
     * Elimina el avatar del usuario
     */
    public function deleteAvatar($userId) {
        try {
            $user = $this->getUserById($userId);
            $oldAvatar = $user['avatar'] ?? null;
            
            $sql = "UPDATE users SET avatar = NULL WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['avatar'] = null;
            }
            
            return [
                'success' => true,
                'message' => 'Avatar eliminado exitosamente',
                'old_avatar' => $oldAvatar
            ];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar avatar: ' . $e->getMessage()];
        }
    }
    
    /**
     * Actualiza el perfil del usuario
     */
    public function updateProfile($userId, $data) {
        try {
            $sql = "UPDATE users SET full_name = :full_name, email = :email WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':full_name', trim($data['full_name'] ?? ''));
            $stmt->bindValue(':email', trim($data['email']));
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['full_name'] = trim($data['full_name'] ?? '');
                $_SESSION['user']['email'] = trim($data['email']);
            }
            
            return ['success' => true, 'message' => 'Perfil actualizado exitosamente'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar perfil: ' . $e->getMessage()];
        }
    }
    
    /**
     * Obtiene todos los usuarios (para administración)
     */
    public function getAllUsers($page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT id, username, email, full_name, avatar, role, created_at, last_login, active 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene el total de usuarios
     */
    public function getTotalUsers() {
        $sql = "SELECT COUNT(*) as count FROM users";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Actualiza un usuario por el admin
     */
    public function updateUserByAdmin($userId, $data) {
        try {
            $fields = [];
            $params = [':id' => $userId];
            
            if (isset($data['username'])) {
                $fields[] = "username = :username";
                $params[':username'] = trim($data['username']);
            }
            
            if (isset($data['email'])) {
                $fields[] = "email = :email";
                $params[':email'] = trim($data['email']);
            }
            
            if (isset($data['full_name'])) {
                $fields[] = "full_name = :full_name";
                $params[':full_name'] = trim($data['full_name']);
            }
            
            if (isset($data['role'])) {
                $fields[] = "role = :role";
                $params[':role'] = $data['role'];
            }
            
            if (isset($data['password'])) {
                $fields[] = "password = :password";
                $params[':password'] = $data['password'];
            }
            
            if (isset($data['active'])) {
                $fields[] = "active = :active";
                $params[':active'] = $data['active'] ? 1 : 0;
            }
            
            if (empty($fields)) {
                return false;
            }
            
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    /**
     * Elimina un usuario
     */
    public function deleteUser($userId) {
        try {
            $sql = "DELETE FROM users WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    /**
     * Cambia la contraseña de un usuario
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $sql = "SELECT password FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        
        if (!password_verify($currentPassword, $result['password'])) {
            return ['success' => false, 'message' => 'La contraseña actual es incorrecta'];
        }
        
        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 6 caracteres'];
        }
        
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':password', password_hash($newPassword, PASSWORD_BCRYPT));
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        
        return ['success' => true, 'message' => 'Contraseña actualizada exitosamente'];
    }
    
    /**
     * Verifica si un usuario o email ya existe
     */
    private function userExists($username, $email) {
        $sql = "SELECT COUNT(*) as count FROM users WHERE username = :username OR email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', trim($username));
        $stmt->bindValue(':email', trim($email));
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Actualiza la fecha del último login
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    /**
     * Valida los datos de registro
     */
    private function validateRegistration($data) {
        $errors = [];
        
        if (empty($data['username'])) {
            $errors[] = 'El nombre de usuario es requerido';
        } elseif (strlen($data['username']) < 3 || strlen($data['username']) > 50) {
            $errors[] = 'El nombre de usuario debe tener entre 3 y 50 caracteres';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors[] = 'El nombre de usuario solo puede contener letras, números y guiones bajos';
        }
        
        if (empty($data['email'])) {
            $errors[] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        }
        
        if (empty($data['password'])) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if (isset($data['password_confirm']) && $data['password'] !== $data['password_confirm']) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        return $errors;
    }
    
    /**
     * Obtiene estadísticas de un usuario
     */
    public function getUserStats($userId) {
        $stats = [];
        
        // Posts creados
        $sql = "SELECT COUNT(*) as count FROM posts WHERE author_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['posts_created'] = $stmt->fetch()['count'];
        
        // Total de vistas en posts del usuario
        $sql = "SELECT SUM(views) as total FROM posts WHERE author_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['total_views'] = $stmt->fetch()['total'] ?? 0;
        
        return $stats;
    }
    
    /**
     * Obtiene los posts creados por un usuario
     */
    public function getUserPosts($userId, $limit = 10) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.author_id = :user_id 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
