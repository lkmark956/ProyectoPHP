<?php

namespace App;

use PDO;

/**
 * Clase Post - Gestiona las publicaciones del blog
 */
class Post {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtiene todos los posts con paginación
     * @param int $page Número de página
     * @param int $perPage Posts por página
     * @return array
     */
    public function getAllPosts($page = 1, $perPage = 6) {
        $offset = ($page - 1) * $perPage;
        
        // Verificar si existe la columna avatar
        try {
            $checkCol = $this->db->query("SHOW COLUMNS FROM users LIKE 'avatar'");
            $hasAvatar = $checkCol->rowCount() > 0;
        } catch (Exception $e) {
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
     * @return int
     */
    public function getTotalPosts() {
        $sql = "SELECT COUNT(*) as total FROM posts WHERE published = 1";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'];
    }
    
    /**
     * Obtiene un post por su ID
     * @param int $id
     * @return array|false
     */
    public function getPostById($id) {
        // Verificar si existe la columna avatar
        try {
            $checkCol = $this->db->query("SHOW COLUMNS FROM users LIKE 'avatar'");
            $hasAvatar = $checkCol->rowCount() > 0;
        } catch (Exception $e) {
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
     * @param int $categoryId
     * @return array
     */
    public function getPostsByCategory($categoryId) {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
                FROM posts p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.category_id = :category_id AND p.published = 1 
                ORDER BY p.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Crea un nuevo post
     * @param array $data
     * @return int ID del post creado
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
        $stmt->bindValue(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':author_id', $data['author_id'], PDO::PARAM_INT);
        $stmt->bindValue(':published', $data['published'] ?? 1, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualiza un post existente
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updatePost($id, $data) {
        // Si hay nueva imagen, actualizar el campo
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
        $stmt->bindValue(':category_id', $data['category_id'], PDO::PARAM_INT);
        $stmt->bindValue(':published', $data['published'] ?? 1, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Elimina un post
     * @param int $id
     * @return bool
     */
    public function deletePost($id) {
        $sql = "DELETE FROM posts WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Obtiene la imagen de un post
     * @param int $postId
     * @return string|null
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
     * @param int $postId
     * @param string $imageFilename
     * @return bool
     */
    public function updatePostImage($postId, $imageFilename) {
        $sql = "UPDATE posts SET image = :image WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':image', $imageFilename);
        $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Elimina la imagen de un post
     * @param int $postId
     * @return bool
     */
    public function deletePostImage($postId) {
        $sql = "UPDATE posts SET image = NULL WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Incrementa el contador de vistas
     * @param int $postId
     * @return bool
     */
    public function incrementViews($postId) {
        $sql = "UPDATE posts SET views = views + 1 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Genera un slug amigable para URLs a partir del título
     * @param string $title
     * @return string
     */
    private function generateSlug($title) {
        // Convertir a minúsculas
        $slug = strtolower($title);
        
        // Reemplazar caracteres especiales
        $slug = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $slug
        );
        
        // Reemplazar espacios y caracteres no alfanuméricos con guiones
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Eliminar guiones al inicio y al final
        $slug = trim($slug, '-');
        
        return $slug;
    }
}
