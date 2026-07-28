-- =====================================================================
-- Script de base de datos: Sistema de Gestión Académica
-- Motor: PostgreSQL
-- Convertido desde el script original de MySQL/MariaDB
-- =====================================================================

-- ---------------------------------------------------------------------
-- 1) CREACIÓN DE LA BASE DE DATOS
-- ---------------------------------------------------------------------
-- PostgreSQL NO soporta "CREATE DATABASE IF NOT EXISTS".
-- Ejecuta esta parte por separado (fuera de una transacción), conectado
-- a la base "postgres", y solo si la base aún no existe:
--
-- CREATE DATABASE gestion_academica
--     WITH ENCODING 'UTF8'
--     LC_COLLATE = 'es_ES.UTF-8'
--     LC_CTYPE   = 'es_ES.UTF-8'
--     TEMPLATE = template0;
--
-- Luego conéctate a ella, por ejemplo en psql:
-- \c gestion_academica

-- ---------------------------------------------------------------------
-- Función auxiliar para simular "ON UPDATE CURRENT_TIMESTAMP" de MySQL
-- (Postgres no lo soporta nativamente; se usa un trigger)
-- ---------------------------------------------------------------------
CREATE OR REPLACE FUNCTION set_actualizado_en()
RETURNS TRIGGER AS $$
BEGIN
    NEW.actualizado_en = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- ---------------------------------------------------------------------
-- 2) TABLA: alumnos
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS matriculas;   -- se eliminan primero por las FK
DROP TABLE IF EXISTS alumnos;

CREATE TABLE alumnos (
    id_alumno      INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre         VARCHAR(80)  NOT NULL,
    apellido       VARCHAR(80)  NOT NULL,
    cedula         VARCHAR(15)  NOT NULL,
    correo         VARCHAR(120) NOT NULL,
    telefono       VARCHAR(20)  NOT NULL,
    creado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_alumnos_cedula UNIQUE (cedula),
    CONSTRAINT uq_alumnos_correo UNIQUE (correo)
);

CREATE TRIGGER trg_alumnos_actualizado_en
    BEFORE UPDATE ON alumnos
    FOR EACH ROW
    EXECUTE FUNCTION set_actualizado_en();

-- ---------------------------------------------------------------------
-- 3) TABLA: asignaturas
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS asignaturas;

CREATE TABLE asignaturas (
    id_asignatura  INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre         VARCHAR(100) NOT NULL,
    creditos       SMALLINT NOT NULL,
    creado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_asignaturas_nombre UNIQUE (nombre),
    CONSTRAINT chk_creditos CHECK (creditos BETWEEN 1 AND 10)
);

CREATE TRIGGER trg_asignaturas_actualizado_en
    BEFORE UPDATE ON asignaturas
    FOR EACH ROW
    EXECUTE FUNCTION set_actualizado_en();

-- ---------------------------------------------------------------------
-- 4) TABLA: matriculas (relación N:M entre alumnos y asignaturas)
-- ---------------------------------------------------------------------
CREATE TABLE matriculas (
    id_matricula   INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_alumno      INTEGER NOT NULL,
    id_asignatura  INTEGER NOT NULL,
    fecha          DATE NOT NULL,
    creado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_matriculas_alumno
        FOREIGN KEY (id_alumno) REFERENCES alumnos(id_alumno)
        ON DELETE CASCADE ON UPDATE CASCADE,

    CONSTRAINT fk_matriculas_asignatura
        FOREIGN KEY (id_asignatura) REFERENCES asignaturas(id_asignatura)
        ON DELETE CASCADE ON UPDATE CASCADE,

    -- Evita que un alumno se matricule dos veces en la misma asignatura
    CONSTRAINT uq_matricula_alumno_asignatura UNIQUE (id_alumno, id_asignatura)
);

CREATE TRIGGER trg_matriculas_actualizado_en
    BEFORE UPDATE ON matriculas
    FOR EACH ROW
    EXECUTE FUNCTION set_actualizado_en();

-- ---------------------------------------------------------------------
-- 5) TABLA: convocatorias
-- ---------------------------------------------------------------------
DROP TABLE IF EXISTS convocatorias;

CREATE TABLE convocatorias (
    id_convocatoria INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nombre          VARCHAR(150) NOT NULL,
    fecha_inicio    DATE NOT NULL,
    fecha_fin       DATE NOT NULL,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_fechas_convocatoria CHECK (fecha_fin >= fecha_inicio)
);

CREATE TRIGGER trg_convocatorias_actualizado_en
    BEFORE UPDATE ON convocatorias
    FOR EACH ROW
    EXECUTE FUNCTION set_actualizado_en();

-- ---------------------------------------------------------------------
-- 6) ÍNDICE ADICIONALES (mejoran búsquedas y JOINs frecuentes)
-- ---------------------------------------------------------------------
CREATE INDEX idx_alumnos_apellido ON alumnos(apellido);
CREATE INDEX idx_matriculas_fecha ON matriculas(fecha);
CREATE INDEX idx_convocatorias_fechas ON convocatorias(fecha_inicio, fecha_fin);

-- =====================================================================
-- 7) DATOS DE EJEMPLO
-- =====================================================================

-- --------------------- Alumnos ---------------------
INSERT INTO alumnos (nombre, apellido, cedula, correo, telefono) VALUES
('María',  'Pérez',  '1102345678', 'maria.perez@correo.com',  '0991234567'),
('Carlos', 'Gómez',  '1709876543', 'carlos.gomez@correo.com', '0987654321'),
('Andrea', 'Torres', '1755566677', 'andrea.torres@correo.com','0965544332');

-- --------------------- Asignaturas ---------------------
INSERT INTO asignaturas (nombre, creditos) VALUES
('Matemáticas I',   4),
('Programación I',  5),
('Base de Datos',   4),
('Inglés Técnico',  2);

-- --------------------- Matrículas ---------------------
-- (id_alumno / id_asignatura referencian los IDENTITY insertados arriba)
INSERT INTO matriculas (id_alumno, id_asignatura, fecha) VALUES
(1, 2, '2026-03-10'),  -- María Pérez     -> Programación I
(2, 3, '2026-03-12'),  -- Carlos Gómez    -> Base de Datos
(3, 1, '2026-03-15');  -- Andrea Torres   -> Matemáticas I

-- --------------------- Convocatorias ---------------------
INSERT INTO convocatorias (nombre, fecha_inicio, fecha_fin) VALUES
('Convocatoria Primer Semestre 2026',   '2026-01-15', '2026-02-15'),
('Convocatoria Segundo Semestre 2026',  '2026-07-01', '2026-07-31'),
('Convocatoria Especial de Nivelación', '2026-09-05', '2026-09-20');

-- =====================================================================
-- 8) TABLA: usuarios
-- =====================================================================

DROP TYPE IF EXISTS rol_usuario CASCADE;
CREATE TYPE rol_usuario AS ENUM ('admin', 'docente');

DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id            INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    usuario       VARCHAR(60) NOT NULL UNIQUE,
    nombre        VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol           rol_usuario NOT NULL DEFAULT 'admin',
    estado        BOOLEAN NOT NULL DEFAULT TRUE
);

-- Usuario por defecto: admin / admin123
INSERT INTO usuarios (usuario, nombre, password_hash, rol)
VALUES ('admin', 'Administrador', '$2y$12$...', 'admin');

SELECT * FROM usuarios;


select * from alumnos ;
-- =====================================================================
-- FIN DEL SCRIPT
-- =====================================================================