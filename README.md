# Diálogo y Desarrollo Perú

Aplicación web desarrollada para el curso **Plataformas para el Desarrollo de Aplicaciones** de la carrera de **Ingeniería de Sistemas**.

El proyecto consiste en la adaptación y administración dinámica de un portal digital de noticias y reportajes, incorporando un **panel administrativo**, persistencia de datos con **MySQL**, gestión de archivos multimedia y un flujo de **despliegue automático con GitHub Actions hacia InfinityFree**.

---

## Descripción del proyecto

**Diálogo y Desarrollo Perú** es una aplicación web desarrollada en PHP que permite administrar y publicar contenido digital desde un panel privado.

El sistema permite gestionar reportajes, noticias, podcasts, boletines, videos, autores y alianzas. Los contenidos registrados desde el panel administrativo se almacenan en una base de datos MySQL y posteriormente son mostrados en el sitio público.

El proyecto cuenta con un entorno local de desarrollo mediante XAMPP y un entorno de producción desplegado en InfinityFree.

---

## Objetivo

Desarrollar y desplegar una aplicación web basada en **PHP y MySQL** que permita administrar contenidos digitales de manera centralizada, aplicando control de acceso, validación de archivos, persistencia de datos y despliegue automatizado mediante GitHub.

---

## Tecnologías utilizadas

| Tecnología | Uso |
|---|---|
| PHP 8 | Lógica del servidor y procesamiento del sistema |
| MySQL / MariaDB | Persistencia de datos |
| PDO | Conexión segura y consultas preparadas |
| HTML5 | Estructura de las páginas |
| CSS3 | Estilos y presentación |
| JavaScript | Interactividad del sitio |
| Bootstrap | Componentes y diseño responsive |
| Quill | Editor visual para contenido de reportajes |
| XAMPP | Entorno local de desarrollo |
| Git | Control de versiones |
| GitHub | Repositorio remoto |
| GitHub Actions | Automatización del despliegue |
| FTP | Transferencia automática hacia el hosting |
| InfinityFree | Hosting de producción |

---

## Funcionalidades principales

### Sitio público

- Página principal con contenido dinámico.
- Visualización de noticias recientes.
- Listado y detalle de reportajes.
- Visualización de podcasts.
- Visualización de boletines.
- Integración de contenido multimedia.
- Navegación responsive.
- Acceso a páginas informativas y de contacto.

### Panel administrativo

- Inicio de sesión.
- Control de sesiones.
- Gestión de roles.
- Creación, edición y eliminación de contenidos.
- Gestión de reportajes.
- Gestión de noticias.
- Gestión de podcasts.
- Gestión de boletines.
- Gestión de videos.
- Gestión de autores.
- Gestión de alianzas.
- Gestión de reportajes destacados.
- Subida de imágenes.
- Subida de archivos PDF.
- Editor visual para el contenido de reportajes.
- Validación de formatos y tamaños de archivos.

---

## Validación de archivos

El sistema valida tanto la extensión como el tamaño de los archivos antes de almacenarlos.

### Reportajes

| Archivo | Formatos permitidos | Tamaño máximo |
|---|---|---:|
| Imagen principal | JPG, JPEG, PNG, WEBP | 8 MB |
| Fotos adicionales | JPG, JPEG, PNG, WEBP | 8 MB por imagen |
| PDF adjunto | PDF | 10 MB |

### Podcasts

| Archivo | Formatos permitidos | Tamaño máximo |
|---|---|---:|
| Portada | JPG, JPEG, PNG, WEBP | 8 MB |

### Boletines

| Archivo | Formatos permitidos | Tamaño máximo |
|---|---|---:|
| Portada | JPG, JPEG, PNG, WEBP | 8 MB |
| Documento | PDF | 10 MB |

Además de la extensión, el sistema verifica el tipo MIME del archivo cuando el servidor lo permite.

---

## Seguridad

El proyecto incorpora medidas básicas de seguridad para proteger el panel y los datos:

- Contraseñas almacenadas mediante `password_hash()`.
- Verificación de credenciales mediante funciones seguras de PHP.
- Consultas preparadas con PDO.
- Control de sesiones.
- Restricción de acciones según rol.
- Validación de extensiones de archivos.
- Validación de tamaño de archivos.
- Validación del tipo MIME.
- Generación de nombres aleatorios para archivos subidos.
- Exclusión de archivos sensibles mediante `.gitignore`.
- Separación entre configuración local y configuración de producción.

Los archivos que contienen credenciales o información privada **no se almacenan en el repositorio público**.

---

## Estructura general del proyecto

```text
dialogo_desarrollo/
│
├── admin/
│   ├── config/
│   │   ├── conexion.php
│   │   └── config.php           # No se versiona
│   │
│   ├── includes/
│   │   ├── admin_layout.php
│   │   ├── funciones.php
│   │   └── sesion.php
│   │
│   ├── uploads/                 # No se versiona
│   │   ├── reportajes/
│   │   ├── noticias/
│   │   ├── podcasts/
│   │   └── boletines/
│   │
│   ├── index.php
│   ├── login.php
│   ├── reportajes.php
│   ├── noticias.php
│   ├── podcasts.php
│   └── boletines.php
│
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
│
├── .github/
│   └── workflows/
│       └── deploy.yml
│
├── index.php
├── reportajes.php
├── reportaje.php
├── podcasts.php
├── boletines.php
├── alianzas.php
├── contacto.php
├── sobre-dd.php
├── .gitignore
└── README.md
```

---

## Base de datos

La aplicación utiliza una base de datos MySQL llamada:

```text
revista_digital
```

Entre las principales tablas utilizadas se encuentran:

- `usuarios`
- `autores`
- `reportajes`
- `reportajes_fotos`
- `noticias`
- `boletines`
- `podcasts`
- `videos`
- `alianzas`
- `password_resets`

La base de datos de producción se encuentra separada de la base local de desarrollo.

---

## Requisitos para ejecución local

- XAMPP con Apache y MySQL.
- PHP 8 o superior.
- Navegador web actualizado.
- Git instalado si se desea trabajar con control de versiones.

---

## Instalación local

### 1. Clonar el repositorio

```bash
git clone https://github.com/baleryry/dialogo_desarrollo.git
```

### 2. Mover el proyecto al directorio de XAMPP

Ejemplo en Windows:

```text
D:\XAMPP\htdocs\dialogo_desarrollo
```

### 3. Iniciar servicios

Desde XAMPP Control Panel:

- Apache
- MySQL

### 4. Crear la base de datos

En phpMyAdmin, crear:

```text
revista_digital
```

e importar la estructura correspondiente del proyecto.

### 5. Configurar conexión local

Crear o configurar:

```text
admin/config/config.php
```

Ejemplo local:

```php
<?php

const APP_NAME = 'Diálogo y Desarrollo';

const APP_URL = '/dialogo_desarrollo';
const ADMIN_URL = APP_URL . '/admin';

const DB_HOST = 'localhost';
const DB_NAME = 'revista_digital';
const DB_USER = 'root';
const DB_PASS = '';
const DB_CHARSET = 'utf8mb4';
```

### 6. Abrir la aplicación

```text
http://localhost/dialogo_desarrollo/
```

---

## Control de versiones

El proyecto utiliza Git para registrar los cambios realizados durante el desarrollo.

Flujo habitual:

```bash
git status
git add archivo_modificado.php
git commit -m "Descripción del cambio"
git push
```

Se recomienda agregar únicamente los archivos modificados y evitar `git add .` cuando existan cambios que todavía no han sido revisados.

---

## Despliegue automático

El proyecto utiliza **GitHub Actions** para automatizar el despliegue hacia InfinityFree.

Cada vez que se realiza un `push` a la rama `main`, el workflow ejecuta el proceso de publicación mediante FTP.

### Flujo de despliegue

```text
Desarrollo local
      ↓
Git
      ↓
GitHub
      ↓
GitHub Actions
      ↓
FTP
      ↓
InfinityFree
      ↓
Aplicación en producción
```

Los archivos sensibles y las carpetas de contenido subido por los usuarios se excluyen del despliegue automático.

---

## Entorno de producción

### Sitio web

https://dialogoydesarrollo.great-site.net/

### Panel administrativo

https://dialogoydesarrollo.great-site.net/admin/

### Repositorio

https://github.com/baleryry/dialogo_desarrollo

---

## Archivos excluidos del repositorio

Por razones de seguridad y mantenimiento, determinados archivos no se incluyen en GitHub:

```text
admin/config/config.php
admin/config/mail.php
admin/prueba_conexion.php
admin/uploads/
crear_usuario.php
*.sql
*.zip
*.rar
.env
```

Esto evita publicar credenciales, copias de seguridad y archivos generados durante el funcionamiento de la aplicación.

---

## Estado del proyecto

Actualmente se encuentran implementados y operativos:

- Sitio público.
- Panel administrativo.
- Conexión con MySQL.
- Gestión de contenidos.
- Subida de archivos.
- Validación de archivos.
- Control de usuarios y sesiones.
- Despliegue automático con GitHub Actions.
- Publicación en InfinityFree.

---

## Autor

**Davis Vitorino**

Ingeniería de Sistemas  
Universidad Andina del Cusco

---

## Contexto académico

Proyecto desarrollado como parte del curso:

**Plataformas para el Desarrollo de Aplicaciones**

Universidad Andina del Cusco — 2026.
