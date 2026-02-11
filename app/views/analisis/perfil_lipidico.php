<?php
/**
 * Perfil Lipídico
 * Formulario para registrar Colesterol, HDL, LDL, Triglicéridos
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Analisis.php';
require_once '../../models/Paciente.php';

$page_title = 'Perfil Lipídico';

$database = new Database();
$db = $database->getConnection();
$analisis_model = new Analisis($db);
$paciente_model = new Paciente($db);

$mensaje = '';
$tipo_mensaje = '';

$pacientes = $paciente_model->obtenerTodos('', 1000);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_paciente = $_POST['id_paciente'] ?? '';
    $fecha_analisis = $_POST['fecha_analisis'] ?? date('Y-m-d');

    if (empty($id_paciente)) {
        $mensaje = 'Debe seleccionar un paciente.';
        $tipo_mensaje = 'danger';
    } else {
        $id_visita = $analisis_model->obtenerOCrearVisita($id_paciente, $fecha_analisis, $_SESSION['usuario_id']);

        if ($id_visita) {
            $datos = [
                'id_visita' => $id_visita,
                'fecha_analisis' => $fecha_analisis,
                'colesterol_total' => !empty($_POST['colesterol']) ? $_POST['colesterol'] : null,
                'ldl' => !empty($_POST['ldl']) ? $_POST['ldl'] : null,
                'hdl' => !empty($_POST['hdl']) ? $_POST['hdl'] : null,
                'trigliceridos' => !empty($_POST['trigliceridos']) ? $_POST['trigliceridos'] : null,
                'interpretacion_colesterol' => $_POST['interpretacion_colesterol'] ?? null,
                'interpretacion_ldl' => $_POST['interpretacion_ldl'] ?? null,
                'interpretacion_hdl' => $_POST['interpretacion_hdl'] ?? null,
                'interpretacion_trigliceridos' => $_POST['interpretacion_trigliceridos'] ?? null,
                'observaciones' => trim($_POST['observaciones']),
                'created_by' => $_SESSION['usuario_id']
            ];

            if ($analisis_model->registrarPerfilLipidico($datos)) {
                $mensaje = 'Perfil lipídico registrado correctamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al registrar el perfil lipídico.';
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
        <h2><i class="bi bi-heart-pulse"></i> Perfil Lipídico</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Perfil Lipídico</li>
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
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <i class="bi bi-person"></i> Paciente
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <label class="form-label">Buscar Paciente</label>
                    <input class="form-control" list="datalistOptions" id="busqueda_paciente" required>
                    <datalist id="datalistOptions">
                        <?php foreach ($pacientes as $paciente): ?>
                            <option data-id="<?= $paciente['id_paciente'] ?>"
                                value="<?= htmlspecialchars($paciente['numero_expediente'] . ' - ' . $paciente['nombre_completo']) ?>">
                            <?php endforeach; ?>
                    </datalist>
                    <input type="hidden" name="id_paciente" id="id_paciente">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha del Análisis</label>
                    <input type="date" class="form-control" name="fecha_analisis" value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header">
            <i class="bi bi-activity"></i> Resultados de Lípidos
        </div>
        <div class="card-body">
            <div class="row g-4">
                <!-- Colesterol -->
                <div class="col-md-6 border-end">
                    <h5 class="card-title text-secondary">Colesterol Total</h5>
                    <div class="mb-2">
                        <label class="form-label">Valor (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" id="colesterol" name="colesterol">
                    </div>
                    <div>
                        <select class="form-select form-select-sm" id="inter_colesterol"
                            name="interpretacion_colesterol">
                            <option value="">Interpretación...</option>
                            <option value="Normal">Deseable (< 200)</option>
                            <option value="Precaución">Límite alto (200-239)</option>
                            <option value="Alerta">Alto (≥ 240)</option>
                        </select>
                    </div>
                </div>

                <!-- LDL -->
                <div class="col-md-6">
                    <h5 class="card-title text-secondary">LDL (Malo)</h5>
                    <div class="mb-2">
                        <label class="form-label">Valor (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" id="ldl" name="ldl">
                    </div>
                    <div>
                        <select class="form-select form-select-sm" id="inter_ldl" name="interpretacion_ldl">
                            <option value="">Interpretación...</option>
                            <option value="Normal">Óptimo (< 100)</option>
                            <option value="Precaución">Cercano al óptimo (100-129)</option>
                            <option value="Alerta">Alto (≥ 160)</option>
                        </select>
                    </div>
                </div>

                <div class="col-12">
                    <hr>
                </div>

                <!-- HDL -->
                <div class="col-md-6 border-end">
                    <h5 class="card-title text-secondary">HDL (Bueno)</h5>
                    <div class="mb-2">
                        <label class="form-label">Valor (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" id="hdl" name="hdl">
                    </div>
                    <div>
                        <select class="form-select form-select-sm" id="inter_hdl" name="interpretacion_hdl">
                            <option value="">Interpretación...</option>
                            <option value="Normal">Alto/Bueno (≥ 60)</option>
                            <option value="Alerta">Bajo (< 40)</option>
                        </select>
                    </div>
                </div>

                <!-- Triglicéridos -->
                <div class="col-md-6">
                    <h5 class="card-title text-secondary">Triglicéridos</h5>
                    <div class="mb-2">
                        <label class="form-label">Valor (mg/dL)</label>
                        <input type="number" step="0.01" class="form-control" id="trigliceridos" name="trigliceridos">
                    </div>
                    <div>
                        <select class="form-select form-select-sm" id="inter_trig" name="interpretacion_trigliceridos">
                            <option value="">Interpretación...</option>
                            <option value="Normal">Normal (< 150)</option>
                            <option value="Precaución">Límite alto (150-199)</option>
                            <option value="Alerta">Alto (≥ 200)</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" name="observaciones" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
        <a href="../../index.php" class="btn btn-secondary me-md-2">Cancelar</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar P. Lipídico</button>
    </div>
</form>

<script>
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

    // Auto-calc helper
    function setSelect(id, val, thresholds) {
        var select = document.getElementById(id);
        if (isNaN(val)) return;
        // thresholds = [limit1, limit2] where limit1 = end of Normal, limit2 = start of Alert
        if (val < thresholds[0]) select.value = 'Normal';
        else if (val < thresholds[1]) select.value = 'Precaución';
        else select.value = 'Alerta';
    }

    document.getElementById('colesterol').addEventListener('input', function () {
        setSelect('inter_colesterol', parseFloat(this.value), [200, 240]);
    });

    document.getElementById('ldl').addEventListener('input', function () {
        setSelect('inter_ldl', parseFloat(this.value), [100, 160]);
    });

    document.getElementById('trigliceridos').addEventListener('input', function () {
        setSelect('inter_trig', parseFloat(this.value), [150, 200]);
    });
</script>

<?php include '../../includes/footer.php'; ?>