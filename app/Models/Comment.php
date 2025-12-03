<?php

namespace App\Models;

use PDO;

/**
 * Modelo Comment - Gestiona los comentarios de posts
 */
class Comment {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crea un nuevo comentario
     */
    public function createComment($postId, $userId, $content) {
        if (empty($content)) {
            return ['success' => false, 'message' => 'El comentario no puede estar vacío'];
        }
        
        try {
            $sql = "INSERT INTO comments (post_id, user_id, content, created_at) 
                    VALUES (:post_id, :user_id, :content, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':content', trim($content));
            $stmt->execute();
            
            return [
                'success' => true,
                'message' => 'Comentario publicado exitosamente',
                'comment_id' => $this->db->lastInsertId()
            ];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error al publicar comentario'];
        }
    }
    
    /**
     * Obtiene comentarios de un post
     */
    public function getCommentsByPost($postId) {
        $sql = "SELECT c.*, u.username, u.avatar, u.role 
                FROM comments c 
                INNER JOIN users u ON c.user_id = u.id 
                WHERE c.post_id = :post_id 
                ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un comentario por ID
     */
    public function getCommentById($commentId) {
        $sql = "SELECT c.*, u.username 
                FROM comments c 
                INNER JOIN users u ON c.user_id = u.id 
                WHERE c.id = :id 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $commentId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch();
    }
    
    /**
     * Actualiza un comentario
     */
    public function updateComment($commentId, $content) {
        if (empty($content)) {
            return ['success' => false, 'message' => 'El comentario no puede estar vacío'];
        }
        
        try {
            $sql = "UPDATE comments SET content = :content, updated_at = NOW() WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':content', trim($content));
            $stmt->bindValue(':id', $commentId, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Comentario actualizado exitosamente'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar comentario'];
        }
    }
    
    /**
     * Elimina un comentario
     */
    public function deleteComment($commentId) {
        try {
            $sql = "DELETE FROM comments WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $commentId, PDO::PARAM_INT);
            $stmt->execute();
            
            return ['success' => true, 'message' => 'Comentario eliminado exitosamente'];
        } catch (\PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar comentario'];
        }
    }
    
    /**
     * Cuenta comentarios de un post
     */
    public function countCommentsByPost($postId) {
        $sql = "SELECT COUNT(*) as count FROM comments WHERE post_id = :post_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Verifica si un usuario puede editar un comentario
     */
    public function canEditComment($commentId, $userId, $userRole) {
        $comment = $this->getCommentById($commentId);
        
        if (!$comment) {
            return false;
        }
        
        // Admin puede editar cualquier comentario
        if ($userRole === 'admin') {
            return true;
        }
        
        // El autor puede editar su propio comentario
        if ($comment['user_id'] == $userId) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Verifica si un usuario puede eliminar un comentario
     */
    public function canDeleteComment($commentId, $userId, $userRole, $postAuthorId = null) {
        $comment = $this->getCommentById($commentId);
        
        if (!$comment) {
            return false;
        }
        
        // Admin puede eliminar cualquier comentario
        if ($userRole === 'admin') {
            return true;
        }
        
        // El autor del comentario puede eliminarlo
        if ($comment['user_id'] == $userId) {
            return true;
        }
        
        // El autor del post puede eliminar comentarios en su post
        if ($postAuthorId && $postAuthorId == $userId) {
            return true;
        }
        
        return false;
    }
}
