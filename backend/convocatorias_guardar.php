<?php
/**
 * convocatorias_guardar.php
 * ---------------------------------------------------
 * Recibe POST desde el modal de convocatorias.
 * Inserta o actualiza según presencia de id_convocatoria.
 * Valida que fecha_fin >= fecha_inicio.
 * ---------------------------------------------------
 */
declare(strict_types=1);

session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/convocatorias.php');
    exit;
}

require_once __DIR__ . '/includes/conexion.php';

$id           = (int)($_POST['id_convocatoria'] ?? 0);
$nombre       = trim((string)($_POST['nombre']       ?? ''));
$fecha_inicio = trim((string)($_POST['fecha_inicio'] ?? ''));
$fecha_fin    = trim((string)($_POST['fecha_fin']    ?? ''));

if ($nombre === '' || $fecha_inicio === '' || $fecha_fin === '') {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Todos los campos son obligatorios.'];
    header('Location: ../frontend/convocatorias.php');
    exit;
}

// Validar que fecha_fin no sea anterior a fecha_inicio
if ($fecha_fin < $fecha_inicio) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'La fecha de fin no puede ser anterior a la fecha de inicio.'];
    header('Location: ../frontend/convocatorias.php');
    exit;
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare(
            'UPDATE convocatorias
             SET nombre = ?, fecha_inicio = ?, fecha_fin = ?
             WHERE id_convocatoria = ?'
        );
        $stmt->execute([$nombre, $fecha_inicio, $fecha_fin, $id]);
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Convocatoria <strong>' . htmlspecialchars($nombre) . '</strong> actualizada correctamente.'];
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO convocatorias (nombre, fecha_inicio, fecha_fin) VALUES (?, ?, ?)'
        );
        $stmt->execute([$nombre, $fecha_inicio, $fecha_fin]);
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Convocatoria <strong>' . htmlspecialchars($nombre) . '</strong> registrada correctamente.'];
    }
} catch (PDOException $e) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Error al guardar la convocatoria: ' . $e->getMessage()];
}

header('Location: ../frontend/convocatorias.php');
exit;
?>
