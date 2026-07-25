<?php
/**
 * dashboard.php
 * ---------------------------------------------------
 * Panel principal del Sistema de Gestión Académica.
 * Protegido por sesión. Muestra conteos reales desde la BD.
 * ---------------------------------------------------
 */
declare(strict_types=1);
session_start();

if (!isset($_SESSION['usuario_activo'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/../backend/includes/conexion.php';

// Recuperar flash message si existe
$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// Conteos reales desde la base de datos
$totalAlumnos       = (int)$pdo->query('SELECT COUNT(*) FROM alumnos')->fetchColumn();
$totalAsignaturas   = (int)$pdo->query('SELECT COUNT(*) FROM asignaturas')->fetchColumn();
$totalMatriculas    = (int)$pdo->query('SELECT COUNT(*) FROM matriculas')->fetchColumn();
$totalConvocatorias = (int)$pdo->query('SELECT COUNT(*) FROM convocatorias')->fetchColumn();

$usuario = $_SESSION['usuario_activo'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Gestión Académica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="ga-wrapper">

    <?php require 'sidebar.php'; ?>

    <div class="ga-content ga-animate-in">

        <!-- Barra superior -->
        <header class="ga-topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary ga-sidebar-toggle" type="button">
                    <i class="bi bi-list"></i>
                </button>
                <h5 class="mb-0">Dashboard</h5>
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

            <!-- ================= HERO SECTION ================= -->
            <section class="hero-section">
                <div class="hero-content">
                    <h1>Bienvenido de vuelta,<br><?php echo htmlspecialchars(explode(' ', trim($usuario['nombre']))[0]); ?></h1>
                    <p>Gestiona alumnos, asignaturas y mantén al día las matrículas y convocatorias desde este panel de control integrado.</p>
                    <div>
                        <a href="alumnos.php" class="btn btn-light text-primary fw-bold px-4 py-2 me-2 shadow-sm rounded-pill">
                            <i class="bi bi-person-plus-fill me-1"></i> Añadir Alumno
                        </a>
                    </div>
                </div>
                
                <div class="hero-image-container">
                    <!-- Espacio grande reservado para colocar una imagen -->
                    <div class="hero-image-placeholder">
                    
                        <!-- Cuando tengas la imagen, colócala aquí: -->
                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSsnD6YnMBjw3v7kIuD9z3BDxKmsP6Exc-giH94OPK6sQ&s=10" alt="Portada Dashboard">
                    </div>
                </div>
            </section>
            <!-- ================= FIN HERO SECTION ================= -->

            <!-- Tarjetas de resumen -->
            <div class="row g-3 mb-4 mt-1">
                <div class="col-sm-6 col-xl-3">
                    <div class="card ga-stat-card bg-alumnos">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-value"><?php echo $totalAlumnos; ?></div>
                                <div>Total Alumnos</div>
                            </div>
                            <i class="bi bi-people-fill stat-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card ga-stat-card bg-asignaturas">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-value"><?php echo $totalAsignaturas; ?></div>
                                <div>Total Asignaturas</div>
                            </div>
                            <i class="bi bi-journal-bookmark-fill stat-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card ga-stat-card bg-matriculas">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-value"><?php echo $totalMatriculas; ?></div>
                                <div>Total Matrículas</div>
                            </div>
                            <i class="bi bi-clipboard2-check-fill stat-icon"></i>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card ga-stat-card bg-convocatorias">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-value"><?php echo $totalConvocatorias; ?></div>
                                <div>Total Convocatorias</div>
                            </div>
                            <i class="bi bi-megaphone-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Accesos rápidos -->
            <div class="card ga-card">
                <div class="card-header bg-white">
                    <span><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Acciones rápidas</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-3">
                            <a href="alumnos.php" class="btn btn-outline-primary w-100 py-2">
                                <i class="bi bi-person-plus-fill me-1"></i> Lista de Alumnos
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="asignaturas.php" class="btn btn-outline-info w-100 py-2">
                                <i class="bi bi-journal-plus me-1"></i> Ver Asignaturas
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="matriculas.php" class="btn btn-outline-purple w-100 py-2" style="border-color:#6f42c1; color:#6f42c1;">
                                <i class="bi bi-clipboard2-plus-fill me-1"></i> Ver Matrículas
                            </a>
                        </div>
                        <div class="col-sm-6 col-lg-3">
                            <a href="convocatorias.php" class="btn btn-outline-warning w-100 py-2">
                                <i class="bi bi-megaphone me-1"></i> Ver Convocatorias
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js?v=4"></script>
</body>
</html>
