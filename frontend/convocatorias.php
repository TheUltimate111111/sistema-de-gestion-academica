<?php
/**
 * convocatorias.php
 * ---------------------------------------------------
 * Módulo CONVOCATORIAS — Conectado a BD.
 * Protegido por sesión.
 * ---------------------------------------------------
 */
declare(strict_types=1);
session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../backend/includes/conexion.php';

$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// Cargar datos reales desde la base de datos
$convocatorias = $pdo->query(
    'SELECT id_convocatoria, nombre, fecha_inicio, fecha_fin
     FROM convocatorias
     ORDER BY fecha_inicio DESC'
)->fetchAll(PDO::FETCH_ASSOC);

$usuario = $_SESSION['usuario_activo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Convocatorias | Gestión Académica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

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
                <h5 class="mb-0">Convocatorias</h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-circle fs-4 text-secondary"></i>
                <span class="d-none d-sm-inline"><?php echo htmlspecialchars($usuario['nombre']); ?></span>
            </div>
        </header>

        <main class="ga-main">

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flash['tipo']); ?> alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bi bi-<?php echo $flash['tipo'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo $flash['mensaje']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <div class="card ga-card">
                <div class="card-header">
                    <span><i class="bi bi-megaphone-fill text-warning me-1"></i> Listado de convocatorias
                        <span class="badge bg-warning text-dark ms-1"><?php echo count($convocatorias); ?></span>
                    </span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalConvocatoria" onclick="prepararNuevaConvocatoria()">
                        <i class="bi bi-plus-lg me-1"></i> Nueva convocatoria
                    </button>
                </div>

                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" placeholder="Buscar convocatoria..."
                                       data-table-search="tablaConvocatorias">
                            </div>
                        </div>
                    </div>

                    <!-- Listado Premium de Convocatorias -->
                    <div class="ga-floating-list" id="tablaConvocatorias">
                        <?php if (empty($convocatorias)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                <h5>No hay convocatorias registradas.</h5>
                                <p>Crea una nueva convocatoria para empezar.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($convocatorias as $convocatoria):
                                $hoy = date('Y-m-d');
                                if ($hoy < $convocatoria['fecha_inicio']) {
                                    $estado = ['texto' => 'Próxima', 'clase' => 'text-bg-secondary'];
                                    $bgColor = '#64748b'; // Gray
                                } elseif ($hoy > $convocatoria['fecha_fin']) {
                                    $estado = ['texto' => 'Finalizada', 'clase' => 'text-bg-danger'];
                                    $bgColor = '#ef4444'; // Red
                                } else {
                                    $estado = ['texto' => 'Activa', 'clase' => 'text-bg-success'];
                                    $bgColor = '#10b981'; // Green
                                }
                            ?>
                                <div class="ga-floating-row">
                                    <div class="ga-row-content">
                                        <!-- Avatar -->
                                        <div class="ga-avatar" style="background-color: <?php echo $bgColor; ?>; border-radius: 8px;">
                                            <i class="bi bi-megaphone"></i>
                                        </div>
                                        
                                        <!-- Info -->
                                        <div class="ga-row-info">
                                            <div>
                                                <span class="ga-row-label">Nombre</span>
                                                <span class="ga-row-title"><?php echo htmlspecialchars($convocatoria['nombre']); ?></span>
                                            </div>
                                            <div>
                                                <span class="ga-row-label">Periodo</span>
                                                <span class="ga-row-value"><i class="bi bi-calendar-event text-muted me-1"></i><?php echo htmlspecialchars($convocatoria['fecha_inicio']) . ' al ' . htmlspecialchars($convocatoria['fecha_fin']); ?></span>
                                            </div>
                                            <div>
                                                <span class="ga-row-label">Estado</span>
                                                <span class="ga-row-value"><span class="badge <?php echo $estado['clase']; ?> rounded-pill px-3"><?php echo $estado['texto']; ?></span></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="ga-row-actions">
                                        <button type="button" class="btn btn-light text-primary border-0 shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalConvocatoria"
                                                onclick='prepararEditarConvocatoria(<?php echo json_encode($convocatoria); ?>)'>
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-light text-danger border-0 shadow-sm btn-eliminar"
                                                data-id="<?php echo $convocatoria['id_convocatoria']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($convocatoria['nombre']); ?>"
                                                data-url="../backend/convocatorias_eliminar.php">
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

<!-- ===================== MODAL: Agregar / Editar convocatoria ===================== -->
<div class="modal fade" id="modalConvocatoria" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="ga-form" id="formConvocatoria" action="../backend/convocatorias_guardar.php" method="POST" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalConvocatoria">
                        <i class="bi bi-megaphone me-1"></i> Nueva convocatoria
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <input type="hidden" name="id_convocatoria" id="id_convocatoria">

                    <div class="form-floating mb-3">
                        <input type="text" class="form-control" id="nombre_convocatoria" name="nombre" placeholder="Nombre de la convocatoria" required>
                        <label for="nombre_convocatoria" class="required"><i class="bi bi-megaphone me-1"></i>Nombre de la convocatoria</label>
                        <div class="invalid-feedback">El nombre es obligatorio.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                                <label for="fecha_inicio" class="required"><i class="bi bi-calendar-check me-1"></i>Fecha inicio</label>
                                <div class="invalid-feedback">La fecha de inicio es obligatoria.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                                <label for="fecha_fin" class="required"><i class="bi bi-calendar-x me-1"></i>Fecha fin</label>
                                <div class="invalid-feedback">La fecha fin no puede ser anterior a la fecha inicio.</div>
                            </div>
                        </div>
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

<!-- ===================== MODAL: Confirmar eliminación ===================== -->
<div class="modal fade" id="modalConfirmarEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="formEliminar" action="../backend/convocatorias_eliminar.php" method="POST">
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
<script src="js/app.js?v=2"></script>
<script>
    function prepararNuevaConvocatoria() {
        document.getElementById('tituloModalConvocatoria').innerHTML = '<i class="bi bi-megaphone me-1"></i> Nueva convocatoria';
        document.getElementById('formConvocatoria').reset();
        document.getElementById('id_convocatoria').value = '';
        document.querySelectorAll('#formConvocatoria .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function prepararEditarConvocatoria(convocatoria) {
        document.getElementById('tituloModalConvocatoria').innerHTML = '<i class="bi bi-pencil-square me-1"></i> Editar convocatoria';
        document.getElementById('id_convocatoria').value = convocatoria.id_convocatoria;
        document.getElementById('nombre_convocatoria').value = convocatoria.nombre;
        document.getElementById('fecha_inicio').value = convocatoria.fecha_inicio;
        document.getElementById('fecha_fin').value = convocatoria.fecha_fin;
        document.querySelectorAll('#formConvocatoria .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }
</script>
</body>
</html>
