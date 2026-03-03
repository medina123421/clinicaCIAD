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

$id_visita = $_GET['id_visita'] ?? null;
$id_paciente = $_GET['id_paciente'] ?? null;
$message = '';
$message_type = '';

$database = new Database();
$db = $database->getConnection();

$psicologiaModel = new Psicologia($db);
$visitaModel = new Visita($db);
$pacienteModel = new Paciente($db);

// ── Guardar ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_consulta'])) {
    if ($psicologiaModel->guardar($_POST)) {
        $message = "Consulta de Psicología guardada exitosamente.";
        $message_type = "success";
    } else {
        $message = "Error al guardar la consulta.";
        $message_type = "danger";
    }
}

// ── Obtener datos ─────────────────────────────────────────────────────────────
$datos = [];
$visita = null;
$paciente = null;

if ($id_visita) {
    $datos = $psicologiaModel->obtenerPorVisita($id_visita) ?: [];
    $visita = $visitaModel->obtenerPorId($id_visita);
    if ($visita)
        $id_paciente = $visita['id_paciente'];
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
function escalaRadio($name, $opciones, $valor_actual)
{
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

$escala_beck = ['Leve', 'Moderada', 'Severa', 'N/A'];
$escala_siempre = ['Siempre', 'Casi Siempre', 'Nunca', 'Algunas Veces', 'N/A'];
?>

<style>
    :root {
        --psi-purple: #6f42c1;
        --psi-light: #f8f5ff;
        --psi-border: #d8c8f0;
    }

    body {
        background-color: #f5f7fb;
    }

    .card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
        margin-bottom: 1.5rem;
    }

    .section-title {
        color: var(--psi-purple);
        border-bottom: 2px solid var(--psi-border);
        padding-bottom: .5rem;
        margin-bottom: 1rem;
        font-weight: 700;
    }

    .nav-tabs .nav-link {
        color: #666;
        font-weight: 600;
        border: none;
        padding: .9rem 1.2rem;
        border-bottom: 3px solid transparent;
    }

    .nav-tabs .nav-link.active {
        color: var(--psi-purple);
        background: transparent;
        border-bottom-color: var(--psi-purple);
    }

    .visita-header {
        background: var(--psi-purple);
        color: #fff;
        border-radius: 8px;
        padding: .6rem 1rem;
        margin-bottom: 1rem;
    }

    .eval-table th {
        background: var(--psi-light);
        font-size: .85rem;
    }

    .eval-table td {
        vertical-align: middle;
    }
</style>

<div class="container-fluid py-4">

    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0"><i class="bi bi-brain text-purple" style="color:var(--psi-purple)"></i> Psicología Clínica
            </h2>
            <?php if ($paciente): ?>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a
                                href="<?= PROJECT_PATH ?>/app/views/pacientes/detalle.php?id=<?= $paciente['id_paciente'] ?>">
                                <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?>
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
            <i class="bi <?= $message_type == 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle' ?> me-2"></i>
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
                        <p class="text-muted mb-4">Busque y seleccione al paciente para su registro de Psicología Clínica.
                        </p>
                        <input type="text" class="form-control form-control-lg mb-3" id="patientSelectionInput"
                            placeholder="Buscar por nombre o número de expediente..."
                            style="border-color:var(--psi-purple)">
                        <div id="patientSearchResults" class="list-group list-group-flush shadow-sm rounded-3 border"
                            style="max-height:400px;overflow-y:auto;">
                            <?php foreach ($lista_pacientes as $p):
                                $searchText = strtolower($p['nombre_completo'] . ' ' . $p['numero_expediente']);
                                ?>
                                <div class="list-group-item p-3 border-bottom patient-item"
                                    data-search="<?= htmlspecialchars($searchText) ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1 fw-bold"><?= htmlspecialchars($p['nombre_completo']) ?></h6>
                                            <small class="text-muted"><?= htmlspecialchars($p['numero_expediente']) ?></small>
                                        </div>
                                        <?php if ($p['ultima_visita_id']): ?>
                                            <a href="psicologia.php?id_visita=<?= $p['ultima_visita_id'] ?>"
                                                class="btn btn-sm rounded-pill"
                                                style="border:1px solid var(--psi-purple);color:var(--psi-purple)">
                                                <i class="bi bi-brain"></i> Abrir Última
                                                (<?= date('d/m/y', strtotime($p['ultima_visita_fecha'])) ?>)
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
            <input type="hidden" name="id_paciente" value="<?= $id_paciente ?>">
            <input type="hidden" name="id_visita" value="<?= $id_visita ?>">
            <input type="hidden" name="save_consulta" value="1">

            <!-- Cuestionario Escala de Beck -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white p-4 pb-0 border-0">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold text-uppercase" style="color:var(--psi-purple); letter-spacing:1px;">Escala de
                            Beck</h4>
                        <p class="text-muted mb-0">Se trata de una escala <strong>autoadministrada</strong></p>
                        <small class="text-muted d-block mt-2">Instrucciones para el paciente: Por favor, señale si las
                            siguientes afirmaciones se ajustan o no a su situación personal. Las opciones de respuestas son
                            verdadero (V) o falso (F).</small>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0 align-middle">
                            <thead style="background-color: var(--psi-light);">
                                <tr>
                                    <th class="text-center" style="width: 5%">#</th>
                                    <th style="width: 75%">Afirmación</th>
                                    <th class="text-center" style="width: 10%">V</th>
                                    <th class="text-center" style="width: 10%">F</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $preguntas = [
                                    1 => 'Espero el futuro con esperanza y entusiasmo',
                                    2 => 'Puedo darme por vencido, renunciar, ya que no puedo hacer mejor las cosas por mí mismo',
                                    3 => 'Cuando las cosas van mal me alivia saber que las cosas no pueden permanecer tiempo así',
                                    4 => 'No puedo imaginar como será mi vida dentro de 10 años',
                                    5 => 'Tengo bastante tiempo para llevar a cabo las cosas que quisiera poder hacer',
                                    6 => 'En el futuro, espero conseguir lo que me pueda interesar',
                                    7 => 'Mi futuro me parece oscuro',
                                    8 => 'Espero más cosas buenas de la vida que lo que la gente suele conseguir por término medio',
                                    9 => 'No logro hacer que las cosas cambien, y no existen razones para creer que pueda en el futuro',
                                    10 => 'Mis pasadas experiencias me han preparado bien para mi futuro',
                                    11 => 'Todo lo que puedo ver por delante de mí es más desagradable que agradable',
                                    12 => 'No espero conseguir lo que realmente deseo',
                                    13 => 'Cuando miro hacia el futuro, espero que seré más feliz de lo que soy ahora',
                                    14 => 'Las cosas no marchan como yo quisiera',
                                    15 => 'Tengo una gran confianza en el futuro',
                                    16 => 'Nunca consigo lo que deseo, por lo que es absurdo desear cualquier cosa',
                                    17 => 'Es muy improbable que pueda lograr una satisfacción real en el futuro',
                                    18 => 'El futuro me parece vago e incierto',
                                    19 => 'Espero más bien épocas buenas que malas',
                                    20 => 'No merece la pena que intente conseguir algo que desee, porque probablemente no lo lograré'
                                ];

                                foreach ($preguntas as $num => $texto) {
                                    $campo = "q" . $num;
                                    $val = $datos[$campo] ?? '';
                                    ?>
                                    <tr>
                                        <td class="text-center fw-bold text-muted"><?= $num ?>.</td>
                                        <td><?= htmlspecialchars($texto) ?></td>
                                        <td class="text-center">
                                            <input class="form-check-input beck-radio border-secondary cursor-pointer"
                                                style="width: 1.25em; height: 1.25em;" type="radio" name="<?= $campo ?>"
                                                value="V" <?= $val === 'V' ? 'checked' : '' ?> data-q="<?= $num ?>">
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input beck-radio border-secondary cursor-pointer"
                                                style="width: 1.25em; height: 1.25em;" type="radio" name="<?= $campo ?>"
                                                value="F" <?= $val === 'F' ? 'checked' : '' ?> data-q="<?= $num ?>">
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                            <tfoot style="background-color: var(--psi-light);">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold fs-5 py-3" style="color:var(--psi-purple)">
                                        PUNTUACIÓN TOTAL</td>
                                    <td colspan="2" class="text-center align-middle py-3">
                                        <input type="number" id="puntuacion_total" name="puntuacion_total"
                                            class="form-control text-center fw-bold fs-4 mx-auto shadow-sm"
                                            style="width: 100px; color:var(--psi-purple); background:#fff;"
                                            value="<?= htmlspecialchars($datos['puntuacion_total'] ?? 0) ?>" readonly>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div><!-- end Beck card -->

        <!-- /Beck card -->

                <!-- ═══════════════════════════════════════════════════════════════
                 INVENTARIO DE DEPRESIÓN DE BECK (BDI-2)
            ═══════════════════════════════════════════════════════════════ -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-4 pb-0 border-0">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-uppercase" style="color:var(--psi-purple); letter-spacing:1px;">Inventario de Depresión de Beck (BDI-2)</h4>
                            <small class="text-muted d-block mt-1">Instrucciones: Elija el enunciado de cada grupo que mejor describe cómo se ha sentido las <strong>últimas dos semanas</strong>, incluyendo el día de hoy.</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0 align-middle">
                                <thead style="background-color: var(--psi-light);">
                                    <tr>
                                        <th class="text-center" style="width:5%">#</th>
                                        <th style="width:50%">Grupo</th>
                                        <th class="text-center" style="width:10%">0</th>
                                        <th class="text-center" style="width:10%">1</th>
                                        <th class="text-center" style="width:10%">2</th>
                                        <th class="text-center" style="width:10%">3</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $bdi_items = [
                                        1 => ['label' => 'Tristeza', 'opts' => ['No me siento triste.', 'Me siento triste gran parte del tiempo.', 'Me siento triste todo el tiempo.', 'Me siento tan triste o soy tan infeliz que no puedo soportarlo.']],
                                        2 => ['label' => 'Pesimismo', 'opts' => ['No estoy desalentado respecto del mi futuro.', 'Me siento más desalentado respecto de mi futuro que lo que solía estarlo.', 'No espero que las cosas funcionen para mí.', 'Siento que no hay esperanza para mi futuro y que sólo puede empeorar.']],
                                        3 => ['label' => 'Fracaso', 'opts' => ['No me siento como un fracasado.', 'He fracasado más de lo que hubiera debido.', 'Cuando miro hacia atrás, veo muchos fracasos.', 'Siento que como persona soy un fracaso total.']],
                                        4 => ['label' => 'Pérdida de Placer', 'opts' => ['Obtengo tanto placer como siempre por las cosas de las que disfruto.', 'No disfruto tanto de las cosas como solía hacerlo.', 'Obtengo muy poco placer de las cosas que solía disfrutar.', 'No puedo obtener ningún placer de las cosas de las que solía disfrutar.']],
                                        5 => ['label' => 'Sentimientos de Culpa', 'opts' => ['No me siento particularmente culpable.', 'Me siento culpable respecto de varias cosas que he hecho o que debería haber hecho.', 'Me siento bastante culpable la mayor parte del tiempo.', 'Me siento culpable todo el tiempo.']],
                                        6 => ['label' => 'Sentimientos de Castigo', 'opts' => ['No siento que esté siendo castigado.', 'Siento que tal vez pueda ser castigado.', 'Espero ser castigado.', 'Siento que estoy siendo castigado.']],
                                        7 => ['label' => 'Disconformidad con uno mismo', 'opts' => ['Siento acerca de mí mismo lo mismo que siempre.', 'He perdido la confianza en mí mismo.', 'Estoy decepcionado conmigo mismo.', 'No me gusto a mí mismo.']],
                                        8 => ['label' => 'Autocrítica', 'opts' => ['No me critico ni me culpo más de lo habitual.', 'Estoy más crítico conmigo mismo de lo que solía estarlo.', 'Me critico a mí mismo por todos mis errores.', 'Me culpo a mí mismo por todo lo malo que sucede.']],
                                        9 => ['label' => 'Pensamientos o Deseos Suicidas', 'opts' => ['No tengo ningún pensamiento de matarme.', 'He tenido pensamientos de matarme, pero no lo haría.', 'Querría matarme.', 'Me mataría si tuviera la oportunidad de hacerlo.']],
                                        10 => ['label' => 'Llanto', 'opts' => ['No lloro más de lo que solía hacerlo.', 'Lloro más de lo que solía hacerlo.', 'Lloro por cualquier pequeñez.', 'Siento ganas de llorar pero no puedo.']],
                                        11 => ['label' => 'Agitación', 'opts' => ['No estoy más inquieto o tenso que lo habitual.', 'Me siento más inquieto o tenso que lo habitual.', 'Estoy tan inquieto o agitado que me es difícil quedarme quieto.', 'Estoy tan inquieto o agitado que tengo que estar siempre en movimiento o haciendo algo.']],
                                        12 => ['label' => 'Pérdida de Interés', 'opts' => ['No he perdido el interés en otras actividades o personas.', 'Estoy menos interesado que antes en otras personas o cosas.', 'He perdido casi todo el interés en otras personas o cosas.', 'Me es difícil interesarme por algo.']],
                                        13 => ['label' => 'Indecisión', 'opts' => ['Tomo mis propias decisiones tan bien como siempre.', 'Me resulta más difícil que de costumbre tomar decisiones.', 'Encuentro mucha más dificultad que antes para tomar decisiones.', 'Tengo problemas para tomar cualquier decisión.']],
                                        14 => ['label' => 'Desvalorización', 'opts' => ['No siento que yo no sea valioso.', 'No me considero a mí mismo tan valioso y útil como solía considerarme.', 'Me siento menos valioso cuando me comparo con otros.', 'Siento que no valgo nada.']],
                                        15 => ['label' => 'Pérdida de Energía', 'opts' => ['Tengo tanta energía como siempre.', 'Tengo menos energía que la que solía tener.', 'No tengo suficiente energía para hacer demasiado.', 'No tengo suficiente energía para hacer nada.']],
                                        16 => ['label' => 'Cambios en los Hábitos de Sueño', 'opts' => ['No he experimentado ningún cambio en mis hábitos de sueño.', 'Duermo un poco más/menos que lo habitual.', 'Duermo mucho más/menos que lo habitual.', 'Me despierto 1-2 horas más temprano y no puedo volver a dormirme.']],
                                        17 => ['label' => 'Irritabilidad', 'opts' => ['No estoy tan irritable que lo habitual.', 'Estoy más irritable que lo habitual.', 'Estoy mucho más irritable que lo habitual.', 'Estoy irritable todo el tiempo.']],
                                        18 => ['label' => 'Cambios en el Apetito', 'opts' => ['No he experimentado ningún cambio en mi apetito.', 'Mi apetito es un poco menor/mayor que lo habitual.', 'Mi apetito es mucho menor/mayor que antes.', 'No tengo apetito en absoluto / Quiero comer todo el día.']],
                                        19 => ['label' => 'Dificultad de Concentración', 'opts' => ['Puedo concentrarme tan bien como siempre.', 'No puedo concentrarme tan bien como habitualmente.', 'Me es difícil mantener la mente en algo por mucho tiempo.', 'Encuentro que no puedo concentrarme en nada.']],
                                        20 => ['label' => 'Cansancio o Fatiga', 'opts' => ['No estoy más cansado o fatigado que lo habitual.', 'Me fatigo o me canso más fácilmente que lo habitual.', 'Estoy demasiado fatigado o cansado para hacer muchas de las cosas que solía hacer.', 'Estoy demasiado fatigado o cansado para hacer la mayoría de las cosas que solía hacer.']],
                                        21 => ['label' => 'Pérdida de Interés en el Sexo', 'opts' => ['No he notado ningún cambio reciente en mi interés por el sexo.', 'Estoy menos interesado en el sexo de lo que solía estarlo.', 'Estoy mucho menos interesado en el sexo.', 'He perdido completamente el interés en el sexo.']],
                                    ];
                                    foreach ($bdi_items as $num => $item):
                                        $campo = "bdi_q{$num}";
                                        $val = $datos[$campo] ?? null;
                                        ?>
                                        <tr>
                                            <td class="text-center fw-bold text-muted"><?= $num ?>.</td>
                                            <td><strong><?= $item['label'] ?></strong><br>
                                                <small class="text-muted"><?= implode(' / ', $item['opts']) ?></small>
                                            </td>
                                            <?php for ($v = 0; $v <= 3; $v++): ?>
                                                <td class="text-center">
                                                    <input class="form-check-input" style="width:1.25em;height:1.25em;" type="radio"
                                                           name="<?= $campo ?>" value="<?= $v ?>" <?= ((string) $val === (string) $v) ? 'checked' : '' ?>>
                                                    <div class="small text-muted"><?= $v ?></div>
                                                </td>
                                            <?php endfor; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot style="background-color: var(--psi-light);">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold fs-5 py-3" style="color:var(--psi-purple)">PUNTAJE TOTAL</td>
                                        <td colspan="2" class="text-center align-middle py-3">
                                            <input type="number" id="bdi_total" name="bdi_total"
                                                   class="form-control text-center fw-bold fs-4 mx-auto shadow-sm"
                                                   style="width:100px; background:#fff;"
                                                   value="<?= htmlspecialchars($datos['bdi_total'] ?? 0) ?>" readonly>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white p-3">
                        <small class="text-muted"><strong>Interpretación:</strong>
                            <span class="text-success">0-13: Mínimo</span> &nbsp;|&nbsp;
                            <span class="text-warning">14-19: Leve</span> &nbsp;|&nbsp;
                            <span class="text-danger">20-28: Moderado</span> &nbsp;|&nbsp;
                            <span style="color:#6f42c1">29-63: Grave</span>
                        </small>
                    </div>
                </div><!-- /BDI-2 card -->

                <!-- ═══════════════════════════════════════════════════════════════
                 INVENTARIO DE ANSIEDAD DE BECK (BAI)
            ═══════════════════════════════════════════════════════════════ -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white p-4 pb-0 border-0">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold text-uppercase" style="color:var(--psi-purple); letter-spacing:1px;">Inventario de Ansiedad de Beck (BAI)</h4>
                            <small class="text-muted d-block mt-1">Instrucciones: Indique cuánto le ha afectado cada síntoma durante la <strong>última semana incluyendo hoy</strong>.</small>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0 align-middle">
                                <thead style="background-color: var(--psi-light);">
                                    <tr>
                                        <th class="text-center" style="width:5%">#</th>
                                        <th style="width:45%">Síntoma</th>
                                        <th class="text-center" style="width:12%">En absoluto<br><small>(0)</small></th>
                                        <th class="text-center" style="width:12%">Levemente<br><small>(1)</small></th>
                                        <th class="text-center" style="width:12%">Moderadamente<br><small>(2)</small></th>
                                        <th class="text-center" style="width:14%">Severamente<br><small>(3)</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $bai_items = [
                                        1 => 'Torpe o entumecido.',
                                        2 => 'Acalorado.',
                                        3 => 'Con temblor en las piernas.',
                                        4 => 'Incapaz de relajarse.',
                                        5 => 'Con temor a que ocurra lo peor.',
                                        6 => 'Mareado, o que se le va la cabeza.',
                                        7 => 'Con latidos del corazón fuertes y acelerados.',
                                        8 => 'Inestable.',
                                        9 => 'Atemorizado o asustado.',
                                        10 => 'Nervioso.',
                                        11 => 'Con sensación de bloqueo.',
                                        12 => 'Con temblores en las manos.',
                                        13 => 'Inquieto, Inseguro.',
                                        14 => 'Con miedo a perder el control.',
                                        15 => 'Con sensación de ahogo.',
                                        16 => 'Con temor a morir.',
                                        17 => 'Con miedo.',
                                        18 => 'Con problemas digestivos.',
                                        19 => 'Con desvanecimientos.',
                                        20 => 'Con rubor facial.',
                                        21 => 'Con sudores, fríos o calientes.',
                                    ];
                                    $bai_labels = ['En absoluto', 'Levemente', 'Moderadamente', 'Severamente'];
                                    foreach ($bai_items as $num => $sintoma):
                                        $campo = "bai_q{$num}";
                                        $val = $datos[$campo] ?? null;
                                        ?>
                                        <tr>
                                            <td class="text-center fw-bold text-muted"><?= $num ?>.</td>
                                            <td><?= htmlspecialchars($sintoma) ?></td>
                                            <?php for ($v = 0; $v <= 3; $v++): ?>
                                                <td class="text-center">
                                                    <input class="form-check-input" style="width:1.25em;height:1.25em;" type="radio"
                                                           name="<?= $campo ?>" value="<?= $v ?>" <?= ((string) $val === (string) $v) ? 'checked' : '' ?>>
                                                </td>
                                            <?php endfor; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot style="background-color: var(--psi-light);">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold fs-5 py-3" style="color:var(--psi-purple)">PUNTAJE TOTAL</td>
                                        <td colspan="2" class="text-center align-middle py-3">
                                            <input type="number" id="bai_total" name="bai_total"
                                                   class="form-control text-center fw-bold fs-4 mx-auto shadow-sm"
                                                   style="width:100px; background:#fff;"
                                                   value="<?= htmlspecialchars($datos['bai_total'] ?? 0) ?>" readonly>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white p-3">
                        <small class="text-muted"><strong>Interpretación:</strong>
                            <span class="text-success">0-21: Ansiedad Baja</span> &nbsp;|&nbsp;
                            <span class="text-warning">22-35: Ansiedad Moderada</span> &nbsp;|&nbsp;
                            <span class="text-danger">36+: Ansiedad Grave</span>
                        </small>
                    </div>
                </div><!-- /BAI card -->

                <!-- Botón guardar global -->
                <div class="d-flex justify-content-end gap-3 mb-4">
                    <a href="<?= PROJECT_PATH ?>/index.php" class="btn btn-light px-4 border text-muted">Cancelar</a>
                    <button type="submit" class="btn btn-success px-4" style="background-color:var(--psi-purple); border-color:var(--psi-purple);">
                        <i class="bi bi-save me-2"></i>Guardar Consulta Psicológica
                    </button>
                </div>

            </form>
    <?php endif; ?>

</div><!-- /container -->

<script>
    document.addEventListener('DOMContentLoaded', function () {

        /* ══════════ ESCALA DE BECK (Desesperanza) ══════════ */
        const beckRadios = document.querySelectorAll('.beck-radio');
        const beckTotal = document.getElementById('puntuacion_total');
        const trueAddsOne  = [2, 4, 7, 9, 11, 12, 14, 16, 17, 18, 20];
        const falseAddsOne = [1, 3, 5, 6, 8, 10, 13, 15, 19];

        function calcularBeck() {
            let total = 0;
            beckRadios.forEach(r => {
                if (r.checked) {
                    const q = parseInt(r.getAttribute('data-q'));
                    if (r.value === 'V' && trueAddsOne.includes(q))  total++;
                    if (r.value === 'F' && falseAddsOne.includes(q)) total++;
                }
            });
            beckTotal.value = total;
        }
        beckRadios.forEach(r => r.addEventListener('change', calcularBeck));
        calcularBeck();

        /* ══════════ BDI-2 ══════════ */
        function calcularBdi() {
            let total = 0;
            document.querySelectorAll('input[name^="bdi_q"]:checked').forEach(r => {
                total += parseInt(r.value);
            });
            document.getElementById('bdi_total').value = total;
            // Color según interpretación
            const el = document.getElementById('bdi_total');
            el.style.color = total <= 13 ? '#198754' : total <= 19 ? '#fd7e14' : total <= 28 ? '#dc3545' : '#6f42c1';
        }
        document.querySelectorAll('input[name^="bdi_q"]').forEach(r => r.addEventListener('change', calcularBdi));
        calcularBdi();

        /* ══════════ BAI ══════════ */
        function calcularBai() {
            let total = 0;
            document.querySelectorAll('input[name^="bai_q"]:checked').forEach(r => {
                total += parseInt(r.value);
            });
            document.getElementById('bai_total').value = total;
            const el = document.getElementById('bai_total');
            el.style.color = total <= 21 ? '#198754' : total <= 35 ? '#fd7e14' : '#dc3545';
        }
        document.querySelectorAll('input[name^="bai_q"]').forEach(r => r.addEventListener('change', calcularBai));
        calcularBai();

        /* ══════════ Buscador de pacientes ══════════ */
        const inp = document.getElementById('patientSelectionInput');
        if (inp) {
            inp.addEventListener('input', function () {
                const search = this.value.toLowerCase().trim();
                document.querySelectorAll('#patientSearchResults .patient-item').forEach(item => {
                    const text = item.getAttribute('data-search') || '';
                    item.classList.toggle('d-none', text.indexOf(search) === -1);
                });
            });
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>