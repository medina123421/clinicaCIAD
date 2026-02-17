<?php
/**
 * Lista de Visitas
 * Muestra el historial de visitas registradas
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Visita.php';

$page_title = 'Historial de Visitas';

$database = new Database();
$db = $database->getConnection();
// Necesitamos un método para listar todas las visitas, agregaré uno genérico en el modelo o haré query directa aquí por simplicidad inicial
// Idealmente el modelo debería tener obtenerTodas().
// Check Visita.php content again... it only has obtenerPorPaciente().
// I will add a quick query here or update model. Updating model is better practice but for speed here query is fine, 
// actually let's just add a method to getting recent visits directly here for now to save a turn, or update the model request?
// Let's stick to inline query for the list wrapper for now, user asked for "interfaces".

// Query para obtener últimas visitas
$query = "SELECT v.*, 
          CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) as paciente_nombre,
          p.numero_expediente,
          CONCAT(u.nombre, ' ', u.apellido_paterno) as doctor_nombre
          FROM visitas v
          JOIN pacientes p ON v.id_paciente = p.id_paciente
          JOIN usuarios u ON v.id_doctor = u.id_usuario
          ORDER BY v.fecha_visita DESC
          LIMIT 50";
$stmt = $db->prepare($query);
$stmt->execute();
$visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-calendar-check"></i> Historial de Visitas Médicas</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Visitas</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= $project_folder ?>/app/views/visitas/nueva.php" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Registrar Nueva Visita
        </a>
    </div>
</div>

<!-- Búsqueda -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" id="visitaSearchInput"
                placeholder="Buscar por paciente o número de expediente...">
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <?php if (count($visitas) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Doctor</th>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th>Estatus</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visitas as $visita): ?>
                            <tr>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($visita['fecha_visita'])) ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($visita['paciente_nombre']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($visita['numero_expediente']) ?></small>
                                </td>
                                <td>
                                    <?= htmlspecialchars($visita['doctor_nombre']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($visita['tipo_visita']) ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars(substr($visita['motivo_consulta'], 0, 50)) . (strlen($visita['motivo_consulta']) > 50 ? '...' : '') ?>
                                </td>
                                <td>
                                    <?php
                                    $estatusColor = 'secondary';
                                    switch ($visita['estatus']) {
                                        case 'Programada':
                                            $estatusColor = 'primary';
                                            break;
                                        case 'En Curso':
                                            $estatusColor = 'warning';
                                            break;
                                        case 'Completada':
                                            $estatusColor = 'success';
                                            break;
                                        case 'Cancelada':
                                            $estatusColor = 'danger';
                                            break;
                                    }
                                    ?>
                                    <span
                                        class="badge bg-<?= $estatusColor ?>"><?= htmlspecialchars($visita['estatus']) ?></span>
                                </td>
                                <td>
                                    <a href="../pacientes/detalle.php?id=<?= $visita['id_paciente'] ?>"
                                        class="btn btn-sm btn-outline-info" title="Ver Paciente">
                                        <i class="bi bi-person-eye"></i>
                                    </a>
                                    <a href="../especialidades/medicina_interna.php?id_visita=<?= $visita['id_visita'] ?>"
                                        class="btn btn-sm btn-outline-primary" title="Consulta Medicina Interna">
                                        <i class="bi bi-stethoscope"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No hay visitas registradas recientemente.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    $(document).ready(function () {
        const searchDebounce = debounce(function (searchTerm) {
            searchAjax(
                '../../ajax/buscar_visitas.php',
                searchTerm,
                'table tbody'
            );
        }, 500);

        $('#visitaSearchInput').on('keyup', function () {
            searchDebounce($(this).val());
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>