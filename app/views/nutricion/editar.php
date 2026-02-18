<?php
/**
 * Editor de Historia Clínica Nutricional
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Paciente.php';
require_once '../../models/Nutricion.php';

$page_title = 'Historia Clínica Nutricional';

// Verificar ID de paciente
if (!isset($_GET['id'])) {
    header('Location: ../pacientes/lista.php');
    exit();
}

$id_paciente = (int) $_GET['id'];
$database = new Database();
$db = $database->getConnection();

$paciente_model = new Paciente($db);
$nutricion_model = new Nutricion($db);

$paciente = $paciente_model->obtenerPorId($id_paciente);
if (!$paciente) {
    header('Location: ../pacientes/lista.php?error=no_encontrado');
    exit();
}

// Obtener historia existente
$historia = $nutricion_model->obtenerPorPaciente($id_paciente);
$is_edit = !empty($historia);

// Procesar formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $datos = $_POST;
        $datos['id_paciente'] = $id_paciente;

        // Procesar checkboxes y arrays JSON
        $json_fields = [
            'diagnosticos_medicos',
            'sintomas',
            'suplementos_detalle',
            'diagnostico_nutricional',
            'objetivos_tratamiento',
            'recomendaciones_generales' // Se enviará como array de claves (evitar_azucar, evitar_ultra, etc) o estructura custom
        ];

        foreach ($json_fields as $field) {
            $datos[$field] = $_POST[$field] ?? [];
        }
        
        // Procesar Recomendaciones Generales (Checkboxes manuales mapeados a JSON)
        $recomendaciones = [];
        if(isset($_POST['rec_azucar'])) $recomendaciones[] = 'Evitar altos en azúcar';
        if(isset($_POST['rec_ultra'])) $recomendaciones[] = 'Evitar Ultraprocesados';
        if(isset($_POST['rec_fritas'])) $recomendaciones[] = 'Evitar preparaciones fritas/empanizadas';
        $datos['recomendaciones_generales'] = $recomendaciones;

        // Procesar Frecuencia de Consumo (Map inputs to JSON structure)
        $frecuencia = [];
        $grupos_alimentos = [
            'verduras', 'frutas', 'cereales', 'leguminosas', 'carnes_rojas', 
            'pollo_pescado', 'lacteos', 'huevos', 'procesados', 
            'te', 'cafe', 'cafe_azucar', 'refresco', 'jugos', 'agua'
        ];
        foreach ($grupos_alimentos as $g) {
            $frecuencia[$g] = $_POST['freq_' . $g] ?? '';
        }
        $datos['frecuencia_consumo'] = $frecuencia;

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
        $datos['recordatorio_24h'] = $recordatorio;

        $id_historia = $nutricion_model->guardar($datos);

        $mensaje = 'Historia nutricional guardada correctamente.';
        $tipo_mensaje = 'success';
        
        // Recargar datos
        $historia = $nutricion_model->obtenerPorPaciente($id_paciente);
        $is_edit = true;

    } catch (Exception $e) {
        $mensaje = 'Error al guardar: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="bi bi-journal-medical"></i> Historia Clínica Nutricional</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="../pacientes/lista.php">Pacientes</a></li>
                <li class="breadcrumb-item"><a href="../pacientes/detalle.php?id=<?= $id_paciente ?>">
                        <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?>
                    </a></li>
                <li class="breadcrumb-item active">Nutrición</li>
            </ol>
        </nav>
    </div>
</div>

<?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($mensaje) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST" action="" id="formNutricion">

    <ul class="nav nav-tabs mb-4" id="nutriTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="antro-tab" data-bs-toggle="tab" data-bs-target="#antro" type="button"><i class="bi bi-rulers"></i> Antropometría</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="clinica-tab" data-bs-toggle="tab" data-bs-target="#clinica" type="button"><i class="bi bi-heart-pulse"></i> C. Clínica</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="dietetica-tab" data-bs-toggle="tab" data-bs-target="#dietetica" type="button"><i class="bi bi-egg-fried"></i> Dietética</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="estilo-tab" data-bs-toggle="tab" data-bs-target="#estilo" type="button"><i class="bi bi-bicycle"></i> Estilo de Vida</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="dx-tab" data-bs-toggle="tab" data-bs-target="#dx" type="button"><i class="bi bi-clipboard-check"></i> Diagnóstico y Tx</button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">

        <!-- ANTROPOMETRÍA -->
        <div class="tab-pane fade show active" id="antro" role="tabpanel">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">A. Evaluación Antropométrica</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" step="0.01" class="form-control" name="peso" value="<?= $historia['peso'] ?? '' ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Talla (cm)</label>
                            <input type="number" step="0.01" class="form-control" name="talla" value="<?= $historia['talla'] ?? '' ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Circunferencia Cintura (cm)</label>
                            <input type="number" step="0.01" class="form-control" name="circunferencia_cintura" value="<?= $historia['circunferencia_cintura'] ?? '' ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">IMC</label>
                            <input type="number" step="0.01" class="form-control" name="imc" value="<?= $historia['imc'] ?? '' ?>">
                        </div>
                        
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">% Grasa</label>
                            <input type="number" step="0.01" class="form-control" name="porcentaje_grasa" value="<?= $historia['porcentaje_grasa'] ?? '' ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Kg de Grasa</label>
                            <input type="number" step="0.01" class="form-control" name="kilos_grasa" value="<?= $historia['kilos_grasa'] ?? '' ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Índice Masa Muscular</label>
                            <input type="number" step="0.01" class="form-control" name="indice_masa_muscular" value="<?= $historia['indice_masa_muscular'] ?? '' ?>">
                        </div>
                        <div class="col-md-6 col-lg-3">
                            <label class="form-label">Kg de Masa Muscular</label>
                            <input type="number" step="0.01" class="form-control" name="kilos_masa_muscular" value="<?= $historia['kilos_masa_muscular'] ?? '' ?>">
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary" onclick="nextTab('clinica')">Siguiente <i class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <!-- CLÍNICA -->
        <div class="tab-pane fade" id="clinica" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">C. Evaluación Clínica</h5>
                </div>
                <div class="card-body">
                    <h6 class="text-primary mb-3">I. Diagnóstico Médico Actual</h6>
                    <div class="row g-2 mb-4">
                        <?php 
                        $dx_opts = [
                            'Diabetes Tipo 1', 'Diabetes Tipo 2', 'Pre-Diabetes', 'Hipertensión Arterial', 
                            'Obesidad', 'Dislipidemia (Colesterol/Triglicéridos)', 'Enfermedad Renal', 
                            'Enfermedad Hepática', 'Enfermedad Cardiovascular', 'Hipotiroidismo | Hipertiroidismo', 'Cáncer'
                        ];
                        $vals = $historia['diagnosticos_medicos'] ?? [];
                        foreach($dx_opts as $opt): 
                        ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="diagnosticos_medicos[]" value="<?= $opt ?>" <?= in_array($opt, $vals) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $opt ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="col-12 mt-2">
                            <label class="form-label">Especifique el diagnóstico y las alergias:</label>
                            <textarea class="form-control" name="diagnostico_especificar" rows="2"><?= htmlspecialchars($historia['diagnostico_especificar'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <h6 class="text-primary mb-3">II. Síntomas</h6>
                    <div class="row g-2 mb-4">
                        <?php 
                        $sintomas = ['Náuseas', 'Vómito', 'Diarrea', 'Estreñimiento', 'Distensión Abdominal', 'Acidez/Reflujo', 'Fatiga/Cansancio'];
                        $vals = $historia['sintomas'] ?? [];
                        foreach($sintomas as $opt): 
                        ?>
                        <div class="col-md-3 col-sm-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="sintomas[]" value="<?= $opt ?>" <?= in_array($opt, $vals) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $opt ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <h6 class="text-primary mb-3">III. Signos</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Temperatura (°C)</label>
                            <input type="number" step="0.1" class="form-control" name="temperatura" value="<?= $historia['temperatura'] ?? '' ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Presión Arterial (mmHg)</label>
                            <input type="text" class="form-control" name="presion_arterial" value="<?= htmlspecialchars($historia['presion_arterial'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                     <h5 class="mb-0">I. Medicamentos y Suplementos</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                             <label class="form-label fw-bold">¿Toma medicamentos actualmente?</label>
                             <div class="d-flex gap-3">
                                 <div class="form-check">
                                     <input class="form-check-input" type="radio" name="toma_medicamentos" value="Si" <?= ($historia['toma_medicamentos'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                     <label class="form-check-label">Sí</label>
                                 </div>
                                 <div class="form-check">
                                     <input class="form-check-input" type="radio" name="toma_medicamentos" value="No" <?= ($historia['toma_medicamentos'] ?? '') == 'No' ? 'checked' : '' ?>>
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
                                             <input class="form-check-input" type="radio" name="toma_suplementos" value="Si" <?= ($historia['toma_suplementos'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                             <label class="form-check-label">Sí</label>
                                         </div>
                                         <div class="form-check">
                                             <input class="form-check-input" type="radio" name="toma_suplementos" value="No" <?= ($historia['toma_suplementos'] ?? '') == 'No' ? 'checked' : '' ?>>
                                             <label class="form-check-label">No</label>
                                         </div>
                                    </div>
                                </div>
                                <div class="col-md-10">
                                    <div class="d-flex flex-wrap gap-3">
                                        <?php 
                                        $sups = ['Multivitamínico', 'Proteína Suero de leche', 'Proteína Vegana', 'Monohidrato de Creatina', 'Vit D', 'Omega 3'];
                                        $vals = $historia['suplementos_detalle'] ?? [];
                                        foreach($sups as $opt): 
                                        ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="suplementos_detalle[]" value="<?= $opt ?>" <?= in_array($opt, $vals) ? 'checked' : '' ?>>
                                            <label class="form-check-label"><?= $opt ?></label>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="mt-2">
                                        <input type="text" class="form-control form-control-sm" name="suplementos_otro" placeholder="Otros: Especificar" value="<?= htmlspecialchars($historia['suplementos_otro'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary" onclick="nextTab('dietetica')">Siguiente <i class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <!-- DIETÉTICA -->
        <div class="tab-pane fade" id="dietetica" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">D. Evaluación Dietética (Frecuencia de Consumo)</h5>
                </div>
                <div class="card-body">
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
                                $freq_data = $historia['frecuencia_consumo'] ?? [];
                                
                                function renderFreqRow($key, $label, $options, $current) {
                                    echo "<tr><td>$label</td><td><div class='d-flex flex-wrap gap-2'>";
                                    foreach($options as $opt) {
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
                                                <input class="form-check-input" type="radio" name="alergias_alimentarias" value="Si" <?= ($historia['alergias_alimentarias'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                                <label class="form-check-label">Sí</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="alergias_alimentarias" value="No" <?= ($historia['alergias_alimentarias'] ?? '') == 'No' ? 'checked' : '' ?>>
                                                <label class="form-check-label">No</label>
                                            </div>
                                            <input type="text" class="form-control form-control-sm ms-2" name="alergias_alimentarias_cual" placeholder="Cual: Especificar" value="<?= htmlspecialchars($historia['alergias_alimentarias_cual'] ?? '') ?>">
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recordatorio 24 Horas</h5>
                </div>
                <div class="card-body">
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
                                $rec = $historia['recordatorio_24h'] ?? [];
                                $comidas = [
                                    'desayuno' => 'Desayuno',
                                    'almuerzo' => 'Almuerzo',
                                    'comida' => 'Comida',
                                    'colacion' => 'Colación',
                                    'cena' => 'Cena'
                                ];
                                foreach($comidas as $key => $label):
                                    $data = $rec[$key] ?? [];
                                ?>
                                <tr>
                                    <td class="fw-bold"><?= $label ?></td>
                                    <td><input type="text" class="form-control form-control-sm" name="rec_<?= $key ?>_hora" value="<?= htmlspecialchars($data['hora'] ?? '') ?>"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="rec_<?= $key ?>_desc" value="<?= htmlspecialchars($data['desc'] ?? '') ?>"></td>
                                    <td><input type="number" class="form-control form-control-sm" name="rec_<?= $key ?>_hc" placeholder="1 | 2 | 3..." value="<?= htmlspecialchars($data['hc'] ?? '') ?>"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary" onclick="nextTab('estilo')">Siguiente <i class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <!-- ESTILO DE VIDA -->
        <div class="tab-pane fade" id="estilo" role="tabpanel">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">E. Evaluación de Estilo de Vida</h5></div>
                <div class="card-body">
                    <h6 class="text-primary">I. Actividad Física</h6>
                    <div class="row mb-4">
                        <div class="col-md-12 mb-2">
                             <label class="me-3 fw-bold">Realiza ejercicio:</label>
                             <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="realiza_ejercicio" value="Si" <?= ($historia['realiza_ejercicio'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                 <label class="form-check-label">Sí</label>
                             </div>
                             <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="realiza_ejercicio" value="No" <?= ($historia['realiza_ejercicio'] ?? '') == 'No' ? 'checked' : '' ?>>
                                 <label class="form-check-label">No</label>
                             </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Frecuencia</label>
                            <input type="text" class="form-control" name="ejercicio_frecuencia" placeholder="0 veces | 1-2 veces | 3-4 veces | 5+" value="<?= htmlspecialchars($historia['ejercicio_frecuencia'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Tipo de ejercicio</label>
                            <input type="text" class="form-control" name="ejercicio_tipo" placeholder="Aeróbico | Pesas/Fuerza | Combinado" value="<?= htmlspecialchars($historia['ejercicio_tipo'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Duración promedio (minutos)</label>
                            <input type="text" class="form-control" name="ejercicio_duracion" placeholder="0-30 | 30-60 | 60+" value="<?= htmlspecialchars($historia['ejercicio_duracion'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label">Días de Descanso</label>
                            <input type="text" class="form-control" name="dias_descanso" placeholder="1-2 | 2-3 | +3" value="<?= htmlspecialchars($historia['dias_descanso'] ?? '') ?>">
                        </div>
                    </div>

                    <h6 class="text-primary">II. Hábitos</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="fw-bold d-block">Tabaquismo</label>
                            <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="tabaquismo" value="Si" <?= ($historia['tabaquismo'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                 <label class="form-check-label">Sí</label>
                             </div>
                             <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="tabaquismo" value="No" <?= ($historia['tabaquismo'] ?? '') == 'No' ? 'checked' : '' ?>>
                                 <label class="form-check-label">No</label>
                             </div>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold d-block">Alcoholismo</label>
                            <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="alcoholismo" value="Si" <?= ($historia['alcoholismo'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                 <label class="form-check-label">Sí</label>
                             </div>
                             <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="alcoholismo" value="No" <?= ($historia['alcoholismo'] ?? '') == 'No' ? 'checked' : '' ?>>
                                 <label class="form-check-label">No</label>
                             </div>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold d-block">Maneja el estrés adecuadamente</label>
                             <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="maneja_estres" value="Si" <?= ($historia['maneja_estres'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                 <label class="form-check-label">Sí</label>
                             </div>
                             <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="maneja_estres" value="No" <?= ($historia['maneja_estres'] ?? '') == 'No' ? 'checked' : '' ?>>
                                 <label class="form-check-label">No</label>
                             </div>
                        </div>
                        <div class="col-md-6">
                             <label class="fw-bold d-block">Duerme bien</label>
                             <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="duerme_bien" value="Si" <?= ($historia['duerme_bien'] ?? '') == 'Si' ? 'checked' : '' ?>>
                                 <label class="form-check-label">Sí</label>
                             </div>
                             <div class="form-check form-check-inline">
                                 <input class="form-check-input" type="radio" name="duerme_bien" value="No" <?= ($historia['duerme_bien'] ?? '') == 'No' ? 'checked' : '' ?>>
                                 <label class="form-check-label">No</label>
                             </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Horario de Sueño (Hrs)</label>
                            <input type="text" class="form-control" name="horas_sueno" placeholder="2 a 4 | 4 - 6 | +7" value="<?= htmlspecialchars($historia['horas_sueno'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                             <label class="form-label">Comentarios adicionales sobre objetivos:</label>
                             <textarea class="form-control" name="comentarios_objetivos" rows="2"><?= htmlspecialchars($historia['comentarios_objetivos'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary" onclick="nextTab('dx')">Siguiente <i class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <!-- DIAGNÓSTICO Y TRATAMIENTO -->
        <div class="tab-pane fade" id="dx" role="tabpanel">
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">I. Diagnóstico Nutricional</h5></div>
                <div class="card-body">
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
                        $vals = $historia['diagnostico_nutricional'] ?? [];
                        foreach($dx_nutri as $opt):
                        ?>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="diagnostico_nutricional[]" value="<?= $opt ?>" <?= in_array($opt, $vals) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $opt ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="col-12 mt-2">
                            <input type="text" class="form-control" name="diagnostico_nutricional_otro" placeholder="Otro:" value="<?= htmlspecialchars($historia['diagnostico_nutricional_otro'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">II. Tratamiento y Objetivos Nutricionales</h5></div>
                <div class="card-body">
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
                            foreach($dietas as $d) {
                                $sel = ($historia['tipo_dieta'] ?? '') == $d ? 'selected' : '';
                                echo "<option value='$d' $sel>$d</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <label class="form-label fw-bold">Objetivos</label>
                    <div class="row g-2 mb-3">
                        <?php 
                        $objs = ['Reducción de masa corporal', 'Aumentar masa muscular', 'Mejorar estado Nutricional', 'Aumentar energía', 'Mejorar digestión'];
                        $vals = $historia['objetivos_tratamiento'] ?? [];
                        foreach($objs as $opt):
                        ?>
                        <div class="col-md-4">
                             <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="objetivos_tratamiento[]" value="<?= $opt ?>" <?= in_array($opt, $vals) ? 'checked' : '' ?>>
                                <label class="form-check-label"><?= $opt ?></label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="col-12">
                             <input type="text" class="form-control" name="objetivos_otro" placeholder="Otro:" value="<?= htmlspecialchars($historia['objetivos_otro'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <label class="form-label fw-bold">Recomendaciones Generales</label>
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="rec_azucar" id="rec_azucar" 
                                    <?= in_array('Evitar altos en azúcar', ($historia['recomendaciones_generales'] ?? [])) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="rec_azucar">
                                    <strong>Evitar altos en azúcar:</strong> Refresco | Pan Dulce | Jugos (Todos) | Pastel | Mermeladas | Aguas de sabor | Tamales | Atoles
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="rec_ultra" id="rec_ultra"
                                    <?= in_array('Evitar Ultraprocesados', ($historia['recomendaciones_generales'] ?? [])) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="rec_ultra">
                                    <strong>Evitar Ultraprocesados:</strong> Hamburguesas | Pizza | Hot dogs | Papas fritas | Nuggets
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="rec_fritas" id="rec_fritas"
                                    <?= in_array('Evitar preparaciones fritas/empanizadas', ($historia['recomendaciones_generales'] ?? [])) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="rec_fritas">
                                    <strong>Evitar preparaciones:</strong> Fritas | Empanizados | Capeados
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                             <input type="text" class="form-control" name="recomendaciones_otros" placeholder="Otros:" value="<?= htmlspecialchars($historia['recomendaciones_otros'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="../pacientes/detalle.php?id=<?= $id_paciente ?>" class="btn btn-secondary me-md-2">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar Historia Nutricional</button>
            </div>
        </div>

    </div>
</form>

<script>
    function nextTab(tabId) {
        var triggerEl = document.querySelector('#nutriTabs button[data-bs-target="#' + tabId + '"]');
        var tab = new bootstrap.Tab(triggerEl);
        tab.show();
        window.scrollTo(0, 0);
    }
</script>

<?php include '../../includes/footer.php'; ?>
