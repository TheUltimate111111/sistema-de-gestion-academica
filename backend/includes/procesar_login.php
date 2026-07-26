<?php
/**
 * procesar_login.php
 * ---------------------------------------------------
 * Procesa el formulario de login (POST desde frontend/index.php).
 * Valida las credenciales contra la tabla `usuarios` con PDO.
 * En éxito: crea sesión y redirige al dashboard.
 * En fallo: redirige al login con mensaje de error.
 * ---------------------------------------------------
 */
declare(strict_types=1);

session_start();

// Si ya tiene sesión activa, redirigir directo
if (isset($_SESSION['usuario_activo'])) {
    header('Location: ../../frontend/dashboard.php');
    exit;
}

require_once __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit;
}

$usuarioInput  = trim((string)($_POST['usuario'] ?? ''));
$passwordInput = (string)($_POST['password'] ?? '');

if ($usuarioInput === '' || $passwordInput === '') {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Usuario y contraseña son obligatorios.'];
    header('Location: ../../index.php');
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, usuario, nombre, password_hash, rol
         FROM usuarios
         WHERE usuario = ? AND estado = TRUE
         LIMIT 1'
    );
    $stmt->execute([$usuarioInput]);
    $usuarioDB = $stmt->fetch(PDO::FETCH_ASSOC);

    $credencialesOk = false;
    if ($usuarioDB) {
        $storedHash = (string)($usuarioDB['password_hash'] ?? '');
        if ($storedHash !== '' && password_verify($passwordInput, $storedHash)) {
            $credencialesOk = true;
        }
    }

    if ($credencialesOk && $usuarioDB) {
        $_SESSION['usuario_activo'] = [
            'id'      => (int)$usuarioDB['id'],
            'usuario' => $usuarioDB['usuario'],
            'nombre'  => $usuarioDB['nombre'],
            'rol'     => $usuarioDB['rol'],
        ];
        // Regenerar ID de sesión para evitar session fixation
        session_regenerate_id(true);
        header('Location: ../../frontend/dashboard.php');
        exit;
    }

    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Usuario o contraseña incorrectos.'];
    header('Location: ../../index.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Error de base de datos. Intenta de nuevo.'];
    header('Location: ../../index.php');
    exit;
}
