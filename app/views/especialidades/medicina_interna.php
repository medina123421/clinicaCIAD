<?php
session_start();
include '../../includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Medicina Interna</h2>
        <a href="../../index.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>

    <div class="alert alert-info py-5 text-center shadow-sm">
        <i class="bi bi-info-circle fs-1 d-block mb-3 text-primary"></i>
        <h4 class="fw-bold">Módulo en Desarrollo</h4>
        <p class="mb-0 fs-5">La sección de <strong>Medicina Interna</strong> se encuentra actualmente configurándose
            para el registro de consultas especializadas.</p>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>