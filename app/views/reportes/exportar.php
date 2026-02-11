<?php
/**
 * Script de Exportación (Excel/Word)
 */

require_once '../../config/database.php';
require_once '../../models/Analisis.php';

$database = new Database();
$db = $database->getConnection();
$analisis_model = new Analisis($db);

$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
$tipo = $_GET['tipo'] ?? 'excel';

$reportes = $analisis_model->obtenerReporteGeneral($fecha_inicio, $fecha_fin);

// Configurar Headers para descarga
if ($tipo === 'excel') {
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=reporte_laboratorio_" . date('Ymd') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
} elseif ($tipo === 'word') {
    header("Content-Type: application/vnd.ms-word; charset=utf-8");
    header("Content-Disposition: attachment; filename=reporte_laboratorio_" . date('Ymd') . ".doc");
    header("Pragma: no-cache");
    header("Expires: 0");
} else {
    die("Tipo de exportación no soportado.");
}

// Salida en formato HTML Table simple (Excel/Word lo interpretan)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Laboratorio</title>
    <style>
        body { font-family: Arial, sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h2>Reporte de Análisis Clínicos</h2>
    <p><strong>Periodo:</strong> <?= $fecha_inicio ?> al <?= $fecha_fin ?></p>
    
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Paciente</th>
                <th>Expediente</th>
                <th>Biometría Hemática</th>
                <th>Química Sanguínea</th>
                <th>Examen Orina</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reportes as $fila): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($fila['fecha_visita'])) ?></td>
                    <td><?= htmlspecialchars($fila['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($fila['numero_expediente']) ?></td>
                    <td><?= $fila['id_biometria'] ? 'Sí' : 'No' ?></td>
                    <td><?= $fila['id_quimica'] ? 'Sí' : 'No' ?></td>
                    <td><?= $fila['id_orina'] ? 'Sí' : 'No' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
