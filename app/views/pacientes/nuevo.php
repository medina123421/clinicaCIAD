<?php
/**
 * Nuevo Paciente
 * Formulario de registro de paciente
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Paciente.php';

$page_title = 'Nuevo Paciente';

$database = new Database();
$db = $database->getConnection();
$paciente_model = new Paciente($db);

// Generar número de expediente
$numero_expediente = $paciente_model->generarNumeroExpediente();

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'numero_expediente' => $_POST['numero_expediente'],
        'nombre' => trim($_POST['nombre']),
        'apellido_paterno' => trim($_POST['apellido_paterno']),
        'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
        'fecha_nacimiento' => $_POST['fecha_nacimiento'],
        'sexo' => $_POST['sexo'],
        'telefono' => trim($_POST['telefono'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'direccion' => trim($_POST['direccion'] ?? ''),
        'ciudad' => trim($_POST['ciudad'] ?? ''),
        'estado' => trim($_POST['estado'] ?? ''),
        'codigo_postal' => trim($_POST['codigo_postal'] ?? ''),
        'tipo_sangre' => $_POST['tipo_sangre'] ?? null,
        'alergias' => trim($_POST['alergias'] ?? '')
    ];

    try {
        $id_paciente = $paciente_model->crear($datos, $usuario_id);

        if ($id_paciente) {
            header('Location: detalle.php?id=' . $id_paciente . '&mensaje=creado');
            exit();
        } else {
            $mensaje = 'Error al crear el paciente. Por favor, intente nuevamente.';
            $tipo_mensaje = 'danger';
        }
    } catch (PDOException $e) {
        error_log("Error al crear paciente: " . $e->getMessage());
        $mensaje = 'Error al crear el paciente. Verifique que el expediente no esté duplicado.';
        $tipo_mensaje = 'danger';
    }
}

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="bi bi-person-plus"></i> Nuevo Paciente</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php">Pacientes</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
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

<form method="POST" action="" id="formPaciente">
    <div class="card">
        <div class="card-header">
            <i class="bi bi-person-vcard"></i> Datos Personales
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="numero_expediente" class="form-label">Número de Expediente *</label>
                    <input type="text" class="form-control" id="numero_expediente" name="numero_expediente"
                        value="<?= htmlspecialchars($numero_expediente) ?>" readonly required>
                </div>

                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>

                <div class="col-md-4">
                    <label for="apellido_paterno" class="form-label">Apellido Paterno *</label>
                    <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno" required>
                </div>

                <div class="col-md-4">
                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" id="apellido_materno" name="apellido_materno">
                </div>

                <div class="col-md-4">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                        max="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="sexo" class="form-label">Sexo *</label>
                    <select class="form-select" id="sexo" name="sexo" required>
                        <option value="">Seleccione...</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="tipo_sangre" class="form-label">Tipo de Sangre</label>
                    <select class="form-select" id="tipo_sangre" name="tipo_sangre">
                        <option value="">Seleccione...</option>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <i class="bi bi-telephone"></i> Información de Contacto
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono" placeholder="5551234567">
                </div>

                <div class="col-md-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="paciente@email.com">
                </div>

                <div class="col-md-4">
                    <label for="codigo_postal" class="form-label">Código Postal</label>
                    <input type="text" class="form-control" id="codigo_postal" name="codigo_postal" maxlength="5">
                </div>

                <div class="col-md-12">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion"
                        placeholder="Calle, número, colonia">
                </div>

                <div class="col-md-6">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad">
                </div>

                <div class="col-md-6">
                    <label for="estado" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="estado" name="estado">
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <i class="bi bi-clipboard2-pulse"></i> Información Médica
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-12">
                    <label for="alergias" class="form-label">Alergias Conocidas</label>
                    <textarea class="form-control" id="alergias" name="alergias" rows="3"
                        placeholder="Describa las alergias conocidas del paciente..."></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 mb-5">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-save"></i> Guardar Paciente
        </button>
        <a href="lista.php" class="btn btn-secondary btn-lg">
            <i class="bi bi-x-circle"></i> Cancelar
        </a>
    </div>
</form>

<script>
    // Validación del formulario
    document.getElementById('formPaciente').addEventListener('submit', function (e) {
        const telefono = document.getElementById('telefono').value;
        const email = document.getElementById('email').value;

        if (telefono && !isValidPhone(telefono)) {
            e.preventDefault();
            showAlert('El teléfono debe tener 10 dígitos', 'danger');
            return false;
        }

        if (email && !isValidEmail(email)) {
            e.preventDefault();
            showAlert('El email no es válido', 'danger');
            return false;
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>