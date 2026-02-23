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
        // Intentar buscar por el input de texto si el ID no se envió (browsers a veces no envían value de hidden si no se populate)
        // Por ahora confiamos en la validación básica
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
                $mensaje = 'Visita registrada correctamente.';
                $tipo_mensaje = 'success';
                // Redirigir después de un momento o limpiar formulario
                // header('Location: lista.php'); // Opcional
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

include '../../includes/header.php';
?>

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

<form method="POST" action="">

    <!-- Selección de Paciente -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-person"></i> Datos del Paciente
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <label for="busqueda_paciente" class="form-label">Buscar Paciente (Nombre o Expediente)</label>
                    <input class="form-control" list="datalistOptions" id="busqueda_paciente"
                        placeholder="Escriba para buscar..."
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
                <div class="col-md-4 d-flex align-items-end">
                    <?php if (!$paciente_preseleccionado): ?>
                        <small class="text-muted">Seleccione un paciente de la lista desplegable.</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Programación de la Visita -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-calendar-check"></i> Programación de la Visita
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Fecha</label>
                    <input type="date" class="form-control" name="fecha_visita" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hora</label>
                    <input type="time" class="form-control" name="hora_visita" value="<?= date('H:i') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo de Visita</label>
                    <select class="form-select" name="tipo_visita" required>
                        <option value="Seguimiento">Seguimiento</option>
                        <option value="Primera Vez">Primera Vez</option>
                        <option value="Urgencia">Urgencia</option>
                        <option value="Control">Control</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Número de Visita</label>
                    <select class="form-select" name="numero_visita">
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

                <div class="col-md-4">
                    <label class="form-label">Estatus</label>
                    <select class="form-select" name="estatus">
                        <option value="Completada">Completada</option>
                        <option value="En Curso">En Curso</option>
                        <option value="Programada" selected>Programada</option>
                        <option value="Cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="col-md-12 mt-2">
                    <div class="alert alert-info py-2">
                        <i class="bi bi-info-circle"></i> Esta visita será atendida por el equipo multidisciplinario de
                        CIADI.
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Próxima Cita (Opcional)</label>
                    <input type="date" class="form-control" name="proxima_cita" min="<?= date('Y-m-d') ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-5">
        <a href="lista.php" class="btn btn-secondary me-md-2">Cancelar</a>
        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar Visita</button>
    </div>

</form>

<script>
    // Script sencillo para manejar la selección del datalist y llenar el input hidden
    document.getElementById('busqueda_paciente').addEventListener('input', function (e) {
        var input = e.target;
        var list = input.getAttribute('list');
        var options = document.getElementById(list).childNodes;

        for (var i = 0; i < options.length; i++) {
            if (options[i].innerText === input.value) { // Ojo: datalist value vs display text
                // En standard HTML5 datalist, el value es lo que se muestra.
                // Aquí pusimos value="EXP - Nombre".
                // Necesitamos el ID.
                // Como datalist no soporta data-attributes directamente accesibles fácilmente al seleccionar,
                // Una estrategia común es poner el ID en el value o hacer un lookup inverso.
                // Re-verifiquemos la implementación del datalist arriba.
            }
        }
    });

    // Mejor enfoque para datalist ID mapping:
    // Al cambiar el valor, buscar en las opciones el value coincidente y sacar el data-id.
    document.getElementById('busqueda_paciente').addEventListener('change', function () {
        var val = this.value;
        var options = document.getElementById('datalistOptions').options;
        var idInput = document.getElementById('id_paciente');
        idInput.value = ''; // Reset

        for (var i = 0; i < options.length; i++) {
            if (options[i].value === val) {
                idInput.value = options[i].getAttribute('data-id');
                break;
            }
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>