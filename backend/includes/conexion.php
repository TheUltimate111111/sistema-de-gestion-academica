<?php

$dbUrl = getenv('DATABASE_URL');

if ($dbUrl) {
    $parsed = parse_url($dbUrl);

    if (!$parsed) {
        die("Error: DATABASE_URL inválida");
    }

    $host = $parsed['host'] ?? null;
    $port = $parsed['port'] ?? '5432';
    $dbname = isset($parsed['path']) ? ltrim($parsed['path'], '/') : null;
    $user = $parsed['user'] ?? null;
    $pass = $parsed['pass'] ?? null;

    if (!$host || !$user || !$dbname) {
        die("Error: Datos incompletos en DATABASE_URL");
    }

    $sslMode = 'require';
    if (isset($parsed['query'])) {
        parse_str($parsed['query'], $queryParams);
        $sslMode = $queryParams['sslmode'] ?? 'require';
    }

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslMode}";
} else {
    $host = 'localhost';
    $dbname = 'gestion_academica';
    $user = 'root';
    $pass = '';
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
}

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}

$esPostgreSQL = (bool) getenv('DATABASE_URL');

if ($esPostgreSQL) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS alumnos (
        id_alumno   SERIAL PRIMARY KEY,
        nombre      VARCHAR(100) NOT NULL,
        apellido    VARCHAR(100) NOT NULL,
        cedula      VARCHAR(20)  NOT NULL,
        correo      VARCHAR(150) NOT NULL,
        telefono    VARCHAR(20)  NOT NULL,
        creado_en   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT uq_alumnos_cedula UNIQUE (cedula)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS asignaturas (
        id_asignatura SERIAL PRIMARY KEY,
        nombre        VARCHAR(150) NOT NULL,
        creditos      SMALLINT NOT NULL DEFAULT 1,
        CONSTRAINT uq_asignaturas_nombre UNIQUE (nombre),
        CONSTRAINT chk_creditos CHECK (creditos >= 1 AND creditos <= 10)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS matriculas (
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
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS convocatorias (
        id_convocatoria SERIAL PRIMARY KEY,
        nombre          VARCHAR(150) NOT NULL,
        fecha_inicio    DATE NOT NULL,
        fecha_fin       DATE NOT NULL,
        creado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT chk_fechas CHECK (fecha_fin >= fecha_inicio)
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id            SERIAL PRIMARY KEY,
        usuario       VARCHAR(60)  NOT NULL,
        nombre        VARCHAR(100) NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        rol           VARCHAR(20)  NOT NULL DEFAULT 'admin'
                      CHECK (rol IN ('admin', 'docente')),
        estado        BOOLEAN NOT NULL DEFAULT TRUE,
        creado_en     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT uq_usuarios_usuario UNIQUE (usuario)
    )");
}