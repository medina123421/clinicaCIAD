<?php
session_start();
include '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Educación en Diabetes</h2>
        <a href="../../index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="alert alert-info py-5 text-center shadow-sm">
        <i class="bi bi-book fs-1 d-block mb-3 text-primary"></i>
        <h4 class="fw-bold">Módulo en Desarrollo</h4>
        <p class="mb-0 fs-5">En esta sección se registrará el progreso del esquema educativo y talleres para pacientes.
        </p>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>