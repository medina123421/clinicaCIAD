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
    <link href="<?= PROJECT_PATH ?>/app/assets/css/dashboard.css" rel="stylesheet">
</head>

<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <a href="<?= PROJECT_PATH ?>/index.php" class="sidebar-logo">
                <img src="<?= PROJECT_PATH ?>/app/assets/img/logo_ciadi.png" alt="CIADI">
                <span>CIADI</span>
            </a>

            <div class="nav-section">
                <div class="nav-section-title">Menú Principal</div>
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/index.php"
                            class="nav-link-custom <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/app/views/pacientes/lista.php"
                            class="nav-link-custom <?= strpos($_SERVER['PHP_SELF'], 'pacientes') !== false ? 'active' : '' ?>">
                            <i class="bi bi-people-fill"></i>
                            <span>Pacientes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/app/views/visitas/lista.php"
                            class="nav-link-custom <?= strpos($_SERVER['PHP_SELF'], 'visitas') !== false ? 'active' : '' ?>">
                            <i class="bi bi-calendar-check"></i>
                            <span>Visitas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/app/views/analisis/registro_completo.php"
                            class="nav-link-custom <?= strpos($_SERVER['PHP_SELF'], 'analisis') !== false ? 'active' : '' ?>">
                            <i class="bi bi-clipboard2-pulse"></i>
                            <span>Registrar Análisis</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/app/views/reportes/index.php"
                            class="nav-link-custom <?= strpos($_SERVER['PHP_SELF'], 'reportes') !== false ? 'active' : '' ?>">
                            <i class="bi bi-file-earmark-pdf"></i>
                            <span>Reportes</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Especialidades</div>
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/app/views/especialidades/medicina_interna.php"
                            class="nav-link-custom">
                            <i class="bi bi-heart-pulse"></i>
                            <span>Medicina Interna</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/app/views/especialidades/nutricion.php" class="nav-link-custom">
                            <i class="bi bi-clipboard2-data"></i>
                            <span>Nutrición</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/app/views/especialidades/psicologia.php" class="nav-link-custom">
                            <i class="bi bi-emoji-smile"></i>
                            <span>Psicología</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/app/views/especialidades/actividad_fisica.php"
                            class="nav-link-custom">
                            <i class="bi bi-bicycle"></i>
                            <span>Actividad Física</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/app/views/especialidades/cuidado_pies.php" class="nav-link-custom">
                            <i class="bi bi-bandaid"></i>
                            <span>Cuidado de Pies</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/app/views/especialidades/educacion_diabetes.php"
                            class="nav-link-custom">
                            <i class="bi bi-book"></i>
                            <span>Educación</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="nav-section mt-auto">
                <div class="nav-section-title">Sesión</div>
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="<?= PROJECT_PATH ?>/logout.php" class="nav-link-custom text-danger">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Cerrar Sesión</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Bar -->
            <header class="top-bar border-bottom">
                <div></div> <!-- Espacio reservado -->

                <div class="top-bar-icons">
                    <a href="#" class="icon-btn">
                        <i class="bi bi-chat-dots"></i>
                        <span class="badge-dot"></span>
                    </a>
                    <a href="#" class="icon-btn">
                        <i class="bi bi-heart"></i>
                    </a>
                    <div class="user-profile">
                        <div class="user-avatar d-flex align-items-center justify-content-center bg-light">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="d-none d-md-block">
                            <div class="fw-bold fs-7"><?= htmlspecialchars($usuario_nombre ?? 'Usuario') ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($usuario_rol ?? 'Admin') ?></div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="container-fluid p-0">