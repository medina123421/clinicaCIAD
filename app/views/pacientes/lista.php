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

$pacientes = $paciente_model->obtenerTodos('', 7);

include '../../includes/header.php';
?>

<?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i> Paciente eliminado correctamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i> Error al realizar la operación.
        <?php if (isset($_GET['msg'])): ?>
            <br><small><?= htmlspecialchars($_GET['msg']) ?></small>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

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
                        <th>Protocolo</th>
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
                                    <?php if (($paciente['protocolo'] ?? 'Diabético') === 'Diabético'): ?>
                                        <span class="badge bg-danger">Diabético</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Prediabético</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= date('d/m/Y', strtotime($paciente['fecha_registro'])) ?>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="detalle.php?id=<?= $paciente['id_paciente'] ?>"
                                            class="btn btn-info btn-sm text-white" title="Ver Detalle" data-bs-toggle="tooltip">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="historial.php?id=<?= $paciente['id_paciente'] ?>"
                                            class="btn btn-primary btn-sm" title="Expediente de Paciente"
                                            data-bs-toggle="tooltip">
                                            <i class="bi bi-person-bounding-box"></i>
                                        </a>
                                        <a href="editar.php?id=<?= $paciente['id_paciente'] ?>"
                                            class="btn btn-warning btn-sm text-white" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm btn-eliminar"
                                            data-id="<?= $paciente['id_paciente'] ?>"
                                            data-nombre="<?= htmlspecialchars($paciente['nombre_completo']) ?>"
                                            title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
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

<!-- Modal de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro que desea eliminar al paciente <strong id="deletePacienteNombre"></strong>?
                <p class="text-danger mt-2 small"><i class="bi bi-exclamation-triangle"></i> Esta acción no se puede
                    deshacer de forma sencilla.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmDeleteBtn" class="btn btn-danger">Eliminar Paciente</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Búsqueda AJAX
    $(document).ready(function () {
        const searchDebounce = debounce(function (searchTerm) {
            if (searchTerm.length >= 2 || searchTerm.length === 0) {
                searchAjax(
                    '<?= PROJECT_PATH ?>/app/ajax/buscar_pacientes.php',
                    searchTerm,
                    '#pacientesTable tbody'
                );
            }
        }, 500);

        $('#searchInput').on('keyup', function () {
            searchDebounce($(this).val());
        });

        // Lógica de eliminación (con delegación para AJAX)
        $(document).on('click', '.btn-eliminar', function () {
            const btn = $(this);
            const id = btn.attr('data-id');
            const nombre = btn.attr('data-nombre');

            if (!id) {
                console.error("No se pudo obtener el ID del paciente");
                return;
            }

            $('#deletePacienteNombre').text(nombre);
            $('#confirmDeleteBtn').attr('href', 'eliminar.php?id=' + id);
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>