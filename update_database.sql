-- =====================================================
-- Script de actualización para agregar soporte de imágenes
-- Ejecutar SOLO si la base de datos ya existe
-- =====================================================

USE cms_blog;

-- Agregar columna avatar a la tabla users
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) DEFAULT NULL AFTER full_name;

-- Agregar columna image a la tabla posts  
ALTER TABLE posts ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER content;

-- Verificar las actualizaciones
SELECT 'Actualizacion completada. Verificando columnas...' AS mensaje;
DESCRIBE users;
DESCRIBE posts;
