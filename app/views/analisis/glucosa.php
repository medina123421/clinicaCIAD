<?php
/**
 * Análisis de Glucosa
 * Formulario para registrar niveles de glucosa y HbA1c
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Analisis.php';
require_once '../../models/Paciente.php';

$page_title = 'Registro de Glucosa';

$database = new Database();
$db = $database->getConnection();
$analisis_model = new Analisis($db);
$paciente_model = new Paciente($db);

$mensaje = '';
$tipo_mensaje = '';

// Pre-carga de paciente
$id_paciente_preseleccionado = $_GET['paciente'] ?? '';
$paciente_preseleccionado = null;
if ($id_paciente_preseleccionado) {
    $paciente_preseleccionado = $paciente_model->obtenerPorId($id_paciente_preseleccionado);
}
$pacientes = $paciente_model->obtenerTodos('', 1000);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_paciente = $_POST['id_paciente'] ?? '';
    $fecha_analisis = $_POST['fecha_analisis'] ?? date('Y-m-d');

    if (empty($id_paciente)) {
        $mensaje = 'Debe seleccionar un paciente.';
        $tipo_mensaje = 'danger';
    } else {
        // 1. Obtener o crear visita
        $id_visita = $analisis_model->obtenerOCrearVisita($id_paciente, $fecha_analisis, $_SESSION['usuario_id']);

        if ($id_visita) {
            $datos = [
                'id_visita' => $id_visita,
                'fecha_analisis' => $fecha_analisis,
                'glucosa_ayunas' => !empty($_POST['glucosa_ayunas']) ? $_POST['glucosa_ayunas'] : null,
                'glucosa_postprandial_2h' => !empty($_POST['glucosa_postprandial_2h']) ? $_POST['glucosa_postprandial_2h'] : null,
                'hemoglobina_glicosilada' => !empty($_POST['hemoglobina_glicosilada']) ? $_POST['hemoglobina_glicosilada'] : null,
                'interpretacion_glucosa_ayunas' => $_POST['interpretacion_glucosa_ayunas'] ?? null,
                'interpretacion_hba1c' => $_POST['interpretacion_hba1c'] ?? null,
                'observaciones' => trim($_POST['observaciones']),
                'created_by' => $_SESSION['usuario_id']
            ];

            if ($analisis_model->registrarGlucosa($datos)) {
                $mensaje = 'Análisis de glucosa registrado correctamente.';
                $tipo_mensaje = 'success';
                // Limpiar form o redirigir
            } else {
                $mensaje = 'Error al registrar el análisis.';
                $tipo_mensaje = 'danger';
            }
        } else {
            $mensaje = 'Error al vincular con una visita médica.';
            $tipo_mensaje = 'danger';
        }
    }
}

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="bi bi-droplet-half"></i> Registro de Glucosa y HbA1c</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Análisis Glucosa</li>
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

<form method="POST" action="">

    <!-- Selección de Paciente -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <i class="bi bi-person"></i> Paciente
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <label for="busqueda_paciente" class="form-label">Buscar Paciente</label>
                    <input class="form-control" list="datalistOptions" id="busqueda_paciente"
                        placeholder="Escriba nombre o expediente..."
                        value="<?= $paciente_preseleccionado ? htmlspecialchars($paciente_preseleccionado['nombre'] . ' ' . $paciente_preseleccionado['apellido_paterno']) : '' ?>"
                        <?= $paciente_preseleccionado ? 'readonly' : '' ?> autocomplete="off" required>
                    <datalist id="datalistOptions">
                        <?php foreach ($pacientes as $paciente): ?>
                            <option data-id="<?= $paciente['id_paciente'] ?>"
                                value="<?= htmlspecialchars($paciente['numero_expediente'] . ' - ' . $paciente['nombre_completo']) ?>">
                            <?php endforeach; ?>
                    </datalist>
                    <input type="hidden" name="id_paciente" id="id_paciente"
                        value="<?= $id_paciente_preseleccionado ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha del Análisis</label>
                    <input type="date" class="form-control" name="fecha_analisis" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
        </div>
    </div>

    <!-- Datos del Análisis -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <i class="bi bi-activity"></i> Resultados
        </div>
        <div class="card-body">
            <div class="row g-4">
                <!-- Columna Izquierda: Glucosa Ayunas -->
                <div class="col-md-6 border-end">
                    <h5 class="card-title text-primary">Glucosa en Ayunas</h5>
                    <div class="mb-3">
                        <label class="form-label">Valor (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" id="glucosa_ayunas" name="glucosa_ayunas"
                            placeholder="Ej. 95">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Interpretación Sugerida</label>
                        <select class="form-select" id="interpretacion_glucosa" name="interpretacion_glucosa_ayunas">
                            <option value="">Seleccione...</option>
                            <option value="Normal">Normal (70-100)</option>
                            <option value="Precaución">Precaución (100-125)</option>
                            <option value="Alerta">Alerta (>126)</option>
                        </select>
                    </div>
                </div>

                <!-- Columna Derecha: HbA1c -->
                <div class="col-md-6">
                    <h5 class="card-title text-primary">Hemoglobina Glicosilada (HbA1c)</h5>
                    <div class="mb-3">
                        <label class="form-label">Valor (%)</label>
                        <input type="number" step="0.01" class="form-control" id="hba1c" name="hemoglobina_glicosilada"
                            placeholder="Ej. 5.7">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Interpretación Sugerida</label>
                        <select class="form-select" id="interpretacion_hba1c" name="interpretacion_hba1c">
                            <option value="">Seleccione...</option>
                            <option value="Normal">Normal (< 5.7%)</option>
                            <option value="Precaución">Prediabetes (5.7% - 6.4%)</option>
                            <option value="Alerta">Diabetes (≥ 6.5%)</option>
                        </select>
                    </div>
                </div>

                <div class="col-12">
                    <hr>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Glucosa Postprandial 2h (mg/dL)</label>
                    <input type="number" step="0.01" class="form-control" name="glucosa_postprandial_2h">
                </div>

                <div class="col-md-8">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" name="observaciones" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
        <a href="../../../index.php" class="btn btn-secondary me-md-2">Cancelar</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar Análisis</button>
    </div>
</form>

<script>
    // Seleccionar ID paciente
    document.getElementById('busqueda_paciente').addEventListener('change', function () {
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

    // Auto-interpretación Glucosa
    document.getElementById('glucosa_ayunas').addEventListener('input', function () {
        var val = parseFloat(this.value);
        var select = document.getElementById('interpretacion_glucosa');
        if (!isNaN(val)) {
            if (val < 100) select.value = 'Normal';
            else if (val <= 125) select.value = 'Precaución';
            else select.value = 'Alerta';
        }
    });

    // Auto-interpretación HbA1c
    document.getElementById('hba1c').addEventListener('input', function () {
        var val = parseFloat(this.value);
        var select = document.getElementById('interpretacion_hba1c');
        if (!isNaN(val)) {
            if (val < 5.7) select.value = 'Normal';
            else if (val <= 6.4) select.value = 'Precaución';
            else select.value = 'Alerta';
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>