# CMS Blog Personal - Sistema de Gestión de Contenido

## 📋 Descripción del Proyecto

Sistema de gestión de contenido (CMS) desarrollado en PHP para un blog personal. Implementa conceptos avanzados de desarrollo web incluyendo POO, MVC, enrutamiento, autenticación segura, gestión de archivos y separación de responsabilidades.

## 🎯 Conceptos Implementados

### 1. **Enrutamiento**
- Sistema de enrutamiento personalizado en `app/Core/Router.php`
- Manejo de URLs amigables mediante expresiones regulares
- Soporta rutas GET y POST con parámetros dinámicos
- Ejemplo: `/post.php?id=5` se accede de forma limpia mediante el sistema

### 2. **Programación Orientada a Objetos (POO)**
El proyecto está completamente estructurado con clases y objetos:
- **Clase `User`** (`app/Models/User.php`): Gestión de usuarios con propiedades (id, username, password, email, role) y métodos para autenticación, registro, actualización de perfil
- **Clase `Post`** (`app/Models/Post.php`): CRUD completo de publicaciones con propiedades (id, title, content, image, category_id, author_id)
- **Clase `Comment`** (`app/Models/Comment.php`): Sistema de comentarios con relaciones entre usuarios y posts
- **Clase `Category`** (`app/Models/Category.php`): Organización de contenido por categorías
- **Clase `Database`** (`app/Models/Database.php`): Patrón Singleton para gestión centralizada de conexión PDO

### 3. **Base de Datos con PDO**
- Uso exclusivo de PHP Data Objects (PDO) para todas las operaciones de base de datos
- **Sentencias preparadas** en todas las consultas para prevención de SQL Injection
- Binding de parámetros con `bindValue()` y tipos específicos (PDO::PARAM_INT, PDO::PARAM_STR)
- Manejo de errores con excepciones PDOException
- Configuración segura con `PDO::ATTR_EMULATE_PREPARES => false`

### 4. **Autenticación y Sesiones Seguras**
- **`password_hash()`** con algoritmo BCRYPT para almacenamiento seguro de contraseñas
- **`password_verify()`** para validación de credenciales sin comparación directa
- Gestión de sesiones con `$_SESSION` para mantener estado del usuario
- Configuración segura de cookies de sesión:
  - `session.cookie_httponly = 1` (protección contra XSS)
  - `session.use_only_cookies = 1` (prevención de session fixation)
- Sistema de roles (admin, author, user) con control de permisos
- Middleware de autenticación en rutas protegidas

### 5. **Separación de Plantillas y Lógica (MVC)**
```
app/
├── Controllers/    → Lógica de negocio y flujo de aplicación
├── Models/        → Interacción con base de datos
└── Views/         → Presentación HTML (header, footer, sidebar)
```
- Vistas separadas en `app/Views/` con includes para header, footer y sidebar
- Controladores en `app/Controllers/` (AuthController, PostController, ProfileController, HomeController)
- Modelos en `app/Models/` para acceso a datos
- Archivos públicos en `public/` que utilizan el sistema MVC

### 6. **Subida y Gestión de Archivos**
- Clase `ImageUpload` (`app/Models/ImageUpload.php`) para procesamiento de archivos
- Validación de tipo MIME con `finfo_file()` para verificar tipos reales
- Validación de extensiones permitidas (jpg, jpeg, png, gif, webp)
- Límite de tamaño de archivo (5MB por defecto)
- Redimensionamiento automático de imágenes que exceden dimensiones máximas
- Nombres únicos con `uniqid()` y timestamp para evitar colisiones
- Gestión de uploads para posts (`uploads/posts/`) y usuarios (`uploads/users/`)
- Archivos `index.php` en carpetas de uploads para prevenir listado de directorios

### 7. **Validación y Saneamiento de Datos**
Implementación exhaustiva en todos los modelos:
- **`trim()`**: Eliminación de espacios en blanco en inicio y fin
- **`htmlspecialchars()`**: Prevención de XSS en salida de datos
- **`filter_var()`**: Validación de emails y otros tipos de datos
- Validaciones personalizadas:
  - Longitud mínima/máxima de campos
  - Formato de email
  - Complejidad de contraseñas
  - Existencia de registros duplicados
- Mensajes de error descriptivos y seguros

### 8. **Arquitectura MVC Mantenible**
- Autoloader PSR-4 para carga automática de clases
- Namespaces organizados (`App\Models`, `App\Controllers`, `App\Core`)
- Archivo de configuración centralizado (`config/config.php`)
- Helpers reutilizables en `app/helpers.php`
- Constantes globales para rutas y configuración
- Separación clara de responsabilidades por capas

## 🚀 Funcionalidades del Sistema

### Frontend (Usuarios Públicos y Registrados)

#### **Área Pública**
- ✅ **Visualización de posts**: Listado paginado de publicaciones (6 por página)
- ✅ **Lectura de posts completos**: Vista detallada con título, contenido, autor, fecha, categoría
- ✅ **Sistema de categorías**: Filtrado de posts por categoría con emojis personalizados
- ✅ **Sidebar dinámico**: Posts recientes, categorías con conteo
- ✅ **Diseño responsive**: Adaptado a móviles y escritorio
- ✅ **Avatares de autor**: Imágenes de perfil en posts (si están disponibles)

#### **Autenticación**
- ✅ **Registro de usuarios**: 
  - Validación de campos requeridos
  - Verificación de usuario/email únicos
  - Confirmación de contraseña
  - Hash seguro de contraseñas
- ✅ **Inicio de sesión**: 
  - Verificación de credenciales con password_verify
  - Control de cuentas activas/inactivas
  - Actualización de último acceso
  - Creación de sesión persistente
- ✅ **Cierre de sesión seguro**: Destrucción completa de sesión y cookies

#### **Perfil de Usuario**
- ✅ **Actualización de datos personales**: Nombre completo, email
- ✅ **Cambio de avatar**: Subida de imagen de perfil con validación y redimensionamiento
- ✅ **Cambio de contraseña**: Con verificación de contraseña actual
- ✅ **Visualización de información**: Username, rol, fecha de registro

#### **Gestión de Posts (Usuarios Autenticados)**
- ✅ **Crear publicaciones**: 
  - Editor de contenido HTML
  - Selección de categoría
  - Subida de imagen destacada
  - Estado publicado/borrador
- ✅ **Editar mis posts**: Modificación de publicaciones propias
- ✅ **Eliminar mis posts**: Con confirmación de seguridad
- ✅ **Visualizar mis posts**: Listado personal de publicaciones

#### **Sistema de Comentarios**
- ✅ **Crear comentarios**: En posts publicados (usuarios autenticados)
- ✅ **Editar comentarios propios**: Modificación del contenido
- ✅ **Eliminar comentarios propios**: Con confirmación
- ✅ **Visualización con avatares**: Indicadores de rol (admin/author badge)

### Panel de Administración (Solo Admin)

Acceso: `/public/admin/index.php`

#### **Dashboard Administrativo**
- ✅ **Estadísticas generales**:
  - Total de usuarios
  - Total de posts
  - Total de categorías
  - Total de comentarios
- ✅ **Actividad reciente**: Posts y usuarios recientes
- ✅ **Interfaz profesional**: Diseño moderno con sidebar navegable

#### **Gestión de Usuarios**
- ✅ **Listar usuarios**: Tabla con todos los usuarios del sistema
- ✅ **Crear usuarios**: Formulario completo con validación
- ✅ **Editar usuarios**: Modificación de datos, rol y estado
- ✅ **Eliminar usuarios**: Con confirmación (protección para admin actual)
- ✅ **Ver detalles**: Vista completa de información de usuario
- ✅ **Gestión de roles**: admin, author, user
- ✅ **Activar/desactivar cuentas**: Control de acceso

#### **Gestión de Posts**
- ✅ **Listar todos los posts**: Vista administrativa completa
- ✅ **Crear posts administrativos**: Como cualquier autor
- ✅ **Editar cualquier post**: Sin restricción de autoría
- ✅ **Eliminar cualquier post**: Control total sobre contenido
- ✅ **Cambiar estado**: Publicar/despublicar posts
- ✅ **Estadísticas**: Visualizaciones, autor, fecha

#### **Gestión de Categorías**
- ✅ **Listar categorías**: Con conteo de posts asociados
- ✅ **Crear categorías**: Nombre, slug y descripción
- ✅ **Editar categorías**: Modificación de datos
- ✅ **Eliminar categorías**: Con verificación de posts asociados

## 📦 Estructura del Proyecto

```
ProyectoPHP/
│
├── app/                          # Capa de aplicación (MVC)
│   ├── Controllers/              # Controladores
│   │   ├── AuthController.php    # Autenticación y registro
│   │   ├── HomeController.php    # Página principal
│   │   ├── PostController.php    # Gestión de posts
│   │   └── ProfileController.php # Perfil de usuario
│   │
│   ├── Models/                   # Modelos de datos
│   │   ├── Database.php          # Singleton de conexión PDO
│   │   ├── User.php              # Gestión de usuarios
│   │   ├── Post.php              # Gestión de publicaciones
│   │   ├── Comment.php           # Gestión de comentarios
│   │   ├── Category.php          # Gestión de categorías
│   │   └── ImageUpload.php       # Procesamiento de imágenes
│   │
│   ├── Views/                    # Vistas (plantillas HTML)
│   │   ├── header.php            # Encabezado público
│   │   ├── footer.php            # Pie de página público
│   │   ├── sidebar.php           # Barra lateral
│   │   └── admin/                # Plantillas administrativas
│   │       ├── header.php
│   │       └── footer.php
│   │
│   ├── Core/                     # Núcleo del framework
│   │   ├── Router.php            # Sistema de enrutamiento
│   │   └── Controller.php        # Controlador base
│   │
│   └── helpers.php               # Funciones auxiliares
│
├── config/                       # Configuración
│   └── config.php                # Constantes y conexión DB
│
├── public/                       # Carpeta pública (DocumentRoot)
│   ├── index.php                 # Página principal
│   ├── login.php                 # Inicio de sesión
│   ├── register.php              # Registro
│   ├── profile.php               # Perfil de usuario
│   ├── post.php                  # Vista de post individual
│   ├── category.php              # Posts por categoría
│   ├── create-post.php           # Crear publicación
│   ├── edit-post.php             # Editar publicación
│   ├── delete-post.php           # Eliminar publicación
│   ├── my-posts.php              # Mis publicaciones
│   ├── comment_create.php        # Crear comentario
│   ├── comment_edit.php          # Editar comentario
│   ├── comment_delete.php        # Eliminar comentario
│   ├── logout.php                # Cerrar sesión
│   │
│   ├── admin/                    # Panel administrativo
│   │   ├── index.php             # Dashboard
│   │   ├── auth.php              # Middleware de autenticación
│   │   ├── users/                # CRUD de usuarios
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   ├── edit.php
│   │   │   ├── delete.php
│   │   │   └── view.php
│   │   ├── posts/                # CRUD de posts
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   ├── edit.php
│   │   │   └── delete.php
│   │   └── categories/           # CRUD de categorías
│   │       ├── index.php
│   │       ├── create.php
│   │       ├── edit.php
│   │       └── delete.php
│   │
│   ├── css/                      # Hojas de estilo
│   │   ├── style.css             # Estilos principales
│   │   ├── auth.css              # Login/registro
│   │   ├── profile.css           # Perfil de usuario
│   │   ├── comments.css          # Sistema de comentarios
│   │   ├── admin.css             # Panel admin
│   │   └── admin-professional.css # Estilos admin mejorados
│   │
│   └── uploads/                  # Archivos subidos
│       ├── posts/                # Imágenes de posts
│       │   └── index.php         # Protección de directorio
│       └── users/                # Avatares de usuarios
│           └── index.php         # Protección de directorio
│
├── cms_blog_COMPLETO.sql         # Base de datos completa con datos
├── setup_comments.php            # Script de instalación de comentarios
├── verificar_sistema.php         # Verificación de configuración
└── verificar_uploads.php         # Verificación de permisos
```

## 🗄️ Base de Datos

### Tablas Principales

#### **users**
```sql
- id (INT, PK, AUTO_INCREMENT)
- username (VARCHAR, UNIQUE)
- email (VARCHAR, UNIQUE)
- password (VARCHAR) -- Hash BCRYPT
- full_name (VARCHAR)
- avatar (VARCHAR, NULLABLE)
- role (ENUM: admin, author, user)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
- last_login (TIMESTAMP, NULLABLE)
- active (BOOLEAN)
```

#### **posts**
```sql
- id (INT, PK, AUTO_INCREMENT)
- title (VARCHAR)
- slug (VARCHAR, UNIQUE)
- description (TEXT)
- content (LONGTEXT)
- image (VARCHAR, NULLABLE)
- category_id (INT, FK -> categories)
- author_id (INT, FK -> users)
- published (BOOLEAN)
- views (INT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### **categories**
```sql
- id (INT, PK, AUTO_INCREMENT)
- name (VARCHAR, UNIQUE)
- slug (VARCHAR, UNIQUE)
- description (TEXT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

#### **comments**
```sql
- id (INT, PK, AUTO_INCREMENT)
- post_id (INT, FK -> posts)
- user_id (INT, FK -> users)
- content (TEXT)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)
```

## 🔐 Credenciales de Acceso

### Usuario Administrador
- **Usuario**: `admin`
- **Contraseña**: `password`
- **Email**: admin@cmsblog.com
- **Rol**: Administrador
- **Permisos**: Acceso completo al panel administrativo

### Usuario de Prueba (Autor)
- **Usuario**: `autor1`
- **Contraseña**: `password`
- **Email**: autor1@cmsblog.com
- **Rol**: Author
- **Permisos**: Crear, editar y eliminar sus propios posts

### Nota de Seguridad
⚠️ **IMPORTANTE**: Estas contraseñas son para desarrollo/pruebas. En producción, cambiar todas las credenciales.

El hash almacenado en la base de datos es:
```
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```
Que corresponde a la contraseña: `password`

## 🛠️ Instalación y Configuración

### Requisitos Previos
- PHP 7.4 o superior
- MySQL 5.7+ o MariaDB 10.4+
- Servidor web (Apache con mod_rewrite o similar)
- Extensiones PHP requeridas:
  - PDO
  - pdo_mysql
  - mbstring
  - gd o imagick (para procesamiento de imágenes)
  - fileinfo

### Pasos de Instalación

1. **Clonar o descargar el proyecto**
   ```bash
   cd c:\Users\marco\OneDrive\Escritorio\PHP
   ```

2. **Importar Base de Datos**
   
   El archivo `cms_blog_COMPLETO.sql` funciona así:
   - 🔴 **BORRA** la base de datos `cms_blog` si existe
   - 🟢 **CREA** la base de datos desde cero
   - 📦 **INSERTA** todos los datos (usuarios, posts, categorías, comentarios)
   
   **Desde Terminal:**
   ```bash
   mysql -u root -p < cms_blog_COMPLETO.sql
   ```
   
   **Desde phpMyAdmin:**
   - Abrir http://localhost/phpmyadmin
   - Click en "Importar"
   - Seleccionar `cms_blog_COMPLETO.sql`
   - Click en "Continuar"
   
   ⚠️ **IMPORTANTE**: Este archivo se ejecuta COMPLETO cada vez. Cualquier cambio que hagas en la base de datos se PERDERÁ al reimportar.

3. **Configurar conexión a base de datos**
   
   Editar `config/config.php` si es necesario:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_PORT', '3306');
   define('DB_NAME', 'cms_blog');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Configurar permisos de carpetas**
   
   Asegurar que estas carpetas tengan permisos de escritura:
   ```bash
   # En Windows (PowerShell como admin)
   icacls "public\uploads" /grant Users:F /T
   
   # En Linux/Mac
   chmod -R 777 public/uploads
   ```

5. **Configurar URL base**
   
   En `config/config.php`, ajustar según tu entorno:
   ```php
   // Para XAMPP en puerto 80
   define('BASE_URL', 'http://localhost/ProyectoPHP/public');
   
   // Para servidor de desarrollo PHP en puerto 3000
   define('BASE_URL', 'http://localhost:3000/public');
   ```

6. **Verificar instalación**
   
   Acceder a: http://localhost:3000/public/verificar_sistema.php
   
   Este script verificará:
   - ✅ Conexión a base de datos
   - ✅ Extensiones PHP requeridas
   - ✅ Permisos de carpetas
   - ✅ Configuración de sesiones
   - ✅ Integridad de archivos

7. **Iniciar el servidor con PHP Server Extension (VS Code)**
   
   **RECOMENDADO:**
   - Abrir el proyecto en VS Code
   - Click derecho en `public/index.php`
   - Seleccionar "PHP Server: Serve project"
   - O presionar el botón "Go Live" en la barra inferior
   
   El servidor se iniciará automáticamente en: `http://localhost:3000/ProyectoPHP/public`
   
   **Alternativa manual (NO recomendado):**
   ```bash
   cd ProyectoPHP
   php -S localhost:3000
   ```
   ⚠️ Nota: El método manual puede tener problemas con rutas CSS

8. **Acceder a la aplicación**
   - **Frontend**: http://localhost:3000/ProyectoPHP/public/index.php
   - **Login**: http://localhost:3000/ProyectoPHP/public/login.php
   - **Panel Admin**: http://localhost:3000/ProyectoPHP/public/admin/index.php

---

## 🔐 Credenciales de Acceso

El sistema incluye usuarios de prueba con diferentes roles:

### Administrador
```
Usuario: admin
Contraseña: password
```
- ✅ Acceso completo al panel de administración
- ✅ Gestión de usuarios, posts, categorías y comentarios
- ✅ Permisos para crear, editar y eliminar cualquier contenido

### Autor
```
Usuario: autor1
Contraseña: password
```
- ✅ Puede crear y gestionar sus propios posts
- ✅ Puede comentar en cualquier publicación
- ✅ Acceso limitado al panel admin (solo sus posts)

### Usuario Normal
```
Usuario: usuario1
Contraseña: password
```
- ✅ Puede leer posts y comentar
- ✅ Puede gestionar su perfil
- ❌ No puede crear posts

---

## 💾 Sistema de Persistencia de Datos

### ⚠️ Cómo Funciona la Base de Datos

Este proyecto usa un sistema similar al de gestión hotelera:

1. **El archivo `cms_blog_COMPLETO.sql` contiene TODO:**
   - Estructura de tablas
   - Datos iniciales (usuarios, posts, categorías)
   - Se ejecuta COMPLETO cada vez

2. **Al reimportar el SQL:**
   - 🔴 Se BORRA la base de datos completa
   - 🟢 Se CREA desde cero
   - ⚠️ PIERDES todos los cambios que hayas hecho

### 📦 Cómo Hacer Backup de Tus Cambios

**Método 1: Desde Terminal (Recomendado)**
```bash
cd "c:\Users\marco\OneDrive\Escritorio\PHP\ProyectoPHP"
mysqldump -u root -p cms_blog > cms_blog_COMPLETO.sql
```

**Método 2: Desde phpMyAdmin**
1. Abrir http://localhost/phpmyadmin
2. Seleccionar base de datos `cms_blog`
3. Click en "Exportar"
4. Seleccionar "Método rápido" y formato SQL
5. Click en "Continuar"
6. Guardar como `cms_blog_COMPLETO.sql` (reemplazar el existente)

### 🔄 Cuándo Hacer Backup

Haz backup ANTES de:
- ✅ Entregar el proyecto
- ✅ Cerrar el proyecto por el día
- ✅ Hacer cambios importantes
- ✅ Probar algo que pueda romper la BD

**Si haces cambios importantes** (crear posts, usuarios, categorías):
```bash
# Guardar cambios
mysqldump -u root -p cms_blog > cms_blog_COMPLETO.sql
```

---

## 📚 Uso del Sistema

### Para Usuarios

1. **Registrarse**: Ir a "Registrarse" en el menú
2. **Iniciar sesión**: Usar credenciales creadas o las de prueba
3. **Explorar posts**: Navegar por publicaciones en la página principal
4. **Crear post**: Click en "Nueva Publicación" (usuarios autenticados)
5. **Comentar**: Escribir comentarios en posts publicados
6. **Gestionar perfil**: Actualizar datos y avatar en "Mi Perfil"

### Para Administradores

1. **Acceder al panel**: http://localhost:3000/public/admin/
2. **Ver estadísticas**: Dashboard con resumen del sistema
3. **Gestionar usuarios**:
   - Crear nuevos usuarios con roles específicos
   - Editar información y roles
   - Activar/desactivar cuentas
   - Ver actividad de usuarios
4. **Gestionar contenido**:
   - Aprobar/rechazar posts
   - Editar cualquier publicación
   - Organizar categorías
5. **Moderación**:
   - Revisar comentarios (funcionalidad extensible)
   - Gestionar reportes (funcionalidad extensible)

## 🔒 Seguridad Implementada

### Autenticación
- ✅ Hash de contraseñas con BCRYPT (cost factor 10)
- ✅ Sin almacenamiento de contraseñas en texto plano
- ✅ Verificación con `password_verify()`
- ✅ Control de sesiones con regeneración de ID

### Prevención de Ataques
- ✅ **SQL Injection**: Sentencias preparadas PDO en todas las consultas
- ✅ **XSS**: `htmlspecialchars()` en todas las salidas de usuario
- ✅ **CSRF**: Validación de origen y sesión en formularios críticos
- ✅ **Session Fixation**: Configuración segura de cookies
- ✅ **Directory Traversal**: Archivos `index.php` en carpetas sensibles
- ✅ **File Upload Attacks**: Validación estricta de tipo MIME y extensiones

### Validación de Datos
- ✅ Sanitización con `trim()` en entradas
- ✅ Validación de tipos con `filter_var()`
- ✅ Verificación de longitudes min/max
- ✅ Validación de formatos (email, username, etc.)
- ✅ Comprobación de datos requeridos

### Control de Acceso
- ✅ Middleware de autenticación en rutas protegidas
- ✅ Sistema de roles (RBAC básico)
- ✅ Verificación de permisos antes de acciones
- ✅ Redirecciones automáticas para acceso no autorizado

## 🎨 Características de Diseño

- **Diseño responsive**: Adaptado a móviles, tablets y escritorio
- **Sistema de grid moderno**: Flexbox y CSS Grid
- **Tipografía legible**: Fuentes web optimizadas
- **Paleta de colores profesional**: Azules, verdes y grises
- **Animaciones sutiles**: Transiciones suaves
- **Iconografía consistente**: Emojis para categorías
- **Feedback visual**: Mensajes de éxito/error estilizados
- **Accesibilidad**: Contraste adecuado y navegación por teclado

## 🧪 Testing y Verificación

### Scripts de Verificación Incluidos

1. **verificar_sistema.php**: Diagnóstico completo del sistema
2. **verificar_uploads.php**: Verificación de permisos de subida
3. **setup_comments.php**: Instalación del sistema de comentarios

### Testing Manual Recomendado

- [ ] Registro de nuevo usuario
- [ ] Login con credenciales correctas/incorrectas
- [ ] Creación de post con imagen
- [ ] Edición de post propio
- [ ] Intento de editar post ajeno (debe fallar para user)
- [ ] Creación de comentario
- [ ] Cambio de avatar
- [ ] Cambio de contraseña
- [ ] Acceso al panel admin sin permisos (debe redirigir)
- [ ] CRUD completo desde panel admin

## 🚀 Extensiones Futuras Sugeridas

- [ ] **API RESTful**: Endpoints JSON para consumo externo
- [ ] **Sistema de likes/favoritos**: Interacción adicional con posts
- [ ] **Búsqueda avanzada**: Full-text search con filtros
- [ ] **Notificaciones**: Sistema de alertas para comentarios/respuestas
- [ ] **Editor WYSIWYG**: TinyMCE o similar para contenido rico
- [ ] **Moderación de comentarios**: Panel de aprobación admin
- [ ] **Etiquetas/Tags**: Sistema adicional de categorización
- [ ] **Vistas/Analytics**: Estadísticas detalladas de visualizaciones
- [ ] **Exportación**: Backup de contenido en diferentes formatos
- [ ] **Multiidioma**: Soporte i18n para internacionalización

## 💾 Persistencia de Datos - Sistema AUTO-BACKUP ✨

### 🎯 ¡SIN BACKUPS MANUALES!

Este proyecto incluye un **sistema de backup automático** que guarda tus cambios sin intervención manual.

### ✅ Funcionamiento Automático

El sistema hace backup automáticamente en estos casos:

**Posts:**
- ✅ Al crear un post nuevo
- ✅ Al editar un post existente
- ✅ Al eliminar un post

**Usuarios:**
- ✅ Al registrar un nuevo usuario

**Comentarios:**
- ✅ Al crear un comentario nuevo

**Categorías:**
- ✅ Al crear, editar o eliminar una categoría

### 📁 Archivo de Backup

El backup se guarda automáticamente en:
```
ProyectoPHP/cms_blog_COMPLETO.sql
```

### ⚙️ Configuración

- **Intervalo mínimo:** 5 minutos entre backups
- **Ejecución:** En segundo plano (no interrumpe operaciones)
- **Actualización:** Automática tras cada cambio importante

### 🛠️ Backup Manual (Opcional)

Si necesitas forzar un backup inmediato:

```bash
php backup_database.php
```

### 💡 Ventajas

- ✅ **Cero intervención manual:** Todo se guarda automáticamente
- ✅ **Protección de datos:** Backup después de cada operación importante
- ✅ **No bloquea el sistema:** Se ejecuta en segundo plano
- ✅ **Optimizado:** Mínimo 5 minutos entre backups para evitar sobrecarga

📖 **Implementación técnica:** Ver `app/Core/AutoBackup.php`  
📖 **Más detalles:** Ver archivo `INSTRUCCIONES_PERSISTENCIA.md`

---

## 📖 Conceptos de Programación Aplicados

### Patrones de Diseño
- **Singleton**: Clase Database con instancia única
- **MVC**: Separación clara de Modelo-Vista-Controlador
- **Repository**: Modelos como repositorios de datos
- **Factory**: Creación dinámica de controladores en Router

### Principios SOLID (aplicados parcialmente)
- **SRP**: Cada clase tiene una responsabilidad única
- **OCP**: Extensibilidad mediante herencia (Controller base)
- **DIP**: Dependencia de abstracciones (PDO interface)

### Buenas Prácticas
- ✅ Código comentado y documentado
- ✅ Nombres descriptivos de variables y funciones
- ✅ Indentación y formato consistentes
- ✅ Separación de concerns
- ✅ DRY (Don't Repeat Yourself)
- ✅ Configuración centralizada
- ✅ Manejo de errores con try-catch
- ✅ Logging de errores críticos

## 📝 Licencia

Este proyecto es de uso educativo desarrollado como parte del curso de PHP. Libre de usar y modificar con fines de aprendizaje.

## 👤 Autor

**Marco** - Proyecto PHP - Diciembre 2025

---

## 📞 Soporte y Problemas Comunes

### Error: "Error de conexión a la base de datos"
- Verificar que MySQL esté activo
- Comprobar credenciales en `config/config.php`
- Asegurar que la base de datos `cms_blog` exista

### Error: "No se puede subir imagen"
- Verificar permisos de carpeta `uploads/`
- Comprobar directiva `upload_max_filesize` en php.ini
- Verificar extensión GD o Imagick activa

### Error: "404 - Página no encontrada"
- Verificar configuración de `BASE_URL` en config.php
- Comprobar que mod_rewrite esté activo (Apache)
- Revisar permisos de archivos

### Posts no se muestran
- Verificar que posts tengan `published = 1`
- Comprobar que exista un autor válido
- Revisar errores en consola del navegador

---

**¡Sistema listo para usar! 🎉**

Para cualquier consulta sobre el código o funcionamiento, revisar los comentarios inline en cada archivo PHP.
