<?php
/**
 * Dashboard Principal
 * Página de inicio con estadísticas y resumen
 */

// RUTAS CORREGIDAS: Se agrega el prefijo 'app/' porque los archivos están en esa carpeta
require_once 'app/includes/auth.php';
require_once 'app/config/database.php';

$page_title = 'Dashboard';

// Obtener estadísticas
$database = new Database();
$db = $database->getConnection();

try {
    // Total de pacientes activos
    $query = "SELECT COUNT(*) as total FROM pacientes WHERE activo = 1";
    $stmt = $db->query($query);
    $total_pacientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Visitas del mes actual
    $query = "SELECT COUNT(*) as total FROM visitas 
              WHERE MONTH(fecha_visita) = MONTH(CURRENT_DATE()) 
              AND YEAR(fecha_visita) = YEAR(CURRENT_DATE())";
    $stmt = $db->query($query);
    $visitas_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Pacientes con control inadecuado (HbA1c > 7%)
    $query = "SELECT COUNT(DISTINCT v.id_paciente) as total
              FROM visitas v
              JOIN analisis_glucosa ag ON v.id_visita = ag.id_visita
              WHERE ag.hemoglobina_glicosilada > 7.0
              AND ag.id_analisis IN (
                  SELECT MAX(ag2.id_analisis)
                  FROM analisis_glucosa ag2
                  JOIN visitas v2 ON ag2.id_visita = v2.id_visita
                  WHERE v2.id_paciente = v.id_paciente
                  GROUP BY v2.id_paciente
              )";
    $stmt = $db->query($query);
    $pacientes_descontrolados = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Próximas citas (próximos 7 días)
    $query = "SELECT COUNT(*) as total FROM visitas 
              WHERE proxima_cita BETWEEN CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)";
    $stmt = $db->query($query);
    $proximas_citas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Últimos pacientes registrados
    $query = "SELECT id_paciente, numero_expediente, 
              CONCAT(nombre, ' ', apellido_paterno, ' ', IFNULL(apellido_materno, '')) as nombre_completo,
              edad, sexo, fecha_registro
              FROM pacientes 
              WHERE activo = 1
              ORDER BY fecha_registro DESC 
              LIMIT 5";
    $stmt = $db->query($query);
    $ultimos_pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    $total_pacientes = $visitas_mes = $pacientes_descontrolados = $proximas_citas = 0;
    $ultimos_pacientes = [];
}

// RUTA CORREGIDA: Header dentro de app
include 'app/includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4">
            <i class="bi bi-speedometer2"></i> Dashboard
        </h2>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Total Pacientes</p>
                        <h3 class="stat-number mb-0">
                            <?= number_format($total_pacientes) ?>
                        </h3>
                    </div>
                    <i class="bi bi-people-fill stat-icon text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Visitas Este Mes</p>
                        <h3 class="stat-number mb-0">
                            <?= number_format($visitas_mes) ?>
                        </h3>
                    </div>
                    <i class="bi bi-calendar-check stat-icon text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Control Inadecuado</p>
                        <h3 class="stat-number mb-0">
                            <?= number_format($pacientes_descontrolados) ?>
                        </h3>
                    </div>
                    <i class="bi bi-exclamation-triangle-fill stat-icon text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card stat-card warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label mb-1">Próximas Citas</p>
                        <h3 class="stat-number mb-0">
                            <?= number_format($proximas_citas) ?>
                        </h3>
                    </div>
                    <i class="bi bi-clock-fill stat-icon text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-plus-fill"></i> Últimos Pacientes Registrados
            </div>
            <div class="card-body">
                <?php if (count($ultimos_pacientes) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Expediente</th>
                                    <th>Nombre</th>
                                    <th>Edad</th>
                                    <th>Sexo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimos_pacientes as $paciente): ?>
                                    <tr class="cursor-pointer"
                                        onclick="window.location.href='app/views/pacientes/detalle.php?id=<?= $paciente['id_paciente'] ?>'">
                                        <td>
                                            <?= htmlspecialchars($paciente['numero_expediente']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($paciente['nombre_completo']) ?>
                                        </td>
                                        <td>
                                            <?= $paciente['edad'] ?> años
                                        </td>
                                        <td>
                                            <?= $paciente['sexo'] === 'M' ? 'Masculino' : 'Femenino' ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="app/views/pacientes/lista.php" class="btn btn-primary btn-sm">
                            <i class="bi bi-list-ul"></i> Ver Todos los Pacientes
                        </a>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No hay pacientes registrados aún.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-lightning-fill"></i> Accesos Rápidos
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?= $project_folder ?>/app/views/pacientes/nuevo.php" class="btn btn-primary">
                        <i class="bi bi-person-plus"></i> Registrar Nuevo Paciente
                    </a>
                    <a href="<?= $project_folder ?>/app/views/visitas/nueva.php" class="btn btn-success">
                        <i class="bi bi-calendar-plus"></i> Registrar Nueva Visita
                    </a>
                    <a href="<?= $project_folder ?>/app/views/analisis/glucosa.php" class="btn btn-info text-white">
                        <i class="bi bi-clipboard2-pulse"></i> Registrar Análisis de Glucosa
                    </a>
                    <a href="<?= $project_folder ?>/app/views/reportes/index.php" class="btn btn-warning">
                        <i class="bi bi-file-earmark-pdf"></i> Generar Reportes
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-info-circle-fill"></i> Información del Sistema
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Usuario:</strong>
                    <?= htmlspecialchars($usuario_nombre ?? 'No identificado') ?>
                </p>
                <p class="mb-2">
                    <strong>Rol:</strong>
                    <?= htmlspecialchars($usuario_rol ?? 'Sin rol') ?>
                </p>
                <p class="mb-0">
                    <strong>Último acceso:</strong>
                    <?= date('d/m/Y H:i') ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php
// RUTA CORREGIDA: Footer dentro de app
include 'app/includes/footer.php';
?>