<?php
/**
 * convocatorias_eliminar.php
 * ---------------------------------------------------
 * Elimina una convocatoria por su id_convocatoria.
 * ---------------------------------------------------
 */
declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: ../frontend/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/convocatorias.php');
    exit;
}

require_once __DIR__ . '/includes/conexion.php';

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'ID de convocatoria inválido.'];
    header('Location: ../frontend/convocatorias.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM convocatorias WHERE id_convocatoria = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Convocatoria eliminada correctamente.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'warning', 'mensaje' => 'No se encontró la convocatoria a eliminar.'];
    }
} catch (PDOException $e) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'No se pudo eliminar la convocatoria: ' . $e->getMessage()];
}

header('Location: ../frontend/convocatorias.php');
exit;
?>
