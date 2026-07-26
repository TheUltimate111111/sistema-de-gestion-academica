<?php
/**
 * matriculas_eliminar.php
 * ---------------------------------------------------
 * Elimina una matrícula por su id_matricula.
 * ---------------------------------------------------
 */
declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/matriculas.php');
    exit;
}

require_once __DIR__ . '/includes/conexion.php';

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'ID de matrícula inválido.'];
    header('Location: ../frontend/matriculas.php');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM matriculas WHERE id_matricula = ?');
    $stmt->execute([$id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Matrícula eliminada correctamente.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'warning', 'mensaje' => 'No se encontró la matrícula a eliminar.'];
    }
} catch (PDOException $e) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'No se pudo eliminar la matricula.'];
}

header('Location: ../frontend/matriculas.php');
exit;
