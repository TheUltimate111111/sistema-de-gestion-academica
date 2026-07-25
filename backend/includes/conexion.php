<?php
/**
 * conexion.php
 * ---------------------------------------------------
 * Archivo de conexión a la base de datos usando PDO.
 * Este archivo se deja preparado para que el backend
 * (procesos PHP que reciban los formularios del frontend)
 * pueda conectarse a MySQL/MariaDB.
 *
 * NOTA: Por ahora el frontend NO consume este archivo,
 * ya que solo se está construyendo la interfaz gráfica.
 * Cuando se implemente el backend, cada módulo (alumnos,
 * asignaturas, matriculas, convocatorias) deberá incluir
 * este archivo con require_once para obtener $pdo.
 * ---------------------------------------------------
 */

// Datos de conexión (ajustar según el entorno local)
define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_academica');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $opciones);
} catch (PDOException $e) {
    // Devuelve error genérico sin exponer detalles del servidor
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    die('Error de conexión a la base de datos. Contacta al administrador.');
}
