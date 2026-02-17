<?php
/**
 * Historial Clínico (Expediente) del Paciente
 * Vista consolidada de todas las interacciones clínicas
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Paciente.php';
require_once '../../models/Visita.php';
require_once '../../models/Analisis.php';

$page_title = 'Expediente del Paciente';

if (!isset($_GET['id'])) {
    header('Location: lista.php');
    exit();
}

$id_paciente = (int) $_GET['id'];

$database = new Database();
$db = $database->getConnection();
$paciente_model = new Paciente($db);
$visita_model = new Visita($db);

$paciente = $paciente_model->obtenerPorId($id_paciente);

if (!$paciente) {
    header('Location: lista.php?error=no_encontrado');
    exit();
}

// Obtener todas las visitas (sin límite para el expediente completo)
$visitas = $visita_model->obtenerPorPaciente($id_paciente);

// Obtener reportes de Medicina Interna
$query_mi = "SELECT * FROM consulta_medicina_interna WHERE id_paciente = :id ORDER BY fecha_registro DESC";
$stmt_mi = $db->prepare($query_mi);
$stmt_mi->bindParam(':id', $id_paciente);
$stmt_mi->execute();
$reportes_mi = $stmt_mi->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los análisis (referenciados por visita)
$query_lab = "SELECT v.fecha_visita, v.id_visita,
              lbh.id_biometria, lqs.id_quimica, leo.id_orina, lph.id_hepatico, lpt.id_tiroideo, li.id_insulina,
              ag.id_glucosa as id_glucosa_lab, apr.id_perfil_renal, apl.id_perfil_lipidico
              FROM visitas v
              LEFT JOIN lab_biometria_hematica lbh ON v.id_visita = lbh.id_visita
              LEFT JOIN lab_quimica_sanguinea lqs ON v.id_visita = lqs.id_visita
              LEFT JOIN lab_examen_orina leo ON v.id_visita = leo.id_visita
              LEFT JOIN lab_perfil_hepatico lph ON v.id_visita = lph.id_visita
              LEFT JOIN lab_perfil_tiroideo lpt ON v.id_visita = lpt.id_visita
              LEFT JOIN lab_insulina li ON v.id_visita = li.id_visita
              LEFT JOIN analisis_glucosa ag ON v.id_visita = ag.id_visita
              LEFT JOIN analisis_perfil_renal apr ON v.id_visita = apr.id_visita
              LEFT JOIN analisis_perfil_lipidico apl ON v.id_visita = apl.id_visita
              WHERE v.id_paciente = :id
              AND (lbh.id_biometria IS NOT NULL OR lqs.id_quimica IS NOT NULL OR leo.id_orina IS NOT NULL 
                   OR lph.id_hepatico IS NOT NULL OR lpt.id_tiroideo IS NOT NULL OR li.id_insulina IS NOT NULL
                   OR ag.id_glucosa IS NOT NULL OR apr.id_perfil_renal IS NOT NULL OR apl.id_perfil_lipidico IS NOT NULL)
              ORDER BY v.fecha_visita DESC";
$stmt_lab = $db->prepare($query_lab);
$stmt_lab->bindParam(':id', $id_paciente);
$stmt_lab->execute();
$analisis_clinicos = $stmt_lab->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h2><i class="bi bi-person-bounding-box"></i> Expediente Clínico:
            <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?>
        </h2>
        <p class="text-muted">Expediente: <strong>
                <?= htmlspecialchars($paciente['numero_expediente']) ?>
            </strong> | Protocolo:
            <?php if (($paciente['protocolo'] ?? 'Diabético') === 'Diabético'): ?>
                <span class="badge bg-danger">Diabético</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark">Prediabético</span>
            <?php endif; ?>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <a href="lista.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Volver a Lista</a>
        <a href="detalle.php?id=<?= $id_paciente ?>" class="btn btn-info text-white"><i class="bi bi-eye"></i>
            Perfil</a>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs mb-4" id="expedienteTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="visitas-tab" data-bs-toggle="tab" data-bs-target="#visitas"
                    type="button"><i class="bi bi-calendar3"></i> Consultas Agendadas</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="analisis-tab" data-bs-toggle="tab" data-bs-target="#analisis"
                    type="button"><i class="bi bi-clipboard2-pulse"></i> Análisis de Laboratorio</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="reportes-tab" data-bs-toggle="tab" data-bs-target="#reportes"
                    type="button"><i class="bi bi-file-earmark-medical"></i> Reportes Especialidad</button>
            </li>
        </ul>

        <div class="tab-content card p-4 shadow-sm" id="expedienteTabsContent">
            <!-- Pestaña 1: Visitas -->
            <div class="tab-pane fade show active" id="visitas" role="tabpanel">
                <h5 class="mb-3 text-primary">Historial de Visitas</h5>
                <?php if (count($visitas) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Tipo</th>
                                    <th>Estatus</th>
                                    <th>Doctor</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($visitas as $v): ?>
                                    <tr>
                                        <td>
                                            <?= date('d/m/Y H:i', strtotime($v['fecha_visita'])) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($v['tipo_visita']) ?>
                                        </td>
                                        <td><span
                                                class="badge bg-<?= ($v['estatus'] == 'Completada' ? 'success' : ($v['estatus'] == 'Cancelada' ? 'danger' : 'primary')) ?>">
                                                <?= htmlspecialchars($v['estatus']) ?>
                                            </span></td>
                                        <td>
                                            <?= htmlspecialchars($v['doctor_nombre']) ?>
                                        </td>
                                        <td>
                                            <a href="../visitas/lista.php" class="btn btn-sm btn-outline-secondary">Ver en
                                                Lista</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted my-4">No hay visitas registradas para este paciente.</p>
                <?php endif; ?>
            </div>

            <!-- Pestaña 2: Análisis -->
            <div class="tab-pane fade" id="analisis" role="tabpanel">
                <h5 class="mb-3 text-primary">Resultados de Laboratorio</h5>
                <?php if (count($analisis_clinicos) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Estudios Registrados</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($analisis_clinicos as $ac): ?>
                                    <tr>
                                        <td>
                                            <?= date('d/m/Y', strtotime($ac['fecha_visita'])) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $tags = [];
                                            if ($ac['id_biometria'])
                                                $tags[] = '<span class="badge bg-secondary">Biometría</span>';
                                            if ($ac['id_quimica'])
                                                $tags[] = '<span class="badge bg-secondary">Química</span>';
                                            if ($ac['id_orina'])
                                                $tags[] = '<span class="badge bg-secondary">EGO</span>';
                                            if ($ac['id_glucosa_lab'])
                                                $tags[] = '<span class="badge bg-info">Glucosa</span>';
                                            if ($ac['id_perfil_renal'])
                                                $tags[] = '<span class="badge bg-info">P. Renal</span>';
                                            if ($ac['id_perfil_lipidico'])
                                                $tags[] = '<span class="badge bg-info">P. Lipídico</span>';
                                            echo implode(' ', $tags);
                                            ?>
                                        </td>
                                        <td>
                                            <a href="../reportes/imprimir.php?id_visita=<?= $ac['id_visita'] ?>" target="_blank"
                                                class="btn btn-sm btn-primary"><i class="bi bi-printer"></i> Ver Resultados</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted my-4">No se han encontrado registros de análisis para este paciente.
                    </p>
                <?php endif; ?>
            </div>

            <!-- Pestaña 3: Reportes Medicina Interna -->
            <div class="tab-pane fade" id="reportes" role="tabpanel">
                <h5 class="mb-3 text-primary">Reportes de Medicina Interna</h5>
                <?php if (count($reportes_mi) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha de Registro</th>
                                    <th>Tipo de Diabetes</th>
                                    <th>Control</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportes_mi as $mi): ?>
                                    <tr>
                                        <td>
                                            <?= date('d/m/Y', strtotime($mi['fecha_registro'])) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($mi['tipo_diabetes']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($mi['control_actual']) ?>
                                        </td>
                                        <td>
                                            <a href="../especialidades/medicina_interna.php?id_visita=<?= $mi['id_visita'] ?>"
                                                class="btn btn-sm btn-primary"><i class="bi bi-pencil-square"></i>
                                                Editar/Ver</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted my-4">No hay reportes de medicina interna para este paciente.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>