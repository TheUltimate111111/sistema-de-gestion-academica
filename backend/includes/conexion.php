<?php
/**
 * conexion.php
 * ---------------------------------------------------
 * Archivo de conexión a la base de datos usando PDO.
 * Soporta PostgreSQL (Render) y MySQL/MariaDB (local).
 * ---------------------------------------------------
 */

$dbUrl = getenv('DATABASE_URL');

if ($dbUrl) {
    // PostgreSQL en Render (DATABASE_URL)
    $parsed = parse_url($dbUrl);
    $host   = $parsed['host'];
    $port   = $parsed['port'] ?? '5432';
    $dbname = ltrim($parsed['path'], '/');
    $user   = $parsed['user'];
    $pass   = $parsed['pass'];
    $dsn    = "pgsql:host={$host};port={$port};dbname={$dbname}";
} else {
    // MySQL/MariaDB local (XAMPP)
    $host = 'localhost';
    $dbname = 'gestion_academica';
    $user = 'root';
    $pass = '';
    $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";
}

try {
    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $opciones);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    die('Error de conexión a la base de datos. Contacta al administrador.');
}
