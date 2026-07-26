<?php
/**
 * asignaturas_guardar.php
 * ---------------------------------------------------
 * Recibe POST desde el modal de asignaturas.
 * Inserta o actualiza según presencia de id_asignatura.
 * ---------------------------------------------------
 */
declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/asignaturas.php');
    exit;
}

require_once __DIR__ . '/includes/conexion.php';

$id       = (int)($_POST['id_asignatura'] ?? 0);
$nombre   = trim((string)($_POST['nombre']   ?? ''));
$creditos = (int)($_POST['creditos'] ?? 0);

if ($nombre === '') {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'El nombre de la asignatura es obligatorio.'];
    header('Location: ../frontend/asignaturas.php');
    exit;
}

if ($creditos < 1 || $creditos > 10) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Los créditos deben estar entre 1 y 10.'];
    header('Location: ../frontend/asignaturas.php');
    exit;
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE asignaturas SET nombre = ?, creditos = ? WHERE id_asignatura = ?'
        );
        $stmt->execute([$nombre, $creditos, $id]);
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Asignatura <strong>' . htmlspecialchars($nombre) . '</strong> actualizada correctamente.'];
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO asignaturas (nombre, creditos) VALUES (?, ?)'
        );
        $stmt->execute([$nombre, $creditos]);
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Asignatura <strong>' . htmlspecialchars($nombre) . '</strong> registrada correctamente.'];
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Ya existe una asignatura con ese nombre.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Error al guardar la asignatura. Verifica los datos e intenta de nuevo.'];
    }
}

header('Location: ../frontend/asignaturas.php');
exit;
