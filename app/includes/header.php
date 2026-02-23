<?php
/**
 * Header común de la aplicación
 * Incluye navegación y estilos
 */

require_once __DIR__ . '/config.php';

// Título de la página (puede ser sobrescrito)
$page_title = $page_title ?? 'CIADI';

// Forzar UTF-8 en la salida
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>
        <?= htmlspecialchars($page_title) ?> - CIADI
    </title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap 5 JS Bundle (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- jQuery (Necesario para scripts en línea) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Custom CSS -->
    <link href="<?= PROJECT_PATH ?>/app/assets/css/custom.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= PROJECT_PATH ?>/index.php">
                <img src="<?= PROJECT_PATH ?>/app/assets/img/logo_ciadi.png" alt="CIADI" height="30"
                    class="d-inline-block align-top me-2">
                CIADI
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= PROJECT_PATH ?>/index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= PROJECT_PATH ?>/app/views/pacientes/lista.php">
                            <i class="bi bi-people-fill"></i> Pacientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= PROJECT_PATH ?>/app/views/visitas/lista.php">
                            <i class="bi bi-calendar-check"></i> Visitas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= PROJECT_PATH ?>/app/views/analisis/registro_completo.php">
                            <i class="bi bi-clipboard2-pulse"></i> Registrar Análisis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= PROJECT_PATH ?>/app/views/reportes/index.php">
                            <i class="bi bi-file-earmark-pdf"></i> Reportes
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-stethoscope"></i> Especialidades
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item"
                                    href="<?= PROJECT_PATH ?>/app/views/especialidades/medicina_interna.php">Medicina
                                    Interna</a></li>
                            <li><a class="dropdown-item"
                                    href="<?= PROJECT_PATH ?>/app/views/especialidades/nutricion.php">Nutrición
                                    Clínica</a></li>
                            <li><a class="dropdown-item"
                                    href="<?= PROJECT_PATH ?>/app/views/especialidades/psicologia.php">Psicología
                                    Clínica</a></li>
                            <li><a class="dropdown-item"
                                    href="<?= PROJECT_PATH ?>/app/views/especialidades/actividad_fisica.php">Actividad
                                    Física</a></li>
                            <li><a class="dropdown-item"
                                    href="<?= PROJECT_PATH ?>/app/views/especialidades/cuidado_pies.php">Cuidado de
                                    los pies</a></li>
                            <li><a class="dropdown-item"
                                    href="<?= PROJECT_PATH ?>/app/views/especialidades/educacion_diabetes.php">Educación
                                    en Diabetes</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($usuario_nombre ?? 'Usuario') ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= PROJECT_PATH ?>/logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                                </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <div class="container-fluid mt-4">