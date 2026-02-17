<?php
/**
 * Index de Reportes
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Analisis.php';

$database = new Database();
$db = $database->getConnection();
$analisis_model = new Analisis($db);

$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$reportes = $analisis_model->obtenerReporteGeneral($fecha_inicio, $fecha_fin);
$total_registros = count($reportes);

$page_title = 'Reportes Clínicos';
include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div
            class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Reportes y Exportación</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <a href="exportar.php?tipo=excel&fecha_inicio=<?= $fecha_inicio ?>&fecha_fin=<?= $fecha_fin ?>"
                        target="_blank" class="btn btn-sm btn-outline-success">
                        <i class="bi bi-file-earmark-excel"></i> Exportar Lista Excel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros y Total -->
<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" class="form-control" name="fecha_inicio" value="<?= $fecha_inicio ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Fecha Fin</label>
                <input type="date" class="form-control" name="fecha_fin" value="<?= $fecha_fin ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
            <div class="col-md-4 text-end">
                <h5 class="mb-0 text-primary">Total Registros: <span
                        class="badge bg-primary"><?= $total_registros ?></span></h5>
            </div>
        </form>
    </div>
</div>

<!-- Búsqueda en Tabla -->
<div class="row mb-3">
    <div class="col-md-4">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" id="reporteSearchInput"
                placeholder="Filtrar resultados por paciente o expediente...">
        </div>
    </div>
</div>

<!-- Tabla de Resultados -->
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Expediente</th>
                <th>Bio. Hemática</th>
                <th>Química S.</th>
                <th>Ex. Orina</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($total_registros > 0): ?>
                <?php foreach ($reportes as $fila): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($fila['fecha_visita'])) ?></td>
                        <td><?= htmlspecialchars($fila['nombre_completo']) ?></td>
                        <td><?= htmlspecialchars($fila['numero_expediente']) ?></td>
                        <td class="text-center">
                            <?= $fila['id_biometria'] ? '<span class="badge bg-success small">Sí</span>' : '<span class="text-muted small">-</span>' ?>
                        </td>
                        <td class="text-center">
                            <?= $fila['id_quimica'] ? '<span class="badge bg-success small">Sí</span>' : '<span class="text-muted small">-</span>' ?>
                        </td>
                        <td class="text-center">
                            <?= $fila['id_orina'] ? '<span class="badge bg-success small">Sí</span>' : '<span class="text-muted small">-</span>' ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <!-- Botón Imprimir Resultados -->
                                <a href="imprimir.php?id=<?= $fila['id_visita'] ?>" target="_blank"
                                    class="btn btn-sm btn-outline-dark" title="Imprimir Reporte">
                                    <i class="bi bi-printer-fill"></i>
                                </a>
                                <!-- Botón Ver Detalle -->
                                <a href="../pacientes/detalle.php?id=<?= $fila['id_visita'] ?>&tab=historial"
                                    class="btn btn-sm btn-outline-info" title="Ver Paciente">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-3">No hay registros para mostrar.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
$(document).ready(function() {
    $("#reporteSearchInput").on("keyup", function() {
        const value = $(this).val().toLowerCase();
        $("table tbody tr").filter(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(value) > -1);
        });
    });
});
</script>
<?php include '../../includes/footer.php'; ?>
