<?php
/**
 * Página principal de usuarios - Redirección automática
 * Redirige al dashboard principal del sistema
 */
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

// Redirigir automáticamente al dashboard principal
header('Location: dashboard.php');
exit();
?>
