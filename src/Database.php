<?php

namespace App;

use PDO;
use PDOException;

/**
 * Clase Database - Gestiona la conexión a la base de datos usando PDO
 * Implementa el patrón Singleton para una única instancia de conexión
 */
class Database {
    private static $instance = null;
    private $connection;
    
    /**
     * Constructor privado para prevenir instanciación directa
     */
    private function __construct() {
        try {
            $host = DB_HOST;
            $port = defined('DB_PORT') ? DB_PORT : '3306';
            $dbname = DB_NAME;
            $username = DB_USER;
            $password = DB_PASS;
            $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';
            
            // Intentar conexión con puerto específico
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 5, // Timeout de 5 segundos
            ];
            
            $this->connection = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Mensaje de error más descriptivo
            $errorMsg = "Error de conexión a la base de datos: " . $e->getMessage();
            $errorMsg .= "\n\nPor favor verifica que:";
            $errorMsg .= "\n1. El servidor MySQL/MariaDB esté activo (XAMPP, WAMP, etc.)";
            $errorMsg .= "\n2. Las credenciales en config/config.php sean correctas";
            $errorMsg .= "\n3. La base de datos 'cms_blog' exista";
            $errorMsg .= "\n4. El puerto sea el correcto (usualmente 3306)";
            
            die("<pre style='background:#ff4444;color:white;padding:20px;border-radius:8px;'>$errorMsg</pre>");
        }
    }
    
    /**
     * Obtiene la instancia única de la conexión (Singleton)
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtiene la conexión PDO
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Previene la clonación de la instancia
     */
    private function __clone() {}
    
    /**
     * Previene la deserialización de la instancia
     */
    public function __wakeup() {
        throw new \Exception("No se puede deserializar un Singleton.");
    }
}
