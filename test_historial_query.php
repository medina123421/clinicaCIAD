<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();

$id_paciente = 1; // Assuming patient 1 exists from previous steps

// The modified query from historial.php
$query_lab = "SELECT v.fecha_visita, v.id_visita,
              lbh.id_biometria, lqs.id_quimica, leo.id_orina, lph.id_hepatico, lpt.id_tiroideo, li.id_insulina,
              ag.id_analisis as id_glucosa_lab, apr.id_analisis as id_perfil_renal, apl.id_analisis as id_perfil_lipidico
              FROM visitas v
              LEFT JOIN lab_biometria_hematica lbh ON v.id_visita = lbh.id_visita
              LEFT JOIN lab_quimica_sanguinea lqs ON v.id_visita = lqs.id_visita
              LEFT JOIN lab_examen_orina leo ON v.id_visita = leo.id_visita
              LEFT JOIN lab_perfil_hepatico lph ON v.id_visita = lph.id_visita
              LEFT JOIN lab_perfil_tiroideo lpt ON v.id_visita = lpt.id_visita
              LEFT JOIN lab_insulina li ON v.id_visita = li.id_visita
              LEFT JOIN analisis_glucosa ag ON v.id_visita = ag.id_visita
              LEFT JOIN analisis_perfil_renal apr ON v.id_visita = apr.id_visita
              LEFT JOIN analisis_perfil_lipidico apl ON v.id_visita = apl.id_visita
              WHERE v.id_paciente = :id
              AND (lbh.id_biometria IS NOT NULL OR lqs.id_quimica IS NOT NULL OR leo.id_orina IS NOT NULL 
                   OR lph.id_hepatico IS NOT NULL OR lpt.id_tiroideo IS NOT NULL OR li.id_insulina IS NOT NULL
                   OR ag.id_analisis IS NOT NULL OR apr.id_analisis IS NOT NULL OR apl.id_analisis IS NOT NULL)
              ORDER BY v.fecha_visita DESC";

try {
    $stmt_lab = $db->prepare($query_lab);
    $stmt_lab->bindParam(':id', $id_paciente);
    $stmt_lab->execute();
    $results = $stmt_lab->fetchAll(PDO::FETCH_ASSOC);
    echo "Query executed successfully. Found " . count($results) . " records.\n";
    // echo "First record: \n";
    // print_r($results[0] ?? 'No records');
} catch (PDOException $e) {
    echo "Query FAILED: " . $e->getMessage();
}
