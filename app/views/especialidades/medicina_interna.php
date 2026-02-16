<?php
/**
 * Vista de Medicina Interna - REDISEÑADA
 * Formulario completo basado en los formatos físicos con lógica dinámica
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/MedicinaInterna.php';
require_once '../../models/Visita.php';
require_once '../../models/Paciente.php';

$id_visita = $_GET['id_visita'] ?? null;
$message = '';
$message_type = '';

$database = new Database();
$db = $database->getConnection();

$medicinaInterna = new MedicinaInterna($db);
$visitaModel = new Visita($db);
$pacienteModel = new Paciente($db);

// Procesar Formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consulta'])) {
    try {
        if ($medicinaInterna->guardar($_POST)) {
            $message = "Consulta de Medicina Interna guardada exitosamente.";
            $message_type = "success";
        } else {
            $message = "Error al guardar la consulta.";
            $message_type = "danger";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Obtener datos
$datos = [];
$visita = null;
$paciente = null;

if ($id_visita) {
    $datos = $medicinaInterna->obtenerPorVisita($id_visita) ?: [];
    $visita = $visitaModel->obtenerPorId($id_visita);
    if ($visita) {
        $paciente = $pacienteModel->obtenerPorId($visita['id_paciente']);
    }
} else {
    // Si no hay visita, obtener visitas recientes para mostrar una lista de selección
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

$page_title = 'Consulta Medicina Interna';

// Funciones auxiliares para la UI
function renderCheckbox($id, $label, $datos)
{
    $checked = ($datos[$id] ?? 0) ? 'checked' : '';
    return "
    <div class='form-check'>
        <input name='$id' class='form-check-input' type='checkbox' id='$id' $checked>
        <label class='form-check-label' for='$id'>$label</label>
    </div>";
}

function renderTextSection($title, $check_id, $detail_id, $datos)
{
    $checked = ($datos[$check_id] ?? 0) ? 'checked' : '';
    $display = ($datos[$check_id] ?? 0) ? 'display:block' : '';
    $value = htmlspecialchars($datos[$detail_id] ?? '');
    return "
    <div class='card border-0 mb-3 shadow-sm'>
        <div class='card-body'>
            <div class='form-check form-switch fs-6'>
                <input class='form-check-input dynamic-check' type='checkbox' role='switch' 
                       id='$check_id' name='$check_id' data-target='$detail_id' $checked>
                <label class='form-check-label fw-bold' for='$check_id'>$title</label>
            </div>
            <div id='{$detail_id}_container' class='detail-box mt-2' style='$display'>
                <textarea name='$detail_id' class='form-control form-control-sm' rows='2'>$value</textarea>
            </div>
        </div>
    </div>";
}

include '../../includes/header.php';
?>

<style>
    :root {
        --medical-blue: #0d6efd;
        --medical-light: #f8faff;
        --medical-border: #e0e6ed;
    }

    body {
        background-color: #f5f7fb;
    }

    .card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }

    .card-header {
        background-color: var(--medical-blue);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }

    .section-title {
        color: var(--medical-blue);
        border-bottom: 2px solid var(--medical-light);
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 0.5rem;
    }

    .form-check-label {
        font-weight: 500;
        color: #444;
    }

    .detail-box {
        display: none;
        margin-top: 0.5rem;
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .nav-tabs .nav-link {
        color: #666;
        font-weight: 600;
        border: none;
        padding: 1rem 1.5rem;
        border-bottom: 3px solid transparent;
    }

    .nav-tabs .nav-link.active {
        color: var(--medical-blue);
        background: transparent;
        border-bottom-color: var(--medical-blue);
    }

    .medical-input-group {
        background-color: var(--medical-light);
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid var(--medical-border);
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-heart-pulse-fill text-danger"></i> Medicina Interna</h2>
            <?php if ($paciente): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a
                                href="detalle_paciente.php?id=<?= $paciente['id_paciente'] ?>"><?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?></a>
                        </li>
                        <li class="breadcrumb-item active">Consulta Médica</li>
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
            <a href="../visitas/lista.php" class="btn btn-primary shadow-sm"><i class="bi bi-clipboard-data"></i>
                Visitas</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi <?= $message_type == 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> me-2"></i>
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!$id_visita): ?>
        <div class="row">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-search"></i> Buscar Paciente para Consulta
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Busque al paciente para ver sus visitas y comenzar la consulta de
                            medicina interna.</p>
                        <div class="search-box mb-3">
                            <i class="bi bi-search"></i>
                            <input type="text" class="form-control" id="patientSearchInput"
                                placeholder="Nombre o expediente...">
                        </div>
                        <div id="patientSearchResults" class="list-group list-group-flush"
                            style="max-height: 300px; overflow-y: auto;">
                            <!-- AJAX results here -->
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
                                    <?php if (count($visitas_recientes) > 0): ?>
                                        <?php foreach ($visitas_recientes as $v): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-bold"><?= date('d/m/Y', strtotime($v['fecha_visita'])) ?></div>
                                                    <small
                                                        class="text-muted"><?= date('H:i', strtotime($v['fecha_visita'])) ?></small>
                                                </td>
                                                <td>
                                                    <div><?= htmlspecialchars($v['paciente_nombre']) ?></div>
                                                    <small
                                                        class="badge bg-light text-dark border"><?= htmlspecialchars($v['numero_expediente']) ?></small>
                                                </td>
                                                <td>
                                                    <a href="?id_visita=<?= $v['id_visita'] ?>"
                                                        class="btn btn-primary btn-sm rounded-pill">
                                                        Abrir Consulta <i class="bi bi-arrow-right-short"></i>
                                                    </a>
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
                        <a href="../visitas/lista.php" class="btn btn-link btn-sm text-decoration-none">Ver todas las
                            visitas</a>
                    </div>
                </div>

                <div class="alert alert-info mt-4 border-0 shadow-sm">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Si el paciente no tiene una visita registrada hoy, <a href="../visitas/nueva.php"
                        class="alert-link">registre una nueva visita</a> primero.
                </div>
            </div>
        </div>
    <?php else: ?>

        <form method="POST" id="formMedicinaInterna">
            <input type="hidden" name="id_visita" value="<?= $id_visita ?>">
            <input type="hidden" name="save_consulta" value="1">

            <!-- TABS NAVIGATION -->
            <ul class="nav nav-tabs mb-4 px-2 bg-white rounded shadow-sm sticky-top" style="top: 10px; z-index: 100;"
                id="medicalTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="diag-tab" data-bs-toggle="tab" data-bs-target="#tab-diag"
                        type="button">1. Antecedentes y Diagnóstico</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="med-tab" data-bs-toggle="tab" data-bs-target="#tab-med" type="button">2.
                        Tratamiento Médico</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="lab-tab" data-bs-toggle="tab" data-bs-target="#tab-lab" type="button">3.
                        Laboratorios y Estudios</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="eval-tab" data-bs-toggle="tab" data-bs-target="#tab-eval" type="button">4.
                        Evaluación y Seguimiento</button>
                </li>
            </ul>

            <div class="tab-content" id="medicalTabsContent">

                <!-- TAB 1: ANTECEDENTES Y DIAGNÓSTICO -->
                <div class="tab-pane fade show active" id="tab-diag" role="tabpanel">
                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-clipboard2-pulse"></i> Diagnóstico de Diabetes</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Tipo de Diabetes</label>
                                    <select name="tipo_diabetes" class="form-select border-primary-subtle">
                                        <option value="">Seleccione...</option>
                                        <?php foreach (['Tipo 1', 'Tipo 2', 'Gestacional', 'Otra'] as $opt): ?>
                                            <option value="<?= $opt ?>" <?= ($datos['tipo_diabetes'] ?? '') == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Año de Diagnóstico</label>
                                    <input type="number" name="anio_diagnostico" class="form-control"
                                        value="<?= htmlspecialchars($datos['anio_diagnostico'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Última HbA1c (%)</label>
                                    <input type="number" step="0.1" name="ultima_hba1c" class="form-control"
                                        value="<?= htmlspecialchars($datos['ultima_hba1c'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Control Actual</label>
                                    <select name="control_actual" class="form-select">
                                        <option value="Adecuado" <?= ($datos['control_actual'] ?? '') == 'Adecuado' ? 'selected' : '' ?>>Adecuado</option>
                                        <option value="Inadecuado" <?= ($datos['control_actual'] ?? '') == 'Inadecuado' ? 'selected' : '' ?>>Inadecuado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- COMORBILIDADES POR CATEGORÍA -->
                            <div class="row">
                                <!-- 1. Enfermedades Endocrinometabólicas -->
                                <div class="col-md-6 mb-4">
                                    <div class="medical-section h-100">
                                        <h6 class="fw-bold text-primary mb-3">1. Enfermedades Endocrinometabólicas</h6>
                                        <div class="row g-2">
                                            <div class="col-12"><?= renderCheckbox('obesidad', 'Obesidad', $datos) ?></div>
                                            <div class="col-12">
                                                <?= renderCheckbox('enfermedad_tiroidea', 'Enfermedad tiroidea', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('sindrome_metabolico', 'Síndrome metabólico', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Enfermedades cardiovasculares -->
                                <div class="col-md-6 mb-4">
                                    <div class="medical-section h-100">
                                        <h6 class="fw-bold text-primary mb-3">2. Enfermedades cardiovasculares</h6>
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <?= renderCheckbox('hta', 'Hipertensión Arterial (HTA)', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('enfermedad_coronaria', 'Enfermedad Coronaria / Angina', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('infarto_miocardio', 'Infarto Agudo al Miocardio (IAM)', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('insuficiencia_cardiaca', 'Insuficiencia Cardiaca', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('dislipidemia', 'Dislipidemia', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('enf_vascular_periferica', 'Enfermedad Vascular Periférica', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Enfermedades renales -->
                                <div class="col-md-6 mb-4">
                                    <div class="medical-section h-100">
                                        <h6 class="fw-bold text-primary mb-3">3. Enfermedades renales</h6>
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <?= renderCheckbox('insuficiencia_renal_cronica', 'Insuficiencia renal crónica (IRC)', $datos) ?>
                                            </div>
                                            <div class="col-12"><?= renderCheckbox('proteinuria', 'Proteinuria', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('nefrolitiasis', 'Nefrolitiasis', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('infecciones_urinarias', 'Infecciones urinarias recurrentes', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 4. Enfermedades gastrointestinales -->
                                <div class="col-md-6 mb-4">
                                    <div class="medical-section h-100">
                                        <h6 class="fw-bold text-primary mb-3">4. Enfermedades gastrointestinales</h6>
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <?= renderCheckbox('higado_graso', 'Hígado graso', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('pancreatitis', 'Pancreatitis', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('gastroparesia', 'Gastroparesia', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Enfermedades neurológicas -->
                                <div class="col-md-6 mb-4">
                                    <div class="medical-section h-100">
                                        <h6 class="fw-bold text-primary mb-3">5. Enfermedades neurológicas</h6>
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <?= renderCheckbox('evc', 'Accidente cerebrovascular (EVC)', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('neuropatia_periferica_previa', 'Neuropatía periférica previa', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('amputaciones', 'Amputaciones', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 6. Complicaciones microvasculares -->
                                <div class="col-md-6 mb-4">
                                    <div class="medical-section h-100">
                                        <h6 class="fw-bold text-primary mb-3">6. Complicaciones microvasculares</h6>
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <?= renderCheckbox('retinopatia_diabetica', 'Retinopatía diabética', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('nefropatia_diabetica', 'Nefropatia diabetica /  insuficiensia renal', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('neuropatia_periferica', 'Neuropatía periférica', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('neuropatia_autonomica', 'Neuropatía autonomica (Gastroparesia, disfunsion erectil, etc)', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 7. Salud mental -->
                                <div class="col-md-6 mb-4">
                                    <div class="medical-section h-100">
                                        <h6 class="fw-bold text-primary mb-3">7. Salud mental</h6>
                                        <div class="row g-2">
                                            <div class="col-6"><?= renderCheckbox('depresion', 'Depresión', $datos) ?></div>
                                            <div class="col-6"><?= renderCheckbox('ansiedad', 'Ansiedad', $datos) ?></div>
                                            <div class="col-12">
                                                <?= renderCheckbox('trastornos_sueno', 'Trastornos del sueño', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 8. Enfermedades infecciosas -->
                                <div class="col-md-6 mb-4">
                                    <div class="medical-section h-100">
                                        <h6 class="fw-bold text-primary mb-3">8. Enfermedades infecciosas</h6>
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <?= renderCheckbox('tuberculosis', 'Tuberculosis (TB)', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('hepatitis_b_c', 'Hepatitis B o C', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('infecciones_piel', 'Infecciones de piel recurrentes', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('infecciones_urinarias', 'infecciones urinarias recurentes', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('pie_diabetico', 'pie diabetico / Ulceras cronicas', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= renderTextSection('Alergias', 'alergias_check', 'detalle_alergias', $datos) ?>
                        </div>
                        <div class="col-md-6">
                            <?= renderTextSection('Otras Enfermedades Crónicas', 'enfermedades_cronicas_check', 'detalle_enfermedades_cronicas', $datos) ?>
                        </div>
                        <div class="col-md-6">
                            <?= renderTextSection('Cirugías Previas', 'cirugias_previas_check', 'detalle_cirugias_previas', $datos) ?>
                        </div>
                        <div class="col-md-6">
                            <?= renderTextSection('Hospitalizaciones Previas', 'hospitalizaciones_previas_check', 'detalle_hospitalizaciones_previas', $datos) ?>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: TRATAMIENTO MÉDICO -->
                <div class="tab-pane fade" id="tab-med" role="tabpanel">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-capsule-pill"></i> Farmacoterapia
                                Anti-hiperglucemiante</h5>

                            <div class="row">
                                <!-- Antidiabéticos Orales -->
                                <div class="col-md-7">
                                    <div class="medical-section mb-4">
                                        <h6 class="fw-bold text-primary border-bottom pb-2">Antidiabéticos Orales por Clases
                                        </h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <p class="small fw-bold border-start border-3 border-primary ps-2 mb-2">
                                                    Biguanidas</p>
                                                <?= renderCheckbox('med_metformina', 'Metformina', $datos) ?>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <p class="small fw-bold border-start border-3 border-primary ps-2 mb-2">
                                                    Sulfonilureas</p>
                                                <?= renderCheckbox('med_sulfonilureas_glibenclamida', 'Glibenclamida', $datos) ?>
                                                <?= renderCheckbox('med_sulfonilureas_glimepirida', 'Glimepirida', $datos) ?>
                                                <?= renderCheckbox('med_sulfonilureas_gliclazida', 'Gliclazida', $datos) ?>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <p class="small fw-bold border-start border-3 border-primary ps-2 mb-2">
                                                    Inhibidores de DPP-4</p>
                                                <?= renderCheckbox('med_inhibidores_dpp4_sitagliptina', 'Sitagliptina', $datos) ?>
                                                <?= renderCheckbox('med_inhibidores_dpp4_saxaglipina', 'Saxaglipina', $datos) ?>
                                                <?= renderCheckbox('med_inhibidores_dpp4_linagliptina', 'Linagliptina', $datos) ?>
                                                <?= renderCheckbox('med_inhibidores_dpp4_alogliptina', 'Alogliptina', $datos) ?>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <p class="small fw-bold border-start border-3 border-primary ps-2 mb-2">
                                                    Inhibidores de SGLT-2</p>
                                                <?= renderCheckbox('med_inhibidores_sglt2_empagliflozina', 'Empagliflozina', $datos) ?>
                                                <?= renderCheckbox('med_inhibidores_sglt2_dapagliflozina', 'Dapagliflozina', $datos) ?>
                                                <?= renderCheckbox('med_inhibidores_sglt2_canagliflozina', 'Canagliflozina', $datos) ?>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <p class="small fw-bold border-start border-3 border-primary ps-2 mb-2">
                                                    Tiazolidinedionas (TZD)</p>
                                                <?= renderCheckbox('med_tiazolidinedionas_pioglitazona', 'Pioglitazona', $datos) ?>
                                                <?= renderCheckbox('med_tiazolidinedionas_rosiglitazona', 'Rosiglitazona', $datos) ?>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <p class="small fw-bold border-start border-3 border-primary ps-2 mb-2">
                                                    Otras Clases</p>
                                                <?= renderCheckbox('med_meglitinidas_repaglinida', 'Repaglinida', $datos) ?>
                                                <?= renderCheckbox('med_meglitinidas_nateglinida', 'Nateglinida', $datos) ?>
                                                <?= renderCheckbox('med_inhibidores_alfaglucosidasa_acarbosa', 'Acarbosa', $datos) ?>
                                                <?= renderCheckbox('med_inhibidores_alfaglucosidasa_miglitol', 'Miglitol', $datos) ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="medical-section">
                                        <h6 class="fw-bold text-primary border-bottom pb-2">Agonistas del Receptor de GLP-1
                                        </h6>
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <?= renderCheckbox('med_agonistas_glp1_exenatida', 'Exenatida', $datos) ?>
                                            </div>
                                            <div class="col-md-6">
                                                <?= renderCheckbox('med_agonistas_glp1_liraglutida', 'Liraglutida', $datos) ?>
                                            </div>
                                            <div class="col-md-6">
                                                <?= renderCheckbox('med_agonistas_glp1_dulaglutida', 'Dulaglutida', $datos) ?>
                                            </div>
                                            <div class="col-md-6">
                                                <?= renderCheckbox('med_agonistas_glp1_lixisenatida', 'Lixisenatida', $datos) ?>
                                            </div>
                                            <div class="col-md-6">
                                                <?= renderCheckbox('med_agonistas_glp1_semaglutida', 'Semaglutida', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Insulinas -->
                                <div class="col-md-5">
                                    <div class="medical-section h-100 bg-light border-primary-subtle shadow-sm">
                                        <h6 class="fw-bold text-primary border-bottom pb-2"><i
                                                class="bi bi-droplet-fill"></i> Terapia con Insulina</h6>

                                        <div class="mb-3">
                                            <p class="small fw-bold mb-1">Acción Rápida / Ultrarrápida</p>
                                            <?= renderCheckbox('ins_rapida_regular', 'Regular (R)', $datos) ?>
                                            <?= renderCheckbox('ins_ultrarrapida_lispro', 'Lispro', $datos) ?>
                                            <?= renderCheckbox('ins_ultrarrapida_aspart', 'Aspart', $datos) ?>
                                            <?= renderCheckbox('ins_ultrarrapida_glulisina', 'Glulisina', $datos) ?>
                                        </div>

                                        <div class="mb-3">
                                            <p class="small fw-bold mb-1">Acción Intermedia / Prolongada</p>
                                            <?= renderCheckbox('ins_intermedia_nph', 'NPH (N)', $datos) ?>
                                            <?= renderCheckbox('ins_prolongada_glargina', 'Glargina U100', $datos) ?>
                                            <?= renderCheckbox('ins_prolongada_detemir', 'Detemir', $datos) ?>
                                            <?= renderCheckbox('ins_prolongada_degludec', 'Degludec U100', $datos) ?>
                                        </div>

                                        <div class="mb-3">
                                            <p class="small fw-bold mb-1">Acción Ultralarga / Mezclas</p>
                                            <?= renderCheckbox('ins_ultralarga_glargina_u300', 'Glargina U300', $datos) ?>
                                            <?= renderCheckbox('ins_ultralarga_degludec', 'Degludec U200/U300', $datos) ?>
                                            <?= renderCheckbox('ins_mezcla_nph_regular', '70/30 (NPH/R)', $datos) ?>
                                            <?= renderCheckbox('ins_mezcla_lispro', 'Mezcla Lispro', $datos) ?>
                                            <?= renderCheckbox('ins_mezcla_aspart', 'Mezcla Aspart', $datos) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-primary mb-3">Otros Medicamentos</h6>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <?= renderCheckbox('med_estatinas', 'Estatinas (Atorvastatina, Simvastatina, etc.)', $datos) ?>
                                        </div>
                                        <div class="col-12">
                                            <?= renderCheckbox('med_antihipertensivos', 'Antihipertensivos (IECA, ARA-II, etc.)', $datos) ?>
                                        </div>
                                        <div class="col-12">
                                            <?= renderCheckbox('med_antiagregantes', 'Antiagregantes (Aspirina, Clopidogrel)', $datos) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Detalle Completo de Esquema (Dosis/Horarios)</label>
                                    <textarea name="detalles_medicacion" class="form-control" rows="4"
                                        placeholder="Ej: Metformina 850mg c/12hrs, Insulina Glargina 20 UI nocturna..."><?= htmlspecialchars($datos['detalles_medicacion'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: LABORATORIOS Y ESTUDIOS -->
                <div class="tab-pane fade" id="tab-lab" role="tabpanel">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-microscope"></i> Estudios de Laboratorio por Perfil
                            </h5>

                            <div class="row">
                                <!-- Perfil Glucémico -->
                                <div class="col-md-4 mb-4">
                                    <div class="medical-section">
                                        <h6 class="fw-bold text-primary mb-3">Perfil Glucémico</h6>
                                        <?= renderCheckbox('lab_glucosa_ayunas', 'Glucosa en ayunas', $datos) ?>
                                        <?= renderCheckbox('lab_glucosa_postprandial', 'Glucosa postprandial', $datos) ?>
                                        <?= renderCheckbox('lab_hba1c', 'HbA1c (Glucosilada)', $datos) ?>
                                        <?= renderCheckbox('lab_curva_tolerancia', 'Curva de tolerancia', $datos) ?>
                                    </div>
                                </div>

                                <!-- Perfil Renal -->
                                <div class="col-md-4 mb-4">
                                    <div class="medical-section">
                                        <h6 class="fw-bold text-primary mb-3">Perfil Renal y Orina</h6>
                                        <?= renderCheckbox('lab_creatinina_serica', 'Creatinina sérica', $datos) ?>
                                        <?= renderCheckbox('lab_tfg', 'TFG (Tasa Filtrado)', $datos) ?>
                                        <?= renderCheckbox('lab_urea_bun', 'Urea / BUN', $datos) ?>
                                        <?= renderCheckbox('lab_microalbuminuria_orina', 'Microalbuminuria', $datos) ?>
                                        <?= renderCheckbox('lab_relacion_acr', 'Relación Alb/Creat (ACR)', $datos) ?>
                                        <?= renderCheckbox('lab_ego', 'EGO Completo', $datos) ?>
                                    </div>
                                </div>

                                <!-- Perfil Lipídico -->
                                <div class="col-md-4 mb-4">
                                    <div class="medical-section">
                                        <h6 class="fw-bold text-primary mb-3">Perfil Lipídico</h6>
                                        <?= renderCheckbox('lab_colesterol_total', 'Colesterol total', $datos) ?>
                                        <?= renderCheckbox('lab_ldl', 'Colesterol LDL', $datos) ?>
                                        <?= renderCheckbox('lab_hdl', 'Colesterol HDL', $datos) ?>
                                        <?= renderCheckbox('lab_trigliceridos', 'Triglicéridos', $datos) ?>
                                    </div>
                                </div>

                                <!-- Electrolitos -->
                                <div class="col-md-4 mb-4">
                                    <div class="medical-section">
                                        <h6 class="fw-bold text-primary mb-3">Electrolitos y Gasometría</h6>
                                        <div class="row g-1">
                                            <div class="col-6"><?= renderCheckbox('lab_sodio', 'Sodio (Na)', $datos) ?>
                                            </div>
                                            <div class="col-6"><?= renderCheckbox('lab_potasio', 'Potasio (K)', $datos) ?>
                                            </div>
                                            <div class="col-6"><?= renderCheckbox('lab_cloro', 'Cloro (Cl)', $datos) ?>
                                            </div>
                                            <div class="col-6">
                                                <?= renderCheckbox('lab_bicarbonato', 'Bicarbonato', $datos) ?>
                                            </div>
                                            <div class="col-6"><?= renderCheckbox('lab_calcio', 'Calcio (Ca)', $datos) ?>
                                            </div>
                                            <div class="col-6"><?= renderCheckbox('lab_fosforo', 'Fósforo (P)', $datos) ?>
                                            </div>
                                            <div class="col-6">
                                                <?= renderCheckbox('lab_magnesio', 'Magnesio (Mg)', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('lab_gasometria', 'Gasometría arterial', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Función Hepática -->
                                <div class="col-md-4 mb-4">
                                    <div class="medical-section">
                                        <h6 class="fw-bold text-primary mb-3">Función Hepática / Pancreática</h6>
                                        <?= renderCheckbox('lab_alt', 'ALT / GPT', $datos) ?>
                                        <?= renderCheckbox('lab_ast', 'AST / GOT', $datos) ?>
                                        <?= renderCheckbox('lab_fosfatasa_alcalina', 'Fosfatasa alcalina', $datos) ?>
                                        <?= renderCheckbox('lab_bilirrubinas', 'Bilirrubinas', $datos) ?>
                                        <?= renderCheckbox('lab_albumina_serica', 'Albúmina sérica', $datos) ?>
                                        <?= renderCheckbox('lab_cetonas', 'Cetonas (Sangre/Orina)', $datos) ?>
                                    </div>
                                </div>

                                <!-- Otros Estudios -->
                                <div class="col-md-4 mb-4">
                                    <div class="medical-section">
                                        <h6 class="fw-bold text-primary mb-3">Marcadores y Otros</h6>
                                        <?= renderCheckbox('lab_pcr', 'Proteína C Reactiva (PCR)', $datos) ?>
                                        <?= renderCheckbox('lab_vsg', 'VSG', $datos) ?>
                                        <?= renderCheckbox('lab_peptido_c', 'Péptido C', $datos) ?>
                                        <?= renderCheckbox('lab_insulinemia', 'Insulinemia', $datos) ?>
                                        <?= renderCheckbox('lab_vitamina_d', 'Vitamina D', $datos) ?>
                                        <?= renderCheckbox('lab_hormonas_tiroideas', 'Perfil Tiroideo', $datos) ?>
                                        <?= renderCheckbox('lab_hemograma', 'Hemograma / BH', $datos) ?>
                                        <?= renderCheckbox('lab_troponina', 'Troponina (I/T)', $datos) ?>
                                        <?= renderCheckbox('lab_bnp', 'BNP / Pro-BNP', $datos) ?>
                                        <?= renderCheckbox('lab_homocisteina', 'Homocisteína', $datos) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: EVALUACIÓN CLÍNICA Y SEGUIMIENTO -->
                <div class="tab-pane fade" id="tab-eval" role="tabpanel">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-stethoscope"></i> Evaluación Clínica Actual</h5>

                            <div class="row g-4">
                                <!-- Signos Vitales -->
                                <div class="col-md-12">
                                    <div class="bg-light p-3 rounded-3 border mb-4">
                                        <h6 class="fw-bold mb-3"><i class="bi bi-activity text-danger"></i> Signos Vitales y
                                            Antropometría</h6>
                                        <div class="row g-3">
                                            <div class="col-md-2">
                                                <label class="form-label small fw-bold">Peso (kg)</label>
                                                <input type="number" step="0.1" name="peso" id="peso" class="form-control"
                                                    value="<?= htmlspecialchars($datos['peso'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-bold">Talla (cm)</label>
                                                <input type="number" name="talla" id="talla" class="form-control"
                                                    value="<?= htmlspecialchars($datos['talla'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-bold text-primary">IMC</label>
                                                <input type="text" name="imc" id="imc"
                                                    class="form-control bg-white border-primary"
                                                    value="<?= htmlspecialchars($datos['imc'] ?? '') ?>" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-bold">Presión Art.</label>
                                                <input type="text" name="presion_arterial" class="form-control"
                                                    placeholder="120/80"
                                                    value="<?= htmlspecialchars($datos['presion_arterial'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-bold">F. Cardiaca</label>
                                                <div class="input-group">
                                                    <input type="number" name="frecuencia_cardiaca" class="form-control"
                                                        value="<?= htmlspecialchars($datos['frecuencia_cardiaca'] ?? '') ?>">
                                                    <span class="input-group-text small px-1">lpm</span>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small fw-bold">Temp.</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.1" name="temperatura" class="form-control"
                                                        value="<?= htmlspecialchars($datos['temperatura'] ?? '') ?>">
                                                    <span class="input-group-text small px-1">°C</span>
                                                </div>
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label small fw-bold">F. Resp.</label>
                                                <div class="input-group">
                                                    <input type="number" name="frecuencia_respiratoria" class="form-control"
                                                        value="<?= htmlspecialchars($datos['frecuencia_respiratoria'] ?? '') ?>">
                                                    <span class="input-group-text small px-1">rpm</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold">Circ. Abdominal</label>
                                                <div class="input-group">
                                                    <input type="number" step="0.1" name="circunferencia_abdominal"
                                                        class="form-control"
                                                        value="<?= htmlspecialchars($datos['circunferencia_abdominal'] ?? '') ?>">
                                                    <span class="input-group-text small px-1">cm</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small fw-bold">Glucosa Capilar</label>
                                                <div class="input-group">
                                                    <input type="number" name="glucosa_capilar" class="form-control"
                                                        value="<?= htmlspecialchars($datos['glucosa_capilar'] ?? '') ?>">
                                                    <span class="input-group-text">mg/dL</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Control Glucémico -->
                                <div class="col-md-6">
                                    <div class="medical-section h-100">
                                        <h6 class="fw-bold text-primary border-bottom pb-2">Control Glucémico y Bitácora
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <?= renderCheckbox('control_bitacora', 'Cuenta con bitácora de monitoreo', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('control_hipoglucemias', 'Presenta hipoglucemias recurrentes', $datos) ?>
                                                <div id="control_hipoglucemias_detalles_container" class="mt-2 ps-4"
                                                    style="<?= ($datos['control_hipoglucemias'] ?? 0) ? '' : 'display:none' ?>">
                                                    <textarea name="control_hipoglucemias_detalles"
                                                        class="form-control form-control-sm" rows="2"
                                                        placeholder="Frecuencia, horario, síntomas..."><?= htmlspecialchars($datos['control_hipoglucemias_detalles'] ?? '') ?></textarea>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('control_hiperglucemias_sintomaticas', 'Hiperglucemias sintomáticas', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('control_adherencia', 'Buena adherencia al tratamiento', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Revisiones Especializadas -->
                                <div class="col-md-6">
                                    <div class="medical-section h-100">
                                        <h6 class="fw-bold text-primary border-bottom pb-2">Revisiones de Complicaciones
                                        </h6>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <?= renderCheckbox('rev_neuropatia_monofilamento', 'Examen con monofilamento (Sensibilidad)', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('rev_renal_laboratorios', 'Revisión de función renal (Microalb/TFG)', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderTextSection('Hallazgos en Pies', 'pie_diabetico', 'rev_pies_detalles', $datos) ?>
                                            </div>
                                            <div class="col-12">
                                                <?= renderCheckbox('rev_riesgo_cv', 'Evaluación de riesgo CV (SCORE/Framingham)', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Estilo de Vida y Salud Mental -->
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="medical-section">
                                                <h6 class="fw-bold text-success mb-3"><i class="bi bi-bicycle"></i> Estilo
                                                    de Vida</h6>
                                                <?= renderCheckbox('alimentacion_adecuada', 'Alimentación saludable', $datos) ?>
                                                <?= renderCheckbox('actividad_fisica', 'Actividad física regular', $datos) ?>
                                                <?= renderCheckbox('horarios_comida_regulares', 'Horarios de comida regulares', $datos) ?>
                                                <?= renderCheckbox('tabaquismo', 'Tabaquismo activo', $datos) ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="medical-section">
                                                <h6 class="fw-bold text-info mb-3"><i class="bi bi-mortarboard"></i>
                                                    Educación y Psico-Social</h6>
                                                <?= renderCheckbox('educacion_diabetologica', 'Recibió educación diabetológica', $datos) ?>
                                                <?= renderCheckbox('tecnica_insulina', 'Técnica de inyección correcta', $datos) ?>
                                                <?= renderCheckbox('revision_metas', 'Revisión de metas terapéuticas', $datos) ?>
                                                <?= renderCheckbox('apoyo_familiar_social', 'Cuenta con apoyo familiar/social', $datos) ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Plan Médico, Conducta y Próximos Pasos</label>
                                    <textarea name="observaciones_adicionales" class="form-control" rows="5"
                                        placeholder="Escriba el plan detallado, cambios en dosis, solicitud de exámenes..."><?= htmlspecialchars($datos['observaciones_adicionales'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 mb-5 text-center sticky-bottom bg-white p-3 rounded shadow">
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="bi bi-save2"></i> Guardar Consulta Medicina Interna
                </button>

        </form>

    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Cálculo de IMC
        const pesoInput = document.getElementById('peso');
        const tallaInput = document.getElementById('talla');
        const imcInput = document.getElementById('imc');

        function calcularIMC() {
            const peso = parseFloat(pesoInput.value);
            const talla = parseFloat(tallaInput.value) / 100;

            if (peso > 0 && talla > 0) {
                const imc = peso / (talla * talla);
                imcInput.value = imc.toFixed(2);
            } else {
                imcInput.value = '';
            }
        }

        pesoInput.addEventListener('input', calcularIMC);
        tallaInput.addEventListener('input', calcularIMC);

        // Lógica para cuadros de detalle dinámicos
        const dynamicChecks = document.querySelectorAll('.dynamic-check');
        dynamicChecks.forEach(check => {
            check.addEventListener('change', function () {
                const targetId = this.getAttribute('data-target');
                const container = document.getElementById(targetId + '_container');
                if (this.checked) {
                    container.style.display = 'block';
                    container.querySelector('textarea').focus();
                } else {
                    container.style.display = 'none';
                    container.querySelector('textarea').value = '';
                }
            });
        });

        // Persistir la pestaña activa
        const triggerTabList = [].slice.call(document.querySelectorAll('#medicalTabs button'))
        triggerTabList.forEach(function (triggerEl) {
            const tabTrigger = new bootstrap.Tab(triggerEl)
            triggerEl.addEventListener('click', function (event) {
                event.preventDefault()
                tabTrigger.show()
                localStorage.setItem('activeMedicalTab', triggerEl.id);
            })
        });

        const activeTab = localStorage.getItem('activeMedicalTab');
        if (activeTab) {
            const tab = document.getElementById(activeTab);
            if (tab) {
                const bsTab = new bootstrap.Tab(tab);
                bsTab.show();
            }
        }

        // Búsqueda de pacientes (Fallback)
        const patientSearchInput = document.getElementById('patientSearchInput');
        if (patientSearchInput) {
            const resultsContainer = document.getElementById('patientSearchResults');

            patientSearchInput.addEventListener('input', debounce(function () {
                const search = this.value;
                if (search.length < 2) {
                    resultsContainer.innerHTML = '<div class="text-center py-3 text-muted"><i class="bi bi-person-fill-gear fs-2 d-block mb-2"></i><small>Escriba para buscar</small></div>';
                    return;
                }

                resultsContainer.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

                $.ajax({
                    url: '../../ajax/buscar_pacientes_medicina.php',
                    data: { search: search },
                    success: function (response) {
                        if (response.success) {
                            resultsContainer.innerHTML = response.html;
                        }
                    }
                });
            }, 500));
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>