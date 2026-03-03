<?php
/**
 * AJAX: Buscar Visitas
 * Endpoint para búsqueda de visitas en tiempo real
 */

require_once '../includes/config.php';
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

            // Obtener estudios pendientes
            $id_paciente = $visita['id_paciente'];
            $estudios_html = '<small class="text-muted">Sin estudios pendientes</small>';

            try {
                if (!isset($medInterna)) {
                    require_once '../models/MedicinaInterna.php';
                    $medInterna = new MedicinaInterna($db);
                }
                $estudios_pendientes = $medInterna->obtenerEstudiosPendientes($id_paciente);
                if (!empty($estudios_pendientes)) {
                    $estudios_html = '<div class="d-flex flex-wrap gap-1">';
                    foreach ($estudios_pendientes as $estudio) {
                        $estudios_html .= '<span class="badge bg-light text-primary border border-primary-subtle" style="font-size: 0.75rem;"><i class="bi bi-file-earmark-medical"></i> ' . htmlspecialchars($estudio) . '</span>';
                    }
                    $estudios_html .= '</div>';
                }
            } catch (Exception $e) {
                $estudios_html = '<small class="text-danger">Error al cargar estudios</small>';
            }

            $html .= '<tr>';
            $html .= '<td>' . date('d/m/Y H:i', strtotime($visita['fecha_visita'])) . '</td>';
            $html .= '<td><strong>' . htmlspecialchars($visita['paciente_nombre']) . '</strong><br>';
            $html .= '<small class="text-muted">' . htmlspecialchars($visita['numero_expediente']) . '</small></td>';
            $html .= '<td>' . htmlspecialchars($visita['tipo_visita']) . '</td>';
            $html .= '<td>' . ($visita['numero_visita'] ?? '-') . '</td>';
            $html .= '<td><span class="badge bg-' . $estatusColor . '">' . htmlspecialchars($visita['estatus']) . '</span></td>';
            $html .= '<td>' . $estudios_html . '</td>';
            $html .= '<td>';
            $html .= '<div class="btn-group btn-group-sm">';
            if ($visita['estatus'] === 'Programada' || $visita['estatus'] === 'En Curso') {
                $html .= '<button type="button" class="btn btn-outline-warning" onclick="abrirModalReagendar(' . $visita['id_visita'] . ', \'' . $visita['fecha_visita'] . '\', \'' . addslashes($visita['paciente_nombre']) . '\')"><i class="bi bi-calendar-event"></i> Reagendar</button>';
                $html .= '<button type="button" class="btn btn-outline-danger" onclick="confirmarCancelacion(' . $visita['id_visita'] . ')"><i class="bi bi-x-circle"></i> Cancelar</button>';
            }
            $html .= '<a href="' . PROJECT_PATH . '/app/views/especialidades/medicina_interna.php?id_visita=' . $visita['id_visita'] . '" class="btn btn-outline-primary"><i class="bi bi-stethoscope"></i> Consulta</a>';
            $html .= '</div>';
            $html .= '</td>';
            $html .= '</tr>';
        }
    } else {
        $html = '<tr><td colspan="7" class="text-center text-muted">No se encontraron visitas que coincidan con la búsqueda.</td></tr>';
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
