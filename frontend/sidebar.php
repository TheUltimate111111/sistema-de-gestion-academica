<?php
$paginaActual = basename($_SERVER['PHP_SELF']);

function claseActiva(string $pagina, string $paginaActual): string {
    return $pagina === $paginaActual ? 'active' : '';
}

$usuarioNombre = $_SESSION['usuario_activo']['nombre'] ?? 'Usuario';
$usuarioRol    = $_SESSION['usuario_activo']['rol']    ?? '';
?>
<!-- Sidebar -->
<aside class="ga-sidebar" id="gaSidebar">
    <div class="sidebar-header">
        <div class="sidebar-brand">
            <i class="bi bi-mortarboard-fill"></i>
            <span>Gestión Académica</span>
        </div>
    </div>

    <ul class="nav flex-column mt-2">
        <li class="nav-item">
            <a class="nav-link <?php echo claseActiva('dashboard.php', $paginaActual); ?>" href="dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo claseActiva('alumnos.php', $paginaActual); ?>" href="alumnos.php">
                <i class="bi bi-people-fill"></i> Alumnos
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo claseActiva('asignaturas.php', $paginaActual); ?>" href="asignaturas.php">
                <i class="bi bi-journal-bookmark-fill"></i> Asignaturas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo claseActiva('matriculas.php', $paginaActual); ?>" href="matriculas.php">
                <i class="bi bi-clipboard2-check-fill"></i> Matrículas
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo claseActiva('convocatorias.php', $paginaActual); ?>" href="convocatorias.php">
                <i class="bi bi-megaphone-fill"></i> Convocatorias
            </a>
        </li>
    </ul>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="bi bi-person-circle text-secondary fs-5"></i>
            <div>
                <div class="small fw-semibold text-white"><?php echo htmlspecialchars($usuarioNombre); ?></div>
                <?php if ($usuarioRol): ?>
                    <div class="small text-muted" style="font-size:.7rem;"><?php echo ucfirst(htmlspecialchars($usuarioRol)); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <a href="../backend/includes/logout.php" class="nav-link text-danger px-0">
            <i class="bi bi-box-arrow-right"></i> Cerrar sesión
        </a>
    </div>
</aside>

<!-- Backdrop para móvil -->
<div class="ga-backdrop" id="gaBackdrop"></div>
