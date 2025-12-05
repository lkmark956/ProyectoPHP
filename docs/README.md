# Diagramas de Clases - CMS Blog PHP

## Documentacion PlantUML

Este directorio contiene los diagramas de clases del sistema CMS Blog PHP en formato PlantUML.

### Archivos

1. **diagrama-clases-inicial.puml** - Diagrama de clases inicial (version completa con anotaciones)
2. **diagrama-clases-final.puml** - Diagrama de clases final (version simplificada sin colores)

### Como Visualizar

#### Opcion 1: VS Code con extension PlantUML

1. Instala la extension "PlantUML" en VS Code
2. Instala Graphviz: https://graphviz.org/download/
3. Abre cualquier archivo .puml
4. Presiona `Alt + D` para ver preview
5. Click derecho -> "Export Current Diagram" para exportar a PNG/SVG

#### Opcion 2: PlantUML Online

Visita: http://www.plantuml.com/plantuml/uml/

Copia y pega el contenido de cualquier archivo .puml

#### Opcion 3: Linea de comandos

```bash
# Instala PlantUML
# En Windows con chocolatey:
choco install plantuml

# Genera PNG
plantuml diagrama-clases-inicial.puml
plantuml diagrama-clases-final.puml

# Genera SVG
plantuml -tsvg diagrama-clases-inicial.puml
plantuml -tsvg diagrama-clases-final.puml
```

### Diferencias entre Diagramas

#### Diagrama Inicial
- Estructura basica de clases con anotaciones
- Metodos principales con tipos de datos
- Relaciones con etiquetas descriptivas
- Notas explicativas
- Colores por capas (Core, Models, Controllers)
- Patrones de diseño documentados
- Vista completa del sistema

#### Diagrama Final
- Version simplificada y limpia
- Sin colores ni anotaciones
- Solo clases, metodos y relaciones
- Formato minimalista
- Facil de leer e imprimir
- Ideal para documentacion formal

### Patrones de Diseño Implementados

1. **Singleton** - Database (conexion unica)
2. **Template Method** - Controller (clase base abstracta)
3. **Strategy** - Router (enrutamiento)
4. **Active Record** - Models (acceso a datos)
5. **Front Controller** - index.php (punto de entrada unico)
6. **MVC** - Arquitectura general

### Estructura del Sistema

```
Core/
  - Database (Singleton, conexion BD)
  - Controller (Abstract, base controladores)
  - Router (Enrutamiento HTTP)

Models/
  - User (Autenticacion y usuarios)
  - Post (Publicaciones del blog)
  - Category (Categorias)
  - Comment (Comentarios)
  - ImageUpload (Manejo de imagenes)

Controllers/
  - HomeController (Pagina principal)
  - PostController (CRUD posts)
  - AuthController (Login/Register)
  - ProfileController (Perfil usuario)

Helpers/
  - Funciones auxiliares globales
```

### Relaciones en los Diagramas

- `<|--` Herencia (extends)
- `<--` Dependencia (usa)
- `*--` Composicion (tiene)
- `-->` Asociacion (pertenece a)
- `..>` Uso temporal (instancia)

### Clases Principales

#### Core
- **Database**: Singleton para conexion unica a MySQL
- **Controller**: Clase abstracta base con funcionalidad comun
- **Router**: Sistema de enrutamiento HTTP

#### Models
- **User**: Autenticacion y gestion de usuarios
- **Post**: Publicaciones del blog (CRUD completo)
- **Category**: Categorias de posts
- **Comment**: Sistema de comentarios
- **ImageUpload**: Manejo de carga de imagenes

#### Controllers
- **HomeController**: Pagina principal con listado de posts
- **PostController**: CRUD de publicaciones
- **AuthController**: Login y registro
- **ProfileController**: Gestion de perfil de usuario

### Actualizaciones

- **05/12/2025** - Creacion de diagramas inicial (completo) y final (simplificado)
- Sistema sin AutoBackup (eliminado)
- Base de datos con patron DROP/CREATE completo
- Arquitectura MVC limpia y documentada
- Diagramas optimizados para documentacion
