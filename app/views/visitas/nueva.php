<?php
/**
 * Nueva Visita
 * Formulario de registro de visita médica
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Visita.php';
require_once '../../models/Paciente.php';

$page_title = 'Registrar Nueva Visita';

$database = new Database();
$db = $database->getConnection();
$visita_model = new Visita($db);
$paciente_model = new Paciente($db);

$mensaje = '';
$tipo_mensaje = '';

// Obtener ID de paciente si viene por GET
$id_paciente_preseleccionado = $_GET['id_paciente'] ?? '';
$paciente_preseleccionado = null;

if ($id_paciente_preseleccionado) {
    $paciente_preseleccionado = $paciente_model->obtenerPorId($id_paciente_preseleccionado);
}

// Obtener lista de pacientes para el buscador
$pacientes = $paciente_model->obtenerTodos('', 1000); // Límite alto para el datalist

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_paciente = $_POST['id_paciente'] ?? '';

    // Validar que el paciente exista
    if (empty($id_paciente)) {
        $mensaje = 'Debe seleccionar un paciente registrado.';
        $tipo_mensaje = 'danger';
    } else {
        $datos = [
            'id_paciente' => $id_paciente,
            'id_doctor' => $_SESSION['usuario_id'],
            'fecha_visita' => $_POST['fecha_visita'] . ' ' . $_POST['hora_visita'],
            'tipo_visita' => $_POST['tipo_visita'],
            'numero_visita' => $_POST['numero_visita'] ?? null,
            'diagnostico' => trim($_POST['diagnostico'] ?? ''),
            'plan_tratamiento' => trim($_POST['plan_tratamiento'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'proxima_cita' => !empty($_POST['proxima_cita']) ? $_POST['proxima_cita'] : null,
            'estatus' => $_POST['estatus'] ?? 'Programada'
        ];

        try {
            if ($visita_model->crear($datos, $_SESSION['usuario_id'])) {
                // PRG Pattern: Redirect after POST
                $_SESSION['mensaje'] = 'Visita registrada correctamente.';
                $_SESSION['tipo_mensaje'] = 'success';
                header('Location: lista.php');
                exit();
            } else {
                $mensaje = 'Error al registrar la visita.';
                $tipo_mensaje = 'danger';
            }
        } catch (Exception $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
}

// Obtener mensajes de la sesión si existen (PRG)
if (isset($_SESSION['mensaje'])) {
    $mensaje = $_SESSION['mensaje'];
    $tipo_mensaje = $_SESSION['tipo_mensaje'];
    unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
}

include '../../includes/header.php';
?>

<!-- Cargar FullCalendar 6 desde CDN -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<style>
    .calendar-container {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    #calendar {
        max-width: 100%;
        margin: 0 auto;
        height: 550px;
    }

    .fc-day-disabled {
        background-color: rgba(0, 0, 0, 0.05) !important;
        cursor: not-allowed;
    }

    /* Colores MUY vibrantes y sólidos para los estados */
    .fc-day-available {
        background-color: #28a745 !important;
        /* Verde sólido */
    }

    .fc-day-available .fc-daygrid-day-number,
    .fc-day-available .day-capacity-badge {
        color: white !important;
    }

    .fc-day-medium {
        background-color: #ffc107 !important;
        /* Amarillo sólido */
    }

    .fc-day-medium .fc-daygrid-day-number,
    .fc-day-medium .day-capacity-badge {
        color: black !important;
    }

    .fc-day-full {
        background-color: #dc3545 !important;
        /* ROJO FUERTE */
    }

    .fc-day-full .fc-daygrid-day-number,
    .fc-day-full .day-capacity-badge {
        color: white !important;
    }

    /* Resaltar el número de día */
    .fc-daygrid-day-number {
        font-weight: 900 !important;
        color: #212529 !important;
        text-decoration: none !important;
        font-size: 1.2rem;
        padding: 8px !important;
        z-index: 5;
        position: relative;
    }

    .day-capacity-badge {
        font-size: 0.8rem;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: bold;
        display: inline-block;
        margin-left: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        z-index: 5;
        position: relative;
    }

    .capacity-low {
        background-color: #157347;
        color: white;
    }

    .capacity-medium {
        background-color: #e0a800;
        color: black;
    }

    .capacity-full {
        background-color: #a52834;
        color: white;
    }

    /* Bordes bien marcados */
    .fc-theme-standard td,
    .fc-theme-standard th {
        border: 2px solid #6c757d !important;
    }

    .fc-col-header-cell {
        background-color: #343a40 !important;
        padding: 12px 0 !important;
    }

    .fc-col-header-cell-cushion {
        color: #ffffff !important;
        text-transform: uppercase;
        font-weight: 800;
        text-decoration: none !important;
    }

    .fc-day-today {
        border: 4px solid #0d6efd !important;
    }
</style>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="bi bi-calendar-plus"></i> Registrar Nueva Visita</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= PROJECT_PATH ?>/index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= PROJECT_PATH ?>/app/views/visitas/lista.php">Visitas</a></li>
                <li class="breadcrumb-item active">Nueva</li>
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

<form method="POST" action="" id="formVisita">

    <div class="row">
        <!-- Columna Izquierda: Calendario -->
        <div class="col-lg-7">
            <div class="calendar-container mb-4 shadow border">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-calendar3"></i> 1. Seleccione Fecha</h5>
                    <span id="selectedDateDisplay" class="badge bg-secondary p-2 fs-6">Ninguna seleccionada</span>
                </div>
                <div id="calendar"></div>
                <div class="mt-4 p-3 bg-light rounded border">
                    <h6 class="fw-bold mb-2">Guía de colores:</h6>
                    <div class="d-flex gap-4 flex-wrap">
                        <div><span class="badge border"
                                style="background-color: #28a745; width: 15px; height: 15px; display: inline-block; vertical-align: middle;"></span>
                            Disponible</div>
                        <div><span class="badge border"
                                style="background-color: #ffc107; width: 15px; height: 15px; display: inline-block; vertical-align: middle;"></span>
                            Media Ocupación</div>
                        <div><span class="badge border"
                                style="background-color: #dc3545; width: 15px; height: 15px; display: inline-block; vertical-align: middle;"></span>
                            Cupo Lleno</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Datos -->
        <div class="col-lg-5">
            <div class="card mb-4 shadow-sm border-primary">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="bi bi-person-fill"></i> 2. Datos del Paciente
                </div>
                <div class="card-body">
                    <label for="busqueda_paciente" class="form-label fw-bold">Buscar Paciente</label>
                    <input class="form-control form-control-lg border-2" list="datalistOptions" id="busqueda_paciente"
                        placeholder="Nombre o Expediente..."
                        value="<?= $paciente_preseleccionado ? htmlspecialchars($paciente_preseleccionado['nombre'] . ' ' . $paciente_preseleccionado['apellido_paterno']) : '' ?>"
                        <?= $paciente_preseleccionado ? 'readonly' : '' ?> required>
                    <datalist id="datalistOptions">
                        <?php foreach ($pacientes as $paciente): ?>
                            <option data-id="<?= $paciente['id_paciente'] ?>"
                                value="<?= htmlspecialchars($paciente['numero_expediente'] . ' - ' . $paciente['nombre_completo']) ?>">
                            <?php endforeach; ?>
                    </datalist>
                    <input type="hidden" name="id_paciente" id="id_paciente"
                        value="<?= $id_paciente_preseleccionado ?>">
                </div>
            </div>

            <div class="card mb-4 shadow-sm border-info text-dark">
                <div class="card-header bg-info text-white fw-bold">
                    <i class="bi bi-clock-fill"></i> 3. Detalles de Programación
                </div>
                <div class="card-body">
                    <div class="alert alert-danger border-2 fw-bold" id="alertCapacity" style="display:none;">
                        <i class="bi bi-exclamation-octagon-fill"></i> FECHA LLENA (4/4). No se permiten más citas.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha</label>
                            <input type="date" class="form-control bg-light fw-bold" name="fecha_visita"
                                id="input_fecha" required readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Hora</label>
                            <input type="time" class="form-control bg-light fw-bold" name="hora_visita" value="07:00"
                                readonly required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Tipo de Visita</label>
                            <select class="form-select border-2" name="tipo_visita" required>
                                <option value="Seguimiento">Seguimiento</option>
                                <option value="Primera Vez">Primera Vez</option>
                                <option value="Urgencia">Urgencia</option>
                                <option value="Control">Control</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Número de Visita</label>
                            <select class="form-select border-2" name="numero_visita">
                                <option value="">-- Seleccionar --</option>
                                <option value="V1">V1</option>
                                <option value="V2">V2</option>
                                <option value="V3">V3</option>
                                <option value="V4">V4</option>
                                <option value="SEG1">SEG1</option>
                                <option value="SEG2">SEG2</option>
                                <option value="SEG3">SEG3</option>
                                <option value="SEG4">SEG4</option>
                                <option value="SEG5">SEG5</option>
                                <option value="SEG6">SEG6</option>
                                <option value="SEG7">SEG7</option>
                                <option value="SEG8">SEG8</option>
                            </select>
                        </div>
                        <input type="hidden" name="estatus" value="Programada">
                    </div>

                    <div id="dayDetails" class="mt-4 p-3 bg-white border rounded" style="display:none;">
                        <h6 class="fw-bold border-bottom pb-2 text-primary">Pacientes agendados hoy:</h6>
                        <ul id="patientsList" class="list-group list-group-flush mb-0"></ul>
                        <div id="noPatients" class="text-muted small mt-2 italic">Sin pacientes agendados.</div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 mb-5">
                <button type="submit" class="btn btn-success p-3 fs-5 fw-bold shadow-sm" id="btnGuardar" disabled>
                    <i class="bi bi-save-fill"></i> GUARDAR CITA
                </button>
                <a href="lista.php" class="btn btn-outline-secondary">Volver al Listado</a>
            </div>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var capacityData = {};

        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            selectable: true,
            validRange: { start: new Date().toISOString().split('T')[0] },
            dateClick: function (info) { selectDate(info.dateStr); },
            datesSet: function (info) { fetchCounts(info.view.activeStart, info.view.activeEnd); },
            dayCellDidMount: function (arg) { applyCellColor(arg.el, arg.date); }
        });

        calendar.render();

        function fetchCounts(start, end) {
            const startStr = start.toISOString().split('T')[0];
            const endStr = end.toISOString().split('T')[0];

            fetch(`../../ajax/disponibilidad_visitas.php?action=counts&start=${startStr}&end=${endStr}`)
                .then(r => r.json())
                .then(data => {
                    capacityData = {};
                    data.forEach(item => { capacityData[item.fecha] = parseInt(item.total); });
                    updateAllCells();
                });
        }

        function updateAllCells() {
            document.querySelectorAll('.fc-daygrid-day').forEach(cell => {
                const dateStr = cell.getAttribute('data-date');
                if (dateStr) applyCellColor(cell, new Date(dateStr + 'T00:00:00'));
            });
        }

        function applyCellColor(el, date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            const dateStr = `${y}-${m}-${d}`;
            const count = capacityData[dateStr] || 0;

            el.classList.remove('fc-day-available', 'fc-day-medium', 'fc-day-full');

            let badge = el.querySelector('.day-capacity-badge');
            if (count > 0) {
                let cellClass = 'fc-day-available';
                let badgeClass = 'capacity-low';

                if (count >= 4) {
                    cellClass = 'fc-day-full';
                    badgeClass = 'capacity-full';
                } else if (count >= 2) {
                    cellClass = 'fc-day-medium';
                    badgeClass = 'capacity-medium';
                }

                el.classList.add(cellClass);

                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'day-capacity-badge';
                    const top = el.querySelector('.fc-daygrid-day-top');
                    if (top) top.appendChild(badge);
                }
                badge.className = `day-capacity-badge ${badgeClass}`;
                badge.innerText = `${count}/4`;
                badge.style.display = 'inline-block';
            } else if (badge) {
                badge.style.display = 'none';
            }
        }

        function selectDate(dateStr) {
            const inputFecha = document.getElementById('input_fecha');
            const display = document.getElementById('selectedDateDisplay');
            const alertCap = document.getElementById('alertCapacity');
            const btnGuardar = document.getElementById('btnGuardar');
            const list = document.getElementById('patientsList');
            const container = document.getElementById('dayDetails');

            inputFecha.value = dateStr;
            display.innerText = new Date(dateStr + 'T00:00:00').toLocaleDateString('es-MX', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });
            display.classList.remove('bg-secondary');
            display.classList.add('bg-primary');

            alertCap.style.display = 'none';
            btnGuardar.disabled = true;
            list.innerHTML = '';
            container.style.display = 'none';

            fetch(`../../ajax/disponibilidad_visitas.php?action=details&fecha=${dateStr}`)
                .then(r => r.json())
                .then(data => {
                    const noP = document.getElementById('noPatients');
                    list.innerHTML = '';

                    if (data.total > 0) {
                        data.pacientes.forEach(p => {
                            const li = document.createElement('li');
                            li.className = 'list-group-item px-0 border-0 border-bottom';
                            li.innerHTML = `<span class="fw-bold">${p.numero_expediente}</span> - ${p.nombre} ${p.apellido_paterno} <span class="badge bg-light text-dark border">${p.tipo_visita}</span>`;
                            list.appendChild(li);
                        });
                        noP.style.display = 'none';
                    } else {
                        noP.style.display = 'block';
                    }
                    container.style.display = 'block';

                    if (data.total >= 4) {
                        alertCap.style.display = 'block';
                        btnGuardar.disabled = true;
                    } else {
                        alertCap.style.display = 'none';
                        btnGuardar.disabled = false;
                    }

                    // Sincronizar y refrescar visualmente la celda
                    capacityData[dateStr] = data.total;
                    const cell = document.querySelector(`.fc-daygrid-day[data-date="${dateStr}"]`);
                    if (cell) applyCellColor(cell, new Date(dateStr + 'T00:00:00'));
                })
                .catch(err => {
                    console.error("Error fetching details:", err);
                    btnGuardar.disabled = false;
                });
        }

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
    });
</script>

<?php include '../../includes/footer.php'; ?>