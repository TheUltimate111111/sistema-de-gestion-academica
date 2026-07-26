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

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
} else {
    // LOCAL
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
    die("Error real: " . $e->getMessage());
}