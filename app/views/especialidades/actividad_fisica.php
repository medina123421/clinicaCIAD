<?php
session_start();
include '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-activity text-success"></i> Actividad Física</h2>
            <?php if ($paciente): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="../pacientes/detalle.php?id=<?= (int)$paciente['id_paciente'] ?>">
                                <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?>
                            </a>
                        </li>
                        <li class="breadcrumb-item active">Evaluación Actividad Física</li>
                    </ol>
                </nav>
            <?php else: ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Selección de Paciente</li>
                    </ol>
                </nav>
            <?php endif; ?>
        </div>
        <div class="btn-group">
            <a href="../../index.php" class="btn btn-light shadow-sm"><i class="bi bi-house"></i> Home</a>
            <a href="../visitas/lista.php" class="btn btn-success shadow-sm"><i class="bi bi-clipboard-data"></i> Visitas</a>
        </div>
    </div>

    <div class="alert alert-info py-5 text-center shadow-sm">
        <i class="bi bi-activity fs-1 d-block mb-3 text-warning"></i>
        <h4 class="fw-bold">Módulo en Desarrollo</h4>
        <p class="mb-0 fs-5">La sección de <strong>Actividad Física</strong> incluirá herramientas para el seguimiento
            de rutinas y condición física de los pacientes.</p>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>