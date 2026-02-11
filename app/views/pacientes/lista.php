<?php
/**
 * Lista de Pacientes
 * Vista con búsqueda AJAX y paginación
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Paciente.php';

$page_title = 'Pacientes';

$database = new Database();
$db = $database->getConnection();
$paciente_model = new Paciente($db);

// Obtener pacientes
$pacientes = $paciente_model->obtenerTodos();

include '../../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-people-fill"></i> Pacientes</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="nuevo.php" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Nuevo Paciente
        </a>
    </div>
</div>

<!-- Búsqueda -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" class="form-control" id="searchInput"
                placeholder="Buscar por expediente, nombre o email...">
        </div>
    </div>
</div>

<!-- Tabla de pacientes -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="pacientesTable">
                <thead>
                    <tr>
                        <th>Expediente</th>
                        <th>Nombre Completo</th>
                        <th>Edad</th>
                        <th>Sexo</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pacientes) > 0): ?>
                        <?php foreach ($pacientes as $paciente): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($paciente['numero_expediente']) ?>
                                </td>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars($paciente['nombre_completo']) ?>
                                    </strong>
                                </td>
                                <td>
                                    <?= $paciente['edad'] ?> años
                                </td>
                                <td>
                                    <?php if ($paciente['sexo'] === 'M'): ?>
                                        <span class="badge bg-primary">Masculino</span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: #e83e8c; color: white;">Femenino</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($paciente['telefono'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($paciente['email'] ?? 'N/A') ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($paciente['fecha_registro'])) ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="detalle.php?id=<?= $paciente['id_paciente'] ?>" class="btn btn-info"
                                            title="Ver Detalle">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="editar.php?id=<?= $paciente['id_paciente'] ?>" class="btn btn-warning"
                                            title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No hay pacientes registrados.
                                <a href="nuevo.php">Registrar el primero</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Búsqueda AJAX
    $(document).ready(function () {
        const searchDebounce = debounce(function (searchTerm) {
            if (searchTerm.length >= 2 || searchTerm.length === 0) {
                searchAjax(
                    '/app/ajax/buscar_pacientes.php',
                    searchTerm,
                    '#pacientesTable tbody'
                );
            }
        }, 500);

        $('#searchInput').on('keyup', function () {
            searchDebounce($(this).val());
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>