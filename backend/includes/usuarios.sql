-- =====================================================================
-- usuarios.sql
-- Tabla de usuarios para autenticación del Sistema de Gestión Académica
-- Ejecutar DESPUÉS del script principal (gestion_academica.sql)
-- =====================================================================

USE gestion_academica;

CREATE TABLE IF NOT EXISTS usuarios (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario       VARCHAR(60)  NOT NULL,
    nombre        VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol           ENUM('admin','docente') NOT NULL DEFAULT 'admin',
    estado        TINYINT(1) NOT NULL DEFAULT 1,
    creado_en     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_usuarios_usuario UNIQUE (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- NOTA: El usuario admin se crea visitando:
--   http://localhost/proyecto/backend/includes/crear_admin.php
-- Una vez creado, elimina o protege ese archivo.
-- =====================================================================
