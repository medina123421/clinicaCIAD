<?php
/**
 * Vista de Actividad Física
 * Formulario completo: SARC-F5, Dinamometría, Daniels, Sit-to-Stand, EVA, Movilidad, Actividad actual
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/ActividadFisica.php';
require_once __DIR__ . '/../../models/Visita.php';
require_once __DIR__ . '/../../models/Paciente.php';

$id_visita = $_GET['id_visita'] ?? null;
$message = '';
$message_type = '';

$database = new Database();
$db = $database->getConnection();

$actividadFisica = new ActividadFisica($db);
$visitaModel = new Visita($db);
$pacienteModel = new Paciente($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consulta'])) {
    try {
        if ($actividadFisica->guardar($_POST)) {
            $message = "Consulta de Actividad Física guardada correctamente.";
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

$datos = [];
$visita = null;
$paciente = null;

if ($id_visita) {
    $datos = $actividadFisica->obtenerPorVisita($id_visita) ?: [];
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

$page_title = 'Actividad Física';

function renderRadioSarc($name, $label, $datos)
{
    $v = (int)($datos[$name] ?? -1);
    $html = "<div class='mb-2'><label class='form-label small'>$label</label><div class='d-flex gap-3'>";
    foreach (['0' => '0', '1' => '1', '2' => '2'] as $val => $lab) {
        $ch = $v === (int)$val ? 'checked' : '';
        $html .= "<div class='form-check'><input type='radio' name='$name' value='$val' id='{$name}_{$val}' class='form-check-input sarc-item' $ch><label class='form-check-label' for='{$name}_{$val}'>$lab</label></div>";
    }
    $html .= "</div></div>";
    return $html;
}

include __DIR__ . '/../../includes/header.php';
?>

<style>
    :root {
        --af-primary: #198754;
        --af-light: #f0f9f4;
        --af-border: #c3e6cb;
    }
    body { background-color: #f5f7fb; }
    .card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
    }
    .card-header {
        background-color: var(--af-primary);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
    .section-title {
        color: var(--af-primary);
        border-bottom: 2px solid var(--af-light);
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
        color: var(--af-primary);
        background: transparent;
        border-bottom-color: var(--af-primary);
    }
    .sarc-total-box {
        background: var(--af-light);
        border: 1px solid var(--af-border);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-weight: 600;
    }
    .sarc-total-box.alta { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-activity text-success"></i> Actividad Física</h2>
            <?php if ($paciente): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../pacientes/detalle_paciente.php?id=<?= (int)$paciente['id_paciente'] ?>"><?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?></a></li>
                        <li class="breadcrumb-item active">Evaluación Actividad Física</li>
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
            <a href="../visitas/lista.php" class="btn btn-success shadow-sm"><i class="bi bi-clipboard-data"></i> Visitas</a>
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
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-search"></i> Buscar Paciente
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Busque al paciente para abrir la evaluación de Actividad Física.</p>
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
                                                    <a href="?id_visita=<?= (int)$v['id_visita'] ?>" class="btn btn-success btn-sm rounded-pill">Abrir Consulta <i class="bi bi-arrow-right-short"></i></a>
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

        <form method="POST" id="formActividadFisica">
            <input type="hidden" name="id_visita" value="<?= (int)$id_visita ?>">
            <input type="hidden" name="save_consulta" value="1">

            <ul class="nav nav-tabs mb-4 px-2 bg-white rounded shadow-sm sticky-top" style="top: 10px; z-index: 100;" id="afTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="tab-sarc" data-bs-toggle="tab" data-bs-target="#pane-sarc" type="button">1. SARC-F5 y Actividad</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-eval" data-bs-toggle="tab" data-bs-target="#pane-eval" type="button">2. Evaluación Muscular y Funcional</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="tab-obs" data-bs-toggle="tab" data-bs-target="#pane-obs" type="button">3. Observaciones</button>
                </li>
            </ul>

            <div class="tab-content" id="afTabsContent">

                <!-- TAB 1: SARC-F5 y Actividad actual -->
                <div class="tab-pane fade show active" id="pane-sarc" role="tabpanel">
                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-clipboard2-pulse"></i> Tamizaje SARC-F5 (Sarcopenia)</h5>
                            <p class="small text-muted mb-3">Cada ítem: 0 = Ninguno, 1 = Alguna, 2 = Mucha o incapaz. Puntuación total 0-10. ≥4 puntos = Alta probabilidad de sarcopenia.</p>
                            <div class="row">
                                <div class="col-md-6">
                                    <?= renderRadioSarc('sarc_fuerza', 'Fuerza: ¿Dificultad para levantar/transportar 4.5 kg?', $datos) ?>
                                    <?= renderRadioSarc('sarc_asistencia_caminar', 'Asistencia para caminar: ¿Cruzar caminando por un cuarto?', $datos) ?>
                                    <?= renderRadioSarc('sarc_levantarse_silla', 'Levantarse de una silla o cama', $datos) ?>
                                    <?= renderRadioSarc('sarc_subir_escaleras', 'Subir 10 escalones', $datos) ?>
                                    <?= renderRadioSarc('sarc_caidas', 'Caídas en el último año (0: ninguna, 1: 1-3, 2: 4+)', $datos) ?>
                                </div>
                                <div class="col-md-6">
                                    <div class="sarc-total-box mb-3" id="sarcTotalBox">
                                        Puntuación total: <span id="sarcTotalVal"><?= (int)($datos['sarc_puntuacion_total'] ?? 0) ?></span><br>
                                        Riesgo: <span id="sarcRiesgoVal"><?= htmlspecialchars($datos['sarc_riesgo'] ?? 'Baja') ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-bicycle"></i> Actividad Física Actual</h5>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="act_realiza_ejercicio" id="act_realiza_ejercicio" value="1" <?= !empty($datos['act_realiza_ejercicio']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="act_realiza_ejercicio">Realiza ejercicio actualmente</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Frecuencia semanal</label>
                                    <select name="act_frecuencia" class="form-select">
                                        <option value="0" <?= ($datos['act_frecuencia'] ?? '') === '0' ? 'selected' : '' ?>>0 veces</option>
                                        <option value="1-2" <?= ($datos['act_frecuencia'] ?? '') === '1-2' ? 'selected' : '' ?>>1-2 veces</option>
                                        <option value="3-4" <?= ($datos['act_frecuencia'] ?? '') === '3-4' ? 'selected' : '' ?>>3-4 veces</option>
                                        <option value="5+" <?= ($datos['act_frecuencia'] ?? '') === '5+' ? 'selected' : '' ?>>5+ veces</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Tipo</label>
                                    <select name="act_tipo" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <option value="Aerobico" <?= ($datos['act_tipo'] ?? '') === 'Aerobico' ? 'selected' : '' ?>>Aeróbico</option>
                                        <option value="Fuerza" <?= ($datos['act_tipo'] ?? '') === 'Fuerza' ? 'selected' : '' ?>>Fuerza</option>
                                        <option value="Combinado" <?= ($datos['act_tipo'] ?? '') === 'Combinado' ? 'selected' : '' ?>>Combinado</option>
                                        <option value="Otro" <?= ($datos['act_tipo'] ?? '') === 'Otro' ? 'selected' : '' ?>>Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-4" id="actTipoOtroWrap" style="<?= ($datos['act_tipo'] ?? '') === 'Otro' ? '' : 'display:none' ?>">
                                    <label class="form-label fw-bold">Especifique</label>
                                    <input type="text" name="act_tipo_otro" class="form-control" value="<?= htmlspecialchars($datos['act_tipo_otro'] ?? '') ?>" placeholder="Otro tipo">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Duración por sesión</label>
                                    <select name="act_duracion" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <option value="0-30" <?= ($datos['act_duracion'] ?? '') === '0-30' ? 'selected' : '' ?>>0-30 min</option>
                                        <option value="30-60" <?= ($datos['act_duracion'] ?? '') === '30-60' ? 'selected' : '' ?>>30-60 min</option>
                                        <option value="60+" <?= ($datos['act_duracion'] ?? '') === '60+' ? 'selected' : '' ?>>60+ min</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Días de descanso</label>
                                    <select name="act_dias_descanso" class="form-select">
                                        <option value="">Seleccione...</option>
                                        <option value="1-2" <?= ($datos['act_dias_descanso'] ?? '') === '1-2' ? 'selected' : '' ?>>1-2</option>
                                        <option value="2-3" <?= ($datos['act_dias_descanso'] ?? '') === '2-3' ? 'selected' : '' ?>>2-3</option>
                                        <option value="3+" <?= ($datos['act_dias_descanso'] ?? '') === '3+' ? 'selected' : '' ?>>3+</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Evaluación muscular y funcional -->
                <div class="tab-pane fade" id="pane-eval" role="tabpanel">
                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-hand-index"></i> Dinamometría (kg)</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Mano derecha</label>
                                    <input type="number" step="0.01" name="dina_mano_der" class="form-control" value="<?= htmlspecialchars($datos['dina_mano_der'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Mano izquierda</label>
                                    <input type="number" step="0.01" name="dina_mano_izq" class="form-control" value="<?= htmlspecialchars($datos['dina_mano_izq'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Percentil / resultado</label>
                                    <input type="text" name="dina_percentil_resultado" class="form-control" value="<?= htmlspecialchars($datos['dina_percentil_resultado'] ?? '') ?>" placeholder="Ej. P10-P90">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-speedometer2"></i> Escala de Daniels (0-5)</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">MS derecho</label>
                                    <input type="number" min="0" max="5" name="daniels_ms_der" class="form-control" value="<?= htmlspecialchars($datos['daniels_ms_der'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">MS izquierdo</label>
                                    <input type="number" min="0" max="5" name="daniels_ms_izq" class="form-control" value="<?= htmlspecialchars($datos['daniels_ms_izq'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">MI derecho</label>
                                    <input type="number" min="0" max="5" name="daniels_mi_der" class="form-control" value="<?= htmlspecialchars($datos['daniels_mi_der'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">MI izquierdo</label>
                                    <input type="number" min="0" max="5" name="daniels_mi_izq" class="form-control" value="<?= htmlspecialchars($datos['daniels_mi_izq'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-arrow-repeat"></i> Sit-to-Stand</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Test 30 seg (repeticiones)</label>
                                    <input type="number" min="0" name="sts_30seg_reps" class="form-control" value="<?= htmlspecialchars($datos['sts_30seg_reps'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Test 5 rep (segundos)</label>
                                    <input type="number" step="0.1" name="sts_5rep_seg" id="sts_5rep_seg" class="form-control" value="<?= htmlspecialchars($datos['sts_5rep_seg'] ?? '') ?>">
                                </div>
                                <div class="col-md-4">
                                    <div class="pt-4">
                                        <span id="stsAlertaMsg" class="badge bg-warning text-dark" style="display:none;">Alerta: &gt; 15 seg</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-droplet-half"></i> Dolor (EVA 0-10)</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Zona / localización</label>
                                    <input type="text" name="eva_zona" class="form-control" value="<?= htmlspecialchars($datos['eva_zona'] ?? '') ?>" placeholder="Ej. rodilla derecha">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Puntaje (0-10)</label>
                                    <input type="number" min="0" max="10" name="eva_puntaje" class="form-control" value="<?= htmlspecialchars($datos['eva_puntaje'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <h5 class="section-title"><i class="bi bi-arrows-move"></i> Movilidad articular (0-5)</h5>
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">MS der</label>
                                    <input type="number" min="0" max="5" name="mov_ms_der" class="form-control" value="<?= htmlspecialchars($datos['mov_ms_der'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">MS izq</label>
                                    <input type="number" min="0" max="5" name="mov_ms_izq" class="form-control" value="<?= htmlspecialchars($datos['mov_ms_izq'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">MI der</label>
                                    <input type="number" min="0" max="5" name="mov_mi_der" class="form-control" value="<?= htmlspecialchars($datos['mov_mi_der'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">MI izq</label>
                                    <input type="number" min="0" max="5" name="mov_mi_izq" class="form-control" value="<?= htmlspecialchars($datos['mov_mi_izq'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: Observaciones -->
                <div class="tab-pane fade" id="pane-obs" role="tabpanel">
                    <div class="card border-0 mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <label class="form-label fw-bold">Observaciones del especialista</label>
                            <textarea name="observaciones_especialista" class="form-control" rows="6" placeholder="Notas adicionales..."><?= htmlspecialchars($datos['observaciones_especialista'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 mb-5 text-center sticky-bottom bg-white p-3 rounded shadow">
                <button type="submit" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-save2"></i> Guardar Evaluación Actividad Física
                </button>
            </div>
        </form>

    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateSarcTotal() {
        var total = 0;
        ['sarc_fuerza', 'sarc_asistencia_caminar', 'sarc_levantarse_silla', 'sarc_subir_escaleras', 'sarc_caidas'].forEach(function(name) {
            var r = document.querySelector('input[name="' + name + '"]:checked');
            total += r ? parseInt(r.value, 10) : 0;
        });
        document.getElementById('sarcTotalVal').textContent = total;
        var riesgo = total >= 4 ? 'Alta' : 'Baja';
        document.getElementById('sarcRiesgoVal').textContent = riesgo;
        var box = document.getElementById('sarcTotalBox');
        box.classList.toggle('alta', total >= 4);
    }
    document.querySelectorAll('.sarc-item').forEach(function(el) {
        el.addEventListener('change', updateSarcTotal);
    });
    updateSarcTotal();

    document.querySelector('select[name="act_tipo"]').addEventListener('change', function() {
        document.getElementById('actTipoOtroWrap').style.display = this.value === 'Otro' ? 'block' : 'none';
    });

    var stsInput = document.getElementById('sts_5rep_seg');
    if (stsInput) {
        stsInput.addEventListener('input', function() {
            var v = parseFloat(this.value);
            document.getElementById('stsAlertaMsg').style.display = (v > 15) ? 'inline' : 'none';
        });
        if (parseFloat(stsInput.value) > 15) document.getElementById('stsAlertaMsg').style.display = 'inline';
    }

    var patientSearchInput = document.getElementById('patientSearchInput');
    if (patientSearchInput) {
        var resultsContainer = document.getElementById('patientSearchResults');
        var debounce = function(fn, ms) {
            var t;
            return function() { clearTimeout(t); t = setTimeout(fn, ms); };
        };
        patientSearchInput.addEventListener('input', debounce(function() {
            var search = this.value.trim();
            if (search.length < 2) {
                resultsContainer.innerHTML = '<div class="text-center py-3 text-muted"><i class="bi bi-person-fill-gear fs-2 d-block mb-2"></i><small>Escriba para buscar</small></div>';
                return;
            }
            resultsContainer.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-success"></div></div>';
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '../../ajax/buscar_pacientes_actividad.php?search=' + encodeURIComponent(search));
            xhr.onload = function() {
                try {
                    var r = JSON.parse(xhr.responseText);
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
