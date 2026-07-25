<?php
/**
 * index.php
 * ---------------------------------------------------
 * Login del sistema "Gestión Académica".
 * El formulario envía por POST a procesar_login.php,
 * que valida las credenciales contra la BD con PDO.
 * ---------------------------------------------------
 */
declare(strict_types=1);
session_start();

// Si ya hay sesión activa, redirigir al dashboard
if (isset($_SESSION['usuario_activo'])) {
    header('Location: dashboard.php');
    exit;
}

// Recuperar mensaje flash (error de login, etc.)
$flash = null;
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión | Gestión Académica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de Gestión Académica — Panel de acceso para administradores y docentes.">

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons (CDN) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Estilos propios -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<div class="login-wrapper">
    <div class="card login-card">
        <div class="card-header">
            <div class="login-logo">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <h4 class="mb-1">Gestión Académica</h4>
            <p class="text-muted small">Ingresa tus credenciales para continuar</p>
        </div>

        <div class="card-body">

            <?php if ($flash): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flash['tipo']); ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-<?php echo $flash['tipo'] === 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo $flash['mensaje']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            <?php endif; ?>

            <form class="ga-form" id="formLogin"
                  action="../backend/includes/procesar_login.php"
                  method="POST"
                  novalidate>

                <div class="mb-3">
                    <label for="usuario" class="form-label required">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="usuario" name="usuario"
                               placeholder="Ingresa tu usuario" required autocomplete="username">
                    </div>
                    <div class="invalid-feedback">Ingresa tu usuario.</div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label required">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                               placeholder="••••••••" required autocomplete="current-password">
                        <span class="input-group-text cursor-pointer" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </span>
                    </div>
                    <div class="invalid-feedback">Ingresa tu contraseña.</div>
                </div>

                <button type="submit" class="btn btn-primary w-100" id="btnLogin">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Iniciar sesión
                </button>
            </form>

            <p class="text-center text-muted small mt-3 mb-0">
                &copy; <?php echo date('Y'); ?> Gestión Académica. Todos los derechos reservados.
            </p>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle (CDN) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
<script>
    // Mostrar / ocultar contraseña
    document.getElementById('togglePassword').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icono = this.querySelector('i');
        const esPassword = input.type === 'password';
        input.type = esPassword ? 'text' : 'password';
        icono.classList.toggle('bi-eye');
        icono.classList.toggle('bi-eye-slash');
    });
</script>
</body>
</html>
