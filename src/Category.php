<?php

namespace App;

use PDO;

/**
 * Clase Category - Gestiona las categorías del blog
 */
class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtiene todas las categorías
     * @return array
     */
    public function getAllCategories() {
        $sql = "SELECT * FROM categories ORDER BY name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene las categorías con el conteo de posts
     * @return array
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
     * @param int $id
     * @return array|false
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
     * @param array $data
     * @return int ID de la categoría creada
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
     * @param int $id
     * @param array $data
     * @return bool
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
     * @param int $id
     * @return bool
     */
    public function deleteCategory($id) {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    /**
     * Genera un slug amigable para URLs a partir del nombre
     * @param string $name
     * @return string
     */
    private function generateSlug($name) {
        $slug = strtolower($name);
        
        $slug = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $slug
        );
        
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug;
    }
}
