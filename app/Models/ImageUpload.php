<?php

namespace App\Models;

/**
 * Clase para manejar la subida y procesamiento de imágenes
 */
class ImageUpload {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    private $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    private $maxFileSize = 5242880; // 5MB
    private $maxWidth = 1920;
    private $maxHeight = 1920;
    
    public function __construct($uploadDir = null) {
        // Si se pasa un directorio relativo, lo convertimos a absoluto
        if ($uploadDir && strpos($uploadDir, 'uploads/') === 0) {
            $this->uploadDir = PUBLIC_PATH . '/' . $uploadDir;
        } else {
            $this->uploadDir = $uploadDir ?? (PUBLIC_PATH . '/uploads/');
        }
        
        $this->uploadDir = rtrim($this->uploadDir, '/') . '/';
        
        // Crear directorio si no existe
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
            chmod($this->uploadDir, 0777);
        }
    }
    
    /**
     * Validar y subir una imagen
     */
    public function upload($file, $prefix = '') {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'No se seleccionó ningún archivo'];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => $this->getUploadError($file['error'])];
        }
        
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'error' => 'El archivo es demasiado grande. Máximo: ' . ($this->maxFileSize / 1024 / 1024) . 'MB'];
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'error' => 'Tipo de archivo no permitido. Solo imágenes JPG, PNG, GIF y WEBP'];
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return ['success' => false, 'error' => 'Extensión de archivo no permitida'];
        }
        
        $filename = $prefix . uniqid() . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;
        
        $resized = $this->resizeImage($file['tmp_name'], $filepath, $mimeType);
        
        if (!$resized) {
            return ['success' => false, 'error' => 'Error al procesar la imagen'];
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath,
            'url' => 'uploads/' . $filename
        ];
    }
    
    /**
     * Redimensionar imagen si excede las dimensiones máximas
     */
    private function resizeImage($source, $destination, $mimeType) {
        list($width, $height) = getimagesize($source);
        
        $ratio = min($this->maxWidth / $width, $this->maxHeight / $height, 1);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($source);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($source);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }
        
        if (!$sourceImage) {
            return false;
        }
        
        $destImage = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefilledrectangle($destImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        imagecopyresampled(
            $destImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $width, $height
        );
        
        $result = false;
        switch ($mimeType) {
            case 'image/jpeg':
                $result = imagejpeg($destImage, $destination, 85);
                break;
            case 'image/png':
                $result = imagepng($destImage, $destination, 8);
                break;
            case 'image/gif':
                $result = imagegif($destImage, $destination);
                break;
            case 'image/webp':
                $result = imagewebp($destImage, $destination, 85);
                break;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        
        return $result;
    }
    
    /**
     * Eliminar una imagen
     */
    public function delete($filename) {
        if (empty($filename)) {
            return false;
        }
        
        $filepath = $this->uploadDir . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
    
    /**
     * Obtener mensaje de error de subida
     */
    private function getUploadError($code) {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'El archivo excede el tamaño máximo permitido';
            case UPLOAD_ERR_PARTIAL:
                return 'El archivo se subió parcialmente';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Falta la carpeta temporal';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Error al escribir el archivo en disco';
            case UPLOAD_ERR_EXTENSION:
                return 'Una extensión de PHP detuvo la subida';
            default:
                return 'Error desconocido al subir el archivo';
        }
    }
}
