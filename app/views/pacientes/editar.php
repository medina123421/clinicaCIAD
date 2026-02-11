<?php
/**
 * Editar Paciente
 * Formulario de edición de paciente
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Paciente.php';

$page_title = 'Editar Paciente';

$database = new Database();
$db = $database->getConnection();
$paciente_model = new Paciente($db);

$id_paciente = $_GET['id'] ?? null;
if (!$id_paciente) {
    header('Location: lista.php');
    exit;
}

$paciente = $paciente_model->obtenerPorId($id_paciente);
if (!$paciente) {
    die('Paciente no encontrado');
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
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
        if ($paciente_model->actualizar($id_paciente, $datos)) {
            $mensaje = 'Paciente actualizado correctamente.';
            $tipo_mensaje = 'success';
            // Recargar datos
            $paciente = $paciente_model->obtenerPorId($id_paciente);
        } else {
            $mensaje = 'Error al actualizar el paciente. Por favor, intente nuevamente.';
            $tipo_mensaje = 'danger';
        }
    } catch (PDOException $e) {
        error_log("Error al actualizar paciente: " . $e->getMessage());
        $mensaje = 'Error al actualizar el paciente.';
        $tipo_mensaje = 'danger';
    }
}

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2><i class="bi bi-pencil-square"></i> Editar Paciente</h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="lista.php">Pacientes</a></li>
                <li class="breadcrumb-item"><a href="detalle.php?id=<?= $id_paciente ?>">Detalle</a></li>
                <li class="breadcrumb-item active">Editar</li>
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
                    <label for="numero_expediente" class="form-label">Número de Expediente</label>
                    <input type="text" class="form-control" id="numero_expediente"
                        value="<?= htmlspecialchars($paciente['numero_expediente']) ?>" readonly disabled>
                </div>

                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre *</label>
                    <input type="text" class="form-control" id="nombre" name="nombre"
                        value="<?= htmlspecialchars($paciente['nombre']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="apellido_paterno" class="form-label">Apellido Paterno *</label>
                    <input type="text" class="form-control" id="apellido_paterno" name="apellido_paterno"
                        value="<?= htmlspecialchars($paciente['apellido_paterno']) ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="apellido_materno" class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" id="apellido_materno" name="apellido_materno"
                        value="<?= htmlspecialchars($paciente['apellido_materno']) ?>">
                </div>

                <div class="col-md-4">
                    <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento *</label>
                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                        value="<?= $paciente['fecha_nacimiento'] ?>" max="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="col-md-4">
                    <label for="sexo" class="form-label">Sexo *</label>
                    <select class="form-select" id="sexo" name="sexo" required>
                        <option value="">Seleccione...</option>
                        <option value="M" <?= $paciente['sexo'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $paciente['sexo'] === 'F' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="tipo_sangre" class="form-label">Tipo de Sangre</label>
                    <select class="form-select" id="tipo_sangre" name="tipo_sangre">
                        <option value="">Seleccione...</option>
                        <?php
                        $tipos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                        foreach ($tipos as $tipo): ?>
                            <option value="<?= $tipo ?>" <?= $paciente['tipo_sangre'] === $tipo ? 'selected' : '' ?>>
                                <?= $tipo ?>
                            </option>
                        <?php endforeach; ?>
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
                    <input type="tel" class="form-control" id="telefono" name="telefono"
                        value="<?= htmlspecialchars($paciente['telefono']) ?>" placeholder="5551234567">
                </div>

                <div class="col-md-4">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?= htmlspecialchars($paciente['email']) ?>" placeholder="paciente@email.com">
                </div>

                <div class="col-md-4">
                    <label for="codigo_postal" class="form-label">Código Postal</label>
                    <input type="text" class="form-control" id="codigo_postal" name="codigo_postal"
                        value="<?= htmlspecialchars($paciente['codigo_postal']) ?>" maxlength="5">
                </div>

                <div class="col-md-12">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="direccion" name="direccion"
                        value="<?= htmlspecialchars($paciente['direccion']) ?>" placeholder="Calle, número, colonia">
                </div>

                <div class="col-md-6">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad"
                        value="<?= htmlspecialchars($paciente['ciudad']) ?>">
                </div>

                <div class="col-md-6">
                    <label for="estado" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="estado" name="estado"
                        value="<?= htmlspecialchars($paciente['estado']) ?>">
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
                        placeholder="Describa las alergias conocidas del paciente..."><?= htmlspecialchars($paciente['alergias']) ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 mb-5">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-save"></i> Guardar Cambios
        </button>
        <a href="detalle.php?id=<?= $id_paciente ?>" class="btn btn-secondary btn-lg">
            <i class="bi bi-x-circle"></i> Cancelar
        </a>
    </div>
</form>

<script>
    // Validación del formulario
    document.getElementById('formPaciente').addEventListener('submit', function (e) {
        const telefono = document.getElementById('telefono').value;
        const email = document.getElementById('email').value;

        // Validaciones básicas si se requieren
        // if (telefono && !isValidPhone(telefono)) { ... }
    });
</script>

<?php include '../../includes/footer.php'; ?>