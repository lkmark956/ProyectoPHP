<?php
namespace App;

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
    
    public function __construct($uploadDir = 'uploads/') {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        
        // Crear directorio si no existe
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Validar y subir una imagen
     * 
     * @param array $file El archivo de $_FILES
     * @param string $prefix Prefijo para el nombre del archivo
     * @return array ['success' => bool, 'filename' => string, 'error' => string]
     */
    public function upload($file, $prefix = '') {
        // Verificar que se subió un archivo
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => false, 'error' => 'No se seleccionó ningún archivo'];
        }
        
        // Verificar errores de subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => $this->getUploadError($file['error'])];
        }
        
        // Validar tamaño
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'error' => 'El archivo es demasiado grande. Máximo: ' . ($this->maxFileSize / 1024 / 1024) . 'MB'];
        }
        
        // Validar tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'error' => 'Tipo de archivo no permitido. Solo imágenes JPG, PNG, GIF y WEBP'];
        }
        
        // Validar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions)) {
            return ['success' => false, 'error' => 'Extensión de archivo no permitida'];
        }
        
        // Generar nombre único
        $filename = $prefix . uniqid() . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;
        
        // Redimensionar y optimizar imagen
        $resized = $this->resizeImage($file['tmp_name'], $filepath, $mimeType);
        
        if (!$resized) {
            return ['success' => false, 'error' => 'Error al procesar la imagen'];
        }
        
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $filepath,
            'url' => $this->uploadDir . $filename
        ];
    }
    
    /**
     * Redimensionar imagen si excede las dimensiones máximas
     * 
     * @param string $source Ruta del archivo fuente
     * @param string $destination Ruta de destino
     * @param string $mimeType Tipo MIME de la imagen
     * @return bool
     */
    private function resizeImage($source, $destination, $mimeType) {
        // Obtener dimensiones originales
        list($width, $height) = getimagesize($source);
        
        // Calcular nuevas dimensiones manteniendo proporción
        $ratio = min($this->maxWidth / $width, $this->maxHeight / $height, 1);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Crear imagen desde el archivo fuente
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
        
        // Crear imagen de destino
        $destImage = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preservar transparencia para PNG y GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefilledrectangle($destImage, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Redimensionar
        imagecopyresampled(
            $destImage, $sourceImage,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $width, $height
        );
        
        // Guardar imagen
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
        
        // Liberar memoria
        imagedestroy($sourceImage);
        imagedestroy($destImage);
        
        return $result;
    }
    
    /**
     * Eliminar una imagen
     * 
     * @param string $filename Nombre del archivo
     * @return bool
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
     * Crear thumbnail de una imagen
     * 
     * @param string $filename Nombre del archivo
     * @param int $width Ancho del thumbnail
     * @param int $height Alto del thumbnail
     * @return array
     */
    public function createThumbnail($filename, $width = 150, $height = 150) {
        $filepath = $this->uploadDir . $filename;
        
        if (!file_exists($filepath)) {
            return ['success' => false, 'error' => 'Archivo no encontrado'];
        }
        
        $info = pathinfo($filename);
        $thumbFilename = $info['filename'] . '_thumb.' . $info['extension'];
        $thumbPath = $this->uploadDir . $thumbFilename;
        
        // Obtener tipo MIME
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        // Crear imagen fuente
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($filepath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($filepath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($filepath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($filepath);
                break;
            default:
                return ['success' => false, 'error' => 'Tipo de imagen no soportado'];
        }
        
        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);
        
        // Calcular dimensiones para crop cuadrado
        $size = min($origWidth, $origHeight);
        $x = ($origWidth - $size) / 2;
        $y = ($origHeight - $size) / 2;
        
        // Crear thumbnail
        $thumbImage = imagecreatetruecolor($width, $height);
        
        // Preservar transparencia
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumbImage, false);
            imagesavealpha($thumbImage, true);
        }
        
        imagecopyresampled(
            $thumbImage, $sourceImage,
            0, 0, $x, $y,
            $width, $height, $size, $size
        );
        
        // Guardar thumbnail
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($thumbImage, $thumbPath, 85);
                break;
            case 'image/png':
                imagepng($thumbImage, $thumbPath, 8);
                break;
            case 'image/gif':
                imagegif($thumbImage, $thumbPath);
                break;
            case 'image/webp':
                imagewebp($thumbImage, $thumbPath, 85);
                break;
        }
        
        imagedestroy($sourceImage);
        imagedestroy($thumbImage);
        
        return [
            'success' => true,
            'filename' => $thumbFilename,
            'path' => $thumbPath
        ];
    }
    
    /**
     * Obtener mensaje de error de subida
     * 
     * @param int $code Código de error
     * @return string
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
    
    /**
     * Validar imagen sin subirla
     * 
     * @param array $file El archivo de $_FILES
     * @return array
     */
    public function validate($file) {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['valid' => false, 'error' => 'No se seleccionó ningún archivo'];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => $this->getUploadError($file['error'])];
        }
        
        if ($file['size'] > $this->maxFileSize) {
            return ['valid' => false, 'error' => 'El archivo es demasiado grande'];
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['valid' => false, 'error' => 'Tipo de archivo no permitido'];
        }
        
        return ['valid' => true];
    }
}
