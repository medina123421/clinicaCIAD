<?php
/**
 * Vista de Nutrición Clínica
 * Formulario completo basado en los formatos físicos con lógica dinámica
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Nutricion.php';
require_once '../../models/Visita.php';
require_once '../../models/Paciente.php';

$id_visita = $_GET['id_visita'] ?? null;
$id_paciente = $_GET['id_paciente'] ?? null;
$message = '';
$message_type = '';

$database = new Database();
$db = $database->getConnection();

$nutricionModel = new Nutricion($db);
$visitaModel = new Visita($db);
$pacienteModel = new Paciente($db);

// Procesar Formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consulta'])) {
    try {
        // Procesar checkboxes y arrays JSON antes de enviar al modelo
        // Nota: el modelo ya hace json_encode, aquí solo aseguramos que lleguen como arrays los checkboxes
        $json_fields = [
            'diagnosticos_medicos',
            'sintomas',
            'suplementos_detalle',
            'diagnostico_nutricional',
            'objetivos_tratamiento',
            'recomendaciones_generales' // Se enviará como array de claves
        ];

        foreach ($json_fields as $field) {
            $_POST[$field] = $_POST[$field] ?? [];
        }

        // Procesar Recomendaciones Generales (Checkboxes manuales mapeados a array)
        $recomendaciones = [];
        if (isset($_POST['rec_azucar']))
            $recomendaciones[] = 'Evitar altos en azúcar';
        if (isset($_POST['rec_ultra']))
            $recomendaciones[] = 'Evitar Ultraprocesados';
        if (isset($_POST['rec_fritas']))
            $recomendaciones[] = 'Evitar preparaciones fritas/empanizadas';
        $_POST['recomendaciones_generales'] = $recomendaciones;

        // Procesar Frecuencia de Consumo (Map inputs to array structure for model)
        $frecuencia = [];
        $grupos_alimentos = [
            'verduras',
            'frutas',
            'cereales',
            'leguminosas',
            'carnes_rojas',
            'pollo_pescado',
            'lacteos',
            'huevos',
            'procesados',
            'te',
            'cafe',
            'cafe_azucar',
            'refresco',
            'jugos',
            'agua'
        ];
        foreach ($grupos_alimentos as $g) {
            $frecuencia[$g] = $_POST['freq_' . $g] ?? '';
        }
        $_POST['frecuencia_consumo'] = $frecuencia;

        // Procesar Recordatorio 24h
        $recordatorio = [];
        $tiempos = ['desayuno', 'almuerzo', 'comida', 'colacion', 'cena'];
        foreach ($tiempos as $t) {
            $recordatorio[$t] = [
                'hora' => $_POST['rec_' . $t . '_hora'] ?? '',
                'desc' => $_POST['rec_' . $t . '_desc'] ?? '',
                'hc' => $_POST['rec_' . $t . '_hc'] ?? ''
            ];
        }
        $_POST['recordatorio_24h'] = $recordatorio;

        if ($nutricionModel->guardar($_POST)) {
            $message = "Consulta de Nutrición guardada exitosamente.";
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
    $datos = $nutricionModel->obtenerPorVisita($id_visita) ?: [];
    $visita = $visitaModel->obtenerPorId($id_visita);
    if ($visita) {
        $id_paciente = $visita['id_paciente'];
    }
}

if ($id_paciente) {
    $paciente = $pacienteModel->obtenerPorId($id_paciente);
    // Si no cargamos datos por visita, intentamos cargar los últimos datos del paciente para pre-poblar
    if (empty($datos)) {
        $datos = $nutricionModel->obtenerPorPaciente($id_paciente) ?: [];
    }
}

// Si no hay visita ni paciente, esta vista muestra el selector
if (!$id_visita && !$id_paciente) {
    // Si no hay visita ni paciente, obtener todos los pacientes activos para selección inicial
    $query_pacientes = "SELECT p.id_paciente, p.numero_expediente, 
                        CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) as nombre_completo,
                        (SELECT id_visita FROM visitas WHERE id_paciente = p.id_paciente ORDER BY fecha_visita DESC LIMIT 1) as ultima_visita_id,
                        (SELECT fecha_visita FROM visitas WHERE id_paciente = p.id_paciente ORDER BY fecha_visita DESC LIMIT 1) as ultima_visita_fecha
                        FROM pacientes p 
                        WHERE p.activo = 1 
                        ORDER BY p.nombre ASC LIMIT 200";
    $stmt_pacientes = $db->prepare($query_pacientes);
    $stmt_pacientes->execute();
    $lista_pacientes = $stmt_pacientes->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = 'Consulta Nutrición Clínica';

include '../../includes/header.php';
?>

<style>
    :root {
        --nutri-green: #198754;
        --nutri-light: #f8fff9;
        --nutri-border: #d1e7dd;
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
        background-color: var(--nutri-green);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }

    .section-title {
        color: var(--nutri-green);
        border-bottom: 2px solid var(--nutri-border);
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    .nav-tabs .nav-link.active {
        color: var(--nutri-green);
        background: transparent;
        border-bottom-color: var(--nutri-green);
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-apple text-success"></i> Nutrición Clínica</h2>
            <?php if ($paciente): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a
                                href="../pacientes/detalle.php?id=<?= $paciente['id_paciente'] ?>"><?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?></a>
                        </li>
                        <?php if ($id_visita): ?>
                            <li class="breadcrumb-item active">Visita #<?= $id_visita ?></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active">Consulta Nutricional</li>
                    </ol>
                </nav>
            <?php endif; ?>
        </div>
        <div class="btn-group">
            <a href="../../../index.php" class="btn btn-light shadow-sm"><i class="bi bi-house"></i> Home</a>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi <?= $message_type == 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> me-2"></i>
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!$id_visita && !$id_paciente): ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-success text-white d-flex align-items-center">
                        <i class="bi bi-person-plus-fill me-2 fs-5"></i>
                        <span>Seleccionar Paciente para Consulta</span>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Busque y seleccione al paciente para realizar o actualizar su registro de
                            Nutrición Clínica.</p>
                        <div class="search-box mb-4">
                            <i class="bi bi-search text-muted"></i>
                            <input type="text" class="form-control form-control-lg border-success border-opacity-25"
                                id="patientSelectionInput" placeholder="Buscar por nombre o número de expediente...">
                        </div>

                        <div id="patientSearchResults" class="list-group list-group-flush shadow-sm rounded-3 border"
                            style="max-height: 400px; overflow-y: auto;">
                            <?php foreach ($lista_pacientes as $p): 
                                $searchTextNutri = strtolower($p['nombre_completo'] . ' ' . $p['numero_expediente']);
                            ?>
                                <div class="list-group-item list-group-item-action p-3 border-bottom patient-item" data-search="<?= htmlspecialchars($searchTextNutri) ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($p['nombre_completo']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($p['numero_expediente']) ?></small>
                                        </div>
                                        <?php if ($p['ultima_visita_id']): ?>
                                            <a href="nutricion.php?id_visita=<?= $p['ultima_visita_id'] ?>" class="btn btn-sm btn-outline-success rounded-pill">
                                                <i class="bi bi-apple"></i> Abrir Última (<?= date('d/m/y', strtotime($p['ultima_visita_fecha'])) ?>)
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Sin visitas</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>

    <form method="POST" id="formNutricion">
        <input type="hidden" name="id_paciente" value="<?= $id_paciente ?>">
        <input type="hidden" name="id_visita" value="<?= $id_visita ?>">
        <input type="hidden" name="save_consulta" value="1">

        <ul class="nav nav-tabs mb-4 px-2 bg-white rounded shadow-sm sticky-top" style="top: 10px; z-index: 100;"
            id="nutriTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="antro-tab" data-bs-toggle="tab" data-bs-target="#antro"
                    type="button"><i class="bi bi-rulers"></i> Antropometría</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="clinica-tab" data-bs-toggle="tab" data-bs-target="#clinica"
                    type="button"><i class="bi bi-heart-pulse"></i> C. Clínica</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="dietetica-tab" data-bs-toggle="tab" data-bs-target="#dietetica"
                    type="button"><i class="bi bi-egg-fried"></i> Dietética</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="estilo-tab" data-bs-toggle="tab" data-bs-target="#estilo" type="button"><i
                        class="bi bi-bicycle"></i> Estilo de Vida</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="dx-tab" data-bs-toggle="tab" data-bs-target="#dx" type="button"><i
                        class="bi bi-clipboard-check"></i> Diagnóstico y Tx</button>
            </li>
        </ul>

        <div class="tab-content" id="myTabContent">

            <!-- ANTROPOMETRÍA -->
            <div class="tab-pane fade show active" id="antro" role="tabpanel">
                <div class="card border-0 mb-4 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="section-title">A. Evaluación Antropométrica</h5>
                        <div class="row g-3">
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Peso (kg)</label>
                                <input type="number" step="0.01" class="form-control" name="peso"
                                    value="<?= $datos['peso'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Talla (cm)</label>
                                <input type="number" step="0.01" class="form-control" name="talla"
                                    value="<?= $datos['talla'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Circunferencia Cintura (cm)</label>
                                <input type="number" step="0.01" class="form-control" name="circunferencia_cintura"
                                    value="<?= $datos['circunferencia_cintura'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">IMC</label>
                                <input type="number" step="0.01" class="form-control" name="imc"
                                    value="<?= $datos['imc'] ?? '' ?>">
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">% Grasa</label>
                                <input type="number" step="0.01" class="form-control" name="porcentaje_grasa"
                                    value="<?= $datos['porcentaje_grasa'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Kg de Grasa</label>
                                <input type="number" step="0.01" class="form-control" name="kilos_grasa"
                                    value="<?= $datos['kilos_grasa'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Índice Masa Muscular</label>
                                <input type="number" step="0.01" class="form-control" name="indice_masa_muscular"
                                    value="<?= $datos['indice_masa_muscular'] ?? '' ?>">
                            </div>
                            <div class="col-md-6 col-lg-3">
                                <label class="form-label">Kg de Masa Muscular</label>
                                <input type="number" step="0.01" class="form-control" name="kilos_masa_muscular"
                                    value="<?= $datos['kilos_masa_muscular'] ?? '' ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-secondary me-2" onclick="saveForm()"><i class="bi bi-save"></i>
                        Guardar Progreso</button>
                    <button type="button" class="btn btn-primary" onclick="nextTab('clinica')">Siguiente <i
                            class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            <!-- CLÍNICA -->
            <div class="tab-pane fade" id="clinica" role="tabpanel">
                <div class="card border-0 mb-4 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="section-title">C. Evaluación Clínica</h5>

                        <h6 class="text-success mb-3">I. Diagnóstico Médico Actual</h6>
                        <div class="row g-2 mb-4">
                            <?php
                            $dx_opts = ['Diabetes Tipo 1', 'Diabetes Tipo 2', 'Pre-Diabetes', 'Hipertensión Arterial', 'Obesidad', 'Dislipidemia (Colesterol/Triglicéridos)', 'Enfermedad Renal', 'Enfermedad Hepática', 'Enfermedad Cardiovascular', 'Hipotiroidismo | Hipertiroidismo', 'Cáncer'];
                            $vals = $datos['diagnosticos_medicos'] ?? [];
                            foreach ($dx_opts as $opt):
                                ?>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="diagnosticos_medicos[]"
                                            value="<?= $opt ?>" <?= in_array($opt, $vals) ? 'checked' : '' ?>>
                                        <label class="form-check-label"><?= $opt ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="col-12 mt-2">
                                <label class="form-label">Especifique el diagnóstico y las alergias:</label>
                                <textarea class="form-control" name="diagnostico_especificar"
                                    rows="2"><?= htmlspecialchars($datos['diagnostico_especificar'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <h6 class="text-success mb-3">II. Síntomas</h6>
                        <div class="row g-2 mb-4">
                            <?php
                            $sintomas = ['Náuseas', 'Vómito', 'Diarrea', 'Estreñimiento', 'Distensión Abdominal', 'Acidez/Reflujo', 'Fatiga/Cansancio'];
                            $vals = $datos['sintomas'] ?? [];
                            foreach ($sintomas as $opt):
                                ?>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sintomas[]"
                                            value="<?= $opt ?>" <?= in_array($opt, $vals) ? 'checked' : '' ?>>
                                        <label class="form-check-label"><?= $opt ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <h6 class="text-success mb-3">III. Signos</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Temperatura (°C)</label>
                                <input type="number" step="0.1" class="form-control" name="temperatura"
                                    value="<?= $datos['temperatura'] ?? '' ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Presión Arterial (mmHg)</label>
                                <input type="text" class="form-control" name="presion_arterial"
                                    value="<?= htmlspecialchars($datos['presion_arterial'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 mb-4 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="section-title">I. Medicamentos y Suplementos</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">¿Toma medicamentos actualmente?</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="toma_medicamentos" value="Si"
                                            <?= ($datos['toma_medicamentos'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                        <label class="form-check-label">Sí</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="toma_medicamentos" value="No"
                                            <?= ($datos['toma_medicamentos'] ?? '') == 'No' ? 'checked' : '' ?>>
                                        <label class="form-check-label">No</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold">¿Toma suplementos alimenticios/vitaminas?</label>
                                <div class="row g-2">
                                    <div class="col-md-2">
                                        <div class="d-flex gap-2">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="toma_suplementos"
                                                    value="Si" <?= ($datos['toma_suplementos'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                                <label class="form-check-label">Sí</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="toma_suplementos"
                                                    value="No" <?= ($datos['toma_suplementos'] ?? '') == 'No' ? 'checked' : '' ?>>
                                                <label class="form-check-label">No</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-10">
                                        <div class="d-flex flex-wrap gap-3">
                                            <?php
                                            $sups = ['Multivitamínico', 'Proteína Suero de leche', 'Proteína Vegana', 'Monohidrato de Creatina', 'Vit D', 'Omega 3'];
                                            $vals = $datos['suplementos_detalle'] ?? [];
                                            foreach ($sups as $opt):
                                                ?>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="suplementos_detalle[]" value="<?= $opt ?>" <?= in_array($opt, $vals) ? 'checked' : '' ?>>
                                                    <label class="form-check-label"><?= $opt ?></label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="mt-2">
                                            <input type="text" class="form-control form-control-sm"
                                                name="suplementos_otro" placeholder="Otros: Especificar"
                                                value="<?= htmlspecialchars($datos['suplementos_otro'] ?? '') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="button" class="btn btn-secondary me-2" onclick="saveForm()"><i class="bi bi-save"></i>
                        Guardar Progreso</button>
                    <button type="button" class="btn btn-primary" onclick="nextTab('dietetica')">Siguiente <i
                            class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            <!-- DIETÉTICA -->
            <div class="tab-pane fade" id="dietetica" role="tabpanel">
                <div class="card border-0 mb-4 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="section-title">D. Evaluación Dietética (Frecuencia de Consumo)</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Alimento/Grupo</th>
                                        <th>Frecuencia Semanal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $freq_data = $datos['frecuencia_consumo'] ?? [];

                                    function renderFreqRow($key, $label, $options, $current)
                                    {
                                        echo "<tr><td>$label</td><td><div class='d-flex flex-wrap gap-2'>";
                                        foreach ($options as $opt) {
                                            $checked = ($current == $opt) ? 'checked' : '';
                                            echo "<div class='form-check me-2'><input class='form-check-input' type='radio' name='freq_$key' value='$opt' $checked><label class='form-check-label small'>$opt</label></div>";
                                        }
                                        echo "</div></td></tr>";
                                    }

                                    renderFreqRow('verduras', 'Verduras', ['0-2 veces', '3-5 veces', '6-7 veces', 'Varias veces al día'], $freq_data['verduras'] ?? '');
                                    renderFreqRow('frutas', 'Frutas', ['0-2 veces', '3-5 veces', '6-7 veces', 'Varias veces al día'], $freq_data['frutas'] ?? '');
                                    renderFreqRow('cereales', 'Cereales/Tubérculos', ['1-2 veces al día', '3-4 veces al día', '5+ veces al día'], $freq_data['cereales'] ?? '');

                                    $opts_standard = ['0-1 vez', '2-3 veces', '4+ veces'];
                                    renderFreqRow('leguminosas', 'Leguminosas', $opts_standard, $freq_data['leguminosas'] ?? '');
                                    renderFreqRow('carnes_rojas', 'Carnes Rojas', $opts_standard, $freq_data['carnes_rojas'] ?? '');
                                    renderFreqRow('pollo_pescado', 'Pollo/Pescado', $opts_standard, $freq_data['pollo_pescado'] ?? '');

                                    renderFreqRow('lacteos', 'Lácteos', ['0-2 veces al día', '3+ veces al día'], $freq_data['lacteos'] ?? '');
                                    renderFreqRow('huevos', 'Huevos', ['0-2 veces', '3-5 veces', '6+ veces'], $freq_data['huevos'] ?? '');

                                    renderFreqRow('procesados', 'Alimentos procesados', ['Casi nunca', '1-2 veces al mes', '1 vez por semana', '2+ veces por semana'], $freq_data['procesados'] ?? '');

                                    renderFreqRow('te', 'Té con azúcar', $opts_standard, $freq_data['te'] ?? '');
                                    renderFreqRow('cafe', 'Café', $opts_standard, $freq_data['cafe'] ?? '');
                                    renderFreqRow('cafe_azucar', 'Café con azúcar', $opts_standard, $freq_data['cafe_azucar'] ?? '');
                                    renderFreqRow('refresco', 'Refresco', $opts_standard, $freq_data['refresco'] ?? '');
                                    renderFreqRow('jugos', 'Jugos', $opts_standard, $freq_data['jugos'] ?? '');

                                    renderFreqRow('agua', 'Agua simple (Litros al día)', ['<1 litro', '1-2 litros', '2-3 litros', '3+ litros'], $freq_data['agua'] ?? '');
                                    ?>
                                    <tr>
                                        <td>Alergias/Intolerancias Alimentarias</td>
                                        <td>
                                            <div class="d-flex gap-3 align-items-center">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="alergias_alimentarias" value="Si"
                                                        <?= ($datos['alergias_alimentarias'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                                    <label class="form-check-label">Sí</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                        name="alergias_alimentarias" value="No"
                                                        <?= ($datos['alergias_alimentarias'] ?? '') == 'No' ? 'checked' : '' ?>>
                                                    <label class="form-check-label">No</label>
                                                </div>
                                                <input type="text" class="form-control form-control-sm ms-2"
                                                    name="alergias_alimentarias_cual" placeholder="Cual: Especificar"
                                                    value="<?= htmlspecialchars($datos['alergias_alimentarias_cual'] ?? '') ?>">
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 mb-4 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="section-title">Recordatorio 24 Horas</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Dieta Habitual</th>
                                        <th width="100">Hora</th>
                                        <th>Especificar</th>
                                        <th width="150">Conteo Carbohidratos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $rec = $datos['recordatorio_24h'] ?? [];
                                    $comidas = [
                                        'desayuno' => 'Desayuno',
                                        'almuerzo' => 'Almuerzo',
                                        'comida' => 'Comida',
                                        'colacion' => 'Colación',
                                        'cena' => 'Cena'
                                    ];
                                    foreach ($comidas as $key => $label):
                                        $data = $rec[$key] ?? [];
                                        ?>
                                        <tr>
                                            <td class="fw-bold"><?= $label ?></td>
                                            <td><input type="text" class="form-control form-control-sm"
                                                    name="rec_<?= $key ?>_hora"
                                                    value="<?= htmlspecialchars($data['hora'] ?? '') ?>"></td>
                                            <td><input type="text" class="form-control form-control-sm"
                                                    name="rec_<?= $key ?>_desc"
                                                    value="<?= htmlspecialchars($data['desc'] ?? '') ?>"></td>
                                            <td><input type="number" class="form-control form-control-sm"
                                                    name="rec_<?= $key ?>_hc" placeholder="1 | 2 | 3..."
                                                    value="<?= htmlspecialchars($data['hc'] ?? '') ?>"></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-3">
                    <button type="button" class="btn btn-secondary me-2" onclick="saveForm()"><i class="bi bi-save"></i>
                        Guardar Progreso</button>
                    <button type="button" class="btn btn-primary" onclick="nextTab('estilo')">Siguiente <i
                            class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            <!-- ESTILO DE VIDA -->
            <div class="tab-pane fade" id="estilo" role="tabpanel">
                <div class="card border-0 mb-4 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="section-title">E. Evaluación de Estilo de Vida</h5>
                        <h6 class="text-success">I. Actividad Física</h6>
                        <div class="row mb-4">
                            <div class="col-md-12 mb-2">
                                <label class="me-3 fw-bold">Realiza ejercicio:</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="realiza_ejercicio" value="Si"
                                        <?= ($datos['realiza_ejercicio'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                    <label class="form-check-label">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="realiza_ejercicio" value="No"
                                        <?= ($datos['realiza_ejercicio'] ?? '') == 'No' ? 'checked' : '' ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Frecuencia</label>
                                <input type="text" class="form-control" name="ejercicio_frecuencia"
                                    placeholder="0 veces | 1-2 veces | 3-4 veces | 5+"
                                    value="<?= htmlspecialchars($datos['ejercicio_frecuencia'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Tipo de ejercicio</label>
                                <input type="text" class="form-control" name="ejercicio_tipo"
                                    placeholder="Aeróbico | Pesas/Fuerza | Combinado"
                                    value="<?= htmlspecialchars($datos['ejercicio_tipo'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Duración promedio (minutos)</label>
                                <input type="text" class="form-control" name="ejercicio_duracion"
                                    placeholder="0-30 | 30-60 | 60+"
                                    value="<?= htmlspecialchars($datos['ejercicio_duracion'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label">Días de Descanso</label>
                                <input type="text" class="form-control" name="dias_descanso"
                                    placeholder="1-2 | 2-3 | +3"
                                    value="<?= htmlspecialchars($datos['dias_descanso'] ?? '') ?>">
                            </div>
                        </div>

                        <h6 class="text-success">II. Hábitos</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-bold d-block">Tabaquismo</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tabaquismo" value="Si"
                                        <?= ($datos['tabaquismo'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                    <label class="form-check-label">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tabaquismo" value="No"
                                        <?= ($datos['tabaquismo'] ?? '') == 'No' ? 'checked' : '' ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold d-block">Alcoholismo</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="alcoholismo" value="Si"
                                        <?= ($datos['alcoholismo'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                    <label class="form-check-label">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="alcoholismo" value="No"
                                        <?= ($datos['alcoholismo'] ?? '') == 'No' ? 'checked' : '' ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold d-block">Maneja el estrés adecuadamente</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="maneja_estres" value="Si"
                                        <?= ($datos['maneja_estres'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                    <label class="form-check-label">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="maneja_estres" value="No"
                                        <?= ($datos['maneja_estres'] ?? '') == 'No' ? 'checked' : '' ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold d-block">Duerme bien</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="duerme_bien" value="Si"
                                        <?= ($datos['duerme_bien'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                    <label class="form-check-label">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="duerme_bien" value="No"
                                        <?= ($datos['duerme_bien'] ?? '') == 'No' ? 'checked' : '' ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Horario de Sueño (Hrs)</label>
                                <input type="text" class="form-control" name="horas_sueno"
                                    placeholder="2 a 4 | 4 - 6 | +7"
                                    value="<?= htmlspecialchars($datos['horas_sueno'] ?? '') ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Comentarios adicionales sobre objetivos:</label>
                                <textarea class="form-control" name="comentarios_objetivos"
                                    rows="2"><?= htmlspecialchars($datos['comentarios_objetivos'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="button" class="btn btn-secondary me-2" onclick="saveForm()"><i class="bi bi-save"></i>
                        Guardar Progreso</button>
                    <button type="button" class="btn btn-primary" onclick="nextTab('dx')">Siguiente <i
                            class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            <!-- DIAGNÓSTICO Y TRATAMIENTO -->
            <div class="tab-pane fade" id="dx" role="tabpanel">
                <div class="card border-0 mb-4 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="section-title">I. Diagnóstico Nutricional</h5>
                        <div class="row g-2">
                            <?php
                            $dx_nutri = [
                                'Ingesta excesiva de carbohidratos',
                                'Ingesta excesiva de sodio',
                                'Ingesta de proteinas inferior a la necesaria',
                                'Conocimientos deficientes relacionados con la alimentación y la nutrición',
                                'No cumplimiento de las recomendaciones nutricionales',
                                'Mejorar digestión'
                            ];
                            $vals = $datos['diagnostico_nutricional'] ?? [];
                            foreach ($dx_nutri as $opt):
                                ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="diagnostico_nutricional[]"
                                            value="<?= $opt ?>" <?= in_array($opt, $vals) ? 'checked' : '' ?>>
                                        <label class="form-check-label"><?= $opt ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="col-12 mt-2">
                                <input type="text" class="form-control" name="diagnostico_nutricional_otro"
                                    placeholder="Otro:"
                                    value="<?= htmlspecialchars($datos['diagnostico_nutricional_otro'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 mb-4 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="section-title">II. Tratamiento y Objetivos Nutricionales</h5>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Dieta</label>
                            <select class="form-select" name="tipo_dieta">
                                <option value="">Seleccione...</option>
                                <?php
                                $dietas = [
                                    'Dieta Normoproteica con Restricción de Carbohidratos Simples',
                                    'Dieta Normocalórica Proteica con conteo de HC',
                                    'Dieta Hiperproteica con Conteo de HC',
                                    'Dieta Hipoproteica con conteo de HC',
                                    'Normocalórica Hiperproteica'
                                ];
                                foreach ($dietas as $d) {
                                    $sel = ($datos['tipo_dieta'] ?? '') == $d ? 'selected' : '';
                                    echo "<option value='$d' $sel>$d</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <label class="form-label fw-bold">Objetivos</label>
                        <div class="row g-2 mb-3">
                            <?php
                            $objs = ['Reducción de masa corporal', 'Aumentar masa muscular', 'Mejorar estado Nutricional', 'Aumentar energía', 'Mejorar digestión'];
                            $vals = $datos['objetivos_tratamiento'] ?? [];
                            foreach ($objs as $opt):
                                ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="objetivos_tratamiento[]"
                                            value="<?= $opt ?>" <?= in_array($opt, $vals) ? 'checked' : '' ?>>
                                        <label class="form-check-label"><?= $opt ?></label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <div class="col-12">
                                <input type="text" class="form-control" name="objetivos_otro" placeholder="Otro:"
                                    value="<?= htmlspecialchars($datos['objetivos_otro'] ?? '') ?>">
                            </div>
                        </div>

                        <label class="form-label fw-bold">Recomendaciones Generales</label>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="rec_azucar" id="rec_azucar"
                                        <?= in_array('Evitar altos en azúcar', ($datos['recomendaciones_generales'] ?? [])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="rec_azucar">
                                        <strong>Evitar altos en azúcar:</strong> Refresco | Pan Dulce | Jugos (Todos) |
                                        Pastel | Mermeladas | Aguas de sabor | Tamales | Atoles
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="rec_ultra" id="rec_ultra"
                                        <?= in_array('Evitar Ultraprocesados', ($datos['recomendaciones_generales'] ?? [])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="rec_ultra">
                                        <strong>Evitar Ultraprocesados:</strong> Hamburguesas | Pizza | Hot dogs | Papas
                                        fritas | Nuggets
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="rec_fritas" id="rec_fritas"
                                        <?= in_array('Evitar preparaciones fritas/empanizadas', ($datos['recomendaciones_generales'] ?? [])) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="rec_fritas">
                                        <strong>Evitar preparaciones:</strong> Fritas | Empanizados | Capeados
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <input type="text" class="form-control" name="recomendaciones_otros"
                                    placeholder="Otros:"
                                    value="<?= htmlspecialchars($datos['recomendaciones_otros'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <a href="../../../index.php" class="btn btn-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar Consulta
                        Nutricional</button>
                </div>
            </div>

        </div>
    </form>
    <?php endif; ?>
</div>

<script>
    function nextTab(tabId) {
        var triggerEl = document.querySelector('#nutriTabs button[data-bs-target="#' + tabId + '"]');
        var tab = new bootstrap.Tab(triggerEl);
        tab.show();
        window.scrollTo(0, 0);
    }

    function saveForm() {
        document.getElementById('formNutricion').submit();
    }

    // Patient Search (filtrado en cliente, igual que Medicina Interna)
    const patientSearchInput = document.getElementById('patientSelectionInput');
    if (patientSearchInput) {
        patientSearchInput.addEventListener('input', function() {
            const search = this.value.toLowerCase().trim();
            const items = document.querySelectorAll('#patientSearchResults .patient-item');
            items.forEach(function(item) {
                const text = item.getAttribute('data-search') || '';
                if (text.indexOf(search) !== -1) {
                    item.classList.remove('d-none');
                } else {
                    item.classList.add('d-none');
                }
            });
        });
    }
</script>

<?php include '../../includes/footer.php'; ?>