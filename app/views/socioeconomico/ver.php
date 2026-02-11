<?php
/**
 * Vista de Impresión de Estudio Socioeconómico
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Paciente.php';
require_once '../../models/EstudioSocioeconomico.php';

$page_title = 'Impresión Estudio Socioeconómico';

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
$estudio = $estudio_model->obtenerPorPaciente($id_paciente);

if (!$paciente || !$estudio) {
    die("No se encontró el estudio para este paciente.");
}

// Función helper para checkboxes
function checkMark($val, $target)
{
    if (is_array($target)) {
        return in_array($val, $target) ? '<b>[X]</b>' : '[_]';
    }
    return $val == $target ? '<b>[X]</b>' : '[_]';
}

function boolMark($val)
{
    return $val ? '<b>SÍ</b>' : 'NO';
}

include '../../includes/header.php';
?>

<style type="text/css" media="print">
    @page {
        size: letter;
        margin: 2cm;
    }

    body {
        font-family: Arial, sans-serif;
        font-size: 12px;
    }

    .no-print {
        display: none !important;
    }

    .card {
        border: none !important;
    }

    .card-header {
        background-color: #f0f0f0 !important;
        border-bottom: 1px solid #000 !important;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #000 !important;
    }
</style>

<div class="row mb-4 no-print">
    <div class="col-md-12 d-flex justify-content-between align-items-center">
        <h2>Detalle de Estudio Socioeconómico</h2>
        <div>
            <button onclick="window.print()" class="btn btn-primary"><i class="bi bi-printer"></i> Imprimir</button>
            <a href="editar.php?id=<?= $id_paciente ?>" class="btn btn-warning"><i class="bi bi-pencil"></i> Editar</a>
            <a href="../pacientes/detalle.php?id=<?= $id_paciente ?>" class="btn btn-secondary"><i
                    class="bi bi-arrow-return-left"></i> Volver</a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header text-center">
        <h4>ESTUDIO SOCIOECONÓMICO</h4>
        <p class="mb-0">Fecha de Estudio:
            <?= date('d/m/Y', strtotime($estudio['fecha_estudio'])) ?>
        </p>
    </div>
    <div class="card-body">

        <!-- I. DATOS GENERALES -->
        <h6 class="border-bottom pb-2 mb-3 mt-2 fw-bold">I. DATOS GENERALES DE IDENTIFICACIÓN</h6>
        <div class="row mb-2">
            <div class="col-8"><strong>Nombre:</strong>
                <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno']) ?>
            </div>
            <div class="col-2"><strong>Edad:</strong>
                <?= $paciente['edad'] ?>
            </div>
            <div class="col-2"><strong>Sexo:</strong>
                <?= $paciente['sexo'] ?>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-6"><strong>Estado Civil:</strong>
                <?= $estudio['estado_civil'] ?>
            </div>
            <div class="col-6"><strong>Escolaridad:</strong>
                <?= $estudio['escolaridad'] ?>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-6"><strong>Ocupación:</strong>
                <?= $estudio['ocupacion'] ?>
            </div>
            <div class="col-6"><strong>Religión:</strong>
                <?= $estudio['religion'] ?>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-12"><strong>Domicilio:</strong>
                <?= htmlspecialchars($paciente['direccion'] . ', ' . $paciente['ciudad']) ?>
            </div>
        </div>

        <!-- II. ESTRUCTURA FAMILIAR -->
        <h6 class="border-bottom pb-2 mb-3 mt-4 fw-bold">II. ESTRUCTURA Y DINÁMICA FAMILIAR</h6>
        <div class="mb-2">
            <strong>Jefe de Familia:</strong>
            <?= boolMark($estudio['es_jefe_familia']) ?> &nbsp;|&nbsp;
            <strong>Relaciones:</strong>
            <?= $estudio['relaciones_familiares'] ?> &nbsp;|&nbsp;
            <strong>Apoyo Familiar:</strong>
            <?= $estudio['apoyo_familiar'] ?>
        </div>
        <table class="table table-sm table-bordered mt-2">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Parentesco</th>
                    <th>Edad</th>
                    <th>Ocupación</th>
                    <th>Ingreso Mensual</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($estudio['familiares'] ?? []) as $fam): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($fam['nombre']) ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($fam['parentesco']) ?>
                        </td>
                        <td>
                            <?= $fam['edad'] ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($fam['ocupacion']) ?>
                        </td>
                        <td>$
                            <?= number_format($fam['ingreso_mensual'], 2) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($estudio['familiares'])): ?>
                    <tr>
                        <td colspan="5" class="text-center">Sin registros</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- III. VIVIENDA -->
        <h6 class="border-bottom pb-2 mb-3 mt-4 fw-bold">III. CONDICIONES DE LA VIVIENDA</h6>
        <div class="row mb-2">
            <div class="col-6"><strong>Tipo:</strong>
                <?= $estudio['tipo_vivienda'] ?>
            </div>
            <div class="col-6"><strong>No. Habitaciones:</strong>
                <?= $estudio['num_habitaciones'] ?>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-12"><strong>Material Predominante:</strong>
                <?= $estudio['material_vivienda'] ?>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-12">
                <strong>Servicios:</strong>
                <?= boolMark($estudio['servicio_agua']) ?> Agua |
                <?= boolMark($estudio['servicio_drenaje']) ?> Drenaje |
                <?= boolMark($estudio['servicio_electricidad']) ?> Luz |
                <?= boolMark($estudio['servicio_gas']) ?> Gas |
                <?= boolMark($estudio['servicio_internet']) ?> Internet
            </div>
        </div>

        <!-- IV. ECONOMÍA -->
        <h6 class="border-bottom pb-2 mb-3 mt-4 fw-bold">IV. SITUACIÓN ECONÓMICA</h6>
        <div class="row">
            <div class="col-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td><strong>Ingreso Familiar Total:</strong></td>
                        <td>$
                            <?= number_format($estudio['ingreso_mensual_familiar'], 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <hr class="m-1">
                        </td>
                    </tr>
                    <tr>
                        <td>Gasto Renta/Hipoteca:</td>
                        <td>$
                            <?= number_format($estudio['gasto_renta'], 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Gasto Alimentos:</td>
                        <td>$
                            <?= number_format($estudio['gasto_alimentos'], 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Gasto Transporte:</td>
                        <td>$
                            <?= number_format($estudio['gasto_transporte'], 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Gasto Servicios:</td>
                        <td>$
                            <?= number_format($estudio['gasto_servicios'], 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Gasto Tratamientos:</td>
                        <td>$
                            <?= number_format($estudio['gasto_tratamientos'], 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Gasto Total Estimado:</strong></td>
                        <td><strong>$
                                <?= number_format($estudio['gasto_total_estimado'], 2) ?>
                            </strong></td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <p><strong>Apoyo Social:</strong>
                    <?= boolMark($estudio['apoyo_social_check']) ?> (
                    <?= $estudio['apoyo_social_nombre'] ?>)
                </p>
                <p><strong>¿Ingreso cubre necesidades?:</strong>
                    <?= boolMark($estudio['ingreso_cubre_necesidades']) ?>
                </p>
            </div>
        </div>

        <!-- V. SALUD -->
        <h6 class="border-bottom pb-2 mb-3 mt-4 fw-bold">V. INFORMACIÓN DE SALUD (DIABETES)</h6>
        <div class="mb-2"><strong>Diagnóstico:</strong>
            <?= is_array($estudio['diagnostico_desc']) ? implode(', ', $estudio['diagnostico_desc']) : $estudio['diagnostico_desc'] ?>
            <?php if (!empty($estudio['diagnostico_desc_otro'])): ?>
                (<?= htmlspecialchars($estudio['diagnostico_desc_otro']) ?>)
            <?php endif; ?>
        </div>
        <div class="mb-2">
            <strong>Servicio Médico:</strong>
            <?= implode(', ', $estudio['servicio_medico'] ?? []) ?>
            <?php if (in_array('Otros', (array) ($estudio['servicio_medico'] ?? [])) && !empty($estudio['servicio_medico_otro'])): ?>
                (<?= htmlspecialchars($estudio['servicio_medico_otro']) ?>)
            <?php endif; ?>
        </div>
        <div class="mb-2">
            <strong>Tratamiento Actual:</strong>
            <?= $estudio['tiene_tratamiento'] ? 'SÍ' : 'NO' ?>
            <?php if ($estudio['tiene_tratamiento'] && !empty($estudio['tratamiento_detalle'])): ?>
                - <?= nl2br(htmlspecialchars($estudio['tratamiento_detalle'])) ?>
            <?php endif; ?>
        </div>
        <div class="row">
            <div class="col-4">¿Cubre costos medicamento?
                <?= boolMark($estudio['cubre_costos_medicamento']) ?>
            </div>
            <div class="col-4">¿Cuenta con glucómetro?
                <?= boolMark($estudio['cuenta_con_glucometro']) ?>
            </div>
            <div class="col-4">¿Dificultad dieta económica?
                <?= boolMark($estudio['dificultad_dieta_economica']) ?>
            </div>
        </div>

        <!-- VI. ALIMENTACIÓN -->
        <h6 class="border-bottom pb-2 mb-3 mt-4 fw-bold">VI. ALIMENTACIÓN (FRECUENCIA)</h6>
        <div class="row">
            <?php
            $freqs = $estudio['frecuencia_alimentos'] ?? [];
            foreach ($freqs as $food => $freq):
                if (!$freq)
                    continue;
                ?>
                <div class="col-3 mb-1">
                    <strong>
                        <?= ucfirst(str_replace('_', ' ', $food)) ?>:
                    </strong>
                    <?= $freq ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- VII. CONCLUSIONES -->
        <h6 class="border-bottom pb-2 mb-3 mt-4 fw-bold">VII. CONCLUSIONES Y CLASIFICACIÓN</h6>
        <div class="mb-3">
            <strong>Observaciones TS:</strong>
            <p>
                <?= nl2br(htmlspecialchars($estudio['observaciones_trabajo_social'])) ?>
            </p>
        </div>
        <div class="mb-3">
            <strong>Nivel Socioeconómico:</strong> <span class="badge bg-secondary text-white">
                <?= $estudio['nivel_socioeconomico'] ?>
            </span>
        </div>
        <div class="mb-3">
            <strong>Plan de Intervención:</strong>
            <p>
                <?= nl2br(htmlspecialchars($estudio['plan_intervencion'])) ?>
            </p>
        </div>

        <div class="row mt-5 text-center">
            <div class="col-6">
                <div class="border-top border-dark pt-2 mx-5">
                    <?= htmlspecialchars($estudio['nombre_entrevistado']) ?><br>
                    Nombre y Firma del Entrevistado
                </div>
            </div>
            <div class="col-6">
                <div class="border-top border-dark pt-2 mx-5">
                    <?= htmlspecialchars($estudio['nombre_trabajador_social']) ?><br>
                    Nombre y Firma del Trabajador Social
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>