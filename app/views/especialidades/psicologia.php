<?php
/**
 * Vista de Psicología Clínica
 * Formulario con 5 visitas basado en el formato físico
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Psicologia.php';
require_once '../../models/Visita.php';
require_once '../../models/Paciente.php';

$id_visita   = $_GET['id_visita']   ?? null;
$id_paciente = $_GET['id_paciente'] ?? null;
$message     = '';
$message_type = '';

$database = new Database();
$db       = $database->getConnection();

$psicologiaModel = new Psicologia($db);
$visitaModel     = new Visita($db);
$pacienteModel   = new Paciente($db);

// ── Guardar ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consulta'])) {
    if ($psicologiaModel->guardar($_POST)) {
        $message      = "Consulta de Psicología guardada exitosamente.";
        $message_type = "success";
    } else {
        $message      = "Error al guardar la consulta.";
        $message_type = "danger";
    }
}

// ── Obtener datos ─────────────────────────────────────────────────────────────
$datos   = [];
$visita  = null;
$paciente = null;

if ($id_visita) {
    $datos  = $psicologiaModel->obtenerPorVisita($id_visita) ?: [];
    $visita = $visitaModel->obtenerPorId($id_visita);
    if ($visita) $id_paciente = $visita['id_paciente'];
}

if ($id_paciente) {
    $paciente = $pacienteModel->obtenerPorId($id_paciente);
    if (empty($datos)) {
        $datos = $psicologiaModel->obtenerPorPaciente($id_paciente) ?: [];
    }
}

// ── Selector de paciente ──────────────────────────────────────────────────────
$lista_pacientes = [];
if (!$id_visita && !$id_paciente) {
    $q = "SELECT p.id_paciente, p.numero_expediente,
                 CONCAT(p.nombre,' ',p.apellido_paterno,' ',IFNULL(p.apellido_materno,'')) as nombre_completo,
                 (SELECT id_visita   FROM visitas WHERE id_paciente=p.id_paciente ORDER BY fecha_visita DESC LIMIT 1) as ultima_visita_id,
                 (SELECT fecha_visita FROM visitas WHERE id_paciente=p.id_paciente ORDER BY fecha_visita DESC LIMIT 1) as ultima_visita_fecha
          FROM pacientes p WHERE p.activo=1 ORDER BY p.nombre ASC LIMIT 200";
    $s = $db->prepare($q);
    $s->execute();
    $lista_pacientes = $s->fetchAll(PDO::FETCH_ASSOC);
}

$page_title = 'Psicología Clínica';
include '../../includes/header.php';

// ── Helper: radio escala ──────────────────────────────────────────────────────
function escalaRadio($name, $opciones, $valor_actual) {
    $html = '<div class="d-flex flex-wrap gap-3">';
    foreach ($opciones as $op) {
        $checked = ($valor_actual === $op) ? 'checked' : '';
        $id = $name . '_' . preg_replace('/\s+/', '_', strtolower($op));
        $html .= "<div class='form-check'>
            <input class='form-check-input' type='radio' name='$name' id='$id' value='$op' $checked>
            <label class='form-check-label' for='$id'>$op</label>
        </div>";
    }
    $html .= '</div>';
    return $html;
}

$escala_beck    = ['Leve', 'Moderada', 'Severa', 'N/A'];
$escala_siempre = ['Siempre', 'Casi Siempre', 'Nunca', 'Algunas Veces', 'N/A'];
?>

<style>
    :root { --psi-purple: #6f42c1; --psi-light: #f8f5ff; --psi-border: #d8c8f0; }
    body { background-color: #f5f7fb; }
    .card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.05); margin-bottom: 1.5rem; }
    .section-title {
        color: var(--psi-purple);
        border-bottom: 2px solid var(--psi-border);
        padding-bottom: .5rem; margin-bottom: 1rem;
        font-weight: 700;
    }
    .nav-tabs .nav-link { color: #666; font-weight: 600; border: none; padding: .9rem 1.2rem; border-bottom: 3px solid transparent; }
    .nav-tabs .nav-link.active { color: var(--psi-purple); background: transparent; border-bottom-color: var(--psi-purple); }
    .visita-header { background: var(--psi-purple); color: #fff; border-radius: 8px; padding: .6rem 1rem; margin-bottom: 1rem; }
    .eval-table th { background: var(--psi-light); font-size: .85rem; }
    .eval-table td { vertical-align: middle; }
</style>

<div class="container-fluid py-4">

    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-brain text-purple" style="color:var(--psi-purple)"></i> Psicología Clínica</h2>
            <?php if ($paciente): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="<?= PROJECT_PATH ?>/app/views/pacientes/detalle.php?id=<?= $paciente['id_paciente'] ?>">
                                <?= htmlspecialchars($paciente['nombre'].' '.$paciente['apellido_paterno']) ?>
                            </a>
                        </li>
                        <?php if ($id_visita): ?>
                            <li class="breadcrumb-item active">Visita #<?= $id_visita ?></li>
                        <?php endif; ?>
                        <li class="breadcrumb-item active">Psicología Clínica</li>
                    </ol>
                </nav>
            <?php else: ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="<?= PROJECT_PATH ?>/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Selección de Paciente</li>
                    </ol>
                </nav>
            <?php endif; ?>
        </div>
        <a href="<?= PROJECT_PATH ?>/index.php" class="btn btn-light shadow-sm"><i class="bi bi-house"></i> Home</a>
    </div>

    <!-- Mensaje -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show shadow-sm">
            <i class="bi <?= $message_type=='success'?'bi-check-circle':'bi-exclamation-triangle' ?> me-2"></i>
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- ── SELECTOR DE PACIENTE ── -->
    <?php if (!$id_visita && !$id_paciente): ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header d-flex align-items-center" style="background:var(--psi-purple);color:#fff">
                        <i class="bi bi-person-plus-fill me-2 fs-5"></i>
                        <span>Seleccionar Paciente para Consulta</span>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Busque y seleccione al paciente para su registro de Psicología Clínica.</p>
                        <input type="text" class="form-control form-control-lg mb-3"
                               id="patientSelectionInput"
                               placeholder="Buscar por nombre o número de expediente..."
                               style="border-color:var(--psi-purple)">
                        <div id="patientSearchResults" class="list-group list-group-flush shadow-sm rounded-3 border"
                             style="max-height:400px;overflow-y:auto;">
                            <?php foreach ($lista_pacientes as $p): 
                                $searchText = strtolower($p['nombre_completo'] . ' ' . $p['numero_expediente']);
                            ?>
                                <div class="list-group-item p-3 border-bottom patient-item" data-search="<?= htmlspecialchars($searchText) ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($p['nombre_completo']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($p['numero_expediente']) ?></small>
                                        </div>
                                        <?php if ($p['ultima_visita_id']): ?>
                                            <a href="psicologia.php?id_visita=<?= $p['ultima_visita_id'] ?>" class="btn btn-sm rounded-pill" style="border:1px solid var(--psi-purple);color:var(--psi-purple)">
                                                <i class="bi bi-brain"></i> Abrir Última (<?= date('d/m/y', strtotime($p['ultima_visita_fecha'])) ?>)
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

    <!-- ── FORMULARIO ── -->
    <form method="POST" id="formPsicologia">
        <input type="hidden" name="id_paciente"   value="<?= $id_paciente ?>">
        <input type="hidden" name="id_visita"     value="<?= $id_visita ?>">
        <input type="hidden" name="save_consulta" value="1">

        <!-- Descripción del Paciente (global) -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h5 class="section-title"><i class="bi bi-person-lines-fill me-2"></i>Descripción del Paciente</h5>
                <textarea class="form-control" name="descripcion_paciente" rows="3"
                          placeholder="Descripción general del paciente..."><?= htmlspecialchars($datos['descripcion_paciente'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- Tabs de Visitas -->
        <ul class="nav nav-tabs mb-4 px-2 bg-white rounded shadow-sm sticky-top" style="top:10px;z-index:100;" id="psiTabs">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#v1" type="button">
                    <i class="bi bi-1-circle"></i> Visita 1
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#v2" type="button">
                    <i class="bi bi-2-circle"></i> Visita 2
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#v3" type="button">
                    <i class="bi bi-3-circle"></i> Visita 3
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#v4" type="button">
                    <i class="bi bi-4-circle"></i> Visita 4
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#v5" type="button">
                    <i class="bi bi-5-circle"></i> Visita 5
                </button>
            </li>
        </ul>

        <div class="tab-content">

            <!-- ══════════════════════════════════════════════════════════════
                 VISITA 1 – Proceso del Duelo en la Enfermedad
            ══════════════════════════════════════════════════════════════ -->
            <div class="tab-pane fade show active" id="v1">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="visita-header">
                            <strong>VISITA 1</strong> &nbsp;|&nbsp; Proceso del Duelo en la Enfermedad
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered eval-table">
                                <thead>
                                    <tr>
                                        <th style="width:35%">Instrumento de Evaluación</th>
                                        <th>Leve</th>
                                        <th>Moderada</th>
                                        <th>Severa</th>
                                        <th>N/A</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $v1_rows = [
                                        ['v1_ansiedad_beck',     'Inventario Ansiedad de Beck'],
                                        ['v1_depresion_beck',    'Inventario de Depresión de Beck'],
                                        ['v1_desesperanza_beck', 'Escala de Beck (desesperanza)'],
                                    ];
                                    foreach ($v1_rows as [$field, $label]):
                                        $val = $datos[$field] ?? '';
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?= $label ?></td>
                                        <?php foreach (['Leve','Moderada','Severa','N/A'] as $op): ?>
                                        <td class="text-center">
                                            <input type="radio" class="form-check-input"
                                                   name="<?= $field ?>" value="<?= $op ?>"
                                                   <?= $val===$op?'checked':'' ?>>
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" name="v1_observaciones" rows="3"><?= htmlspecialchars($datos['v1_observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('formPsicologia').submit()">
                        <i class="bi bi-save"></i> Guardar Progreso
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextTab('v2')">
                        Siguiente <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 VISITA 2 – Limitantes para la Adherencia al Tratamiento
            ══════════════════════════════════════════════════════════════ -->
            <div class="tab-pane fade" id="v2">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="visita-header">
                            <strong>VISITA 2</strong> &nbsp;|&nbsp; Limitantes para la Adherencia al Tratamiento
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered eval-table">
                                <thead>
                                    <tr>
                                        <th style="width:35%">Repercusiones</th>
                                        <th>Siempre</th>
                                        <th>Casi Siempre</th>
                                        <th>Nunca</th>
                                        <th>Algunas Veces</th>
                                        <th>N/A</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $v2_rows = [
                                        ['v2_nivel_personal',   '• Nivel Personal'],
                                        ['v2_nivel_economico',  '• Nivel Económico'],
                                        ['v2_nivel_social',     '• Nivel Social'],
                                        ['v2_nivel_sanitario',  '• Nivel Sanitario'],
                                    ];
                                    foreach ($v2_rows as [$field, $label]):
                                        $val = $datos[$field] ?? '';
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?= $label ?></td>
                                        <?php foreach (['Siempre','Casi Siempre','Nunca','Algunas Veces','N/A'] as $op): ?>
                                        <td class="text-center">
                                            <input type="radio" class="form-check-input"
                                                   name="<?= $field ?>" value="<?= $op ?>"
                                                   <?= $val===$op?'checked':'' ?>>
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" name="v2_observaciones" rows="3"><?= htmlspecialchars($datos['v2_observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('formPsicologia').submit()">
                        <i class="bi bi-save"></i> Guardar Progreso
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextTab('v3')">
                        Siguiente <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 VISITA 3 – Estados de Cambio en la Motivación
            ══════════════════════════════════════════════════════════════ -->
            <div class="tab-pane fade" id="v3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="visita-header">
                            <strong>VISITA 3</strong> &nbsp;|&nbsp; Estados de Cambio en la Motivación
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered eval-table">
                                <thead>
                                    <tr>
                                        <th style="width:35%">Instrumento de Evaluación</th>
                                        <th>Siempre</th>
                                        <th>Casi Siempre</th>
                                        <th>Nunca</th>
                                        <th>Algunas Veces</th>
                                        <th>N/A</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $v3_rows = [
                                        ['v3_pre_contemplacion', 'Pre-Contemplación'],
                                        ['v3_contemplacion',     'Contemplación'],
                                        ['v3_decision',          'Decisión'],
                                        ['v3_accion',            'Acción'],
                                        ['v3_mantenimiento',     'Mantenimiento'],
                                        ['v3_recaida',           'Recaída'],
                                    ];
                                    foreach ($v3_rows as [$field, $label]):
                                        $val = $datos[$field] ?? '';
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?= $label ?></td>
                                        <?php foreach (['Siempre','Casi Siempre','Nunca','Algunas Veces','N/A'] as $op): ?>
                                        <td class="text-center">
                                            <input type="radio" class="form-check-input"
                                                   name="<?= $field ?>" value="<?= $op ?>"
                                                   <?= $val===$op?'checked':'' ?>>
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" name="v3_observaciones" rows="3"><?= htmlspecialchars($datos['v3_observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('formPsicologia').submit()">
                        <i class="bi bi-save"></i> Guardar Progreso
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextTab('v4')">
                        Siguiente <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 VISITA 4 – Técnicas de Relajación por Respiración Profunda
            ══════════════════════════════════════════════════════════════ -->
            <div class="tab-pane fade" id="v4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="visita-header">
                            <strong>VISITA 4</strong> &nbsp;|&nbsp; Técnicas de Relajación por Respiración Profunda
                        </div>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered eval-table">
                                <thead>
                                    <tr>
                                        <th style="width:35%">Instrumento de Evaluación</th>
                                        <th>Siempre</th>
                                        <th>Casi Siempre</th>
                                        <th>Nunca</th>
                                        <th>Algunas Veces</th>
                                        <th>N/A</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $val = $datos['v4_logro_relajacion'] ?? ''; ?>
                                    <tr>
                                        <td class="fw-semibold">¿Has logrado la relajación?</td>
                                        <?php foreach (['Siempre','Casi Siempre','Nunca','Algunas Veces','N/A'] as $op): ?>
                                        <td class="text-center">
                                            <input type="radio" class="form-check-input"
                                                   name="v4_logro_relajacion" value="<?= $op ?>"
                                                   <?= $val===$op?'checked':'' ?>>
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Descripción del Paciente</label>
                            <textarea class="form-control" name="v4_descripcion_paciente" rows="3"><?= htmlspecialchars($datos['v4_descripcion_paciente'] ?? '') ?></textarea>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" name="v4_observaciones" rows="3"><?= htmlspecialchars($datos['v4_observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <button type="button" class="btn btn-secondary me-2" onclick="document.getElementById('formPsicologia').submit()">
                        <i class="bi bi-save"></i> Guardar Progreso
                    </button>
                    <button type="button" class="btn btn-primary" onclick="nextTab('v5')">
                        Siguiente <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 VISITA 5 – Tristeza y Depresión
            ══════════════════════════════════════════════════════════════ -->
            <div class="tab-pane fade" id="v5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="visita-header">
                            <strong>VISITA 5</strong> &nbsp;|&nbsp; Tristeza y Depresión
                        </div>

                        <p class="text-muted mb-3"><em>¿Alguna vez has sufrido tristeza/depresión?</em></p>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered eval-table">
                                <thead>
                                    <tr>
                                        <th style="width:35%">Instrumento de Evaluación</th>
                                        <th>Siempre</th>
                                        <th>Casi Siempre</th>
                                        <th>Nunca</th>
                                        <th>Algunas Veces</th>
                                        <th>N/A</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $v5_rows = [
                                        ['v5_tristeza',  'Tristeza'],
                                        ['v5_depresion', 'Depresión'],
                                    ];
                                    foreach ($v5_rows as [$field, $label]):
                                        $val = $datos[$field] ?? '';
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?= $label ?></td>
                                        <?php foreach (['Siempre','Casi Siempre','Nunca','Algunas Veces','N/A'] as $op): ?>
                                        <td class="text-center">
                                            <input type="radio" class="form-check-input"
                                                   name="<?= $field ?>" value="<?= $op ?>"
                                                   <?= $val===$op?'checked':'' ?>>
                                        </td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <label class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" name="v5_observaciones" rows="3"><?= htmlspecialchars($datos['v5_observaciones'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Botón final guardar -->
                <div class="d-flex justify-content-end gap-2 mt-3">
                    <a href="../../../index.php" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Guardar Consulta Psicológica
                    </button>
                </div>
            </div>

        </div><!-- /tab-content -->
    </form>
    <?php endif; ?>

</div><!-- /container -->

<script>
function nextTab(tabId) {
    var el = document.querySelector('#psiTabs button[data-bs-target="#' + tabId + '"]');
    new bootstrap.Tab(el).show();
    window.scrollTo(0, 0);
}

const inp = document.getElementById('patientSelectionInput');
if (inp) {
    inp.addEventListener('input', function() {
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