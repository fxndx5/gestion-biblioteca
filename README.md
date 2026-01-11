# Sistema de Gestión de Biblioteca

> Sistema integral de gestión bibliotecaria con aplicación web para empleados y aplicación de escritorio para administradores.

**Desarrollado por:** Fernanda González Alvarenga  
**Módulo:** 2DAM - Acceso a Datos

---

## Descripción del Proyecto

Sistema de gestión bibliotecaria dividido en dos componentes principales que comparten una base de datos unificada:

- **Aplicación Web (Frontend)**: Para empleados que gestionan las operaciones diarias de la biblioteca
- **Aplicación de Escritorio (Backend)**: Para administradores que gestionan empleados y la configuración del sistema

---

## Características Principales

### Aplicación Web
- Gestión de libros (catálogo completo)
- Registro y gestión de clientes
- Sistema de préstamos y devoluciones
- Control de sanciones
- Panel de operaciones diarias

### Aplicación Desktop
- Gestión completa de empleados (CRUD)
- Configuración del sistema
- Administración avanzada de la base de datos
- Panel de control administrativo

---

## Arquitectura del Sistema

```
┌─────────────────┐         ┌──────────────────┐
│   EMPLEADOS     │────────▶│ Aplicación Web   │
│                 │         │     (PHP)        │
└─────────────────┘         └────────┬─────────┘
                                     │
                                     ▼
                            ┌─────────────────┐
                            │   Base de Datos │
                            │     (MySQL)     │
                            └────────▲────────┘
                                     │
┌─────────────────┐         ┌────────┴─────────┐
│ ADMINISTRADORES │────────▶│ Aplicación       │
│                 │         │   Desktop (Java) │
└─────────────────┘         └──────────────────┘
```

---

## Roles y Permisos

| Rol | Acceso | Permisos |
|-----|--------|----------|
| **Empleado** | Aplicación Web | Gestión de libros, clientes, préstamos y devoluciones |
| **Administrador** | Aplicación Desktop + Web | Todas las operaciones + gestión de empleados |

---

## Requisitos Previos

### Para la Aplicación Web
- Servidor web (Apache/Nginx)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensiones PHP: mysqli, pdo_mysql

### Para la Aplicación Desktop
- Java JDK 11 o superior
- Maven 3.6+
- IDE compatible (Eclipse, IntelliJ IDEA, NetBeans)

---

## Instalación

### Configuración de la Base de Datos

```sql
-- Ejecutar el script SQL proporcionado en /database/schema.sql
mysql -u root -p < database/schema.sql
```

### Instalación de la Aplicación Web

```bash
# 1. Copiar archivos al directorio del servidor
cp -r biblioteca-web /var/www/html/

# 2. Configurar credenciales de base de datos
# Editar: /var/www/html/biblioteca-web/config/database.php

# 3. Ajustar permisos
cd /var/www/html/biblioteca-web
find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;

# 4. Acceder a la aplicación
# http://localhost/biblioteca-web/login.php
```

**Configuración de database.php:**
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'biblioteca');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
?>
```

### Instalación de la Aplicación Desktop

```bash
# 1. Clonar o importar el proyecto en tu IDE

# 2. Configurar dependencias Maven
mvn clean install

# 3. Configurar conexión a BD
# Editar: src/main/resources/config.properties

# 4. Compilar y ejecutar desde el IDE
```

**Configuración de config.properties:**
```properties
db.host=localhost
db.port=3306
db.name=biblioteca
db.user=tu_usuario
db.password=tu_contraseña
```

---

## Estructura del Proyecto

```
gestion-biblioteca/
├── biblioteca-web/          # Aplicación Web (PHP)
│   ├── config/             # Configuración de BD
│   ├── controllers/        # Controladores
│   ├── models/             # Modelos
│   ├── views/              # Vistas
│   └── assets/             # CSS, JS, imágenes
│
├── bibliotecaDesktop/      # Aplicación Desktop (Java)
│   ├── src/
│   │   ├── main/
│   │   │   ├── java/      # Código fuente
│   │   │   └── resources/ # Configuración
│   │   └── test/          # Tests
│   ├── pom.xml            # Dependencias Maven
│   └── target/            # Compilados
│
├── database/              # Scripts SQL
│   └── schema.sql
│
└── README.md
```

---

## Tecnologías Utilizadas

### Frontend (Web)
- **PHP** - Lenguaje backend
- **MySQL** - Base de datos
- **HTML/CSS/JavaScript** - Interfaz de usuario
- **Bootstrap** (opcional) - Framework CSS

### Backend (Desktop)
- **Java** - Lenguaje de programación
- **Maven** - Gestor de dependencias
- **JDBC** - Conexión a base de datos
- **JavaFX/Swing** - Interfaz gráfica

---

## Uso del Sistema

### Acceso Web (Empleados)
1. Navegar a `http://localhost/biblioteca-web/`
2. Iniciar sesión con credenciales de empleado
3. Gestionar libros, clientes y préstamos

### Acceso Desktop (Administradores)
1. Ejecutar la aplicación Java
2. Iniciar sesión con credenciales de administrador
3. Gestionar empleados y configuración del sistema

---

## Contribución

Si deseas contribuir a este proyecto:

1. Fork el repositorio
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Añadir nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

---

## Licencia

Este proyecto fue desarrollado como parte del módulo de Acceso a Datos - 2DAM.

---

## Contacto

**Fernanda González Alvarenga**  
Proyecto académico - 2DAM

---

## Reporte de Problemas

Si encuentras algún bug o tienes sugerencias, por favor abre un issue en GitHub.

---

**⭐ Si te gusta este proyecto, dale una estrella en GitHub!**
