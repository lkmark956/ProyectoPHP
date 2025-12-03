<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\ImageUpload;

/**
 * Controlador Profile - Perfil de usuario
 */
class ProfileController extends Controller {
    
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Muestra el perfil del usuario
     */
    public function index() {
        $this->requireAuth();
        
        $user = $this->userModel->getUserById($_SESSION['user_id']);
        
        $data = [
            'pageTitle' => 'Mi Perfil - ' . SITE_NAME,
            'user' => $user,
            'success' => $_SESSION['profile_success'] ?? '',
            'error' => $_SESSION['profile_error'] ?? ''
        ];
        
        unset($_SESSION['profile_success'], $_SESSION['profile_error']);
        
        $this->view('profile.index', $data);
    }
    
    /**
     * Actualiza el perfil
     */
    public function update() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile.php');
        }
        
        $userData = [
            'full_name' => $this->post('full_name'),
            'email' => $this->post('email')
        ];
        
        $result = $this->userModel->updateProfile($_SESSION['user_id'], $userData);
        
        if ($result['success']) {
            $_SESSION['profile_success'] = $result['message'];
        } else {
            $_SESSION['profile_error'] = $result['message'];
        }
        
        $this->redirect('/profile.php');
    }
    
    /**
     * Actualiza el avatar
     */
    public function uploadAvatar() {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/profile.php');
        }
        
        $avatarFile = $this->file('avatar');
        
        if ($avatarFile && $avatarFile['error'] !== UPLOAD_ERR_NO_FILE) {
            $imageUpload = new ImageUpload(PUBLIC_PATH . '/uploads/users/');
            $result = $imageUpload->upload($avatarFile, 'avatar_');
            
            if ($result['success']) {
                $updateResult = $this->userModel->updateAvatar($_SESSION['user_id'], $result['filename']);
                
                if (isset($updateResult['old_avatar']) && $updateResult['old_avatar']) {
                    $imageUpload->delete($updateResult['old_avatar']);
                }
                
                $_SESSION['profile_success'] = 'Avatar actualizado correctamente';
            } else {
                $_SESSION['profile_error'] = $result['error'];
            }
        }
        
        $this->redirect('/profile.php');
    }
}
