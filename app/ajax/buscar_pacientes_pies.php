<?php
/**
 * AJAX: Buscar Pacientes para Cuidado de los Pies
 * Retorna lista de pacientes con enlaces a sus visitas (especialidad Cuidado de los Pies)
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';

if (strlen($search) < 2) {
    echo json_encode(['success' => true, 'html' => '']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT p.id_paciente, p.numero_expediente,
              CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) as nombre_completo,
              (SELECT id_visita FROM visitas WHERE id_paciente = p.id_paciente ORDER BY fecha_visita DESC LIMIT 1) as ultima_visita_id,
              (SELECT fecha_visita FROM visitas WHERE id_paciente = p.id_paciente ORDER BY fecha_visita DESC LIMIT 1) as ultima_visita_fecha
              FROM pacientes p
              WHERE p.activo = 1 AND (
                  p.nombre LIKE :s OR
                  p.apellido_paterno LIKE :s OR
                  p.numero_expediente LIKE :s
              )
              LIMIT 10";

    $stmt = $db->prepare($query);
    $searchTerm = "%$search%";
    $stmt->bindParam(':s', $searchTerm);
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = '';
    if (count($pacientes) > 0) {
        foreach ($pacientes as $p) {
            $html .= '<div class="list-group-item list-group-item-action p-3 border-bottom">';
            $html .= '  <div class="d-flex justify-content-between align-items-center">';
            $html .= '    <div>';
            $html .= '      <h6 class="mb-1 fw-bold">' . htmlspecialchars($p['nombre_completo']) . '</h6>';
            $html .= '      <small class="text-muted">' . htmlspecialchars($p['numero_expediente']) . '</small>';
            $html .= '    </div>';

            if ($p['ultima_visita_id']) {
                $html .= '    <a href="cuidado_pies.php?id_visita=' . (int)$p['ultima_visita_id'] . '" class="btn btn-sm btn-outline-info rounded-pill">';
                $html .= '      Abrir Última (' . date('d/m/y', strtotime($p['ultima_visita_fecha'])) . ') <i class="bi bi-chevron-right"></i>';
                $html .= '    </a>';
            } else {
                $html .= '    <span class="badge bg-light text-muted border">Sin visitas</span>';
            }

            $html .= '  </div>';
            $html .= '</div>';
        }
    } else {
        $html = '<div class="p-4 text-center text-muted"><i class="bi bi-info-circle mb-2 d-block"></i> No se encontraron pacientes</div>';
    }

    echo json_encode([
        'success' => true,
        'html' => $html
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}