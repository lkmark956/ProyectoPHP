<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Post;
use App\Models\Category;

/**
 * Controlador Home - Página principal
 */
class HomeController extends Controller {
    
    private $postModel;
    private $categoryModel;
    
    public function __construct() {
        $this->postModel = new Post();
        $this->categoryModel = new Category();
    }
    
    /**
     * Página principal con listado de posts
     */
    public function index() {
        $currentPage = $this->get('page', 1);
        $currentPage = max(1, intval($currentPage));
        
        $posts = $this->postModel->getAllPosts($currentPage, POSTS_PER_PAGE);
        $totalPosts = $this->postModel->getTotalPosts();
        $totalPages = ceil($totalPosts / POSTS_PER_PAGE);
        
        $categories = $this->categoryModel->getCategoriesWithPostCount();
        
        $data = [
            'pageTitle' => SITE_NAME,
            'posts' => $posts,
            'categories' => $categories,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalPosts' => $totalPosts
        ];
        
        $this->view('home.index', $data);
    }
}
