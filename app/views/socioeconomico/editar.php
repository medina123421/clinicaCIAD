<?php
/**
 * Editor de Estudio Socioeconómico
 * Formulario completo basado en el formato proporcionado
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Paciente.php';
require_once '../../models/EstudioSocioeconomico.php';

$page_title = 'Estudio Socioeconómico';

// Verificar ID de paciente
if (!isset($_GET['id'])) {
    header('Location: ../pacientes/lista.php');
    exit();
}

$id_paciente = (int) $_GET['id'];
$database = new Database();
$db = $database->getConnection();

$paciente_model = new Paciente($db);
$estudio_model = new EstudioSocioeconomico($db);

$paciente = $paciente_model->obtenerPorId($id_paciente);
if (!$paciente) {
    header('Location: ../pacientes/lista.php?error=no_encontrado');
    exit();
}

// Obtener estudio existente o iniciar vacío
$estudio = $estudio_model->obtenerPorPaciente($id_paciente);
$is_edit = !empty($estudio);

// Procesar formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $datos = $_POST;
        $datos['id_paciente'] = $id_paciente;

        // Manejo de checkboxes y arrays
        $datos['diagnostico_diabetes'] = $_POST['diagnostico_diabetes'] ?? [];
        $datos['servicio_medico'] = $_POST['servicio_medico'] ?? [];
        $datos['tratamiento_actual'] = $_POST['tratamiento_actual'] ?? []; // Legacy or secondary

        // Nuevos campos de especificación
        $datos['diagnostico_desc_otro'] = $_POST['diagnostico_desc_otro'] ?? null;
        $datos['servicio_medico_otro'] = $_POST['servicio_medico_otro'] ?? null;
        $datos['tiene_tratamiento'] = $_POST['tiene_tratamiento'] ?? 0;
        $datos['tratamiento_detalle'] = $_POST['tratamiento_detalle'] ?? null;

        // Procesar matriz de alimentación (convertir a formato simple)
        $frecuencia_alimentos = [];
        $alimentos = ['carne_res', 'pollo', 'cerdo', 'pescado', 'leche', 'cereales', 'huevo', 'frutas', 'verduras', 'leguminosas'];
        foreach ($alimentos as $alimento) {
            $frecuencia_alimentos[$alimento] = $_POST['alim_' . $alimento] ?? '';
        }
        $datos['frecuencia_alimentos'] = $frecuencia_alimentos;

        // Procesar familiares (construir array desde los inputs paralelos)
        $familiares = [];
        if (isset($_POST['fam_nombre'])) {
            for ($i = 0; $i < count($_POST['fam_nombre']); $i++) {
                if (!empty($_POST['fam_nombre'][$i])) {
                    $familiares[] = [
                        'nombre' => $_POST['fam_nombre'][$i],
                        'parentesco' => $_POST['fam_parentesco'][$i],
                        'edad' => $_POST['fam_edad'][$i],
                        'ocupacion' => $_POST['fam_ocupacion'][$i],
                        'ingreso_mensual' => $_POST['fam_ingreso'][$i]
                    ];
                }
            }
        }
        $datos['familiares'] = $familiares;

        $id_estudio = $estudio_model->guardar($datos);

        $mensaje = 'Estudio socioeconómico guardado correctamente.';
        $tipo_mensaje = 'success';

        // Recargar datos
        $estudio = $estudio_model->obtenerPorPaciente($id_paciente);
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
        <h2><i class="bi bi-file-earmark-person"></i> Estudio Socioeconómico</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="../pacientes/lista.php">Pacientes</a></li>
                <li class="breadcrumb-item"><a href="../pacientes/detalle.php?id=<?= $id_paciente ?>">
                        <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?>
                    </a></li>
                <li class="breadcrumb-item active">Estudio Socioeconómico</li>
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

<form method="POST" action="" id="formEstudio">

    <!-- Navegación de Pestañas -->
    <ul class="nav nav-tabs mb-4" id="estudioTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general"
                type="button" role="tab"><i class="bi bi-person"></i> I. Datos Generales</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="familia-tab" data-bs-toggle="tab" data-bs-target="#familia" type="button"
                role="tab"><i class="bi bi-people"></i> II. Estructura Familiar</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="vivienda-tab" data-bs-toggle="tab" data-bs-target="#vivienda" type="button"
                role="tab"><i class="bi bi-house"></i> III. Vivienda</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="economia-tab" data-bs-toggle="tab" data-bs-target="#economia" type="button"
                role="tab"><i class="bi bi-currency-dollar"></i> IV. Economía</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="salud-tab" data-bs-toggle="tab" data-bs-target="#salud" type="button"
                role="tab"><i class="bi bi-activity"></i> V. Salud</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="alimentacion-tab" data-bs-toggle="tab" data-bs-target="#alimentacion"
                type="button" role="tab"><i class="bi bi-egg-fried"></i> VI. Alimentación</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="conclusiones-tab" data-bs-toggle="tab" data-bs-target="#conclusiones"
                type="button" role="tab"><i class="bi bi-clipboard-check"></i> Conclusiones</button>
        </li>
    </ul>

    <div class="tab-content" id="myTabContent">

        <!-- I. DATOS GENERALES -->
        <div class="tab-pane fade show active" id="general" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Datos Generales de Identificación</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control"
                                value="<?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno'] . ' ' . $paciente['apellido_materno']) ?>"
                                disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Edad</label>
                            <input type="text" class="form-control" value="<?= $paciente['edad'] ?> años" disabled>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input type="text" class="form-control" value="<?= $paciente['fecha_nacimiento'] ?>"
                                disabled>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Escolaridad Máxima</label>
                            <input type="text" class="form-control" name="escolaridad"
                                value="<?= htmlspecialchars($estudio['escolaridad'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado Civil</label>
                            <select class="form-select" name="estado_civil">
                                <option value="">Seleccione...</option>
                                <?php
                                $opts = ['Soltero', 'Casado', 'Divorciado', 'Viudo', 'Union Libre'];
                                foreach ($opts as $opt) {
                                    $sel = ($estudio['estado_civil'] ?? '') == $opt ? 'selected' : '';
                                    echo "<option value='$opt' $sel>$opt</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ocupación Actual</label>
                            <input type="text" class="form-control" name="ocupacion"
                                value="<?= htmlspecialchars($estudio['ocupacion'] ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Religión</label>
                            <input type="text" class="form-control" name="religion"
                                value="<?= htmlspecialchars($estudio['religion'] ?? '') ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Tiempo de Residencia en Domicilio</label>
                            <input type="text" class="form-control" name="tiempo_residencia"
                                value="<?= htmlspecialchars($estudio['tiempo_residencia'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Domicilio Completo (del Perfil)</label>
                            <input type="text" class="form-control"
                                value="<?= htmlspecialchars($paciente['direccion'] . ', ' . $paciente['ciudad'] . ', ' . $paciente['estado']) ?>"
                                disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary" onclick="nextTab('familia')">Siguiente <i
                        class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <!-- II. ESTRUCTURA FAMILIAR -->
        <div class="tab-pane fade" id="familia" role="tabpanel">
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Dinámica Familiar</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="es_jefe_familia" id="es_jefe"
                                    <?= ($estudio['es_jefe_familia'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="es_jefe">
                                    El paciente es Jefe(a) de Familia
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Relaciones Familiares</label>
                            <select class="form-select" name="relaciones_familiares">
                                <option value="">Seleccione...</option>
                                <?php
                                $opts = ['Armónicas', 'Conflictivas', 'Aisladas'];
                                foreach ($opts as $opt) {
                                    $sel = ($estudio['relaciones_familiares'] ?? '') == $opt ? 'selected' : '';
                                    echo "<option value='$opt' $sel>$opt</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Apoyo Familiar</label>
                            <select class="form-select" name="apoyo_familiar">
                                <option value="">Seleccione...</option>
                                <?php
                                $opts = ['Muy Alto', 'Medio', 'Bajo', 'Nulo'];
                                foreach ($opts as $opt) {
                                    $sel = ($estudio['apoyo_familiar'] ?? '') == $opt ? 'selected' : '';
                                    echo "<option value='$opt' $sel>$opt</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-people-fill"></i> Composición Familiar (Viven en el mismo hogar)</span>
                    <button type="button" class="btn btn-sm btn-success" onclick="agregarFamiliar()">
                        <i class="bi bi-plus-circle"></i> Agregar
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" id="tablaFamiliares">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Parentesco</th>
                                    <th style="width: 80px;">Edad</th>
                                    <th>Ocupación</th>
                                    <th>Ingreso Mensual</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $familiares = $estudio['familiares'] ?? [];
                                // Siempre mostrar al menos 3 filas vacías si no hay datos
                                $rows = max(count($familiares), 3);
                                for ($i = 0; $i < $rows; $i++):
                                    $fam = $familiares[$i] ?? [];
                                    ?>
                                    <tr>
                                        <td><input type="text" name="fam_nombre[]" class="form-control form-control-sm"
                                                value="<?= htmlspecialchars($fam['nombre'] ?? '') ?>"></td>
                                        <td><input type="text" name="fam_parentesco[]" class="form-control form-control-sm"
                                                value="<?= htmlspecialchars($fam['parentesco'] ?? '') ?>"></td>
                                        <td><input type="number" name="fam_edad[]" class="form-control form-control-sm"
                                                value="<?= htmlspecialchars($fam['edad'] ?? '') ?>"></td>
                                        <td><input type="text" name="fam_ocupacion[]" class="form-control form-control-sm"
                                                value="<?= htmlspecialchars($fam['ocupacion'] ?? '') ?>"></td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" name="fam_ingreso[]"
                                                    class="form-control fam-ingreso" onchange="calcularTotales()"
                                                    value="<?= htmlspecialchars($fam['ingreso_mensual'] ?? '') ?>">
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm text-danger"
                                                onclick="eliminarFila(this)"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary" onclick="nextTab('vivienda')">Siguiente <i
                        class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <!-- III. VIVIENDA -->
        <div class="tab-pane fade" id="vivienda" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Condiciones de la Vivienda</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Vivienda</label>
                            <div class="d-flex gap-3">
                                <?php
                                $opts = ['Propia', 'Rentada', 'Prestada', 'Otra'];
                                foreach ($opts as $opt) {
                                    $chk = ($estudio['tipo_vivienda'] ?? '') == $opt ? 'checked' : '';
                                    echo "<div class='form-check'>
                                            <input class='form-check-input' type='radio' name='tipo_vivienda' id='viv_$opt' value='$opt' $chk>
                                            <label class='form-check-label' for='viv_$opt'>$opt</label>
                                          </div>";
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Habitaciones</label>
                            <input type="number" class="form-control" name="num_habitaciones"
                                value="<?= $estudio['num_habitaciones'] ?? '' ?>"
                                placeholder="Sin contar baño ni cocina">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Material Predominante</label>
                            <input type="text" class="form-control" name="material_vivienda"
                                value="<?= htmlspecialchars($estudio['material_vivienda'] ?? '') ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label mb-2">Servicios Básicos Disponibles</label>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="servicio_agua" id="svc_agua"
                                        <?= ($estudio['servicio_agua'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="svc_agua">Agua potable</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="servicio_drenaje"
                                        id="svc_drenaje" <?= ($estudio['servicio_drenaje'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="svc_drenaje">Drenaje</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="servicio_electricidad"
                                        id="svc_luz" <?= ($estudio['servicio_electricidad'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="svc_luz">Electricidad</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="servicio_gas" id="svc_gas"
                                        <?= ($estudio['servicio_gas'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="svc_gas">Gas</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="servicio_internet"
                                        id="svc_net" <?= ($estudio['servicio_internet'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="svc_net">Internet</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary" onclick="nextTab('economia')">Siguiente <i
                        class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <!-- IV. ECONOMÍA -->
        <div class="tab-pane fade" id="economia" role="tabpanel">
            <div class="row">
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <span class="fw-bold">Ingresos y Egresos Mensuales</span>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-success">Ingreso Familiar Total Aproximado</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control"
                                        name="ingreso_mensual_familiar" id="total_ingreso"
                                        value="<?= $estudio['ingreso_mensual_familiar'] ?? '' ?>">
                                </div>
                                <div class="form-text">Puede calcularse automáticamente sumando la tabla familiar.</div>
                            </div>

                            <hr>
                            <h6 class="mb-3">Egresos Principales</h6>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-6"><label>Renta/Hipoteca</label></div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control gasto-input"
                                            name="gasto_renta" value="<?= $estudio['gasto_renta'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-6"><label>Alimentos</label></div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control gasto-input"
                                            name="gasto_alimentos" value="<?= $estudio['gasto_alimentos'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-6"><label>Transporte</label></div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control gasto-input"
                                            name="gasto_transporte" value="<?= $estudio['gasto_transporte'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-6"><label>Servicios (Luz, agua...)</label></div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control gasto-input"
                                            name="gasto_servicios" value="<?= $estudio['gasto_servicios'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-6"><label>Medicamentos/Tratamientos</label></div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control gasto-input"
                                            name="gasto_tratamientos"
                                            value="<?= $estudio['gasto_tratamientos'] ?? '' ?>">
                                    </div>
                                </div>
                            </div>

                            <hr>
                            <div class="row g-2 align-items-center">
                                <div class="col-6"><label class="fw-bold">Gasto Total Estimado</label></div>
                                <div class="col-6">
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control fw-bold"
                                            name="gasto_total_estimado" id="total_gasto"
                                            value="<?= $estudio['gasto_total_estimado'] ?? '' ?>" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <span class="fw-bold">Salud Financiera</span>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <label class="form-label">¿Existe algún programa de apoyo social (pensiones,
                                    becas)?</label>
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0" type="checkbox" name="apoyo_social_check"
                                            <?= ($estudio['apoyo_social_check'] ?? 0) ? 'checked' : '' ?>> &nbsp; Sí
                                    </div>
                                    <input type="text" class="form-control" name="apoyo_social_nombre"
                                        placeholder="¿Cuál?"
                                        value="<?= htmlspecialchars($estudio['apoyo_social_nombre'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">¿El ingreso familiar cubre las necesidades básicas, incluyendo
                                    el tratamiento?</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="ingreso_cubre_necesidades"
                                            id="nec_si" value="1" <?= ($estudio['ingreso_cubre_necesidades'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="nec_si">Sí</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="ingreso_cubre_necesidades"
                                            id="nec_no" value="0" <?= !($estudio['ingreso_cubre_necesidades'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="nec_no">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary" onclick="nextTab('salud')">Siguiente <i
                        class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <!-- V. SALUD -->
        <div class="tab-pane fade" id="salud" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Información Específica (Diabetes)</h5>

                    <div class="mb-3">
                        <label class="form-label">Diagnóstico de Diabetes (Seleccione máximo 2):</label>
                        <div class="d-flex flex-wrap gap-3" id="diabetesChecklist">
                            <?php
                            $diabetes_opts = [
                                'DM Tipo1',
                                'DM Tipo2',
                                'DM Gestacional',
                                'Prediabetes',
                                'Obesidad',
                                'Desnutrición',
                                'Otros tipos de diabetes'
                            ];
                            $diagnostico_vals = $estudio['diagnostico_desc'] ?? [];
                            if (!is_array($diagnostico_vals)) {
                                // Compatibilidad con datos anteriores si existen como texto
                                $diagnostico_vals = $estudio['diagnostico_desc'] ? [$estudio['diagnostico_desc']] : [];
                            }

                            foreach ($diabetes_opts as $opt) {
                                $chk = in_array($opt, $diagnostico_vals) ? 'checked' : '';
                                echo "<div class='form-check'>
                                        <input class='form-check-input diabetes-check' type='checkbox' name='diagnostico_diabetes[]' value='$opt' $chk>
                                        <label class='form-check-label'>$opt</label>
                                      </div>";
                            }
                            ?>
                        </div>
                        <div class="mt-2" id="otro_diabetes_div"
                            style="display: <?= in_array('Otros tipos de diabetes', $diagnostico_vals) ? 'block' : 'none' ?>;">
                            <label class="form-label">Especifique otro tipo:</label>
                            <input type="text" class="form-control" name="diagnostico_desc_otro"
                                id="diagnostico_desc_otro"
                                value="<?= htmlspecialchars($estudio['diagnostico_desc_otro'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Servicio Médico al que tiene acceso:</label>
                        <div class="d-flex flex-wrap gap-3">
                            <?php
                            $opts = ['IMSS', 'ISSSTE', 'IMSS Bienestar', 'Sector Privado', 'Otros', 'No cuenta con servicio'];
                            $vals = $estudio['servicio_medico'] ?? [];
                            foreach ($opts as $opt) {
                                $chk = in_array($opt, $vals) ? 'checked' : '';
                                echo "<div class='form-check'>
                                        <input class='form-check-input svc-medico-check' type='checkbox' name='servicio_medico[]' value='$opt' $chk>
                                        <label class='form-check-label'>$opt</label>
                                      </div>";
                            }
                            ?>
                        </div>
                        <div class="mt-2" id="otro_servicio_div"
                            style="display: <?= in_array('Otros', $vals) ? 'block' : 'none' ?>;">
                            <label class="form-label">Especifique otro servicio:</label>
                            <input type="text" class="form-control" name="servicio_medico_otro"
                                id="servicio_medico_otro"
                                value="<?= htmlspecialchars($estudio['servicio_medico_otro'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tratamiento Actual (Medicamento):</label>
                        <div class="d-flex gap-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tiene_tratamiento" id="trata_si"
                                    value="1" <?= ($estudio['tiene_tratamiento'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="trata_si">Sí</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tiene_tratamiento" id="trata_no"
                                    value="0" <?= !($estudio['tiene_tratamiento'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="trata_no">No</label>
                            </div>
                        </div>
                        <div id="detalle_tratamiento_div"
                            style="display: <?= ($estudio['tiene_tratamiento'] ?? 0) ? 'block' : 'none' ?>;">
                            <label class="form-label">Especifique el tratamiento:</label>
                            <textarea class="form-control" name="tratamiento_detalle" rows="2"
                                placeholder="Escriba el nombre del medicamento y dosis..."><?= htmlspecialchars($estudio['tratamiento_detalle'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">¿Cubre los costos del medicamento con dificultad?</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cubre_costos_medicamento"
                                        value="1" <?= ($estudio['cubre_costos_medicamento'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cubre_costos_medicamento"
                                        value="0" <?= !($estudio['cubre_costos_medicamento'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">¿Cuenta con glucómetro y tiras suficientes?</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cuenta_con_glucometro" value="1"
                                        <?= ($estudio['cuenta_con_glucometro'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="cuenta_con_glucometro" value="0"
                                        <?= !($estudio['cuenta_con_glucometro'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">¿Suele tener dificultades para seguir la dieta recomendada por
                                motivos económicos o culturales?</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="dificultad_dieta_economica"
                                        value="1" <?= ($estudio['dificultad_dieta_economica'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="dificultad_dieta_economica"
                                        value="0" <?= !($estudio['dificultad_dieta_economica'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary" onclick="nextTab('alimentacion')">Siguiente <i
                        class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <!-- VI. ALIMENTACIÓN -->
        <div class="tab-pane fade" id="alimentacion" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Frecuencia de Consumo de Alimentos</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Tipo de Alimento</th>
                                    <th class="text-center">Diario</th>
                                    <th class="text-center">Cada 3er día</th>
                                    <th class="text-center">1 vez/semana</th>
                                    <th class="text-center">1 vez/mes</th>
                                    <th class="text-center">Ocasionalmente</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $alimentos_labels = [
                                    'carne_res' => 'Carne de res',
                                    'pollo' => 'Carne de pollo',
                                    'cerdo' => 'Carne de cerdo',
                                    'pescado' => 'Carne de pescado',
                                    'leche' => 'Leche / Lácteos',
                                    'cereales' => 'Cereales',
                                    'huevo' => 'Huevo',
                                    'frutas' => 'Frutas',
                                    'verduras' => 'Verduras',
                                    'leguminosas' => 'Leguminosas'
                                ];
                                $frecuencias = ['Diario', 'Cada 3er dia', '1 vez sem', '1 vez mes', 'Ocasional'];
                                $values = $estudio['frecuencia_alimentos'] ?? [];

                                foreach ($alimentos_labels as $key => $label) {
                                    echo "<tr>
                                            <td class='fw-bold'>$label</td>";
                                    foreach ($frecuencias as $freq) {
                                        $chk = ($values[$key] ?? '') == $freq ? 'checked' : '';
                                        echo "<td class='text-center'>
                                                <input class='form-check-input' type='radio' name='alim_$key' value='$freq' $chk>
                                              </td>";
                                    }
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="button" class="btn btn-primary" onclick="nextTab('conclusiones')">Siguiente <i
                        class="bi bi-arrow-right"></i></button>
            </div>
        </div>

        <!-- VII. CONCLUSIONES -->
        <div class="tab-pane fade" id="conclusiones" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Dictamen del Trabajador Social</h5>

                    <div class="mb-3">
                        <label class="form-label">Observaciones Generales (Actitud, entorno, necesidad de
                            intervención)</label>
                        <textarea class="form-control" name="observaciones_trabajo_social"
                            rows="3"><?= htmlspecialchars($estudio['observaciones_trabajo_social'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nivel Socioeconómico (Clasificación):</label>
                        <select class="form-select" name="nivel_socioeconomico">
                            <option value="">Seleccione...</option>
                            <?php
                            $opts = ['Alto', 'Medio', 'Bajo', 'Vulnerabilidad Extrema'];
                            foreach ($opts as $opt) {
                                $sel = ($estudio['nivel_socioeconomico'] ?? '') == $opt ? 'selected' : '';
                                echo "<option value='$opt' $sel>$opt</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Plan de Intervención / Recomendaciones</label>
                        <textarea class="form-control" name="plan_intervencion"
                            rows="3"><?= htmlspecialchars($estudio['plan_intervencion'] ?? '') ?></textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Nombre y firma del entrevistado</label>
                            <input type="text" class="form-control" name="nombre_entrevistado"
                                value="<?= htmlspecialchars($estudio['nombre_entrevistado'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre del Trabajador Social</label>
                            <input type="text" class="form-control" name="nombre_trabajador_social"
                                value="<?= htmlspecialchars($estudio['nombre_trabajador_social'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 mb-5 text-center">
                <button type="submit" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-save"></i> Guardar Estudio
                </button>
            </div>
        </div>

    </div>
</form>

<script>
    function nextTab(tabId) {
        const triggerEl = document.querySelector('#estudioTabs button[data-bs-target="#' + tabId + '"]');
        const tab = new bootstrap.Tab(triggerEl);
        tab.show();
        window.scrollTo(0, 0);
    }

    function agregarFamiliar() {
        const tbody = document.querySelector('#tablaFamiliares tbody');
        const row = `
        <tr>
            <td><input type="text" name="fam_nombre[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="fam_parentesco[]" class="form-control form-control-sm"></td>
            <td><input type="number" name="fam_edad[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="fam_ocupacion[]" class="form-control form-control-sm"></td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="fam_ingreso[]" class="form-control fam-ingreso" onchange="calcularTotales()">
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm text-danger" onclick="eliminarFila(this)"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    `;
        tbody.insertAdjacentHTML('beforeend', row);
    }

    function eliminarFila(btn) {
        const row = btn.closest('tr');
        row.remove();
        calcularTotales();
    }

    function calcularTotales() {
        // Ingreso familiar
        let totalIngreso = 0;
        document.querySelectorAll('.fam-ingreso').forEach(input => {
            totalIngreso += parseFloat(input.value) || 0;
        });
        document.getElementById('total_ingreso').value = totalIngreso.toFixed(2);

        // Gasto total
        let totalGasto = 0;
        document.querySelectorAll('.gasto-input').forEach(input => {
            totalGasto += parseFloat(input.value) || 0;
        });
        document.getElementById('total_gasto').value = totalGasto.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar cálculos
        calcularTotales();
        document.querySelectorAll('.gasto-input').forEach(input => {
            input.addEventListener('input', calcularTotales);
        });

        // Límite de selección en Diagnóstico de Diabetes
        const diabetesChecks = document.querySelectorAll('.diabetes-check');
        diabetesChecks.forEach(check => {
            check.addEventListener('change', function () {
                const checkedCount = document.querySelectorAll('.diabetes-check:checked').length;
                if (checkedCount > 2) {
                    this.checked = false;
                    alert('Puede seleccionar un máximo de 2 opciones de diagnóstico.');
                }

                // Mostrar/Ocultar "Otro"
                if (this.value === 'Otros tipos de diabetes') {
                    document.getElementById('otro_diabetes_div').style.display = this.checked ? 'block' : 'none';
                }
            });
        });

        // Mostrar/Ocultar "Otro" servicio médico
        document.querySelectorAll('.svc-medico-check').forEach(check => {
            check.addEventListener('change', function () {
                if (this.value === 'Otros') {
                    document.getElementById('otro_servicio_div').style.display = this.checked ? 'block' : 'none';
                }
            });
        });

        // Mostrar/Ocultar tratamiento detalle
        document.querySelectorAll('input[name="tiene_tratamiento"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.getElementById('detalle_tratamiento_div').style.display = (this.value === '1') ? 'block' : 'none';
            });
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>