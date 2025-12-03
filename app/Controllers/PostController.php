<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;
use App\Models\Category;

/**
 * Controlador Post - Gestión de publicaciones
 */
class PostController extends Controller {
    
    private $postModel;
    private $categoryModel;
    
    public function __construct() {
        $this->postModel = new Post();
        $this->categoryModel = new Category();
    }
    
    /**
     * Muestra un post individual
     */
    public function show() {
        $postId = $this->get('id');
        
        if (!$postId || !is_numeric($postId)) {
            $this->redirect('/index.php');
        }
        
        $post = $this->postModel->getPostById(intval($postId));
        
        if (!$post) {
            $this->redirect('/index.php');
        }
        
        $this->postModel->incrementViews($postId);
        
        $categories = $this->categoryModel->getCategoriesWithPostCount();
        
        $data = [
            'pageTitle' => htmlspecialchars($post['title']) . ' - ' . SITE_NAME,
            'post' => $post,
            'categories' => $categories
        ];
        
        $this->view('post.show', $data);
    }
}
