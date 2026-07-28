<?php
declare(strict_types=1);
session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../backend/includes/conexion.php';

$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

$alumnos = $pdo->query('SELECT id_alumno, nombre, apellido FROM alumnos ORDER BY apellido, nombre')->fetchAll(PDO::FETCH_ASSOC);

$asignaturas = $pdo->query('SELECT id_asignatura, nombre FROM asignaturas ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);

$matriculas = $pdo->query(
    "SELECT m.id_matricula, m.id_alumno, m.id_asignatura, m.fecha,
            CONCAT(a.nombre, ' ', a.apellido) AS alumno,
            s.nombre AS asignatura
     FROM matriculas m
     JOIN alumnos a ON a.id_alumno = m.id_alumno
     JOIN asignaturas s ON s.id_asignatura = m.id_asignatura
     ORDER BY m.fecha DESC"
)->fetchAll(PDO::FETCH_ASSOC);

$usuario = $_SESSION['usuario_activo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Matrículas | Gestión Académica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232f5d9f'><path d='M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z'/></svg>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="ga-wrapper">

    <?php require 'sidebar.php'; ?>

    <div class="ga-content">

        <header class="ga-topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary ga-sidebar-toggle" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="mb-0">Matrículas</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-circle fs-4 text-secondary"></i>
                <span class="d-none d-sm-inline"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
            </div>
        </header>

        <main class="ga-main ga-animate-in">

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flash['tipo']); ?> alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-<?php echo $flash['tipo'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo $flash['mensaje']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <div class="card ga-card">
                <div class="card-header">
                    <span><i class="bi bi-clipboard2-check-fill text-primary me-1"></i> Registros de matrícula
                        <span class="badge bg-primary ms-1"><?php echo count($matriculas); ?></span>
                    </span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMatricula" onclick="prepararNuevaMatricula()">
                        <i class="bi bi-plus-lg me-1"></i> Nueva matrícula
                    </button>
                </div>

                <div class="card-body">

                    <div class="ga-filter-bar">
                        <div class="row g-2 align-items-end ga-filter-container" data-filter-table="tablaMatriculas">
                            <div class="col-md-3">
                                <div class="ga-filter-label">Buscar</div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control ga-filter-input" placeholder="Buscar..."
                                           data-table-search="tablaMatriculas">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="ga-filter-label">Alumno</div>
                                <select class="form-select form-select-sm ga-filter-select" data-filter-target="matAlumno">
                                    <option value="">Todos</option>
                                    <?php foreach ($alumnos as $a): ?>
                                        <option value="<?php echo htmlspecialchars($a['nombre'] . ' ' . $a['apellido']); ?>">
                                            <?php echo htmlspecialchars($a['nombre'] . ' ' . $a['apellido']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="ga-filter-label">Asignatura</div>
                                <select class="form-select form-select-sm ga-filter-select" data-filter-target="matAsignatura">
                                    <option value="">Todas</option>
                                    <?php foreach ($asignaturas as $s): ?>
                                        <option value="<?php echo htmlspecialchars($s['nombre']); ?>">
                                            <?php echo htmlspecialchars($s['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="ga-filter-label">Desde</div>
                                <input type="date" class="form-control form-control-sm ga-filter-date ga-filter-date-from"
                                       data-filter-target="matFecha">
                            </div>
                            <div class="col-md-2">
                                <div class="ga-filter-label">Hasta</div>
                                <div class="d-flex gap-1">
                                    <input type="date" class="form-control form-control-sm ga-filter-date ga-filter-date-to"
                                           data-filter-target="matFecha">
                                    <button class="ga-filter-reset px-2" title="Limpiar filtros"><i class="bi bi-x-circle"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ga-floating-list" id="tablaMatriculas">
                        <?php if (empty($matriculas)): ?>
                            <div class="ga-empty-message">
                                <i class="bi bi-inbox"></i>
                                <h6>No hay matrículas registradas.</h6>
                                <p>Crea una nueva matrícula para empezar.</p>
                            </div>
                        <?php else: ?>
                            <?php
                            $colores = ['#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#ef4444', '#f97316'];
                            foreach ($matriculas as $matricula):
                                $inicial = strtoupper(substr($matricula['alumno'], 0, 1));
                                $colorIndex = ord($inicial) % count($colores);
                                $bgColor = $colores[$colorIndex];
                            ?>
                                <div class="ga-floating-row"
                                     data-mat-alumno="<?php echo htmlspecialchars($matricula['alumno']); ?>"
                                     data-mat-asignatura="<?php echo htmlspecialchars($matricula['asignatura']); ?>"
                                     data-mat-fecha="<?php echo htmlspecialchars($matricula['fecha']); ?>">
                                    <div class="ga-row-content">
                                        <div class="ga-avatar" style="background-color: <?php echo $bgColor; ?>; border-radius: 8px;">
                                            <i class="bi bi-journal-check"></i>
                                        </div>

                                        <div class="ga-row-info">
                                            <div>
                                                <span class="ga-row-label">Alumno</span>
                                                <span class="ga-row-title"><?php echo htmlspecialchars($matricula['alumno']); ?></span>
                                            </div>
                                            <div>
                                                <span class="ga-row-label">Asignatura</span>
                                                <span class="ga-row-value"><i class="bi bi-book text-muted me-1"></i><?php echo htmlspecialchars($matricula['asignatura']); ?></span>
                                            </div>
                                            <div>
                                                <span class="ga-row-label">Fecha de Registro</span>
                                                <span class="ga-row-value"><i class="bi bi-calendar3 text-muted me-1"></i><?php echo htmlspecialchars($matricula['fecha']); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ga-row-actions">
                                        <button type="button" class="btn btn-light text-primary border-0 shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalMatricula"
                                                onclick="prepararEditarMatricula(<?php echo htmlspecialchars(json_encode($matricula, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>)">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-light text-danger border-0 shadow-sm btn-eliminar"
                                                data-id="<?php echo $matricula['id_matricula']; ?>"
                                                data-nombre="Matrícula de <?php echo htmlspecialchars($matricula['alumno']); ?>"
                                                data-url="../backend/matriculas_eliminar.php">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

        </main>
    </div>
</div>

<div class="modal fade" id="modalMatricula" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="ga-form" id="formMatricula" action="../backend/matriculas_guardar.php" method="POST" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalMatricula">
                        <i class="bi bi-clipboard2-plus-fill me-1"></i> Nueva matrícula
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <input type="hidden" name="id_matricula" id="id_matricula">

                    <div class="form-floating mb-3">
                        <select class="form-select" id="id_alumno_select" name="id_alumno" required>
                            <option value="" selected disabled>Selecciona un alumno...</option>
                            <?php foreach ($alumnos as $alumno): ?>
                                <option value="<?php echo htmlspecialchars((string)$alumno['id_alumno']); ?>">
                                    <?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="id_alumno_select" class="required"><i class="bi bi-person me-1"></i>Alumno</label>
                        <div class="invalid-feedback">Selecciona un alumno.</div>
                    </div>

                    <div class="form-floating mb-3">
                        <select class="form-select" id="id_asignatura_select" name="id_asignatura" required>
                            <option value="" selected disabled>Selecciona una asignatura...</option>
                            <?php foreach ($asignaturas as $asignatura): ?>
                                <option value="<?php echo htmlspecialchars((string)$asignatura['id_asignatura']); ?>">
                                    <?php echo htmlspecialchars($asignatura['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="id_asignatura_select" class="required"><i class="bi bi-book me-1"></i>Asignatura</label>
                        <div class="invalid-feedback">Selecciona una asignatura.</div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="date" class="form-control" id="fecha" name="fecha" required>
                        <label for="fecha" class="required"><i class="bi bi-calendar-date me-1"></i>Fecha de matrícula</label>
                        <div class="invalid-feedback">La fecha es obligatoria.</div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEliminar" action="../backend/matriculas_eliminar.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar <strong id="nombreRegistroEliminar"></strong>? Esta acción no se puede deshacer.</p>
                    <input type="hidden" name="id" id="idRegistroEliminar">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash3 me-1"></i> Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js?v=5"></script>
<script>
    function prepararNuevaMatricula() {
        document.getElementById('tituloModalMatricula').innerHTML = '<i class="bi bi-clipboard2-plus-fill me-1"></i> Nueva matrícula';
        document.getElementById('formMatricula').reset();
        document.getElementById('id_matricula').value = '';
        document.querySelectorAll('#formMatricula .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function prepararEditarMatricula(matricula) {
        document.getElementById('tituloModalMatricula').innerHTML = '<i class="bi bi-pencil-square me-1"></i> Editar matrícula';
        document.getElementById('id_matricula').value = matricula.id_matricula;
        document.getElementById('id_alumno_select').value = matricula.id_alumno;
        document.getElementById('id_asignatura_select').value = matricula.id_asignatura;
        document.getElementById('fecha').value = matricula.fecha;
        document.querySelectorAll('#formMatricula .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }
</script>
</body>
</html>
