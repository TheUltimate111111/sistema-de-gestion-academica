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

$asignaturas = $pdo->query(
    'SELECT id_asignatura, nombre, creditos
     FROM asignaturas
     ORDER BY nombre'
)->fetchAll(PDO::FETCH_ASSOC);

$usuario = $_SESSION['usuario_activo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Asignaturas | Gestión Académica</title>
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
                <h5 class="mb-0">Asignaturas</h5>
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
                    <span><i class="bi bi-journal-bookmark-fill text-info me-1"></i> Listado de asignaturas
                        <span class="badge bg-info ms-1"><?php echo count($asignaturas); ?></span>
                    </span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAsignatura" onclick="prepararNuevaAsignatura()">
                        <i class="bi bi-plus-lg me-1"></i> Nueva asignatura
                    </button>
                </div>

                <div class="card-body">

                    <div class="ga-filter-bar">
                        <div class="row g-2 align-items-end ga-filter-container" data-filter-table="tablaAsignaturas">
                            <div class="col-md-4">
                                <div class="ga-filter-label">Buscar</div>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control ga-filter-input" placeholder="Nombre de asignatura..."
                                           data-table-search="tablaAsignaturas">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="ga-filter-label">Créditos mín.</div>
                                <input type="number" class="form-control form-control-sm ga-filter-num-min" placeholder="1"
                                       data-filter-target="asigCreditos" min="1" max="10">
                            </div>
                            <div class="col-md-3">
                                <div class="ga-filter-label">Créditos máx.</div>
                                <input type="number" class="form-control form-control-sm ga-filter-num-max" placeholder="10"
                                       data-filter-target="asigCreditos" min="1" max="10">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="ga-filter-reset" title="Limpiar filtros"><i class="bi bi-x-circle"></i> Limpiar</button>
                            </div>
                        </div>
                    </div>

                    <div class="ga-floating-list" id="tablaAsignaturas">
                        <?php if (empty($asignaturas)): ?>
                            <div class="ga-empty-message">
                                <i class="bi bi-journal-x"></i>
                                <h6>No hay asignaturas registradas.</h6>
                                <p>Crea una nueva asignatura para empezar.</p>
                            </div>
                        <?php else: ?>
                            <?php
                            $colores = ['#ef4444', '#f97316', '#f59e0b', '#10b981', '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#f43f5e'];
                            foreach ($asignaturas as $asignatura):
                                $inicial = strtoupper(substr($asignatura['nombre'], 0, 1));
                                $colorIndex = ord($inicial) % count($colores);
                                $bgColor = $colores[$colorIndex];
                            ?>
                                <div class="ga-floating-row"
                                     data-asig-nombre="<?php echo htmlspecialchars($asignatura['nombre']); ?>"
                                     data-asig-creditos="<?php echo (int)$asignatura['creditos']; ?>">
                                    <div class="ga-row-content">
                                        <div class="ga-avatar" style="background-color: <?php echo $bgColor; ?>; border-radius: 12px;">
                                            <i class="bi bi-book"></i>
                                        </div>

                                        <div class="ga-row-info">
                                            <div>
                                                <span class="ga-row-label">Nombre de Asignatura</span>
                                                <span class="ga-row-title"><?php echo htmlspecialchars($asignatura['nombre']); ?></span>
                                            </div>
                                            <div>
                                                <span class="ga-row-label">Créditos</span>
                                                <span class="ga-row-value"><span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill"><?php echo (string)$asignatura['creditos']; ?> CRÉDITOS</span></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="ga-row-actions">
                                        <button type="button" class="btn btn-light text-primary border-0 shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalAsignatura"
                                                onclick="prepararEditarAsignatura(<?php echo htmlspecialchars(json_encode($asignatura, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>)">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-light text-danger border-0 shadow-sm btn-eliminar"
                                                data-id="<?php echo $asignatura['id_asignatura']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($asignatura['nombre']); ?>"
                                                data-url="../backend/asignaturas_eliminar.php">
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

<div class="modal fade" id="modalAsignatura" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="ga-form" id="formAsignatura" action="../backend/asignaturas_guardar.php" method="POST" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalAsignatura">
                        <i class="bi bi-journal-plus me-1"></i> Nueva asignatura
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <input type="hidden" name="id_asignatura" id="id_asignatura">

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="nombre_asignatura" name="nombre" placeholder="Nombre de la asignatura" required>
                        <label for="nombre_asignatura" class="required"><i class="bi bi-journal-text me-1"></i>Nombre de la asignatura</label>
                        <div class="invalid-feedback">El nombre es obligatorio.</div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="number" class="form-control" id="creditos" name="creditos" min="1" max="10" placeholder="Créditos" required>
                        <label for="creditos" class="required"><i class="bi bi-123 me-1"></i>Créditos</label>
                        <div class="invalid-feedback">Ingresa un número de créditos válido.</div>
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
            <form id="formEliminar" action="../backend/asignaturas_eliminar.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar la asignatura <strong id="nombreRegistroEliminar"></strong>? Esta acción no se puede deshacer.</p>
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
    function prepararNuevaAsignatura() {
        document.getElementById('tituloModalAsignatura').innerHTML = '<i class="bi bi-journal-plus me-1"></i> Nueva asignatura';
        document.getElementById('formAsignatura').reset();
        document.getElementById('id_asignatura').value = '';
        document.querySelectorAll('#formAsignatura .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function prepararEditarAsignatura(asignatura) {
        document.getElementById('tituloModalAsignatura').innerHTML = '<i class="bi bi-pencil-square me-1"></i> Editar asignatura';
        document.getElementById('id_asignatura').value = asignatura.id_asignatura;
        document.getElementById('nombre_asignatura').value = asignatura.nombre;
        document.getElementById('creditos').value = asignatura.creditos;
        document.querySelectorAll('#formAsignatura .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }
</script>
</body>
</html>
