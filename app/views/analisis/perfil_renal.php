<?php
/**
 * Perfil Renal
 * Formulario para registrar Creatinina, TFG, Urea, etc.
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Analisis.php';
require_once '../../models/Paciente.php';

$page_title = 'Perfil Renal';

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
                'creatinina_serica' => !empty($_POST['creatinina']) ? $_POST['creatinina'] : null,
                'tasa_filtracion_glomerular' => !empty($_POST['tfg']) ? $_POST['tfg'] : null,
                'urea' => !empty($_POST['urea']) ? $_POST['urea'] : null,
                'bun' => !empty($_POST['bun']) ? $_POST['bun'] : null,
                'microalbuminuria' => !empty($_POST['microalbuminuria']) ? $_POST['microalbuminuria'] : null,
                'relacion_albumina_creatinina' => !empty($_POST['rac']) ? $_POST['rac'] : null,
                'interpretacion_tfg' => $_POST['interpretacion_tfg'] ?? null,
                'interpretacion_microalbuminuria' => $_POST['interpretacion_micro'] ?? null,
                'observaciones' => trim($_POST['observaciones']),
                'created_by' => $_SESSION['usuario_id']
            ];

            if ($analisis_model->registrarPerfilRenal($datos)) {
                $mensaje = 'Perfil renal registrado correctamente.';
                $tipo_mensaje = 'success';
            } else {
                $mensaje = 'Error al registrar el perfil renal.';
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
        <h2><i class="bi bi-droplet"></i> Perfil Renal</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../../index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Perfil Renal</li>
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
            <i class="bi bi-activity"></i> Resultados Renales
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Creatinina Sérica (mg/dL)</label>
                    <input type="number" step="0.01" class="form-control" name="creatinina" placeholder="0.7 - 1.3">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Urea (mg/dL)</label>
                    <input type="number" step="0.01" class="form-control" name="urea">
                </div>
                <div class="col-md-4">
                    <label class="form-label">BUN (mg/dL)</label>
                    <input type="number" step="0.01" class="form-control" name="bun">
                </div>

                <div class="col-12">
                    <hr>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-bold">Tasa de Filtración Glomerular (TFG) - mL/min</label>
                    <input type="number" step="0.01" class="form-control" id="tfg" name="tfg" placeholder="Ej. 90">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Interpretación TFG</label>
                    <select class="form-select" id="interpretacion_tfg" name="interpretacion_tfg">
                        <option value="">Seleccione...</option>
                        <option value="Normal">Normal (> 90)</option>
                        <option value="Precaución">Daño Leve (60-89)</option>
                        <option value="Alerta">Insuficiencia (< 60)</option>
                    </select>
                </div>

                <div class="col-12">
                    <hr>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Microalbuminuria / RAC</label>
                    <input type="number" step="0.01" class="form-control" name="rac" placeholder="mg/g">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Interpretación Microalbuminuria</label>
                    <select class="form-select" name="interpretacion_micro">
                        <option value="">Seleccione...</option>
                        <option value="Normal">Normal</option>
                        <option value="Precaución">Microalbuminuria</option>
                        <option value="Alerta">Macroalbuminuria</option>
                    </select>
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-control" name="observaciones" rows="2"></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
        <a href="../../../index.php" class="btn btn-secondary me-md-2">Cancelar</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar Perfil Renal</button>
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

    document.getElementById('tfg').addEventListener('input', function () {
        var val = parseFloat(this.value);
        var select = document.getElementById('interpretacion_tfg');
        if (!isNaN(val)) {
            if (val >= 90) select.value = 'Normal';
            else if (val >= 60) select.value = 'Precaución';
            else select.value = 'Alerta';
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>