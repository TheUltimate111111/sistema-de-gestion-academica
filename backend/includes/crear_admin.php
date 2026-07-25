<?php
/**
 * crear_admin.php
 * ---------------------------------------------------
 * Script de configuración inicial (ONE-TIME).
 * Visita esta URL UNA VEZ para crear el usuario admin:
 *   http://localhost/proyecto/backend/includes/crear_admin.php
 *
 * IMPORTANTE: Elimina o restringe este archivo después
 * de ejecutarlo por primera vez.
 * ---------------------------------------------------
 */
declare(strict_types=1);

require_once __DIR__ . '/conexion.php';

$usuario  = 'admin';
$nombre   = 'Administrador';
$password = 'admin123';
$rol      = 'admin';

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare(
        'INSERT INTO usuarios (usuario, nombre, password_hash, rol)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), nombre = VALUES(nombre)'
    );
    $stmt->execute([$usuario, $nombre, $hash, $rol]);

    echo '<div style="font-family:sans-serif;padding:2rem;max-width:500px;margin:auto;">';
    echo '<h2 style="color:#2f5d9f;">✅ Usuario creado correctamente</h2>';
    echo '<p><strong>Usuario:</strong> admin</p>';
    echo '<p><strong>Contraseña:</strong> admin123</p>';
    echo '<p style="color:#dc3545;">⚠️ Elimina este archivo del servidor después de usarlo.</p>';
    echo '<a href="../../index.php" style="color:#2f5d9f;">→ Ir al Login</a>';
    echo '</div>';
} catch (PDOException $e) {
    echo '<div style="font-family:sans-serif;padding:2rem;color:#dc3545;">';
    echo '<h2>❌ Error al crear usuario</h2>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p>Asegúrate de haber ejecutado primero <code>usuarios.sql</code> en phpMyAdmin.</p>';
    echo '</div>';
}
?>
