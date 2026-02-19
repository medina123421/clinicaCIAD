<?php
/**
 * Configuración Global de Rutas
 * Detecta automáticamente la carpeta del proyecto en htdocs
 */

// Detectar la carpeta del proyecto dinámicamente
// dirname(dirname(__DIR__)) apunta a la raíz del proyecto (c:\xampp\htdocs\Clinica InvestLab)
$base_dir = str_replace('\\', '/', dirname(dirname(__DIR__)));
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);

// Extraer la ruta relativa desde el DOCUMENT_ROOT
$project_path = str_replace($doc_root, '', $base_dir);

// Asegurar que comience con / y no termine con /
$project_path = '/' . trim($project_path, '/');
if ($project_path === '/') {
    $project_path = '';
}

// Definir constante global
if (!defined('PROJECT_PATH')) {
    define('PROJECT_PATH', $project_path);
}

// Definir constante para la URL base (opcional para uso futuro)
if (!defined('BASE_URL')) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    define('BASE_URL', $protocol . "://" . $_SERVER['HTTP_HOST'] . PROJECT_PATH);
}
?>