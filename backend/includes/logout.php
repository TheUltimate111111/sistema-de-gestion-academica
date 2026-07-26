<?php
/**
 * logout.php
 * ---------------------------------------------------
 * Destruye la sesión activa y redirige al login.
 * ---------------------------------------------------
 */
declare(strict_types=1);

session_start();
session_unset();
session_destroy();

header('Location: ../../index.php');
exit;
