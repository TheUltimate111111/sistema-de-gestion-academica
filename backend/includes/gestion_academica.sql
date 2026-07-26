-- =====================================================================
-- gestion_academica.sql
-- Esquema completo PostgreSQL - Sistema de Gestión Académica
-- Ejecutar ANTES de usuarios.sql
-- =====================================================================

-- Tabla de alumnos
CREATE TABLE IF NOT EXISTS alumnos (
    id_alumno   SERIAL PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    apellido    VARCHAR(100) NOT NULL,
    cedula      VARCHAR(20)  NOT NULL,
    correo      VARCHAR(150) NOT NULL,
    telefono    VARCHAR(20)  NOT NULL,
    creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_alumnos_cedula UNIQUE (cedula)
);

-- Tabla de asignaturas
CREATE TABLE IF NOT EXISTS asignaturas (
    id_asignatura SERIAL PRIMARY KEY,
    nombre        VARCHAR(150) NOT NULL,
    creditos      SMALLINT NOT NULL DEFAULT 1,
    CONSTRAINT uq_asignaturas_nombre UNIQUE (nombre),
    CONSTRAINT chk_creditos CHECK (creditos >= 1 AND creditos <= 10)
);

-- Tabla de matrículas
CREATE TABLE IF NOT EXISTS matriculas (
    id_matricula   SERIAL PRIMARY KEY,
    id_alumno      INT NOT NULL,
    id_asignatura  INT NOT NULL,
    fecha          DATE NOT NULL DEFAULT CURRENT_DATE,
    creado_en      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_matriculas_alumno_asig UNIQUE (id_alumno, id_asignatura),
    CONSTRAINT fk_matriculas_alumno FOREIGN KEY (id_alumno)
        REFERENCES alumnos(id_alumno) ON DELETE CASCADE,
    CONSTRAINT fk_matriculas_asignatura FOREIGN KEY (id_asignatura)
        REFERENCES asignaturas(id_asignatura) ON DELETE CASCADE
);

-- Tabla de convocatorias
CREATE TABLE IF NOT EXISTS convocatorias (
    id_convocatoria SERIAL PRIMARY KEY,
    nombre          VARCHAR(150) NOT NULL,
    fecha_inicio    DATE NOT NULL,
    fecha_fin       DATE NOT NULL,
    creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_fechas CHECK (fecha_fin >= fecha_inicio)
);

-- =====================================================================
-- El script usuarios.sql se ejecuta después de este archivo.
-- =====================================================================
