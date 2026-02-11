<?php
/**
 * Middleware de Autenticación
 * Verifica que el usuario esté autenticado
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nombre'])) {
    // Redirigir al login
    header('Location: /app/login.php');
    exit();
}

// Regenerar ID de sesión periódicamente para seguridad
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // Cada 5 minutos
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Variables globales del usuario autenticado
$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];
$usuario_rol = $_SESSION['usuario_rol'];
$usuario_email = $_SESSION['usuario_email'];
