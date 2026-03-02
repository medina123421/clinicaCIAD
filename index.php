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
              edad, sexo, created_at
              FROM pacientes 
              WHERE activo = 1
              ORDER BY created_at DESC 
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

<div class="row g-4 mb-4">
    <!-- Welcome Card -->
    <div class="col-lg-12">
        <div class="welcome-card">
            <div class="welcome-content" style="max-width: 100%;">
                <p class="text-uppercase small fw-bold text-muted mb-2">Resumen General</p>
                <h1 class="display-5">Panel de <span class="text-primary-blue">Control</span></h1>
                <p class="lead">Bienvenido de nuevo, Dr. <?= htmlspecialchars($usuario_nombre ?? 'InvestLab') ?>. Aquí
                    tienes un vistazo rápido de lo que está sucediendo en tu clínica hoy.</p>
                <div class="d-flex gap-3">
                    <a href="<?= PROJECT_PATH ?>/app/views/visitas/nueva.php" class="btn btn-modern btn-modern-primary">
                        <i class="bi bi-calendar-plus me-2"></i> Agendar Visita
                    </a>
                    <a href="<?= PROJECT_PATH ?>/app/views/pacientes/nuevo.php" class="btn btn-modern btn-success">
                        <i class="bi bi-person-plus me-2"></i> Registrar Paciente
                    </a>
                    <a href="<?= PROJECT_PATH ?>/app/views/analisis/registro_completo.php"
                        class="btn btn-modern btn-light border">
                        <i class="bi bi-clipboard2-pulse me-2"></i> Registrar Análisis
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Patients Table Section -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: var(--border-radius);">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 fw-bold">Últimos Pacientes Registrados</h5>
                <a href="<?= PROJECT_PATH ?>/app/views/pacientes/lista.php" class="text-decoration-none small">Ver lista
                    completa</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="text-muted small text-uppercase">
                        <tr>
                            <th class="border-0 bg-transparent">Paciente</th>
                            <th class="border-0 bg-transparent">Expediente</th>
                            <th class="border-0 bg-transparent text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimos_pacientes as $paciente): ?>
                            <tr>
                                <td class="border-0">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm bg-light-primary rounded-circle d-flex align-items-center justify-content-center"
                                            style="width:32px; height:32px;">
                                            <i class="bi bi-person text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($paciente['nombre_completo']) ?></div>
                                            <div class="small text-muted">
                                                <?= $paciente['sexo'] == 'M' ? 'Hombre' : 'Mujer' ?>,
                                                <?= $paciente['edad'] ?> años</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="border-0">
                                    <span
                                        class="badge bg-light text-dark fw-normal"><?= htmlspecialchars($paciente['numero_expediente']) ?></span>
                                </td>
                                <td class="border-0 text-end">
                                    <a href="<?= PROJECT_PATH ?>/app/views/pacientes/detalle.php?id=<?= $paciente['id_paciente'] ?>"
                                        class="btn btn-sm btn-light">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($ultimos_pacientes)): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">No hay pacientes recientes.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Situation Sidebar Card -->
    <div class="col-lg-4">
        <div class="stat-group-card shadow-sm p-4 h-100"
            style="border-radius: var(--border-radius); background: white;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0 fw-bold">Situación Clínica</h5>
                <i class="bi bi-activity text-muted"></i>
            </div>

            <div class="stat-item">
                <div class="stat-info">
                    <div class="stat-icon-wrapper stat-icon-blue">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <div class="small text-muted">Total Pacientes</div>
                    </div>
                </div>
                <div class="stat-value text-primary"><?= number_format($total_pacientes) ?></div>
            </div>

            <div class="stat-item">
                <div class="stat-info">
                    <div class="stat-icon-wrapper stat-icon-green">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div>
                        <div class="small text-muted">Visitas del Mes</div>
                    </div>
                </div>
                <div class="stat-value text-success"><?= number_format($visitas_mes) ?></div>
            </div>

            <div class="stat-item">
                <div class="stat-info">
                    <div class="stat-icon-wrapper stat-icon-red">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="small text-muted">Control Inadecuado</div>
                    </div>
                </div>
                <div class="stat-value text-danger"><?= number_format($pacientes_descontrolados) ?></div>
            </div>

            <div class="stat-item">
                <div class="stat-info">
                    <div class="stat-icon-wrapper bg-light text-warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="small text-muted">Próximas Citas</div>
                    </div>
                </div>
                <div class="stat-value text-dark"><?= number_format($proximas_citas) ?></div>
            </div>

            <div class="mt-auto pt-4">
                <a href="<?= PROJECT_PATH ?>/app/views/reportes/index.php"
                    class="btn btn-modern btn-light border w-100">
                    <i class="bi bi-file-earmark-pdf me-2"></i> Generar Reportes
                </a>
            </div>
        </div>
    </div>
</div>

<?php
// RUTA CORREGIDA: Footer dentro de app
include 'app/includes/footer.php';
?>