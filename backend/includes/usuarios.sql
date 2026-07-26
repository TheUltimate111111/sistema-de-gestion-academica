-- =====================================================================
-- usuarios.sql
-- Tabla de usuarios para autenticación del Sistema de Gestión Académica
-- Ejecutar DESPUÉS de gestion_academica.sql
-- =====================================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id            SERIAL PRIMARY KEY,
    usuario       VARCHAR(60)  NOT NULL,
    nombre        VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol           VARCHAR(20)  NOT NULL DEFAULT 'admin'
                  CHECK (rol IN ('admin', 'docente')),
    estado        BOOLEAN NOT NULL DEFAULT TRUE,
    creado_en     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_usuarios_usuario UNIQUE (usuario)
);

-- =====================================================================
-- NOTA: El usuario admin se crea visitando:
--   http://localhost/proyecto/backend/includes/crear_admin.php
-- Una vez creado, elimina o protege ese archivo.
-- =====================================================================
