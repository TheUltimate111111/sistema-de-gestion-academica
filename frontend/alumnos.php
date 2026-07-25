<?php
/**
 * alumnos.php
 * ---------------------------------------------------
 * Módulo ALUMNOS — CRUD completo conectado a la BD.
 * Protegido por sesión.
 * Los formularios hacen POST tradicional al backend,
 * que procesa y redirige de vuelta con un flash message.
 * ---------------------------------------------------
 */
declare(strict_types=1);
session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../backend/includes/conexion.php';

// Recuperar flash message
$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// Cargar datos reales desde la base de datos
$alumnos = $pdo->query(
    'SELECT id_alumno, nombre, apellido, cedula, correo, telefono
     FROM alumnos
     ORDER BY apellido, nombre'
)->fetchAll(PDO::FETCH_ASSOC);

$usuario = $_SESSION['usuario_activo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alumnos | Gestión Académica</title>
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
                <h5 class="mb-0">Alumnos</h5>
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
                    <span><i class="bi bi-people-fill text-primary me-1"></i> Listado de alumnos
                        <span class="badge bg-primary ms-1"><?php echo count($alumnos); ?></span>
                    </span>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAlumno" onclick="prepararNuevoAlumno()">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo alumno
                    </button>
                </div>

                <div class="card-body">

                    <!-- Buscador -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" placeholder="Buscar alumno..."
                                       data-table-search="tablaAlumnos">
                            </div>
                        </div>
                    </div>

                    <!-- Listado Premium de Alumnos -->
                    <div class="ga-floating-list" id="tablaAlumnos">
                        <?php if (empty($alumnos)): ?>
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                <h5>No hay alumnos registrados.</h5>
                                <p>Agrega un alumno nuevo para empezar.</p>
                            </div>
                        <?php else: ?>
                            <?php 
                            // Array de colores para avatares aleatorios pero consistentes por letra
                            $colores = ['#ef4444', '#f97316', '#f59e0b', '#10b981', '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#f43f5e'];
                            foreach ($alumnos as $alumno): 
                                $inicial = strtoupper(substr($alumno['nombre'], 0, 1));
                                $colorIndex = ord($inicial) % count($colores);
                                $bgColor = $colores[$colorIndex];
                            ?>
                                <div class="ga-floating-row">
                                    <div class="ga-row-content">
                                        <!-- Avatar -->
                                        <div class="ga-avatar" style="background-color: <?php echo $bgColor; ?>">
                                            <?php echo $inicial; ?>
                                        </div>
                                        
                                        <!-- Info -->
                                        <div class="ga-row-info">
                                            <div>
                                                <span class="ga-row-label">Nombre Completo</span>
                                                <span class="ga-row-title"><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?></span>
                                            </div>
                                            <div>
                                                <span class="ga-row-label">Cédula</span>
                                                <span class="ga-row-value"><i class="bi bi-person-vcard text-muted me-1"></i><?php echo htmlspecialchars($alumno['cedula']); ?></span>
                                            </div>
                                            <div>
                                                <span class="ga-row-label">Correo</span>
                                                <span class="ga-row-value"><i class="bi bi-envelope text-muted me-1"></i><?php echo htmlspecialchars($alumno['correo']); ?></span>
                                            </div>
                                            <div>
                                                <span class="ga-row-label">Teléfono</span>
                                                <span class="ga-row-value"><i class="bi bi-telephone text-muted me-1"></i><?php echo htmlspecialchars($alumno['telefono']); ?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Acciones -->
                                    <div class="ga-row-actions">
                                        <button type="button" class="btn btn-light text-primary border-0 shadow-sm"
                                                data-bs-toggle="modal" data-bs-target="#modalAlumno"
                                                onclick='prepararEditarAlumno(<?php echo json_encode($alumno); ?>)'>
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </button>
                                        <button type="button" class="btn btn-light text-danger border-0 shadow-sm btn-eliminar"
                                                data-id="<?php echo $alumno['id_alumno']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?>"
                                                data-url="../backend/alumnos_eliminar.php">
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

<!-- ===================== MODAL: Agregar / Editar alumno ===================== -->
<div class="modal fade" id="modalAlumno" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form class="ga-form" id="formAlumno"
                  action="../backend/alumnos_guardar.php"
                  method="POST"
                  novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModalAlumno">
                        <i class="bi bi-person-plus-fill me-1"></i> Nuevo alumno
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <input type="hidden" name="id_alumno" id="id_alumno">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Nombre" data-tipo="letras" required>
                                <label for="nombre" class="required"><i class="bi bi-person me-1"></i>Nombre</label>
                                <div class="invalid-feedback">El nombre es obligatorio.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="apellido" name="apellido" placeholder="Apellido" data-tipo="letras" required>
                                <label for="apellido" class="required"><i class="bi bi-person-fill me-1"></i>Apellido</label>
                                <div class="invalid-feedback">El apellido es obligatorio.</div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="cedula" name="cedula" placeholder="Cédula"
                                       data-tipo="cedula" required maxlength="10" inputmode="numeric">
                                <label for="cedula" class="required"><i class="bi bi-person-vcard me-1"></i>Cédula</label>
                                <div class="invalid-feedback">Cédula obligatoria (solo números).</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="telefono" name="telefono" placeholder="Teléfono" data-tipo="telefono" required maxlength="10" inputmode="numeric">
                                <label for="telefono" class="required"><i class="bi bi-telephone me-1"></i>Teléfono</label>
                                <div class="invalid-feedback">El teléfono es obligatorio.</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="correo" name="correo" placeholder="Correo electrónico"
                               data-tipo="email" required>
                        <label for="correo" class="required"><i class="bi bi-envelope me-1"></i>Correo electrónico</label>
                        <div class="invalid-feedback">Ingresa un correo electrónico válido.</div>
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
            <form id="formEliminar" action="../backend/alumnos_eliminar.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="bi bi-exclamation-triangle-fill me-1"></i> Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar a <strong id="nombreRegistroEliminar"></strong>? Esta acción no se puede deshacer.</p>
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
    function prepararNuevoAlumno() {
        document.getElementById('tituloModalAlumno').innerHTML = '<i class="bi bi-person-plus-fill me-1"></i> Nuevo alumno';
        document.getElementById('formAlumno').reset();
        document.getElementById('id_alumno').value = '';
        document.querySelectorAll('#formAlumno .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function prepararEditarAlumno(alumno) {
        document.getElementById('tituloModalAlumno').innerHTML = '<i class="bi bi-pencil-square me-1"></i> Editar alumno';
        document.getElementById('id_alumno').value  = alumno.id_alumno;
        document.getElementById('nombre').value     = alumno.nombre;
        document.getElementById('apellido').value   = alumno.apellido;
        document.getElementById('cedula').value     = alumno.cedula;
        document.getElementById('correo').value     = alumno.correo;
        document.getElementById('telefono').value   = alumno.telefono;
        document.querySelectorAll('#formAlumno .is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }
</script>
</body>
</html>
