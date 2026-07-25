<?php
/**
 * alumnos_guardar.php
 * ---------------------------------------------------
 * Recibe POST desde el modal de alumnos (frontend/alumnos.php).
 * Inserta un nuevo alumno o actualiza uno existente según
 * si id_alumno viene vacío o con valor.
 * ---------------------------------------------------
 */
declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/alumnos.php');
    exit;
}

require_once __DIR__ . '/includes/conexion.php';

$id       = (int)($_POST['id_alumno'] ?? 0);
$nombre   = trim((string)($_POST['nombre']   ?? ''));
$apellido = trim((string)($_POST['apellido'] ?? ''));
$cedula   = trim((string)($_POST['cedula']   ?? ''));
$correo   = trim((string)($_POST['correo']   ?? ''));
$telefono = trim((string)($_POST['telefono'] ?? ''));

// Validación básica de campos obligatorios
if ($nombre === '' || $apellido === '' || $cedula === '' || $correo === '' || $telefono === '') {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Todos los campos son obligatorios.'];
    header('Location: ../frontend/alumnos.php');
    exit;
}

// Validar formato de correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'El correo electrónico no tiene un formato válido.'];
    header('Location: ../frontend/alumnos.php');
    exit;
}

// Validar que cédula sea solo dígitos
if (!ctype_digit($cedula)) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'La cédula debe contener solo números.'];
    header('Location: ../frontend/alumnos.php');
    exit;
}

try {
    // Verificar explícitamente si la cédula ya existe para otro alumno
    $stmtCheck = $pdo->prepare('SELECT id_alumno FROM alumnos WHERE cedula = ? AND id_alumno != ?');
    $stmtCheck->execute([$cedula, $id]);
    if ($stmtCheck->fetch()) {
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'La cédula ingresada ya está registrada para otro alumno.'];
        header('Location: ../frontend/alumnos.php');
        exit;
    }

    if ($id > 0) {
        // — Actualizar alumno existente —
        $stmt = $pdo->prepare(
            'UPDATE alumnos
             SET nombre = ?, apellido = ?, cedula = ?, correo = ?, telefono = ?
             WHERE id_alumno = ?'
        );
        $stmt->execute([$nombre, $apellido, $cedula, $correo, $telefono, $id]);
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Alumno <strong>' . htmlspecialchars($nombre . ' ' . $apellido) . '</strong> actualizado correctamente.'];
    } else {
        // — Insertar nuevo alumno —
        $stmt = $pdo->prepare(
            'INSERT INTO alumnos (nombre, apellido, cedula, correo, telefono)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$nombre, $apellido, $cedula, $correo, $telefono]);
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Alumno <strong>' . htmlspecialchars($nombre . ' ' . $apellido) . '</strong> registrado correctamente.'];
    }
} catch (PDOException $e) {
    // Error 23000 = violación de clave única (cédula o correo duplicado)
    if ($e->getCode() === '23000') {
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'La cédula o el correo ya están registrados por otro alumno.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Error al guardar el alumno: ' . $e->getMessage()];
    }
}

header('Location: ../frontend/alumnos.php');
exit;
?>
