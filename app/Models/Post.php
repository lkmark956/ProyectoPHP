<?php

namespace App\Models;

use PDO;

/**
 * Modelo Post - Gestiona las publicaciones del blog
 */
class Post {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtiene todos los posts con paginación
     */
    public function getAllPosts($page = 1, $perPage = 6) {
        $offset = ($page - 1) * $perPage;
        
        try {
            $checkCol = $this->db->query("SHOW COLUMNS FROM users LIKE 'avatar'");
            $hasAvatar = $checkCol->rowCount() > 0;
        } catch (\Exception $e) {
            $hasAvatar = false;
        }
        
        if ($hasAvatar) {
            $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                           u.username as author_name, u.avatar as author_avatar
                    FROM posts p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE p.published = 1 
                    ORDER BY p.created_at DESC 
                    LIMIT :limit OFFSET :offset";
        } else {
            $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                           u.username as author_name, NULL as author_avatar
                    FROM posts p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE p.published = 1 
                    ORDER BY p.created_at DESC 
                    LIMIT :limit OFFSET :offset";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Cuenta el total de posts publicados
     */
    public function getTotalPosts() {
        $sql = "SELECT COUNT(*) as total FROM posts WHERE published = 1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Obtiene un post por su ID
     */
    public function getPostById($id) {
        try {
            $checkCol = $this->db->query("SHOW COLUMNS FROM users LIKE 'avatar'");
            $hasAvatar = $checkCol->rowCount() > 0;
        } catch (\Exception $e) {
            $hasAvatar = false;
        }
        
        if ($hasAvatar) {
            $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                           u.username as author_name, u.avatar as author_avatar
                    FROM posts p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE p.id = :id AND p.published = 1";
        } else {
            $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                           u.username as author_name, NULL as author_avatar
                    FROM posts p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN users u ON p.author_id = u.id
                    WHERE p.id = :id AND p.published = 1";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Obtiene posts por categoría
     */
    public function getPostsByCategory($categoryId) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       u.username as author_name, u.avatar as author_avatar
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN users u ON p.author_id = u.id
                WHERE p.category_id = :category_id AND p.published = 1 
                ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Crea un nuevo post
     */
    public function createPost($data) {
        $sql = "INSERT INTO posts (title, slug, description, content, image, category_id, author_id, published, created_at) 
                VALUES (:title, :slug, :description, :content, :image, :category_id, :author_id, :published, NOW())";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':title', trim($data['title']));
        $stmt->bindValue(':slug', $this->generateSlug($data['title']));
        $stmt->bindValue(':description', trim($data['description']));
        $stmt->bindValue(':content', trim($data['content']));
        $stmt->bindValue(':image', $data['image'] ?? null);
        // Si category_id es 0 o vacío, usar NULL para respetar foreign key
        $categoryId = !empty($data['category_id']) ? $data['category_id'] : null;
        $stmt->bindValue(':category_id', $categoryId, $categoryId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':author_id', $data['author_id'], PDO::PARAM_INT);
        $stmt->bindValue(':published', $data['published'] ?? 1, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualiza un post existente
     */
    public function updatePost($id, $data) {
        if (isset($data['image'])) {
            $sql = "UPDATE posts 
                    SET title = :title, 
                        slug = :slug, 
                        description = :description, 
                        content = :content, 
                        image = :image,
                        category_id = :category_id, 
                        published = :published,
                        updated_at = NOW()
                    WHERE id = :id";
        } else {
            $sql = "UPDATE posts 
                    SET title = :title, 
                        slug = :slug, 
                        description = :description, 
                        content = :content, 
                        category_id = :category_id, 
                        published = :published,
                        updated_at = NOW()
                    WHERE id = :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':title', trim($data['title']));
        $stmt->bindValue(':slug', $this->generateSlug($data['title']));
        $stmt->bindValue(':description', trim($data['description']));
        $stmt->bindValue(':content', trim($data['content']));
        if (isset($data['image'])) {
            $stmt->bindValue(':image', $data['image']);
        }
        // Si category_id es 0 o vacío, usar NULL para respetar foreign key
        $categoryId = !empty($data['category_id']) ? $data['category_id'] : null;
        $stmt->bindValue(':category_id', $categoryId, $categoryId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':published', $data['published'] ?? 1, PDO::PARAM_INT);
        
        $result = $stmt->execute();
        
        return $result;
    }
    
    /**
     * Elimina un post
     */
    public function deletePost($id) {
        $sql = "DELETE FROM posts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $result = $stmt->execute();
        
        return $result;
    }
    
    /**
     * Obtiene la imagen de un post
     */
    public function getPostImage($postId) {
        $sql = "SELECT image FROM posts WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['image'] ?? null;
    }
    
    /**
     * Actualiza la imagen de un post
     */
    public function updatePostImage($postId, $imageFilename) {
        $sql = "UPDATE posts SET image = :image WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':image', $imageFilename);
        $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Incrementa el contador de vistas
     */
    public function incrementViews($postId) {
        $sql = "UPDATE posts SET views = views + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Genera un slug a partir de un string
     */
    private function generateSlug($text) {
        $text = mb_strtolower($text, 'UTF-8');
        
        $replacements = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'ñ' => 'n', 'ü' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        ];
        $text = strtr($text, $replacements);
        
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        
        return $text;
    }
}
