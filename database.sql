-- =====================================================
-- CMS Blog Personal - Script de Base de Datos
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS cms_blog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cms_blog;

-- =====================================================
-- Tabla: users (Usuarios del sistema)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar VARCHAR(255) DEFAULT NULL,
    role ENUM('admin', 'author', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    active TINYINT(1) DEFAULT 1,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: categories (Categorías de posts)
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: posts (Publicaciones del blog)
-- =====================================================
CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    content LONGTEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    category_id INT,
    author_id INT NOT NULL,
    published TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_author (author_id),
    INDEX idx_published (published),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Datos de ejemplo
-- =====================================================

-- Insertar usuario administrador de ejemplo
-- Contraseña: admin123 (hash generado con password_hash())
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@cmsblog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin'),
('autor1', 'autor1@cmsblog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Juan Pérez', 'author');

-- Insertar categorías de ejemplo
INSERT INTO categories (name, slug, description) VALUES
('Tecnología', 'tecnologia', 'Artículos sobre tecnología, programación y desarrollo web'),
('Diseño Web', 'diseno-web', 'Tendencias y tutoriales de diseño web moderno'),
('Programación', 'programacion', 'Guías y recursos de programación en diversos lenguajes'),
('SEO', 'seo', 'Estrategias y consejos de optimización para motores de búsqueda'),
('Marketing Digital', 'marketing-digital', 'Contenido sobre marketing en redes sociales y estrategias digitales'),
('Inteligencia Artificial', 'inteligencia-artificial', 'Últimas novedades en IA y machine learning');

-- Insertar posts de ejemplo
INSERT INTO posts (title, slug, description, content, category_id, author_id, published) VALUES
(
    'Introducción a PHP 8: Nuevas características',
    'introduccion-php-8-nuevas-caracteristicas',
    'Descubre las novedades más importantes que trae PHP 8 y cómo pueden mejorar tu código.',
    '<p>PHP 8 es una versión revolucionaria del lenguaje que introduce características modernas como <strong>JIT compilation</strong>, <strong>named arguments</strong>, <strong>union types</strong> y mucho más.</p><p>En este artículo exploraremos cada una de estas características con ejemplos prácticos que te ayudarán a aprovechar al máximo esta nueva versión.</p><p>El compilador JIT (Just-In-Time) puede mejorar significativamente el rendimiento de aplicaciones específicas, especialmente aquellas con cálculos intensivos.</p>',
    3,
    2,
    1
),
(
    'Diseño Responsive: Guía completa 2025',
    'diseno-responsive-guia-completa-2025',
    'Aprende las mejores prácticas para crear sitios web que se adapten perfectamente a cualquier dispositivo.',
    '<p>El diseño responsive es fundamental en el desarrollo web moderno. Con la diversidad de dispositivos disponibles hoy en día, es crucial que tu sitio web se vea perfecto en todos ellos.</p><p>En esta guía cubriremos conceptos como <strong>flexbox</strong>, <strong>CSS Grid</strong>, <strong>media queries</strong> y las últimas técnicas de diseño adaptativo.</p><p>También veremos frameworks modernos y herramientas que facilitan el proceso de creación de interfaces responsive.</p>',
    2,
    2,
    1
),
(
    'SEO en 2025: Lo que necesitas saber',
    'seo-2025-lo-que-necesitas-saber',
    'Estrategias actualizadas de SEO para mejorar el posicionamiento de tu sitio web en los motores de búsqueda.',
    '<p>El SEO ha evolucionado significativamente. Ya no se trata solo de palabras clave, sino de proporcionar valor real a los usuarios.</p><p>Google premia el contenido de calidad, la velocidad del sitio, la experiencia del usuario y la adaptabilidad móvil.</p><p>Aprenderás sobre <strong>Core Web Vitals</strong>, <strong>schema markup</strong>, <strong>optimización de imágenes</strong> y las últimas tendencias en link building.</p>',
    4,
    2,
    1
),
(
    'JavaScript ES2025: Novedades imprescindibles',
    'javascript-es2025-novedades-imprescindibles',
    'Explora las últimas características de JavaScript que harán tu código más limpio y eficiente.',
    '<p>JavaScript continúa evolucionando con cada nueva especificación ECMAScript. ES2025 trae características innovadoras que mejorarán tu productividad.</p><p>Descubre nuevas funcionalidades como <strong>temporal API</strong>, mejoras en <strong>pattern matching</strong>, y optimizaciones en el manejo de promesas.</p><p>Incluye ejemplos prácticos y casos de uso reales para que puedas implementar estas novedades en tus proyectos inmediatamente.</p>',
    3,
    2,
    1
),
(
    'Marketing Digital: Tendencias actuales',
    'marketing-digital-tendencias-actuales',
    'Las estrategias de marketing digital más efectivas para hacer crecer tu negocio online.',
    '<p>El marketing digital está en constante cambio. Las redes sociales, el contenido en video y la personalización son más importantes que nunca.</p><p>Aprende sobre <strong>marketing de influencers</strong>, <strong>automatización</strong>, <strong>email marketing</strong> y cómo crear campañas que realmente conviertan.</p><p>También exploraremos herramientas de análisis y métricas clave para medir el éxito de tus campañas.</p>',
    5,
    2,
    1
),
(
    'Inteligencia Artificial en el desarrollo web',
    'inteligencia-artificial-desarrollo-web',
    'Cómo la IA está transformando la forma en que desarrollamos aplicaciones web modernas.',
    '<p>La Inteligencia Artificial ya no es cosa del futuro, está aquí y está cambiando el desarrollo web.</p><p>Desde chatbots inteligentes hasta sistemas de recomendación personalizados, la IA ofrece infinitas posibilidades.</p><p>Descubre librerías como <strong>TensorFlow.js</strong>, servicios de IA en la nube, y cómo integrar modelos de aprendizaje automático en tus aplicaciones web.</p>',
    6,
    2,
    1
),
(
    'CSS Grid: Domina los layouts modernos',
    'css-grid-domina-layouts-modernos',
    'Tutorial completo sobre CSS Grid para crear diseños complejos de forma sencilla y eficiente.',
    '<p>CSS Grid es el sistema de layout más poderoso disponible en CSS. Permite crear diseños bidimensionales complejos con código simple y limpio.</p><p>Aprenderás sobre <strong>grid-template-areas</strong>, <strong>grid-gap</strong>, <strong>auto-fit</strong> y <strong>auto-fill</strong>, y cómo combinar Grid con Flexbox.</p><p>Incluye ejemplos prácticos de layouts reales como galerías de imágenes, dashboards y estructuras de páginas completas.</p>',
    2,
    2,
    1
),
(
    'Seguridad web: Protege tu aplicación PHP',
    'seguridad-web-protege-aplicacion-php',
    'Guía esencial de seguridad para proteger tus aplicaciones PHP contra vulnerabilidades comunes.',
    '<p>La seguridad es fundamental en cualquier aplicación web. Aprende a proteger tu código PHP contra ataques comunes.</p><p>Cubriremos <strong>inyección SQL</strong>, <strong>XSS</strong>, <strong>CSRF</strong>, <strong>validación de datos</strong>, <strong>sanitización</strong> y mejores prácticas de autenticación.</p><p>También veremos herramientas para detectar vulnerabilidades y cómo implementar capas de seguridad adicionales en tu aplicación.</p>',
    3,
    2,
    1
);

-- =====================================================
-- Consultas útiles para verificar los datos
-- =====================================================

-- Ver todos los posts con sus categorías
-- SELECT p.id, p.title, c.name as category, p.created_at 
-- FROM posts p 
-- LEFT JOIN categories c ON p.category_id = c.id 
-- ORDER BY p.created_at DESC;

-- Contar posts por categoría
-- SELECT c.name, COUNT(p.id) as total_posts 
-- FROM categories c 
-- LEFT JOIN posts p ON c.id = p.category_id 
-- GROUP BY c.id;
