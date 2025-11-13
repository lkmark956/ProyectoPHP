<?php

namespace App;

use PDO;

/**
 * Clase User - Gestiona la autenticación y usuarios
 */
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Registra un nuevo usuario
     * @param array $data
     * @return array ['success' => bool, 'message' => string, 'user_id' => int]
     */
    public function register($data) {
        // Validar datos
        $errors = $this->validateRegistration($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => implode(', ', $errors)];
        }
        
        // Verificar si el usuario ya existe
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
     * @param string $username
     * @param string $password
     * @return array ['success' => bool, 'message' => string, 'user' => array]
     */
    public function login($username, $password) {
        // Validación básica
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Usuario y contraseña son requeridos'];
        }
        
        // Buscar usuario
        $user = $this->getUserByUsername($username);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
        }
        
        // Verificar contraseña
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Usuario o contraseña incorrectos'];
        }
        
        // Verificar si está activo
        if (!$user['active']) {
            return ['success' => false, 'message' => 'Tu cuenta está desactivada'];
        }
        
        // Actualizar último login
        $this->updateLastLogin($user['id']);
        
        // Guardar datos en sesión (sin password)
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
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Obtiene el usuario actual de la sesión
     * @return array|null
     */
    public function getCurrentUser() {
        return $_SESSION['user'] ?? null;
    }
    
    /**
     * Verifica si el usuario tiene un rol específico
     * @param string $role
     * @return bool
     */
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }
    
    /**
     * Obtiene un usuario por su username
     * @param string $username
     * @return array|false
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
     * @param int $id
     * @return array|false
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
     * @param int $userId
     * @param string $avatarFilename
     * @return array
     */
    public function updateAvatar($userId, $avatarFilename) {
        try {
            // Obtener avatar anterior para eliminarlo
            $user = $this->getUserById($userId);
            $oldAvatar = $user['avatar'] ?? null;
            
            $sql = "UPDATE users SET avatar = :avatar WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':avatar', $avatarFilename);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Actualizar sesión
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
     * Obtiene el avatar del usuario
     * @param int $userId
     * @return string|null
     */
    public function getAvatar($userId) {
        $sql = "SELECT avatar FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['avatar'] ?? null;
    }
    
    /**
     * Elimina el avatar del usuario
     * @param int $userId
     * @return array
     */
    public function deleteAvatar($userId) {
        try {
            $sql = "UPDATE users SET avatar = NULL WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Actualizar sesión
            if (isset($_SESSION['user'])) {
                $_SESSION['user']['avatar'] = null;
            }
            
            return ['success' => true, 'message' => 'Avatar eliminado exitosamente'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar avatar: ' . $e->getMessage()];
        }
    }
    
    /**
     * Actualiza el perfil del usuario
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateProfile($userId, $data) {
        try {
            $sql = "UPDATE users SET full_name = :full_name, email = :email WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':full_name', trim($data['full_name'] ?? ''));
            $stmt->bindValue(':email', trim($data['email']));
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            // Actualizar sesión
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
     * @param int $page
     * @param int $perPage
     * @return array
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
     * @return int
     */
    public function getTotalUsers() {
        $sql = "SELECT COUNT(*) as count FROM users";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Verifica si un usuario o email ya existe
     * @param string $username
     * @param string $email
     * @return bool
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
     * @param int $userId
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    /**
     * Valida los datos de registro
     * @param array $data
     * @return array Errores encontrados
     */
    private function validateRegistration($data) {
        $errors = [];
        
        // Username
        if (empty($data['username'])) {
            $errors[] = 'El nombre de usuario es requerido';
        } elseif (strlen($data['username']) < 3 || strlen($data['username']) > 50) {
            $errors[] = 'El nombre de usuario debe tener entre 3 y 50 caracteres';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors[] = 'El nombre de usuario solo puede contener letras, números y guiones bajos';
        }
        
        // Email
        if (empty($data['email'])) {
            $errors[] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no es válido';
        }
        
        // Password
        if (empty($data['password'])) {
            $errors[] = 'La contraseña es requerida';
        } elseif (strlen($data['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        // Confirmar password
        if (isset($data['password_confirm']) && $data['password'] !== $data['password_confirm']) {
            $errors[] = 'Las contraseñas no coinciden';
        }
        
        return $errors;
    }
    
    /**
     * Cambia la contraseña de un usuario
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return array
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->getUserById($userId);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado'];
        }
        
        // Obtener password actual de la BD
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
     * Actualiza un usuario por el admin
     * @param int $userId
     * @param array $data
     * @return bool
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
     * @param int $userId
     * @return bool
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
}
