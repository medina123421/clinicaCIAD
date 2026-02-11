<?php
/**
 * Registro Completo de Análisis de Laboratorio
 * Incluye: Biometría Hemática, Química Sanguínea, Examen General de Orina
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Analisis.php';
require_once '../../models/Paciente.php';

$page_title = 'Resultados de Laboratorio';

$database = new Database();
$db = $database->getConnection();
$analisis_model = new Analisis($db);
$paciente_model = new Paciente($db);

$mensaje = '';
$tipo_mensaje = '';

$pacientes = $paciente_model->obtenerTodos('', 2000);

// Pre-carga 
$id_paciente_pre = $_GET['paciente'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_paciente = $_POST['id_paciente'] ?? '';
    $fecha_analisis = $_POST['fecha_analisis'] ?? date('Y-m-d');

    if (empty($id_paciente)) {
        $mensaje = 'Debe seleccionar un paciente.';
        $tipo_mensaje = 'danger';
    } else {
        // Buscar/Crear visita
        $id_visita = $analisis_model->obtenerOCrearVisita($id_paciente, $fecha_analisis, $_SESSION['usuario_id']);

        if ($id_visita) {
            $exito = true;
            $mensajes_detalle = [];

            $datos_comunes = [
                'id_visita' => $id_visita,
                'fecha_analisis' => $fecha_analisis,
                'observaciones' => $_POST['observaciones_generales'] ?? '',
                'created_by' => $_SESSION['usuario_id']
            ];

            // 1. Guardar Biometría (si se llenó algo clave, e.g. Eritrocitos o Leucocitos)
            if (!empty($_POST['eritrocitos']) || !empty($_POST['leucocitos'])) {
                $datos_bh = array_merge($datos_comunes, [
                    'eritrocitos' => $_POST['eritrocitos'],
                    'hemoglobina' => $_POST['hemoglobina'],
                    'hematocrito' => $_POST['hematocrito'],
                    'vgm' => $_POST['vgm'],
                    'hgm' => $_POST['hgm'],
                    'cmhg' => $_POST['cmhg'],
                    'ide' => $_POST['ide'],
                    'leucocitos' => $_POST['leucocitos'],
                    'neutrofilos_perc' => $_POST['neutrofilos_perc'],
                    'linfocitos_perc' => $_POST['linfocitos_perc'],
                    'mid_perc' => $_POST['mid_perc'],
                    'neutrofilos_abs' => $_POST['neutrofilos_abs'],
                    'linfocitos_abs' => $_POST['linfocitos_abs'],
                    'mid_abs' => $_POST['mid_abs'],
                    'plaquetas' => $_POST['plaquetas']
                ]);
                if (!$analisis_model->registrarBiometriaHematica($datos_bh)) {
                    $exito = false;
                    $mensajes_detalle[] = "Error al guardar Biometría Hemática.";
                } else {
                    $mensajes_detalle[] = "Biometría Hemática guardada.";
                }
            }

            // 2. Guardar Química Sanguínea
            if (!empty($_POST['glucosa']) || !empty($_POST['creatinina'])) {
                $datos_qs = array_merge($datos_comunes, [
                    'glucosa' => $_POST['glucosa'],
                    'urea' => $_POST['urea'],
                    'bun' => $_POST['bun'],
                    'creatinina' => $_POST['creatinina'],
                    'acido_urico' => $_POST['acido_urico'],
                    'colesterol' => $_POST['colesterol'],
                    'trigliceridos' => $_POST['trigliceridos']
                ]);
                if (!$analisis_model->registrarQuimicaSanguinea($datos_qs)) {
                    $exito = false;
                    $mensajes_detalle[] = "Error al guardar Química Sanguínea.";
                } else {
                    $mensajes_detalle[] = "Química Sanguínea guardada.";
                }
            }

            // 3. Guardar Examen de Orina
            if (!empty($_POST['color']) || !empty($_POST['densidad'])) {
                $datos_ego = array_merge($datos_comunes, [
                    'color' => $_POST['color'],
                    'aspecto' => $_POST['aspecto'],
                    'densidad' => $_POST['densidad'],
                    'ph' => $_POST['ph'],
                    'leucocitos_quimico' => $_POST['leucocitos_quimico'],
                    'nitritos' => $_POST['nitritos'],
                    'proteinas' => $_POST['proteinas'],
                    'glucosa_quimico' => $_POST['glucosa_quimico'],
                    'sangre_quimico' => $_POST['sangre_quimico'],
                    'cetonas' => $_POST['cetonas'],
                    'urobilinogeno' => $_POST['urobilinogeno'],
                    'bilirrubina' => $_POST['bilirrubina'],
                    'celulas_escamosas' => $_POST['celulas_escamosas'],
                    'celulas_cilindricas' => $_POST['celulas_cilindricas'],
                    'celulas_urotelio' => $_POST['celulas_urotelio'],
                    'celulas_renales' => $_POST['celulas_renales'],
                    'leucocitos_micro' => $_POST['leucocitos_micro'],
                    'cilindros' => $_POST['cilindros'],
                    'eritrocitos_micro' => $_POST['eritrocitos_micro'],
                    'dismorficos' => $_POST['dismorficos'],
                    'bacterias' => $_POST['bacterias'],
                    'hongos' => $_POST['hongos'],
                    'parasitos' => $_POST['parasitos']
                ]);
                if (!$analisis_model->registrarExamenOrina($datos_ego)) {
                    $exito = false;
                    $mensajes_detalle[] = "Error al guardar Examen de Orina.";
                } else {
                    $mensajes_detalle[] = "Examen de Orina guardado.";
                }
            }

            // 4. Guardar Perfil Hepático
            if (!empty($_POST['alt_gpt']) || !empty($_POST['bilirrubina_total'])) {
                $datos_ph = array_merge($datos_comunes, [
                    'bilirrubina_total' => $_POST['bilirrubina_total'],
                    'bilirrubina_directa' => $_POST['bilirrubina_directa'],
                    'bilirrubina_indirecta' => $_POST['bilirrubina_indirecta'],
                    'alt_gpt' => $_POST['alt_gpt'],
                    'ast_got' => $_POST['ast_got'],
                    'fosfatasa_alcalina' => $_POST['fosfatasa_alcalina'],
                    'ggt' => $_POST['ggt'],
                    'proteinas_totales' => $_POST['proteinas_totales'],
                    'albumina' => $_POST['albumina'],
                    'globulina' => $_POST['globulina']
                ]);
                if (!$analisis_model->registrarPerfilHepatico($datos_ph)) {
                    $exito = false;
                    $mensajes_detalle[] = "Error al guardar Perfil Hepático.";
                } else {
                    $mensajes_detalle[] = "Perfil Hepático guardado.";
                }
            }

            // 5. Guardar Perfil Tiroideo
            if (!empty($_POST['tsh']) || !empty($_POST['t4_libre'])) {
                $datos_pt = array_merge($datos_comunes, [
                    't3_total' => $_POST['t3_total'],
                    't3_libre' => $_POST['t3_libre'],
                    't4_total' => $_POST['t4_total'],
                    't4_libre' => $_POST['t4_libre'],
                    'tsh' => $_POST['tsh']
                ]);
                if (!$analisis_model->registrarPerfilTiroideo($datos_pt)) {
                    $exito = false;
                    $mensajes_detalle[] = "Error al guardar Perfil Tiroideo.";
                } else {
                    $mensajes_detalle[] = "Perfil Tiroideo guardado.";
                }
            }

            // 6. Guardar Insulina
            if (!empty($_POST['insulina_basal'])) {
                $datos_in = array_merge($datos_comunes, [
                    'insulina_basal' => $_POST['insulina_basal']
                ]);
                if (!$analisis_model->registrarInsulina($datos_in)) {
                    $exito = false;
                    $mensajes_detalle[] = "Error al guardar Insulina.";
                } else {
                    $mensajes_detalle[] = "Insulina guardada.";
                }
            }

            if ($exito && count($mensajes_detalle) > 0) {
                $mensaje = 'Resultados registrados exitosamente. (' . implode(' ', $mensajes_detalle) . ')';
                $tipo_mensaje = 'success';
            } elseif (count($mensajes_detalle) === 0) {
                $mensaje = 'No se registraron datos. Llene al menos una sección.';
                $tipo_mensaje = 'warning';
            } else {
                $mensaje = 'Hubo errores al guardar algunos datos.';
                $tipo_mensaje = 'danger';
            }

        } else {
            $mensaje = 'Error al vincular con visita médica.';
            $tipo_mensaje = 'danger';
        }
    }
}

include '../../includes/header.php';
?>

<div class="container-fluid mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-file-medical"></i> Registro de Resultados de Laboratorio</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Resultados Completos</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show">
            <?= htmlspecialchars($mensaje) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <!-- Cabecera Común -->
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">Datos del Paciente</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Buscar Paciente</label>
                        <input class="form-control" list="datalistOptions" id="busqueda_paciente" required>
                        <datalist id="datalistOptions">
                            <?php foreach ($pacientes as $paciente): ?>
                                <option data-id="<?= $paciente['id_paciente'] ?>"
                                    value="<?= htmlspecialchars($paciente['numero_expediente'] . ' - ' . $paciente['nombre_completo']) ?>">
                                <?php endforeach; ?>
                        </datalist>
                        <input type="hidden" name="id_paciente" id="id_paciente" value="<?= $id_paciente_pre ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Informe</label>
                        <input type="date" class="form-control" name="fecha_analisis" value="<?= date('Y-m-d') ?>"
                            required>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs de Navegación -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="bh-tab" data-bs-toggle="tab" data-bs-target="#bh" type="button"
                    role="tab">Biometría Hemática</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="qs-tab" data-bs-toggle="tab" data-bs-target="#qs" type="button"
                    role="tab">Química Sanguínea</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="ego-tab" data-bs-toggle="tab" data-bs-target="#ego" type="button"
                    role="tab">Examen General de Orina</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="hepatico-tab" data-bs-toggle="tab" data-bs-target="#hepatico" type="button"
                    role="tab">Perfil Hepático</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tiroideo-tab" data-bs-toggle="tab" data-bs-target="#tiroideo" type="button"
                    role="tab">Perfil Tiroideo</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="insulina-tab" data-bs-toggle="tab" data-bs-target="#insulina" type="button"
                    role="tab">Insulina</button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 p-3 bg-white" id="myTabContent">

            <!-- BIOMETRÍA HEMÁTICA -->
            <div class="tab-pane fade show active" id="bh" role="tabpanel">
                <h5 class="mt-2 text-secondary border-bottom pb-2">Serie Roja</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Eritrocitos (10⁶/µL)</label>
                        <input type="number" step="0.01" class="form-control" name="eritrocitos">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hemoglobina (g/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="hemoglobina">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Hematocrito (%)</label>
                        <input type="number" step="0.01" class="form-control" name="hematocrito">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">V.G.M (fL)</label>
                        <input type="number" step="0.01" class="form-control" name="vgm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">H.G.M (pg)</label>
                        <input type="number" step="0.01" class="form-control" name="hgm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">C.M.H.G (g/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="cmhg">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">IDE / RDW (%)</label>
                        <input type="number" step="0.01" class="form-control" name="ide">
                    </div>
                </div>

                <h5 class="mt-4 text-secondary border-bottom pb-2">Serie Blanca</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Leucocitos (10³/µL)</label>
                        <input type="number" step="0.01" class="form-control" name="leucocitos">
                    </div>
                    <!-- Porcentajes -->
                    <div class="col-md-3">
                        <label class="form-label">Neutrófilos %</label>
                        <input type="number" step="0.01" class="form-control" name="neutrofilos_perc">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Linfocitos %</label>
                        <input type="number" step="0.01" class="form-control" name="linfocitos_perc">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">MID % (Monocitos/Eos)</label>
                        <input type="number" step="0.01" class="form-control" name="mid_perc">
                    </div>
                    <!-- Absolutos -->
                    <div class="col-md-3">
                        <label class="form-label">Neutrófilos #</label>
                        <input type="number" step="0.01" class="form-control" name="neutrofilos_abs">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Linfocitos #</label>
                        <input type="number" step="0.01" class="form-control" name="linfocitos_abs">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">MID #</label>
                        <input type="number" step="0.01" class="form-control" name="mid_abs">
                    </div>
                </div>

                <h5 class="mt-4 text-secondary border-bottom pb-2">Plaquetas</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Recuento Plaquetas (10³/µL)</label>
                        <input type="number" step="0.01" class="form-control" name="plaquetas">
                    </div>
                </div>
            </div>

            <!-- QUÍMICA SANGUÍNEA -->
            <div class="tab-pane fade" id="qs" role="tabpanel">
                <h5 class="mt-2 text-secondary border-bottom pb-2">Química Sanguínea (6 Elementos)</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Glucosa (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="glucosa">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Urea (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="urea">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">BUN (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="bun">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Creatinina (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="creatinina">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ácido Úrico (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="acido_urico">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Colesterol Total (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="colesterol">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Triglicéridos (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="trigliceridos">
                    </div>
                </div>
            </div>

            <!-- EXAMEN GENERAL DE ORINA -->
            <div class="tab-pane fade" id="ego" role="tabpanel">
                <div class="row">
                    <div class="col-md-4 border-end">
                        <h5 class="text-secondary">Examen Físico</h5>
                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" class="form-control" name="color" placeholder="Ej. Amarillo">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Aspecto</label>
                            <input type="text" class="form-control" name="aspecto" placeholder="Ej. Ligero turbio">
                        </div>

                        <h5 class="text-secondary mt-4">Examen Químico</h5>
                        <div class="row g-2">
                            <div class="col-6"><label class="small">Densidad</label><input type="text"
                                    class="form-control form-control-sm" name="densidad"></div>
                            <div class="col-6"><label class="small">pH</label><input type="number" step="0.1"
                                    class="form-control form-control-sm" name="ph"></div>
                            <div class="col-6"><label class="small">Leucocitos</label><input type="text"
                                    class="form-control form-control-sm" name="leucocitos_quimico"></div>
                            <div class="col-6"><label class="small">Nitritos</label><input type="text"
                                    class="form-control form-control-sm" name="nitritos"></div>
                            <div class="col-6"><label class="small">Proteínas</label><input type="text"
                                    class="form-control form-control-sm" name="proteinas"></div>
                            <div class="col-6"><label class="small">Glucosa</label><input type="text"
                                    class="form-control form-control-sm" name="glucosa_quimico"></div>
                            <div class="col-6"><label class="small">Sangre</label><input type="text"
                                    class="form-control form-control-sm" name="sangre_quimico"></div>
                            <div class="col-6"><label class="small">Cetonas</label><input type="text"
                                    class="form-control form-control-sm" name="cetonas"></div>
                            <div class="col-6"><label class="small">Urobilinógeno</label><input type="text"
                                    class="form-control form-control-sm" name="urobilinogeno"></div>
                            <div class="col-6"><label class="small">Bilirrubina</label><input type="text"
                                    class="form-control form-control-sm" name="bilirrubina"></div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <h5 class="text-secondary">Examen Microscópico (Sedimento)</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small">Céls. Escamosas</label>
                                <input type="text" class="form-control form-control-sm" name="celulas_escamosas">
                            </div>
                            <div class="col-md-4">
                                <label class="small">Céls. Cilíndricas</label>
                                <input type="text" class="form-control form-control-sm" name="celulas_cilindricas">
                            </div>
                            <div class="col-md-4">
                                <label class="small">Céls. Urotelio</label>
                                <input type="text" class="form-control form-control-sm" name="celulas_urotelio">
                            </div>
                            <div class="col-md-4">
                                <label class="small">Céls. Renales</label>
                                <input type="text" class="form-control form-control-sm" name="celulas_renales">
                            </div>
                            <div class="col-md-4">
                                <label class="small">Leucocitos</label>
                                <input type="text" class="form-control form-control-sm" name="leucocitos_micro"
                                    placeholder="x Campo">
                            </div>
                            <div class="col-md-4">
                                <label class="small">Cilindros</label>
                                <input type="text" class="form-control form-control-sm" name="cilindros">
                            </div>
                            <div class="col-md-4">
                                <label class="small">Eritrocitos</label>
                                <input type="text" class="form-control form-control-sm" name="eritrocitos_micro"
                                    placeholder="x Campo">
                            </div>
                            <div class="col-md-4">
                                <label class="small">Dismórficos</label>
                                <input type="text" class="form-control form-control-sm" name="dismorficos">
                            </div>
                            <div class="col-md-3">
                                <label class="small">Bacterias</label>
                                <input type="text" class="form-control form-control-sm" name="bacterias">
                            </div>
                            <div class="col-md-3">
                                <label class="small">Hongos</label>
                                <input type="text" class="form-control form-control-sm" name="hongos">
                            </div>
                            <div class="col-md-3">
                                <label class="small">Parásitos</label>
                                <input type="text" class="form-control form-control-sm" name="parasitos">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PERFIL HEPÁTICO -->
            <div class="tab-pane fade" id="hepatico" role="tabpanel">
                <h5 class="mt-2 text-secondary border-bottom pb-2">Perfil Hepático</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Bilirrubina Total (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="bilirrubina_total">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bilirrubina Directa (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="bilirrubina_directa">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Bilirrubina Indirecta (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="bilirrubina_indirecta">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ALT (TGP) (U/L)</label>
                        <input type="number" step="0.01" class="form-control" name="alt_gpt">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">AST (TGO) (U/L)</label>
                        <input type="number" step="0.01" class="form-control" name="ast_got">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fosfatasa Alcalina (U/L)</label>
                        <input type="number" step="0.01" class="form-control" name="fosfatasa_alcalina">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">GGT (U/L)</label>
                        <input type="number" step="0.01" class="form-control" name="ggt">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Proteínas Totales (g/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="proteinas_totales">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Albúmina (g/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="albumina">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Globulina (g/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="globulina">
                    </div>
                </div>
            </div>

            <!-- PERFIL TIROIDEO -->
            <div class="tab-pane fade" id="tiroideo" role="tabpanel">
                <h5 class="mt-2 text-secondary border-bottom pb-2">Perfil Tiroideo</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">T3 Total (ng/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="t3_total">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">T3 Libre (pg/mL)</label>
                        <input type="number" step="0.01" class="form-control" name="t3_libre">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">T4 Total (µg/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="t4_total">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">T4 Libre (ng/dL)</label>
                        <input type="number" step="0.01" class="form-control" name="t4_libre">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">TSH (µUI/mL)</label>
                        <input type="number" step="0.01" class="form-control" name="tsh">
                    </div>
                </div>
            </div>

            <!-- INSULINA -->
            <div class="tab-pane fade" id="insulina" role="tabpanel">
                <h5 class="mt-2 text-secondary border-bottom pb-2">Insulina</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Insulina Basal (µUI/mL)</label>
                        <input type="number" step="0.01" class="form-control" name="insulina_basal">
                    </div>
                </div>
            </div>

        </div>

        <div class="card mt-3">
            <div class="card-body">
                <label class="form-label">Observaciones Generales</label>
                <textarea class="form-control" name="observaciones_generales" rows="2"></textarea>
            </div>
        </div>

        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
            <button type="submit" class="btn btn-lg btn-success">
                <i class="bi bi-save"></i> Guardar Resultados
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('busqueda_paciente').addEventListener('c hange', function () {
        var val = this.value;
        var options = document.getElementById('datalistOptions').options;
        var idInput = document.getElementById('id_paciente');
        idInput.value = '';
        for (var i = 0; i < options.length; i++) {
            if (options[i].value === val) {
                idInput.value = options[i].getAttribute('data-id');
                break;
            }
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>