<?php
/**
 * Middleware de Autenticación Corregido
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tentar detectar la ruta base si no existe
$base_path = str_replace('\\', '/', dirname(dirname(__DIR__)));
$document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$folder_name = str_replace($document_root, '', $base_path);

// Asegurar que comience con / si no está vacío
if (!empty($folder_name) && $folder_name[0] !== '/') {
    $folder_name = '/' . $folder_name;
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['usuario_nombre'])) {
    // Redirigir al login en la raíz (usando ruta absoluta relativa al host)
    $login_path = ($folder_name ?: '') . '/login.php';
    header("Location: $login_path");
    exit();
}

// Ejemplo de cómo debería quedar esa parte para evitar el error
$usuario_email = $_SESSION['usuario_email'] ?? 'invitado@mail.com';
$usuario_nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$usuario_rol = $_SESSION['usuario_rol'] ?? 'Sin Rol';