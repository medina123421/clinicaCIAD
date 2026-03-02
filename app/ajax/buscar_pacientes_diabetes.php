<?php
/**
 * AJAX: Buscar Pacientes para Educación en Diabetes
 * Devuelve JSON { success, html } con lista de pacientes y enlace a la última visita
 */

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$search = trim($search);

if (strlen($search) < 2) {
    echo json_encode(['success' => true, 'html' => '']);
    exit();
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    $query = "SELECT 
                p.id_paciente,
                p.nombre,
                p.apellido_paterno,
                p.apellido_materno,
                p.fecha_nacimiento,
                p.numero_expediente,
                (
                    SELECT id_visita 
                    FROM visitas v2 
                    WHERE v2.id_paciente = p.id_paciente 
                    ORDER BY v2.fecha_visita DESC 
                    LIMIT 1
                ) AS id_visita,
                (
                    SELECT fecha_visita 
                    FROM visitas v2 
                    WHERE v2.id_paciente = p.id_paciente 
                    ORDER BY v2.fecha_visita DESC 
                    LIMIT 1
                ) AS fecha_visita
              FROM pacientes p
              WHERE p.activo = 1
                AND (
                    p.nombre LIKE :termino
                    OR p.apellido_paterno LIKE :termino
                    OR p.apellido_materno LIKE :termino
                    OR CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', IFNULL(p.apellido_materno, '')) LIKE :termino
                    OR p.numero_expediente LIKE :termino
                )
              ORDER BY p.apellido_paterno, p.apellido_materno, p.nombre
              LIMIT 10";

    $stmt = $conn->prepare($query);
    $termino_busqueda = '%' . $search . '%';
    $stmt->bindParam(':termino', $termino_busqueda, PDO::PARAM_STR);
    $stmt->execute();

    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $html = '';
    if (count($resultados) > 0) {
        foreach ($resultados as $paciente) {
            $nombre_completo = trim($paciente['nombre'] . ' ' . $paciente['apellido_paterno'] . ' ' . ($paciente['apellido_materno'] ?? ''));
            $edad = $paciente['fecha_nacimiento'] ? date_diff(date_create($paciente['fecha_nacimiento']), date_create('today'))->y : null;

            $html .= '<div class="list-group-item list-group-item-action p-3 border-bottom">';
            $html .= '  <div class="d-flex w-100 justify-content-between align-items-center">';
            $html .= '    <div>';
            $html .= '      <h6 class="mb-1 fw-bold">' . htmlspecialchars($nombre_completo) . '</h6>';
            $html .= '      <small class="text-muted me-2"><i class="bi bi-folder2-open"></i> ' . htmlspecialchars($paciente['numero_expediente']) . '</small>';
            if ($edad !== null) {
                $html .= '      <small class="text-muted"><i class="bi bi-person"></i> ' . (int)$edad . ' años</small>';
            }
            $html .= '    </div>';

            if (!empty($paciente['id_visita'])) {
                $html .= '    <a href="../especialidades/educacion_diabetes.php?id_visita=' . (int)$paciente['id_visita'] . '" class="btn btn-sm btn-outline-warning rounded-pill">';
                $html .= '      Abrir última (' . date('d/m/y', strtotime($paciente['fecha_visita'])) . ') <i class="bi bi-chevron-right"></i>';
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
        'html' => $html,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}