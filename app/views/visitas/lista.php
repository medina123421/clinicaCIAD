<?php
/**
 * Lista de Visitas
 * Muestra el historial de visitas registradas
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Visita.php';
require_once '../../models/MedicinaInterna.php';

// Prevenir caché para que el estado de las citas se actualice siempre
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

$medicinaInternaModel = new MedicinaInterna((new Database())->getConnection());

$page_title = 'Historial de Visitas';

$database = new Database();
$db = $database->getConnection();
// Necesitamos un método para listar todas las visitas, agregaré uno genérico en el modelo o haré query directa aquí por simplicidad inicial
// Idealmente el modelo debería tener obtenerTodas().
// Check Visita.php content again... it only has obtenerPorPaciente().
// I will add a quick query here or update model. Updating model is better practice but for speed here query is fine, 
// actually let's just add a method to getting recent visits directly here for now to save a turn, or update the model request?
// Let's stick to inline query for the list wrapper for now, user asked for "interfaces".

// Query para obtener últimas visitas
$query = "SELECT v.*, 
          CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) as paciente_nombre,
          p.numero_expediente,
          CONCAT(u.nombre, ' ', u.apellido_paterno) as doctor_nombre
          FROM visitas v
          JOIN pacientes p ON v.id_paciente = p.id_paciente
          JOIN usuarios u ON v.id_doctor = u.id_usuario
          ORDER BY v.fecha_visita DESC
          LIMIT 15";
$stmt = $db->prepare($query);
$stmt->execute();
$visitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../../includes/header.php';
?>

<div class="row align-items-center mb-4">
    <div class="col-md-5">
        <h2><i class="bi bi-calendar-check text-primary"></i> Historial de Visitas</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= PROJECT_PATH ?>/index.php">Dashboard</a></li>
                <li class="breadcrumb-item active">Visitas</li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
            <input type="text" class="form-control border-start-0" id="visitaSearchInput" placeholder="Buscar por paciente o expediente...">
        </div>
    </div>
    <div class="col-md-3 text-end">
        <a href="<?= PROJECT_PATH ?>/app/views/visitas/nueva.php" class="btn btn-primary shadow-sm w-100">
            <i class="bi bi-plus-lg"></i> Nueva Visita
        </a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <?php if (count($visitas) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Paciente</th>
                            <th>Tipo de Atencion</th>
                            <th>Numero Visita</th>
                            <th>Estatus</th>
                            <th>Estudios para esta Cita</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visitas as $visita): ?>
                            <tr>
                                <td>
                                    <strong><?= date('d/m/Y', strtotime($visita['fecha_visita'])) ?></strong><br>
                                    <small class="text-muted"><?= date('H:i', strtotime($visita['fecha_visita'])) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($visita['paciente_nombre']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($visita['numero_expediente']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-multispecialist">Atención Multidisciplinaria</span>
                                    <br><small class="text-muted"><?= htmlspecialchars($visita['tipo_visita']) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($visita['numero_visita'])): ?>
                                        <span
                                            class="badge bg-info text-dark"><?= htmlspecialchars($visita['numero_visita']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $estatusColor = 'secondary';
                                    switch ($visita['estatus']) {
                                        case 'Programada':
                                            $estatusColor = 'primary';
                                            break;
                                        case 'En Curso':
                                            $estatusColor = 'warning';
                                            break;
                                        case 'Completada':
                                            $estatusColor = 'success';
                                            break;
                                        case 'Cancelada':
                                            $estatusColor = 'danger';
                                            break;
                                    }
                                    ?>
                                    <span
                                        class="badge bg-<?= $estatusColor ?>"><?= htmlspecialchars($visita['estatus']) ?></span>
                                </td>
                                <td>
                                    <?php
                                    $estudios = $medicinaInternaModel->obtenerEstudiosPendientes($visita['id_paciente']);
                                    if (!empty($estudios)): ?>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php foreach ($estudios as $e): ?>
                                                <span class="badge bg-light text-dark border small" style="font-size: 0.7rem;">
                                                    <i class="bi bi-file-earmark-medical text-primary"></i> <?= htmlspecialchars($e) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">Sin estudios pendientes</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($visita['estatus'] === 'Programada' || $visita['estatus'] === 'En Curso'): ?>
                                            <button type="button" class="btn btn-outline-warning"
                                                onclick="abrirModalReagendar(<?= $visita['id_visita'] ?>, '<?= $visita['fecha_visita'] ?>', '<?= htmlspecialchars($visita['paciente_nombre']) ?>')">
                                                <i class="bi bi-calendar-event"></i> Reagendar
                                            </button>
                                            <button type="button" class="btn btn-outline-danger"
                                                onclick="confirmarCancelacion(<?= $visita['id_visita'] ?>)">
                                                <i class="bi bi-x-circle"></i> Cancelar
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?= PROJECT_PATH ?>/app/views/especialidades/medicina_interna.php?id_visita=<?= $visita['id_visita'] ?>"
                                            class="btn btn-outline-primary">
                                            <i class="bi bi-stethoscope"></i> Consulta
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No hay visitas registradas recientemente.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Reagendar -->
<div class="modal fade" id="modalReagendar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="bi bi-calendar-event"></i> Reagendar Cita</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Paciente: <strong id="reagendarPacienteNombre"></strong></p>
                <form id="formReagendar">
                    <input type="hidden" id="reagendarIdVisita" name="id_visita">
                    <input type="hidden" name="accion" value="reagendar">
                    <div class="mb-3">
                        <label class="form-label">Nueva Fecha y Hora:</label>
                        <input type="datetime-local" class="form-control" name="nueva_fecha" id="reagendarNuevaFecha"
                            required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-warning" onclick="ejecutarReagendar()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cancelar -->
<div class="modal fade" id="modalCancelar" tabindex="-1" aria-labelledby="modalCancelarLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog">
        <div class="modal-content border-danger shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalCancelarLabel"><i class="bi bi-x-circle-fill"></i> Confirmar Cancelación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                <p class="mt-3 fs-5">¿Está seguro de que desea cancelar esta cita?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
                <input type="hidden" id="cancelarIdVisita">
            </div>
            <div class="modal-footer justify-content-center border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">No, mantener</button>
                <button type="button" class="btn btn-danger px-4" onclick="ejecutarCancelacion()">Sí, cancelar cita</button>
            </div>
        </div>
    </div>
</div>

<script>
    function abrirModalReagendar(id, fechaActual, nombre) {
        $('#reagendarIdVisita').val(id);
        $('#reagendarPacienteNombre').text(nombre);
        // Ajustar formato fecha para datetime-local (YYYY-MM-DDTHH:MM) y forzar la hora a las 07:00
        const fecha = new Date(fechaActual);
        const yyyy = fecha.getFullYear();
        const mm = String(fecha.getMonth() + 1).padStart(2, '0');
        const dd = String(fecha.getDate()).padStart(2, '0');

        // Forzamos la hora a las 07:00
        const formateada = `${yyyy}-${mm}-${dd}T07:00`;
        $('#reagendarNuevaFecha').val(formateada);
        $('#modalReagendar').modal('show');
    }

    function ejecutarReagendar() {
        $.ajax({
            url: '<?= PROJECT_PATH ?>/app/ajax/gestionar_visita.php',
            type: 'POST',
            data: $('#formReagendar').serialize(),
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            }
        });
    }

    function confirmarCancelacion(id) {
        // En lugar de window.confirm() que puede ser bloqueado, usamos un modal de Bootstrap
        $('#cancelarIdVisita').val(id);
        $('#modalCancelar').modal('show');
    }
    
    function ejecutarCancelacion() {
        const id = $('#cancelarIdVisita').val();
        $.ajax({
            url: '<?= PROJECT_PATH ?>/app/ajax/gestionar_visita.php',
            type: 'POST',
            data: { id_visita: id, accion: 'cancelar' },
            success: function (response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                    $('#modalCancelar').modal('hide');
                }
            },
            error: function() {
                alert('Ocurrió un error en el servidor. Intente nuevamente.');
                $('#modalCancelar').modal('hide');
            }
        });
    }

    $(document).ready(function () {
        const searchDebounce = debounce(function (searchTerm) {
            searchAjax(
                '<?= PROJECT_PATH ?>/app/ajax/buscar_visitas.php',
                searchTerm,
                'table tbody'
            );
        }, 500);

        $('#visitaSearchInput').on('keyup', function () {
            searchDebounce($(this).val());
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>