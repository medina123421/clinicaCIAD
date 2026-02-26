<?php
/**
 * Vista de Cuidado de los Pies
 * Formulario completo: Interrogatorio, Examen dermatológico, Estructura ósea, Vascular y neurológico
 * Incluye mapa interactivo de pies y cálculo automático de riesgo
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/CuidadoPies.php';
require_once __DIR__ . '/../../models/Visita.php';
require_once __DIR__ . '/../../models/Paciente.php';

$id_visita = $_GET['id_visita'] ?? null;
$message = '';
$message_type = '';

$database = new Database();
$db = $database->getConnection();

$cuidadoPies = new CuidadoPies($db);
$visitaModel = new Visita($db);
$pacienteModel = new Paciente($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consulta'])) {
    try {
        if ($cuidadoPies->guardar($_POST)) {
            $message = "Evaluación de Cuidado de los Pies guardada correctamente.";
            $message_type = "success";
        } else {
            $message = "Error al guardar la evaluación.";
            $message_type = "danger";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

$datos = [];
$visita = null;
$paciente = null;

if ($id_visita) {
    $datos = $cuidadoPies->obtenerPorVisita($id_visita) ?: [];
    $visita = $visitaModel->obtenerPorId($id_visita);
    if ($visita) {
        $paciente = $pacienteModel->obtenerPorId($visita['id_paciente']);
    }
} else {
    $query_recent = "SELECT v.*,
                    CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) as paciente_nombre,
                    p.numero_expediente
                    FROM visitas v
                    JOIN pacientes p ON v.id_paciente = p.id_paciente
                    ORDER BY v.fecha_visita DESC
                    LIMIT 10";
    $stmt_recent = $db->prepare($query_recent);
    $stmt_recent->execute();
    $visitas_recientes = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = 'Cuidado de los Pies';

function renderRadio012($name, $label, $datos, $side = '')
{
    $v = (int)($datos[$name] ?? -1);
    $html = "<div class='mb-2'><label class='form-label small fw-bold'>$label $side</label><div class='d-flex gap-2'>";
    foreach (['0' => '0', '1' => '1', '2' => '2'] as $val => $lab) {
        $ch = $v === (int)$val ? 'checked' : '';
        $color = $val == '0' ? 'success' : ($val == '1' ? 'warning' : 'danger');
        $html .= "<div class='form-check'><input type='radio' name='$name' value='$val' id='{$name}_{$val}' class='form-check-input text-$color riesgo-item' $ch><label class='form-check-label small' for='{$name}_{$val}'>$lab</label></div>";
    }
    $html .= "</div></div>";
    return $html;
}

function renderCheckbox($id, $label, $datos)
{
    $checked = ($datos[$id] ?? 0) ? 'checked' : '';
    return "<div class='form-check mb-2'><input name='$id' class='form-check-input' type='checkbox' id='$id' $checked><label class='form-check-label' for='$id'>$label</label></div>";
}

include __DIR__ . '/../../includes/header.php';
?>

<style>
    :root {
        --pies-primary: #0dcaf0;
        --pies-light: #e7f6fd;
        --pies-border: #b8e7f5;
        --pies-danger: #dc3545;
        --pies-warning: #ffc107;
        --pies-success: #198754;
    }
    body { background-color: #f5f7fb; }
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }
    .card-header {
        background-color: var(--pies-primary);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    .section-title {
        color: var(--pies-primary);
        border-bottom: 2px solid var(--pies-light);
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
    }
    .section-title i { margin-right: 0.5rem; }
    .nav-tabs .nav-link {
        color: #666;
        font-weight: 600;
        border: none;
        padding: 1rem 1.5rem;
        border-bottom: 3px solid transparent;
    }
    .nav-tabs .nav-link.active {
        color: var(--pies-primary);
        background: transparent;
        border-bottom-color: var(--pies-primary);
    }
    .pie-map {
        background: var(--pies-light);
        border: 2px solid var(--pies-border);
        border-radius: 12px;
        padding: 1rem;
        text-align: center;
        min-height: 200px;
        position: relative;
    }
    .riesgo-box {
        background: var(--pies-light);
        border: 1px solid var(--pies-border);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    .riesgo-box.moderado { background: #fff3cd; border-color: #ffeaa7; color: #856404; }
    .riesgo-box.alto { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
    .alerta-roja {
        background: var(--pies-danger);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 1rem;
        display: none;
    }
    .bilateral-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .pie-side {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1rem;
    }
    .pie-side h6 {
        color: var(--pies-primary);
        border-bottom: 1px solid var(--pies-border);
        padding-bottom: 0.5rem;
        margin-bottom: 0.75rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-person-walking text-info"></i> Cuidado de los Pies</h2>
            <?php if ($paciente): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../pacientes/detalle_paciente.php?id=<?= (int)$paciente['id_paciente'] ?>"><?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?></a></li>
                        <li class="breadcrumb-item active">Evaluación Podológica</li>
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
            <a href="../visitas/lista.php" class="btn btn-info shadow-sm"><i class="bi bi-clipboard-data"></i> Visitas</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi <?= $message_type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!$id_visita): ?>
        <div class="row">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-search"></i> Buscar Paciente
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Busque al paciente para abrir la evaluación podológica.</p>
                        <div class="mb-3">
                            <input type="text" class="form-control" id="patientSearchInput" placeholder="Nombre o expediente...">
                        </div>
                        <div id="patientSearchResults" class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                            <div class="text-center py-3 text-muted">
                                <i class="bi bi-person-fill-gear fs-2 d-block mb-2"></i>
                                <small>Escriba para buscar</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <i class="bi bi-clock-history"></i> Visitas Recientes
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Paciente</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($visitas_recientes)): ?>
                                        <?php foreach ($visitas_recientes as $v): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= date('d/m/Y', strtotime($v['fecha_visita'])) ?></div>
                                                    <small class="text-muted"><?= date('H:i', strtotime($v['fecha_visita'])) ?></small>
                                                </td>
                                                <td>
                                                    <div><?= htmlspecialchars($v['paciente_nombre']) ?></div>
                                                    <small class="badge bg-light text-dark border"><?= htmlspecialchars($v['numero_expediente']) ?></small>
                                                </td>
                                                <td>
                                                    <a href="?id_visita=<?= (int)$v['id_visita'] ?>" class="btn btn-info btn-sm rounded-pill">Abrir Consulta <i class="bi bi-arrow-right-short"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No hay visitas recientes</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="../visitas/lista.php" class="btn btn-link btn-sm text-decoration-none">Ver todas las visitas</a>
                    </div>
                </div>
                <div class="alert alert-info mt-4 border-0 shadow-sm">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Si el paciente no tiene visita registrada, <a href="../visitas/nueva.php" class="alert-link">registre una nueva visita</a> primero.
                </div>
            </div>
        </div>
    <?php else: ?>

        <form method="POST" id="formCuidadoPies">
            <input type="hidden" name="id_visita" value="<?= (int)$id_visita ?>">
            <input type="hidden" name="save_consulta" value="1">

            <!-- Alerta Roja -->
            <div class="alerta-roja" id="alertaRoja">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                ALERTA ROJA: Paciente requiere envío urgente a especialista
            </div>

            <!-- Resumen de Riesgo -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="riesgo-box" id="riesgoBoxDer">
                        Pie Derecho - Puntuación: <span id="puntajeDer"><?= (int)($datos['puntuacion_total_der'] ?? 0) ?></span> | 
                        Riesgo: <span id="riesgoDer"><?= htmlspecialchars($datos['riesgo_der'] ?? 'Leve') ?></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="riesgo-box" id="riesgoBoxIzq">
                        Pie Izquierdo - Puntuación: <span id="puntajeIzq"><?= (int)($datos['puntuacion_total_izq'] ?? 0) ?></span> | 
                        Riesgo: <span id="riesgoIzq"><?= htmlspecialchars($datos['riesgo_izq'] ?? 'Leve') ?></span>
                    </div>
                </div>
            </div>

            <ul class="nav nav-tabs mb-4 px-2 bg-white rounded shadow-sm sticky-top" style="top: 10px; z-index: 100;" id="piesTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tab-interrog" data-bs-toggle="tab" data-bs-target="#pane-interrog" type="button">1. Interrogatorio</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-dermato" data-bs-toggle="tab" data-bs-target="#pane-dermato" type="button">2. Examen Dermatológico</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-estructura" data-bs-toggle="tab" data-bs-target="#pane-estructura" type="button">3. Estructura Ósea</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-neuro" data-bs-toggle="tab" data-bs-target="#pane-neuro" type="button">4. Vascular y Neurológico</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-obs" data-bs-toggle="tab" data-bs-target="#pane-obs" type="button">5. Observaciones</button>
                </li>
            </ul>

            <div class="tab-content" id="piesTabsContent">

                <!-- TAB 1: Interrogatorio -->
                <div class="tab-pane fade show active" id="pane-interrog" role="tabpanel">
                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-clipboard2-pulse"></i> Antecedentes de Lesiones</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <?= renderCheckbox('ulcera_previa', 'Úlcera previa en pierna o pie', $datos) ?>
                                    <?= renderCheckbox('amputacion_previa', 'Amputación de extremidades inferiores', $datos) ?>
                                    <?= renderCheckbox('cirugia_previa', 'Cirugía previa en pierna o pie', $datos) ?>
                                    <?= renderCheckbox('herida_lenta', 'Herida que tardó más de 3 meses en sanar', $datos) ?>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-info mb-3">Sintomatología Actual</h6>
                                    <?= renderCheckbox('ardor_hormigueo', 'Ardor u hormigueo en piernas o pies', $datos) ?>
                                    <?= renderCheckbox('dolor_actividad', 'Dolor en pierna/pie con actividad', $datos) ?>
                                    <?= renderCheckbox('dolor_reposo', 'Dolor en pierna/pie en reposo', $datos) ?>
                                    <?= renderCheckbox('perdida_sensacion', 'Pérdida de sensación en extremidades inferiores', $datos) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-lungs"></i> Hábitos y Seguimiento</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="fuma" id="fuma" value="1" <?= !empty($datos['fuma']) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold" for="fuma">Fuma actualmente</label>
                                    </div>
                                </div>
                                <div class="col-md-4" id="cigarrillosWrap" style="<?= !empty($datos['fuma']) ? '' : 'display:none' ?>">
                                    <label class="form-label fw-bold">Cigarrillos al día</label>
                                    <input type="number" min="0" name="cigarrillos_dia" class="form-control" value="<?= htmlspecialchars($datos['cigarrillos_dia'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="revision_pies_previa" id="revision_pies_previa" value="1" <?= !empty($datos['revision_pies_previa']) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold" for="revision_pies_previa">Han revisado sus pies previamente</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Examen Dermatológico -->
                <div class="tab-pane fade" id="pane-dermato" role="tabpanel">
                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-bandaid"></i> Hiperqueratosis (Callosidades)</h5>
                            <p class="small text-muted mb-3">Calificación: 0 = Ausente, 1 = Moderado, 2 = Grave</p>
                            <div class="bilateral-section">
                                <div class="pie-side">
                                    <h6><i class="bi bi-arrow-right"></i> Pie Derecho</h6>
                                    <?= renderRadio012('hiper_plantar_der', 'Plantar', $datos) ?>
                                    <?= renderRadio012('hiper_dorsal_der', 'Dorsal', $datos) ?>
                                    <?= renderRadio012('hiper_talar_der', 'Talar', $datos) ?>
                                </div>
                                <div class="pie-side">
                                    <h6><i class="bi bi-arrow-left"></i> Pie Izquierdo</h6>
                                    <?= renderRadio012('hiper_plantar_izq', 'Plantar', $datos) ?>
                                    <?= renderRadio012('hiper_dorsal_izq', 'Dorsal', $datos) ?>
                                    <?= renderRadio012('hiper_talar_izq', 'Talar', $datos) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-scissors"></i> Alteraciones Ungueales</h5>
                            <div class="bilateral-section">
                                <div class="pie-side">
                                    <h6><i class="bi bi-arrow-right"></i> Pie Derecho</h6>
                                    <?= renderRadio012('onicocriptosis_der', 'Onicocriptosis (uña enterrada)', $datos) ?>
                                    <?= renderRadio012('onicomicosis_der', 'Onicomicosis (hongos)', $datos) ?>
                                    <?= renderRadio012('onicogrifosis_der', 'Onicogrifosis (uña engrosada)', $datos) ?>
                                </div>
                                <div class="pie-side">
                                    <h6><i class="bi bi-arrow-left"></i> Pie Izquierdo</h6>
                                    <?= renderRadio012('onicocriptosis_izq', 'Onicocriptosis (uña enterrada)', $datos) ?>
                                    <?= renderRadio012('onicomicosis_izq', 'Onicomicosis (hongos)', $datos) ?>
                                    <?= renderRadio012('onicogrifosis_izq', 'Onicogrifosis (uña engrosada)', $datos) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-exclamation-triangle"></i> Otras Lesiones</h5>
                            <div class="bilateral-section">
                                <div class="pie-side">
                                    <h6><i class="bi bi-arrow-right"></i> Pie Derecho</h6>
                                    <?= renderRadio012('bullosis_der', 'Bullosis (ampollas)', $datos) ?>
                                    <?= renderRadio012('necrosis_der', 'Necrosis', $datos) ?>
                                    <?= renderRadio012('grietas_fisuras_der', 'Grietas y fisuras', $datos) ?>
                                    <?= renderRadio012('lesion_superficial_der', 'Lesión superficial', $datos) ?>
                                    <?= renderRadio012('anhidrosis_der', 'Anhidrosis', $datos) ?>
                                    <?= renderRadio012('tina_der', 'Tiña', $datos) ?>
                                    <?= renderRadio012('proceso_infeccioso_der', 'Proceso infeccioso', $datos) ?>
                                </div>
                                <div class="pie-side">
                                    <h6><i class="bi bi-arrow-left"></i> Pie Izquierdo</h6>
                                    <?= renderRadio012('bullosis_izq', 'Bullosis (ampollas)', $datos) ?>
                                    <?= renderRadio012('necrosis_izq', 'Necrosis', $datos) ?>
                                    <?= renderRadio012('grietas_fisuras_izq', 'Grietas y fisuras', $datos) ?>
                                    <?= renderRadio012('lesion_superficial_izq', 'Lesión superficial', $datos) ?>
                                    <?= renderRadio012('anhidrosis_izq', 'Anhidrosis', $datos) ?>
                                    <?= renderRadio012('tina_izq', 'Tiña', $datos) ?>
                                    <?= renderRadio012('proceso_infeccioso_izq', 'Proceso infeccioso', $datos) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-droplet-fill text-danger"></i> Úlceras por Tipo</h5>
                            <div class="bilateral-section">
                                <div class="pie-side">
                                    <h6><i class="bi bi-arrow-right"></i> Pie Derecho</h6>
                                    <?= renderRadio012('ulcera_venosa_der', 'Úlcera venosa', $datos) ?>
                                    <?= renderRadio012('ulcera_arterial_der', 'Úlcera arterial', $datos) ?>
                                    <?= renderRadio012('ulcera_mixta_der', 'Úlcera mixta', $datos) ?>
                                    <?= renderRadio012('ulcera_otra_der', 'Úlcera otra', $datos) ?>
                                </div>
                                <div class="pie-side">
                                    <h6><i class="bi bi-arrow-left"></i> Pie Izquierdo</h6>
                                    <?= renderRadio012('ulcera_venosa_izq', 'Úlcera venosa', $datos) ?>
                                    <?= renderRadio012('ulcera_arterial_izq', 'Úlcera arterial', $datos) ?>
                                    <?= renderRadio012('ulcera_mixta_izq', 'Úlcera mixta', $datos) ?>
                                    <?= renderRadio012('ulcera_otra_izq', 'Úlcera otra', $datos) ?>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label class="form-label fw-bold">Otros hallazgos dermatológicos</label>
                                <input type="text" name="der_izq_otra_lesion" class="form-control" value="<?= htmlspecialchars($datos['der_izq_otra_lesion'] ?? '') ?>" placeholder="Describir otras lesiones encontradas">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Estructura Ósea -->
                <div class="tab-pane fade" id="pane-estructura" role="tabpanel">
                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-diagram-3"></i> Deformidades Óseas</h5>
                            <p class="small text-muted mb-3">Calificación: 0 = Ausente, 1 = Moderado, 2 = Grave</p>
                            <div class="bilateral-section">
                                <div class="pie-side">
                                    <h6><i class="bi bi-arrow-right"></i> Pie Derecho</h6>
                                    <?= renderRadio012('hallux_valgus_der', 'Hallux valgus', $datos) ?>
                                    <?= renderRadio012('dedos_garra_der', 'Dedos en garra', $datos) ?>
                                    <?= renderRadio012('dedos_martillo_der', 'Dedos en martillo', $datos) ?>
                                    <?= renderRadio012('infraducto_der', 'Infraducto', $datos) ?>
                                    <?= renderRadio012('supraducto_der', 'Supraducto', $datos) ?>
                                    <?= renderRadio012('pie_cavo_der', 'Pie cavo', $datos) ?>
                                    <?= renderRadio012('arco_caido_der', 'Arco plantar caído', $datos) ?>
                                    <?= renderRadio012('talo_varo_der', 'Talo varo', $datos) ?>
                                    <?= renderRadio012('espolon_calcaneo_der', 'Espolón calcáneo', $datos) ?>
                                    <?= renderRadio012('hipercargas_metatarsianos_der', 'Hipercargas bajo metatarsianos', $datos) ?>
                                    <?= renderRadio012('pie_charcot_der', 'Pie de Charcot', $datos) ?>
                                </div>
                                <div class="pie-side">
                                    <h6><i class="bi bi-arrow-left"></i> Pie Izquierdo</h6>
                                    <?= renderRadio012('hallux_valgus_izq', 'Hallux valgus', $datos) ?>
                                    <?= renderRadio012('dedos_garra_izq', 'Dedos en garra', $datos) ?>
                                    <?= renderRadio012('dedos_martillo_izq', 'Dedos en martillo', $datos) ?>
                                    <?= renderRadio012('infraducto_izq', 'Infraducto', $datos) ?>
                                    <?= renderRadio012('supraducto_izq', 'Supraducto', $datos) ?>
                                    <?= renderRadio012('pie_cavo_izq', 'Pie cavo', $datos) ?>
                                    <?= renderRadio012('arco_caido_izq', 'Arco plantar caído', $datos) ?>
                                    <?= renderRadio012('talo_varo_izq', 'Talo varo', $datos) ?>
                                    <?= renderRadio012('espolon_calcaneo_izq', 'Espolón calcáneo', $datos) ?>
                                    <?= renderRadio012('hipercargas_metatarsianos_izq', 'Hipercargas bajo metatarsianos', $datos) ?>
                                    <?= renderRadio012('pie_charcot_izq', 'Pie de Charcot', $datos) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: Vascular y Neurológico -->
                <div class="tab-pane fade" id="pane-neuro" role="tabpanel">
                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-heart-pulse"></i> Valoración Vascular</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Pulso pedio derecho</label>
                                    <select name="pulso_pedio_der" class="form-select">
                                        <option value="Presente" <?= ($datos['pulso_pedio_der'] ?? 'Presente') === 'Presente' ? 'selected' : '' ?>>Presente</option>
                                        <option value="Disminuido" <?= ($datos['pulso_pedio_der'] ?? '') === 'Disminuido' ? 'selected' : '' ?>>Disminuido</option>
                                        <option value="Ausente" <?= ($datos['pulso_pedio_der'] ?? '') === 'Ausente' ? 'selected' : '' ?>>Ausente</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Pulso pedio izquierdo</label>
                                    <select name="pulso_pedio_izq" class="form-select">
                                        <option value="Presente" <?= ($datos['pulso_pedio_izq'] ?? 'Presente') === 'Presente' ? 'selected' : '' ?>>Presente</option>
                                        <option value="Disminuido" <?= ($datos['pulso_pedio_izq'] ?? '') === 'Disminuido' ? 'selected' : '' ?>>Disminuido</option>
                                        <option value="Ausente" <?= ($datos['pulso_pedio_izq'] ?? '') === 'Ausente' ? 'selected' : '' ?>>Ausente</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Pulso tibial derecho</label>
                                    <select name="pulso_tibial_der" class="form-select">
                                        <option value="Presente" <?= ($datos['pulso_tibial_der'] ?? 'Presente') === 'Presente' ? 'selected' : '' ?>>Presente</option>
                                        <option value="Disminuido" <?= ($datos['pulso_tibial_der'] ?? '') === 'Disminuido' ? 'selected' : '' ?>>Disminuido</option>
                                        <option value="Ausente" <?= ($datos['pulso_tibial_der'] ?? '') === 'Ausente' ? 'selected' : '' ?>>Ausente</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Pulso tibial izquierdo</label>
                                    <select name="pulso_tibial_izq" class="form-select">
                                        <option value="Presente" <?= ($datos['pulso_tibial_izq'] ?? 'Presente') === 'Presente' ? 'selected' : '' ?>>Presente</option>
                                        <option value="Disminuido" <?= ($datos['pulso_tibial_izq'] ?? '') === 'Disminuido' ? 'selected' : '' ?>>Disminuido</option>
                                        <option value="Ausente" <?= ($datos['pulso_tibial_izq'] ?? '') === 'Ausente' ? 'selected' : '' ?>>Ausente</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Llenado capilar derecho (seg)</label>
                                    <input type="number" step="0.1" name="llenado_capilar_der" class="form-control" value="<?= htmlspecialchars($datos['llenado_capilar_der'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Llenado capilar izquierdo (seg)</label>
                                    <input type="number" step="0.1" name="llenado_capilar_izq" class="form-control" value="<?= htmlspecialchars($datos['llenado_capilar_izq'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Edema (Signo de Godet)</label>
                                    <select name="edema_godet" class="form-select">
                                        <option value="Sin edema" <?= ($datos['edema_godet'] ?? 'Sin edema') === 'Sin edema' ? 'selected' : '' ?>>Sin edema</option>
                                        <option value="Grado I" <?= ($datos['edema_godet'] ?? '') === 'Grado I' ? 'selected' : '' ?>>Grado I</option>
                                        <option value="Grado II" <?= ($datos['edema_godet'] ?? '') === 'Grado II' ? 'selected' : '' ?>>Grado II</option>
                                        <option value="Grado III" <?= ($datos['edema_godet'] ?? '') === 'Grado III' ? 'selected' : '' ?>>Grado III</option>
                                        <option value="Grado IV" <?= ($datos['edema_godet'] ?? '') === 'Grado IV' ? 'selected' : '' ?>>Grado IV</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="varices" id="varices" value="1" <?= !empty($datos['varices']) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold" for="varices">Presencia de várices</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-lightning"></i> Valoración Neurológica</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Monofilamento 10g (puntos positivos 0-10)</label>
                                    <input type="number" min="0" max="10" name="monofilamento_puntos" class="form-control" value="<?= htmlspecialchars($datos['monofilamento_puntos'] ?? '') ?>">
                                    <small class="text-muted">Más de 5 puntos = alterado</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Sensibilidad vibratoria (segundos)</label>
                                    <input type="number" step="0.1" name="sensibilidad_vibratoria_seg" class="form-control" value="<?= htmlspecialchars($datos['sensibilidad_vibratoria_seg'] ?? '') ?>">
                                    <small class="text-muted">Menos de 8 seg = alterado</small>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Reflejo rotuliano (0-2)</label>
                                    <input type="number" min="0" max="2" name="reflejo_rotuliano" class="form-control" value="<?= htmlspecialchars($datos['reflejo_rotuliano'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Dorsiflexión del pie (0-2)</label>
                                    <input type="number" min="0" max="2" name="dorsiflexion_pie" class="form-control" value="<?= htmlspecialchars($datos['dorsiflexion_pie'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Apertura ortejos en abanico (0-2)</label>
                                    <input type="number" min="0" max="2" name="apertura_ortejos" class="form-control" value="<?= htmlspecialchars($datos['apertura_ortejos'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: Observaciones -->
                <div class="tab-pane fade" id="pane-obs" role="tabpanel">
                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-mortarboard"></i> Educación</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="educacion_cuidado_pies" id="educacion_cuidado_pies" value="1" <?= !empty($datos['educacion_cuidado_pies']) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-bold" for="educacion_cuidado_pies">Se impartió técnica de cuidado de pies y calzado adecuado</label>
                            </div>
                            
                            <label class="form-label fw-bold">Observaciones del especialista</label>
                            <textarea name="observaciones_especialista" class="form-control" rows="6" placeholder="Hallazgos adicionales, recomendaciones, plan de seguimiento..."><?= htmlspecialchars($datos['observaciones_especialista'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 mb-5 text-center sticky-bottom bg-white p-3 rounded shadow">
                <button type="submit" class="btn btn-info btn-lg px-5">
                    <i class="bi bi-save2"></i> Guardar Evaluación Podológica
                </button>
            </div>
        </form>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para calcular riesgo en tiempo real
    function calcularRiesgo() {
        let puntajeDer = 0, puntajeIzq = 0;
        let alertaRoja = false;

        // Sumar todos los campos de riesgo (0-2)
        document.querySelectorAll('.riesgo-item:checked').forEach(function(radio) {
            const val = parseInt(radio.value);
            const name = radio.name;
            
            if (name.includes('_der')) {
                puntajeDer += val;
            } else if (name.includes('_izq')) {
                puntajeIzq += val;
            } else {
                // Campos neurológicos (se suman a ambos pies)
                puntajeDer += val;
                puntajeIzq += val;
            }
            
            // Alerta roja si cualquier campo = 2
            if (val === 2) alertaRoja = true;
            
            // Pie de Charcot o necrosis siempre alerta roja
            if ((name.includes('pie_charcot') || name.includes('necrosis')) && val > 0) {
                alertaRoja = true;
            }
        });

        // Alerta roja por antecedentes
        if (document.getElementById('amputacion_previa')?.checked) alertaRoja = true;

        // Actualizar display
        document.getElementById('puntajeDer').textContent = puntajeDer;
        document.getElementById('puntajeIzq').textContent = puntajeIzq;
        
        const riesgoDer = puntajeDer <= 10 ? 'Leve' : (puntajeDer <= 25 ? 'Moderado' : 'Alto');
        const riesgoIzq = puntajeIzq <= 10 ? 'Leve' : (puntajeIzq <= 25 ? 'Moderado' : 'Alto');
        
        document.getElementById('riesgoDer').textContent = riesgoDer;
        document.getElementById('riesgoIzq').textContent = riesgoIzq;

        // Actualizar clases de riesgo
        const boxDer = document.getElementById('riesgoBoxDer');
        const boxIzq = document.getElementById('riesgoBoxIzq');
        
        boxDer.className = 'riesgo-box ' + (riesgoDer === 'Alto' ? 'alto' : (riesgoDer === 'Moderado' ? 'moderado' : ''));
        boxIzq.className = 'riesgo-box ' + (riesgoIzq === 'Alto' ? 'alto' : (riesgoIzq === 'Moderado' ? 'moderado' : ''));

        // Mostrar/ocultar alerta roja
        document.getElementById('alertaRoja').style.display = alertaRoja ? 'block' : 'none';
    }

    // Event listeners para cálculo en tiempo real
    document.querySelectorAll('.riesgo-item').forEach(function(radio) {
        radio.addEventListener('change', calcularRiesgo);
    });
    
    document.getElementById('amputacion_previa')?.addEventListener('change', calcularRiesgo);
    
    // Calcular al cargar
    calcularRiesgo();

    // Mostrar/ocultar cigarrillos por día
    document.getElementById('fuma')?.addEventListener('change', function() {
        document.getElementById('cigarrillosWrap').style.display = this.checked ? 'block' : 'none';
    });

    // Búsqueda AJAX de pacientes
    const patientSearchInput = document.getElementById('patientSearchInput');
    if (patientSearchInput) {
        const resultsContainer = document.getElementById('patientSearchResults');
        const debounce = function(fn, ms) {
            let t;
            return function() { clearTimeout(t); t = setTimeout(fn, ms); };
        };
        
        patientSearchInput.addEventListener('input', debounce(function() {
            const search = this.value.trim();
            if (search.length < 2) {
                resultsContainer.innerHTML = '<div class="text-center py-3 text-muted"><i class="bi bi-person-fill-gear fs-2 d-block mb-2"></i><small>Escriba para buscar</small></div>';
                return;
            }
            
            resultsContainer.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-info"></div></div>';
            
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '../../ajax/buscar_pacientes_pies.php?search=' + encodeURIComponent(search));
            xhr.onload = function() {
                try {
                    const r = JSON.parse(xhr.responseText);
                    if (r.success && r.html) resultsContainer.innerHTML = r.html;
                    else resultsContainer.innerHTML = '<div class="p-4 text-center text-muted">No se encontraron resultados</div>';
                } catch (e) {
                    resultsContainer.innerHTML = '<div class="p-4 text-center text-danger">Error al buscar</div>';
                }
            };
            xhr.send();
        }, 400));
    }
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>