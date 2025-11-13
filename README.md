# CMS Blog Personal

Sistema de gestión de contenido (CMS) para un blog personal desarrollado en PHP con arquitectura orientada a objetos.

## 🚀 Características

- ✅ **Diseño moderno y responsive** - Interfaz elegante y colorida
- ✅ **Sistema de paginación** - Máximo 6 posts por página
- ✅ **Categorías dinámicas** - Organización de contenido por categorías
- ✅ **Arquitectura POO** - Código limpio y mantenible
- ✅ **PDO con sentencias preparadas** - Seguridad contra SQL Injection
- ✅ **Diseño CSS personalizado** - Sin frameworks externos

## 📁 Estructura del Proyecto

```
ProyectoPHP/
├── config/
│   └── config.php              # Configuración de la aplicación
├── public/
│   ├── css/
│   │   └── style.css           # Estilos CSS personalizados
│   ├── uploads/                # Directorio para archivos subidos
│   └── index.php               # Punto de entrada principal
├── src/
│   ├── Database.php            # Clase de conexión PDO (Singleton)
│   ├── Post.php                # Clase para gestión de posts
│   └── Category.php            # Clase para gestión de categorías
├── views/
│   ├── header.php              # Plantilla del header
│   ├── footer.php              # Plantilla del footer
│   └── sidebar.php             # Plantilla del sidebar
├── database.sql                # Script SQL de la base de datos
└── README.md                   # Este archivo
```

## 🛠️ Instalación

### Requisitos previos

- PHP 7.4 o superior
- MySQL 5.7 o superior / MariaDB
- Servidor web (Apache, Nginx, o PHP built-in server)

### Pasos de instalación

1. **Clonar o descargar el proyecto** en tu servidor local

2. **Crear la base de datos**
   ```bash
   # Opción 1: Desde línea de comandos
   mysql -u root -p < database.sql
   
   # Opción 2: Importar desde phpMyAdmin
   # Abre phpMyAdmin, crea una base de datos llamada 'cms_blog' 
   # e importa el archivo database.sql
   ```

3. **Configurar la conexión a la base de datos**
   
   Edita el archivo `config/config.php` y ajusta estos valores:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'cms_blog');
   define('DB_USER', 'root');        // Tu usuario de MySQL
   define('DB_PASS', '');            // Tu contraseña de MySQL
   ```

4. **Configurar la URL base**
   
   En `config/config.php`, ajusta la URL según tu entorno:
   ```php
   // Si usas XAMPP:
   define('BASE_URL', 'http://localhost/ProyectoPHP/public');
   
   // Si usas otro puerto:
   define('BASE_URL', 'http://localhost:8080/ProyectoPHP/public');
   ```

5. **Iniciar el servidor**

   **Opción A: Usando XAMPP/WAMP**
   - Coloca el proyecto en `htdocs/` (XAMPP) o `www/` (WAMP)
   - Accede a: `http://localhost/ProyectoPHP/public`

   **Opción B: Usando el servidor integrado de PHP**
   ```bash
   cd ProyectoPHP/public
   php -S localhost:8000
   ```
   - Accede a: `http://localhost:8000`

## 👤 Credenciales de ejemplo

El script SQL incluye datos de prueba:

**Usuario Administrador:**
- Username: `admin`
- Email: `admin@cmsblog.com`
- Password: `admin123`

**Usuario Autor:**
- Username: `autor1`
- Email: `autor1@cmsblog.com`
- Password: `admin123`

## 🎨 Características de Diseño

- **Paleta de colores moderna** con gradientes vibrantes
- **Animaciones suaves** en hover y transiciones
- **Tipografía elegante** con Google Fonts (Inter & Playfair Display)
- **Tarjetas de posts** con efectos visuales atractivos
- **Sistema de categorías** con contadores dinámicos
- **Paginación intuitiva** con diseño moderno
- **100% Responsive** - Se adapta a móviles, tablets y desktop

## 🔐 Conceptos Implementados

### Programación Orientada a Objetos (POO)
- ✅ Clases `Database`, `Post`, `Category`
- ✅ Patrón Singleton para la conexión a BD
- ✅ Encapsulación y métodos públicos/privados
- ✅ Namespace `App` para organización

### Seguridad
- ✅ PDO con sentencias preparadas
- ✅ Binding de parámetros para prevenir SQL Injection
- ✅ `htmlspecialchars()` para prevenir XSS
- ✅ Validación de entrada de datos

### Arquitectura
- ✅ Separación de lógica y presentación (MVC básico)
- ✅ Plantillas reutilizables (header, footer, sidebar)
- ✅ Autoload de clases con `spl_autoload_register`
- ✅ Archivo de configuración centralizado

### Base de Datos
- ✅ Relaciones entre tablas (FOREIGN KEY)
- ✅ Índices para optimización
- ✅ Campos TIMESTAMP para auditoría
- ✅ Datos de ejemplo incluidos

## 📝 Próximas Funcionalidades

- 🔜 Sistema de autenticación y sesiones
- 🔜 Panel de administración (CRUD de posts)
- 🔜 Subida y gestión de imágenes
- 🔜 Sistema de comentarios
- 🔜 Búsqueda de posts
- 🔜 Vista individual de post
- 🔜 Editor WYSIWYG para contenido
- 🔜 Sistema de roles y permisos

## 🤝 Contribución

Este es un proyecto educativo. Si encuentras errores o tienes sugerencias, siéntete libre de crear un issue o pull request.

## 📄 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 🎓 Autor

Desarrollado como proyecto de aprendizaje de PHP, POO, PDO y desarrollo web full-stack.

---

**¡Disfruta construyendo tu blog personal! 🚀**