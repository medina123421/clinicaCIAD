<?php
require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Paciente.php';

$database = new Database();
$db = $database->getConnection();

$pacienteModel = new Paciente($db);
$pacientes = $pacienteModel->obtenerTodos('', 500, 0);

$id_paciente = $_GET['paciente'] ?? '';
$tipo = $_GET['tipo'] ?? 'glucosa';
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-6 months'));
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

$labels = [];
$datasets = [];

if ($id_paciente !== '') {
    if ($tipo === 'glucosa') {
        $sql = "SELECT ag.fecha_analisis,
                       ag.glucosa_ayunas,
                       ag.glucosa_postprandial_2h,
                       ag.hemoglobina_glicosilada
                FROM analisis_glucosa ag
                JOIN visitas v ON ag.id_visita = v.id_visita
                WHERE v.id_paciente = :id_paciente
                  AND ag.fecha_analisis BETWEEN :inicio AND :fin
                ORDER BY ag.fecha_analisis ASC";
    } elseif ($tipo === 'lipidico') {
        $sql = "SELECT apl.fecha_analisis,
                       apl.colesterol_total,
                       apl.ldl,
                       apl.hdl,
                       apl.trigliceridos
                FROM analisis_perfil_lipidico apl
                JOIN visitas v ON apl.id_visita = v.id_visita
                WHERE v.id_paciente = :id_paciente
                  AND apl.fecha_analisis BETWEEN :inicio AND :fin
                ORDER BY apl.fecha_analisis ASC";
    } elseif ($tipo === 'renal') {
        $sql = "SELECT apr.fecha_analisis,
                       apr.creatinina_serica,
                       apr.tasa_filtracion_glomerular,
                       apr.urea,
                       apr.bun
                FROM analisis_perfil_renal apr
                JOIN visitas v ON apr.id_visita = v.id_visita
                WHERE v.id_paciente = :id_paciente
                  AND apr.fecha_analisis BETWEEN :inicio AND :fin
                ORDER BY apr.fecha_analisis ASC";
    } else {
        $sql = null;
    }

    if ($sql) {
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
        $stmt->bindParam(':inicio', $fecha_inicio);
        $stmt->bindParam(':fin', $fecha_fin);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $labels[] = date('d/m/Y', strtotime($row['fecha_analisis']));
        }

        if ($tipo === 'glucosa') {
            $datasets = [
                'Glucosa Ayunas' => array_column($rows, 'glucosa_ayunas'),
                'Glucosa 2h' => array_column($rows, 'glucosa_postprandial_2h'),
                'HbA1c' => array_column($rows, 'hemoglobina_glicosilada'),
            ];
        } elseif ($tipo === 'lipidico') {
            $datasets = [
                'Colesterol Total' => array_column($rows, 'colesterol_total'),
                'LDL' => array_column($rows, 'ldl'),
                'HDL' => array_column($rows, 'hdl'),
                'Triglicéridos' => array_column($rows, 'trigliceridos'),
            ];
        } elseif ($tipo === 'renal') {
            $datasets = [
                'Creatinina' => array_column($rows, 'creatinina_serica'),
                'TFG' => array_column($rows, 'tasa_filtracion_glomerular'),
                'Urea' => array_column($rows, 'urea'),
                'BUN' => array_column($rows, 'bun'),
            ];
        }
    }
}

$page_title = 'Consultas y Gráficas';
include '../../includes/header.php';
?>

<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h2 class="mb-0"><i class="bi bi-graph-up-arrow"></i> Consultas con Filtros y Gráficas</h2>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver a
            reportes</a>
    </div>
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Paciente</label>
                <select name="paciente" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($pacientes as $p): ?>
                        <option value="<?= $p['id_paciente'] ?>" <?= $id_paciente == $p['id_paciente'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['nombre_completo']) ?> (<?= htmlspecialchars($p['numero_expediente']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Tipo de estudio</label>
                <select name="tipo" class="form-select">
                    <option value="glucosa" <?= $tipo === 'glucosa' ? 'selected' : '' ?>>Glucosa / HbA1c</option>
                    <option value="lipidico" <?= $tipo === 'lipidico' ? 'selected' : '' ?>>Perfil lipídico</option>
                    <option value="renal" <?= $tipo === 'renal' ? 'selected' : '' ?>>Perfil renal</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= $fecha_inicio ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= $fecha_fin ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i> Aplicar</button>
            </div>
        </form>
    </div>
</div>

<?php if ($id_paciente && $labels && $datasets): ?>
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Tendencia</h5>
                    <canvas id="mainChart" height="140"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Promedios en rango</h5>
                    <canvas id="summaryChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="card-title mb-3">Detalle de valores</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <?php foreach ($datasets as $name => $values): ?>
                                <th><?= htmlspecialchars($name) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < count($labels); $i++): ?>
                            <tr>
                                <td><?= $labels[$i] ?></td>
                                <?php foreach ($datasets as $values): ?>
                                    <td><?= isset($values[$i]) ? htmlspecialchars($values[$i]) : '' ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const labels = <?= json_encode($labels) ?>;
        const rawDatasets = <?= json_encode($datasets) ?>;

        const colors = [
            '#0d6efd',
            '#198754',
            '#fd7e14',
            '#dc3545',
            '#6f42c1'
        ];

        const ds = Object.keys(rawDatasets).map((name, idx) => ({
            label: name,
            data: rawDatasets[name],
            borderColor: colors[idx % colors.length],
            backgroundColor: colors[idx % colors.length],
            tension: 0.2
        }));

        const ctx = document.getElementById('mainChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: ds
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    x: { ticks: { autoSkip: true, maxTicksLimit: 10 } }
                }
            }
        });

        const summaryLabels = Object.keys(rawDatasets);
        const summaryData = summaryLabels.map(name => {
            const arr = rawDatasets[name].map(Number).filter(v => !isNaN(v));
            if (!arr.length) return 0;
            return arr.reduce((a, b) => a + b, 0) / arr.length;
        });

        const ctx2 = document.getElementById('summaryChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: summaryLabels,
                datasets: [{
                    data: summaryData,
                    backgroundColor: colors.slice(0, summaryLabels.length),
                }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } }
            }
        });
    </script>
<?php elseif ($id_paciente): ?>
    <div class="alert alert-warning shadow-sm">
        <i class="bi bi-exclamation-triangle me-2"></i>No se encontraron datos para el rango y tipo seleccionados.
    </div>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>

