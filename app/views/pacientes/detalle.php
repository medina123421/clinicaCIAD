<?php
/**
 * Detalle de Paciente
 * Vista completa del historial del paciente
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Paciente.php';

$page_title = 'Detalle de Paciente';

if (!isset($_GET['id'])) {
    header('Location: lista.php');
    exit();
}

$id_paciente = (int) $_GET['id'];

$database = new Database();
$db = $database->getConnection();
$paciente_model = new Paciente($db);

$paciente = $paciente_model->obtenerPorId($id_paciente);

if (!$paciente) {
    header('Location: lista.php?error=no_encontrado');
    exit();
}

// Obtener última visita
$query = "SELECT v.*, 
          CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_doctor,
          dc.peso, dc.talla, dc.imc, dc.presion_arterial_sistolica, dc.presion_arterial_diastolica
          FROM visitas v
          LEFT JOIN usuarios u ON v.id_doctor = u.id_usuario
          LEFT JOIN datos_clinicos dc ON v.id_visita = dc.id_visita
          WHERE v.id_paciente = :id
          ORDER BY v.fecha_visita DESC
          LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id_paciente);
$stmt->execute();
$ultima_visita = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener último análisis de glucosa
$query = "SELECT ag.*, v.fecha_visita
          FROM analisis_glucosa ag
          JOIN visitas v ON ag.id_visita = v.id_visita
          WHERE v.id_paciente = :id
          ORDER BY ag.fecha_analisis DESC
          LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $id_paciente);
$stmt->execute();
$ultimo_analisis = $stmt->fetch(PDO::FETCH_ASSOC);


// Helper semáforo
function getSemaforoBadge($interpretacion)
{
    if ($interpretacion === 'Normal')
        return '<span class="badge bg-success">🟢 Normal</span>';
    if ($interpretacion === 'Precaución')
        return '<span class="badge bg-warning text-dark">🟡 Precaución</span>';
    if ($interpretacion === 'Alerta')
        return '<span class="badge bg-danger">🔴 Alerta</span>';
    return '';
}

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-7">
        <h2><i class="bi bi-person-vcard-fill"></i> Detalle del Paciente</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php">Pacientes</a></li>
                <li class="breadcrumb-item active">
                    <?= htmlspecialchars($paciente['numero_expediente']) ?>
                </li>
            </ol>
        </nav>
    </div>
    <div class="col-md-5 d-flex flex-wrap justify-content-end align-items-start gap-2">
        <a href="../socioeconomico/editar.php?id=<?= $id_paciente ?>" class="btn btn-info text-white">
            <i class="bi bi-file-earmark-person"></i> Estudio Socioeconómico
        </a>
        <a href="editar.php?id=<?= $id_paciente ?>" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="../visitas/nueva.php?paciente=<?= $id_paciente ?>" class="btn btn-success">
            <i class="bi bi-calendar-plus"></i> Nueva Visita
        </a>
    </div>
</div>

<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'creado'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> Paciente creado exitosamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Información Personal -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-person-vcard"></i> Información Personal
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Expediente:</th>
                        <td><strong>
                                <?= htmlspecialchars($paciente['numero_expediente']) ?>
                            </strong></td>
                    </tr>
                    <tr>
                        <th>Protocolo:</th>
                        <td>
                            <?php if (($paciente['protocolo'] ?? 'Diabético') === 'Diabético'): ?>
                                <span class="badge bg-danger">Diabético</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">Prediabético</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Nombre Completo:</th>
                        <td>
                            <?= htmlspecialchars($paciente['nombre'] . ' ' . $paciente['apellido_paterno'] . ' ' . ($paciente['apellido_materno'] ?? '')) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Fecha de Nacimiento:</th>
                        <td>
                            <?= date('d/m/Y', strtotime($paciente['fecha_nacimiento'])) ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Edad:</th>
                        <td>
                            <?= $paciente['edad'] ?> años
                        </td>
                    </tr>
                    <tr>
                        <th>Sexo:</th>
                        <td>
                            <?= $paciente['sexo'] === 'M' ? 'Masculino' : 'Femenino' ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Tipo de Sangre:</th>
                        <td>
                            <?= $paciente['tipo_sangre'] ?? 'No especificado' ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Información de Contacto -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-telephone"></i> Información de Contacto
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Teléfono:</th>
                        <td>
                            <?= htmlspecialchars($paciente['telefono'] ?? 'No especificado') ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>
                            <?= htmlspecialchars($paciente['email'] ?? 'No especificado') ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Dirección:</th>
                        <td>
                            <?= htmlspecialchars($paciente['direccion'] ?? 'No especificada') ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-info text-white">
                <i class="bi bi-person-exclamation"></i> Contacto de Emergencia
            </div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <th width="40%">Nombre:</th>
                        <td><?= htmlspecialchars($paciente['nombre_emergencia'] ?? 'No registrado') ?></td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td><?= htmlspecialchars($paciente['telefono_emergencia'] ?? 'No registrado') ?></td>
                    </tr>
                    <tr>
                        <th>Parentesco:</th>
                        <td><?= htmlspecialchars($paciente['parentesco_emergencia'] ?? 'No registrado') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Última Visita -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-calendar-check"></i> Última Visita
            </div>
            <div class="card-body">
                <?php if ($ultima_visita): ?>
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Fecha:</th>
                            <td>
                                <?= date('d/m/Y', strtotime($ultima_visita['fecha_visita'])) ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Doctor:</th>
                            <td>
                                <?= htmlspecialchars($ultima_visita['nombre_doctor']) ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Tipo:</th>
                            <td>
                                <?= htmlspecialchars($ultima_visita['tipo_visita']) ?>
                            </td>
                        </tr>
                        <?php if ($ultima_visita['peso']): ?>
                            <tr>
                                <th>Peso:</th>
                                <td>
                                    <?= $ultima_visita['peso'] ?> kg
                                </td>
                            </tr>
                            <tr>
                                <th>IMC:</th>
                                <td>
                                    <?= number_format($ultima_visita['imc'], 2) ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Presión Arterial:</th>
                                <td>
                                    <?= $ultima_visita['presion_arterial_sistolica'] ?>/
                                    <?= $ultima_visita['presion_arterial_diastolica'] ?> mmHg
                                </td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td colspan="2" class="text-center pt-3">
                                <a href="../especialidades/medicina_interna.php?id_visita=<?= $ultima_visita['id_visita'] ?>"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-stethoscope"></i> Ver Consulta Medicina Interna
                                </a>
                                <a href="../especialidades/nutricion.php?id_visita=<?= $ultima_visita['id_visita'] ?>"
                                    class="btn btn-outline-success btn-sm mt-1">
                                    <i class="bi bi-apple"></i> Ver Consulta Nutrición
                                </a>
                                <a href="../especialidades/psicologia.php?id_visita=<?= $ultima_visita['id_visita'] ?>"
                                    class="btn btn-outline-secondary btn-sm mt-1"
                                    style="border-color:#6f42c1;color:#6f42c1">
                                    <i class="bi bi-brain"></i> Ver Consulta Psicología
                                </a>
                            </td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No hay visitas registradas.</p>
                    <div class="text-center mt-3">
                        <a href="<?= $project_folder ?>/app/views/visitas/nueva.php?paciente=<?= $id_paciente ?>"
                            class="btn btn-primary btn-sm">
                            <i class="bi bi-calendar-plus"></i> Registrar Primera Visita
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Último Análisis de Glucosa -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-clipboard2-pulse"></i> Último Análisis de Glucosa
            </div>
            <div class="card-body">
                <?php if ($ultimo_analisis): ?>
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Fecha:</th>
                            <td>
                                <?= date('d/m/Y', strtotime($ultimo_analisis['fecha_analisis'])) ?>
                            </td>
                        </tr>
                        <?php if ($ultimo_analisis['glucosa_ayunas']): ?>
                            <tr>
                                <th>Glucosa en Ayunas:</th>
                                <td>
                                    <?= $ultimo_analisis['glucosa_ayunas'] ?> mg/dL
                                    <?php if ($ultimo_analisis['interpretacion_glucosa_ayunas']): ?>
                                        <br>
                                        <?= getSemaforoBadge($ultimo_analisis['interpretacion_glucosa_ayunas']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($ultimo_analisis['hemoglobina_glicosilada']): ?>
                            <tr>
                                <th>HbA1c:</th>
                                <td>
                                    <?= $ultimo_analisis['hemoglobina_glicosilada'] ?>%
                                    <?php if ($ultimo_analisis['interpretacion_hba1c']): ?>
                                        <br>
                                        <?= getSemaforoBadge($ultimo_analisis['interpretacion_hba1c']) ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No hay análisis registrados.</p>
                    <div class="text-center mt-3">
                        <a href="<?= $project_folder ?>/app/views/analisis/glucosa.php?paciente=<?= $id_paciente ?>"
                            class="btn btn-primary btn-sm">
                            <i class="bi bi-clipboard2-plus"></i> Registrar Análisis
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Información Médica -->
    <?php if ($paciente['alergias']): ?>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i> Alergias
                </div>
                <div class="card-body">
                    <p class="mb-0">
                        <?= nl2br(htmlspecialchars($paciente['alergias'])) ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Función para obtener badge de semáforo
    function getSemaforoBadge(interpretacion) {
        const badges = {
            'Normal': '<span class="badge badge-semaforo badge-normal">🟢 Normal</span>',
            'Precaución': '<span class="badge badge-semaforo badge-precaucion">🟡 Precaución</span>',
            'Alerta': '<span class="badge badge-semaforo badge-alerta">🔴 Alerta</span>'
        };
        return badges[interpretacion] || '';
    }
</script>

<?php include '../../includes/footer.php'; ?>