<?php
/**
 * AJAX: Buscar Pacientes
 * Endpoint para búsqueda en tiempo real
 */

require_once '../config/database.php';
require_once '../models/Paciente.php';

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';

try {
    $database = new Database();
    $db = $database->getConnection();
    $paciente_model = new Paciente($db);

    $pacientes = $paciente_model->obtenerTodos($search);

    $html = '';

    if (count($pacientes) > 0) {
        foreach ($pacientes as $paciente) {
            $sexo_badge = $paciente['sexo'] === 'M'
                ? '<span class="badge bg-primary">Masculino</span>'
                : '<span class="badge bg-pink">Femenino</span>';

            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($paciente['numero_expediente']) . '</td>';
            $html .= '<td><strong>' . htmlspecialchars($paciente['nombre_completo']) . '</strong></td>';
            $html .= '<td>' . $paciente['edad'] . ' años</td>';
            $html .= '<td>' . $sexo_badge . '</td>';
            $html .= '<td>' . htmlspecialchars($paciente['telefono'] ?? 'N/A') . '</td>';
            $html .= '<td>' . htmlspecialchars($paciente['email'] ?? 'N/A') . '</td>';
            $html .= '<td>' . date('d/m/Y', strtotime($paciente['fecha_registro'])) . '</td>';
            $html .= '<td>';
            $html .= '<div class="btn-group btn-group-sm" role="group">';
            $html .= '<a href="detalle.php?id=' . $paciente['id_paciente'] . '" class="btn btn-info" title="Ver Detalle"><i class="bi bi-eye"></i></a>';
            $html .= '<a href="editar.php?id=' . $paciente['id_paciente'] . '" class="btn btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>';
            $html .= '</div>';
            $html .= '</td>';
            $html .= '</tr>';
        }
    } else {
        $html = '<tr><td colspan="8" class="text-center text-muted">No se encontraron resultados</td></tr>';
    }

    echo json_encode([
        'success' => true,
        'html' => $html,
        'count' => count($pacientes)
    ]);

} catch (Exception $e) {
    error_log("Error en búsqueda de pacientes: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error en la búsqueda'
    ]);
}
