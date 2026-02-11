<?php
/**
 * Middleware de Autenticación Corregido
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nombre'])) {
    // Redirigir al login en la raíz
    header('Location: login.php');
    exit();
}

// Ejemplo de cómo debería quedar esa parte para evitar el error
$usuario_email = $_SESSION['usuario_email'] ?? 'invitado@mail.com';
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_rol = $_SESSION['usuario_rol'] ?? 'Sin Rol';