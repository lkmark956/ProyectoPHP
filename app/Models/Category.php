<?php

namespace App\Models;

use PDO;

/**
 * Modelo Category - Gestiona las categorías del blog
 */
class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtiene todas las categorías
     */
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene las categorías con el conteo de posts
     */
    public function getCategoriesWithPostCount() {
        $sql = "SELECT c.*, COUNT(p.id) as post_count 
                FROM categories c 
                LEFT JOIN posts p ON c.id = p.category_id AND p.published = 1
                GROUP BY c.id 
                ORDER BY c.name ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene una categoría por su ID
     */
    public function getCategoryById($id) {
        $sql = "SELECT * FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }
    
    /**
     * Crea una nueva categoría
     */
    public function createCategory($data) {
        $sql = "INSERT INTO categories (name, slug, description) 
                VALUES (:name, :slug, :description)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', trim($data['name']));
        $stmt->bindValue(':slug', $this->generateSlug($data['name']));
        $stmt->bindValue(':description', trim($data['description'] ?? ''));
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualiza una categoría existente
     */
    public function updateCategory($id, $data) {
        $sql = "UPDATE categories 
                SET name = :name, 
                    slug = :slug, 
                    description = :description 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':name', trim($data['name']));
        $stmt->bindValue(':slug', $this->generateSlug($data['name']));
        $stmt->bindValue(':description', trim($data['description'] ?? ''));
        
        return $stmt->execute();
    }
    
    /**
     * Elimina una categoría
     */
    public function deleteCategory($id) {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
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
