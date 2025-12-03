<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

/**
 * Controlador Auth - Autenticación y registro
 */
class AuthController extends Controller {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Muestra el formulario de login
     */
    public function login() {
        if ($this->userModel->isLoggedIn()) {
            $this->redirect('/index.php');
        }
        
        $data = [
            'pageTitle' => 'Iniciar Sesión - ' . SITE_NAME,
            'error' => '',
            'success' => ''
        ];
        
        $this->view('auth.login', $data);
    }
    
    /**
     * Procesa el login
     */
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login.php');
        }
        
        $username = trim($this->post('username', ''));
        $password = $this->post('password', '');
        
        $result = $this->userModel->login($username, $password);
        
        if ($result['success']) {
            $this->redirect('/index.php');
        } else {
            $_SESSION['login_error'] = $result['message'];
            $this->redirect('/login.php');
        }
    }
    
    /**
     * Muestra el formulario de registro
     */
    public function register() {
        if ($this->userModel->isLoggedIn()) {
            $this->redirect('/index.php');
        }
        
        $data = [
            'pageTitle' => 'Registro - ' . SITE_NAME,
            'error' => '',
            'success' => ''
        ];
        
        $this->view('auth.register', $data);
    }
    
    /**
     * Procesa el registro
     */
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register.php');
        }
        
        $userData = [
            'username' => $this->post('username'),
            'email' => $this->post('email'),
            'password' => $this->post('password'),
            'password_confirm' => $this->post('password_confirm'),
            'full_name' => $this->post('full_name', ''),
            'role' => 'user'
        ];
        
        $result = $this->userModel->register($userData);
        
        if ($result['success']) {
            $_SESSION['register_success'] = $result['message'];
            $this->redirect('/login.php');
        } else {
            $_SESSION['register_error'] = $result['message'];
            $this->redirect('/register.php');
        }
    }
    
    /**
     * Cierra la sesión
     */
    public function logout() {
        $this->userModel->logout();
        $this->redirect('/index.php');
    }
}
