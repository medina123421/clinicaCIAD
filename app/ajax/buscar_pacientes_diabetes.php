<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    echo '<div class="alert alert-danger">Sesión no válida</div>';
    exit;
}

$termino = isset($_POST['termino']) ? trim($_POST['termino']) : '';

if (empty($termino)) {
    echo '<div class="alert alert-warning">Ingrese un término de búsqueda</div>';
    exit;
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
                v.id_visita,
                v.fecha_visita,
                v.numero_visita
              FROM pacientes p
              LEFT JOIN visitas v ON p.id_paciente = v.id_paciente
              WHERE (p.nombre LIKE :termino 
                     OR p.apellido_paterno LIKE :termino 
                     OR p.apellido_materno LIKE :termino
                     OR CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) LIKE :termino)
              AND v.id_visita = (
                  SELECT MAX(v2.id_visita) 
                  FROM visitas v2 
                  WHERE v2.id_paciente = p.id_paciente
              )
              ORDER BY p.apellido_paterno, p.apellido_materno, p.nombre
              LIMIT 10";
    
    $stmt = $conn->prepare($query);
    $termino_busqueda = '%' . $termino . '%';
    $stmt->bindParam(':termino', $termino_busqueda);
    $stmt->execute();
    
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($resultados) > 0) {
        echo '<div class="list-group">';
        foreach ($resultados as $paciente) {
            $nombre_completo = $paciente['nombre'] . ' ' . $paciente['apellido_paterno'] . ' ' . $paciente['apellido_materno'];
            $edad = date_diff(date_create($paciente['fecha_nacimiento']), date_create('today'))->y;
            
            echo '<a href="educacion_diabetes.php?id_visita=' . $paciente['id_visita'] . '" class="list-group-item list-group-item-action">';
            echo '<div class="d-flex w-100 justify-content-between">';
            echo '<h6 class="mb-1 text-primary">' . htmlspecialchars($nombre_completo) . '</h6>';
            echo '<small class="text-muted">Edad: ' . $edad . ' años</small>';
            echo '</div>';
            echo '<p class="mb-1"><i class="fas fa-calendar-alt"></i> Última visita: ' . date('d/m/Y', strtotime($paciente['fecha_visita'])) . '</p>';
            echo '<small class="text-muted">Visita #' . $paciente['numero_visita'] . '</small>';
            echo '</a>';
        }
        echo '</div>';
    } else {
        echo '<div class="alert alert-info">No se encontraron pacientes con ese término de búsqueda</div>';
    }
    
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error en la búsqueda: ' . $e->getMessage() . '</div>';
}
?>