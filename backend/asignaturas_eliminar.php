<?php
/**
 * asignaturas_eliminar.php
 * ---------------------------------------------------
 * Elimina una asignatura. Las matrículas asociadas se
 * eliminan en cascada por ON DELETE CASCADE.
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

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'ID de asignatura inválido.'];
    header('Location: ../frontend/asignaturas.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM asignaturas WHERE id_asignatura = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Asignatura eliminada correctamente.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'warning', 'mensaje' => 'No se encontró la asignatura a eliminar.'];
    }
} catch (PDOException $e) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'No se pudo eliminar la asignatura. Verifica que no tenga matriculas asociadas.'];
}

header('Location: ../frontend/asignaturas.php');
exit;
