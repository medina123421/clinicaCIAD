<?php
session_start();
include '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Actividad Física</h2>
        <a href="<?= PROJECT_PATH ?>/index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="alert alert-info py-5 text-center shadow-sm">
        <i class="bi bi-activity fs-1 d-block mb-3 text-warning"></i>
        <h4 class="fw-bold">Módulo en Desarrollo</h4>
        <p class="mb-0 fs-5">La sección de <strong>Actividad Física</strong> incluirá herramientas para el seguimiento
            de rutinas y condición física de los pacientes.</p>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>