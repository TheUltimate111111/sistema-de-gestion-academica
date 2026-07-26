<?php
/**
 * matriculas_guardar.php
 * ---------------------------------------------------
 * Recibe POST desde el modal de matrículas.
 * Inserta o actualiza según presencia de id_matricula.
 * Valida que no exista matrícula duplicada (mismo alumno
 * en la misma asignatura).
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

$id           = (int)($_POST['id_matricula']    ?? 0);
$id_alumno    = (int)($_POST['id_alumno']       ?? 0);
$id_asignatura = (int)($_POST['id_asignatura']  ?? 0);
$fecha        = trim((string)($_POST['fecha']    ?? ''));

if ($id_alumno <= 0 || $id_asignatura <= 0 || $fecha === '') {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Todos los campos son obligatorios.'];
    header('Location: ../frontend/matriculas.php');
    exit;
}

// Validar que la fecha tenga formato correcto
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'La fecha no tiene un formato válido.'];
    header('Location: ../frontend/matriculas.php');
    exit;
}

try {
    if ($id > 0) {
        // — Actualizar matrícula existente —
        $stmt = $pdo->prepare(
            'UPDATE matriculas
             SET id_alumno = ?, id_asignatura = ?, fecha = ?
             WHERE id_matricula = ?'
        );
        $stmt->execute([$id_alumno, $id_asignatura, $fecha, $id]);
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Matrícula actualizada correctamente.'];
    } else {
        // — Insertar nueva matrícula —
        $stmt = $pdo->prepare(
            'INSERT INTO matriculas (id_alumno, id_asignatura, fecha) VALUES (?, ?, ?)'
        );
        $stmt->execute([$id_alumno, $id_asignatura, $fecha]);
        $_SESSION['flash'] = ['tipo' => 'success', 'mensaje' => 'Matrícula registrada correctamente.'];
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'El alumno ya está matriculado en esa asignatura.'];
    } else {
        $_SESSION['flash'] = ['tipo' => 'danger', 'mensaje' => 'Error al guardar la matricula. Verifica los datos e intenta de nuevo.'];
    }
}

header('Location: ../frontend/matriculas.php');
exit;
