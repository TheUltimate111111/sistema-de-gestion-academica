<?php
/**
 * alumnos_eliminar.php
 * ---------------------------------------------------
 * Recibe POST desde el modal de confirmación de eliminación.
 * Elimina el alumno indicado. Las matrículas asociadas se
 * eliminan en cascada por la FK ON DELETE CASCADE.
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

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'ID de alumno inválido.'];
    header('Location: ../frontend/alumnos.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM alumnos WHERE id_alumno = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Alumno eliminado correctamente.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'warning', 'mensaje' => 'No se encontró el alumno a eliminar.'];
    }
} catch (PDOException $e) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'No se pudo eliminar el alumno. Verifica que no tenga matriculas asociadas.'];
}

header('Location: ../frontend/alumnos.php');
exit;
