# Sistema de Gestión Académica

Sistema web para la gestión integral de datos académicos: alumnos, asignaturas, matrículas y convocatorias. Construido con PHP vanilla, MySQL y Bootstrap 5, diseñado para ejecutarse en un entorno local con XAMPP.

---

## Tecnologías

| Tecnología | Versión |
|---|---|
| PHP | 8.x |
| MySQL / MariaDB | 10.x+ |
| Bootstrap | 5.3.3 |
| Bootstrap Icons | 1.11.3 |
| JavaScript | Vanilla (sin frameworks) |

---

## Requisitos

- **XAMPP** (Apache + PHP + MySQL) instalado y ejecutándose
- Navegador web actualizado (Chrome, Firefox, Edge)

---

## Instalación

### 1. Clonar o copiar el proyecto

Copia la carpeta del proyecto en el directorio de XAMPP:

```
C:\xampp\htdocs\proyecto\
```

O clona el repositorio:

```bash
git clone https://github.com/tu-usuario/proyecto.git C:\xampp\htdocs\proyecto
```

### 2. Crear la base de datos

Abre **phpMyAdmin** (`http://localhost/phpmyadmin`) y ejecuta la siguiente consulta SQL para crear la base de datos y las tablas:

```sql
CREATE DATABASE IF NOT EXISTS gestion_academica
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE gestion_academica;

-- Tabla de alumnos
CREATE TABLE alumnos (
  id_alumno INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellido VARCHAR(100) NOT NULL,
  cedula VARCHAR(10) NOT NULL UNIQUE,
  correo VARCHAR(150) NOT NULL UNIQUE,
  telefono VARCHAR(10) NOT NULL
) ENGINE=InnoDB;

-- Tabla de asignaturas
CREATE TABLE asignaturas (
  id_asignatura INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL UNIQUE,
  creditos TINYINT NOT NULL CHECK (creditos BETWEEN 1 AND 10)
) ENGINE=InnoDB;

-- Tabla de matrículas
CREATE TABLE matriculas (
  id_matricula INT AUTO_INCREMENT PRIMARY KEY,
  id_alumno INT NOT NULL,
  id_asignatura INT NOT NULL,
  fecha DATE NOT NULL,
  UNIQUE KEY uk_alumno_asignatura (id_alumno, id_asignatura),
  FOREIGN KEY (id_alumno) REFERENCES alumnos(id_alumno) ON DELETE CASCADE,
  FOREIGN KEY (id_asignatura) REFERENCES asignaturas(id_asignatura) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabla de convocatorias
CREATE TABLE convocatorias (
  id_convocatoria INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL
) ENGINE=InnoDB;

-- Tabla de usuarios
CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('admin', 'docente') NOT NULL DEFAULT 'docente',
  estado TINYINT NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
```

### 3. Crear el usuario administrador

Ejecuta una sola vez el script de seed en tu navegador:

```
http://localhost/proyecto/backend/includes/crear_admin.php
```

Esto creará el usuario administrador con las siguientes credenciales:

| Campo | Valor |
|---|---|
| Usuario | `admin` |
| Contraseña | `admin123` |

> **IMPORTANTE:** Después de iniciar sesión por primera vez, cambia la contraseña del administrador. Elimina o renombra el archivo `crear_admin.php` para mayor seguridad.

### 4. Acceder al sistema

Abre tu navegador y navega a:

```
http://localhost/proyecto/frontend/
```

Inicia sesión con las credenciales del administrador y empezar a gestionar el sistema.

---

## Estructura del Proyecto

```
proyecto/
├── README.md
├── backend/
│   ├── includes/
│   │   ├── conexion.php          # Conexión a la BD (PDO)
│   │   ├── crear_admin.php       # Script para crear admin (ejecutar una vez)
│   │   ├── logout.php            # Cierre de sesión
│   │   ├── procesar_login.php    # Autenticación de login
│   │   └── usuarios.sql          # Schema SQL de la tabla usuarios
│   ├── alumnos_guardar.php       # Crear/actualizar alumno
│   ├── alumnos_eliminar.php      # Eliminar alumno
│   ├── asignaturas_guardar.php   # Crear/actualizar asignatura
│   ├── asignaturas_eliminar.php  # Eliminar asignatura
│   ├── matriculas_guardar.php    # Crear/actualizar matrícula
│   ├── matriculas_eliminar.php   # Eliminar matrícula
│   ├── convocatorias_guardar.php # Crear/actualizar convocatoria
│   └── convocatorias_eliminar.php # Eliminar convocatoria
└── frontend/
    ├── css/
    │   └── styles.css            # Estilos personalizados
    ├── js/
    │   └── app.js                # Lógica JS: sidebar, validaciones, búsqueda
    ├── index.php                 # Página de login (punto de entrada)
    ├── dashboard.php             # Panel principal
    ├── sidebar.php               # Menú lateral reutilizable
    ├── alumnos.php               # Módulo de alumnos (CRUD)
    ├── asignaturas.php           # Módulo de asignaturas (CRUD)
    ├── matriculas.php            # Módulo de matrículas (CRUD)
    └── convocatorias.php         # Módulo de convocatorias (CRUD)
```

---

## Módulos

| Módulo | Descripción |
|---|---|
| **Dashboard** | Panel principal con estadísticas y accesos rápidos |
| **Alumnos** | Alta, baja, modificación y consulta de alumnos |
| **Asignaturas** | Gestión de asignaturas con número de créditos |
| **Matrículas** | Registro de matrículas vinculando alumnos con asignaturas |
| **Convocatorias** | Gestión de convocatorias con fechas y estado (Activa/Próxima/Finalizada) |

---

## Funcionalidades

- Autenticación por sesión con roles (admin / docente)
- CRUD completo en los 4 módulos
- Validación de formularios en cliente (cédula ecuatoriana, teléfono, email, fechas)
- Búsqueda en tiempo real dentro de cada módulo
- Sidebar responsive con animaciones de transición
- Diseño premium con Bootstrap 5 y gradientes personalizados
- Mensajes flash para notificaciones de éxito/error
- Confirmación de eliminación con modal

---

## Credenciales por Defecto

| Usuario | Contraseña | Rol |
|---|---|---|
| `admin` | `admin123` | Administrador |

---

## Licencia

Este es un proyecto de uso académico/educativo.
