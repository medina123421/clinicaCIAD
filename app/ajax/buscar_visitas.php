<?php
/**
 * AJAX: Buscar Visitas
 * Endpoint para búsqueda de visitas en tiempo real
 */

require_once '../config/database.php';
require_once '../models/Visita.php';

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';

try {
    $database = new Database();
    $db = $database->getConnection();
    $visita_model = new Visita($db);

    $visitas = $visita_model->obtenerTodas($search, 7);

    $html = '';

    if (count($visitas) > 0) {
        foreach ($visitas as $visita) {
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

            $html .= '<tr>';
            $html .= '<td>' . date('d/m/Y H:i', strtotime($visita['fecha_visita'])) . '</td>';
            $html .= '<td><strong>' . htmlspecialchars($visita['paciente_nombre']) . '</strong><br>';
            $html .= '<small class="text-muted">' . htmlspecialchars($visita['numero_expediente']) . '</small></td>';
            $html .= '<td>' . htmlspecialchars($visita['doctor_nombre']) . '</td>';
            $html .= '<td><span class="badge bg-secondary">' . htmlspecialchars($visita['tipo_visita']) . '</span></td>';
            $html .= '<td>' . htmlspecialchars(substr($visita['motivo_consulta'], 0, 50)) . (strlen($visita['motivo_consulta']) > 50 ? '...' : '') . '</td>';
            $html .= '<td><span class="badge bg-' . $estatusColor . '">' . htmlspecialchars($visita['estatus']) . '</span></td>';
            $html .= '</tr>';
        }
    } else {
        $html = '<tr><td colspan="6" class="text-center text-muted">No se encontraron visitas que coincidan con la búsqueda.</td></tr>';
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => count($visitas)
    ]);

} catch (Exception $e) {
    error_log("Error en búsqueda de visitas: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en la búsqueda de visitas'
    ]);
}
