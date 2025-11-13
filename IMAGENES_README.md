# CMS Blog Personal - Sistema de Imágenes

## 🎉 Nuevas Funcionalidades Implementadas

### ✨ Características Agregadas

1. **Avatares de Usuario**
   - Los usuarios pueden subir su foto de perfil
   - Se muestra en el header cuando están autenticados
   - Se muestra junto a los posts que escriben
   - Página de perfil completa para gestionar avatar y datos

2. **Imágenes en Posts**
   - Los posts pueden tener imagen destacada
   - Se muestra en la lista de posts (página principal)
   - Se muestra en la vista individual del post
   - Upload desde el panel de administración

3. **Gestión de Imágenes**
   - Validación de tipo y tamaño (máx. 5MB)
   - Redimensionamiento automático (máx. 1920x1920)
   - Formatos soportados: JPG, PNG, GIF, WEBP
   - Almacenamiento seguro con .htaccess
   - Preview antes de subir

---

## 🚀 Instalación y Actualización

### Si es una instalación nueva:

1. **Importar la base de datos completa:**
   ```bash
   mysql -u root -p < database.sql
   ```
   Esto creará las tablas con las columnas de imágenes incluidas.

### Si ya tienes la base de datos instalada:

1. **Actualizar la base de datos existente:**
   ```bash
   mysql -u root -p < update_database.sql
   ```
   Esto agregará las columnas `avatar` y `image` sin perder datos.

2. **Verificar que se crearon las carpetas de uploads:**
   - `public/uploads/users/`
   - `public/uploads/posts/`
   
   Si no existen, se crearán automáticamente al subir la primera imagen.

---

## 📁 Estructura de Archivos Nuevos

```
ProyectoPHP/
├── src/
│   └── ImageUpload.php          # Clase para manejo de imágenes
├── public/
│   ├── profile.php              # Página de perfil de usuario
│   ├── uploads/                 # Carpeta de imágenes
│   │   ├── .htaccess           # Seguridad
│   │   ├── index.php           # Prevenir listado
│   │   ├── users/              # Avatares
│   │   └── posts/              # Imágenes de posts
│   └── admin/posts/
│       ├── create.php          # Actualizado con upload
│       └── edit.php            # Actualizado con upload
└── update_database.sql         # Script de actualización
```

---

## 🎨 Características Técnicas

### Clase ImageUpload (`src/ImageUpload.php`)

**Métodos principales:**
- `upload($file, $prefix)` - Sube y procesa imagen
- `delete($filename)` - Elimina imagen
- `resizeImage()` - Redimensiona manteniendo proporción
- `createThumbnail()` - Crea miniaturas
- `validate($file)` - Valida sin subir

**Seguridad:**
- Validación de tipo MIME real (no solo extensión)
- Límite de tamaño: 5MB
- Nombres únicos con timestamp
- Prevención de ejecución de scripts (.htaccess)

### Actualizaciones en Modelos

**User.php:**
- `updateAvatar($userId, $filename)`
- `getAvatar($userId)`
- `deleteAvatar($userId)`
- `updateProfile($userId, $data)`

**Post.php:**
- `getPostImage($postId)`
- `updatePostImage($postId, $filename)`
- `deletePostImage($postId)`
- `incrementViews($postId)` - Contador de vistas

---

## 🎯 Uso del Sistema

### Para Usuarios:

1. **Actualizar Avatar:**
   - Ir a "Mi Perfil" en el menú
   - Seleccionar imagen
   - Click en "Subir"

2. **Cambiar Información:**
   - Editar nombre completo y email
   - Cambiar contraseña

### Para Administradores/Autores:

1. **Crear Post con Imagen:**
   - Panel Admin → Posts → Crear Nuevo
   - Llenar título, descripción, contenido
   - En "Imagen Destacada" → Seleccionar archivo
   - Guardar

2. **Editar Post e Imagen:**
   - Panel Admin → Posts → Editar
   - Se muestra imagen actual (si existe)
   - Puede cambiar o eliminar imagen
   - Actualizar

---

## 🔒 Seguridad Implementada

1. **Prevención de Ejecución de Scripts:**
   ```apache
   # public/uploads/.htaccess
   <FilesMatch "\.(php|php3|php4|php5|phtml|pl|py|jsp|asp|sh|cgi)$">
       Order Allow,Deny
       Deny from all
   </FilesMatch>
   ```

2. **Validación de Tipos:**
   - Verificación de MIME type con `finfo_file()`
   - No confía solo en la extensión del archivo

3. **Límites:**
   - Tamaño máximo: 5MB
   - Dimensiones máximas: 1920x1920px
   - Solo formatos de imagen

4. **Prevención de Listado:**
   - `Options -Indexes` en .htaccess
   - `index.php` en carpeta uploads

---

## 🎨 Estilos CSS Agregados

### Avatares:
- `.user-avatar-header` - Avatar en header (35x35px)
- `.author-avatar` - Avatar en post completo (50x50px)
- `.author-avatar-small` - Avatar en tarjetas (30x30px)
- `.author-avatar-placeholder` - Placeholder con iniciales

### Imágenes de Posts:
- `.post-image` - Imagen en tarjeta (250px height)
- `.post-featured-image` - Imagen destacada en post completo (500px max-height)
- Efecto hover con zoom en tarjetas

---

## 📝 Notas Importantes

1. **Permisos de Carpetas:**
   Asegúrate que la carpeta `public/uploads/` tenga permisos de escritura:
   ```bash
   chmod 755 public/uploads/
   chmod 755 public/uploads/users/
   chmod 755 public/uploads/posts/
   ```

2. **Límite de Subida en PHP:**
   Si necesitas subir archivos más grandes, edita `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

3. **Imágenes Existentes:**
   Los posts creados antes de esta actualización simplemente no mostrarán imagen.
   Puedes editarlos desde el admin para agregar una.

4. **Backup:**
   Antes de ejecutar `update_database.sql`, haz backup de tu base de datos:
   ```bash
   mysqldump -u root -p cms_blog > backup_antes_imagenes.sql
   ```

---

## 🐛 Solución de Problemas

### "Error al subir imagen"
- Verifica permisos de carpeta uploads
- Revisa límite de PHP (upload_max_filesize)
- Asegúrate que el formato es válido

### "No se muestra la imagen"
- Verifica que existe en `public/uploads/posts/` o `public/uploads/users/`
- Revisa rutas relativas en el HTML
- Comprueba permisos de lectura

### "Error al crear thumbnail"
- Verifica que GD library está instalada: `php -m | grep gd`
- Instalar si falta: `sudo apt-get install php-gd` (Linux)

---

## 🎉 ¡Listo!

Tu CMS ahora tiene soporte completo para imágenes de usuario y posts. Los usuarios pueden personalizar sus perfiles y los posts se ven mucho más atractivos con imágenes destacadas.

**Prueba el sistema:**
1. Inicia sesión
2. Ve a "Mi Perfil" y sube tu avatar
3. Crea un nuevo post con imagen desde el panel admin
4. Visita la página principal y disfruta del diseño mejorado

---

**Desarrollado con ❤️ para el proyecto CMS Blog Personal**
