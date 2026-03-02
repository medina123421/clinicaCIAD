<?php
require_once 'app/config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // 1. Identificar duplicados (mismo paciente, misma fecha)
    // Mantendremos la visita con el ID más bajo y borraremos las demás.
    $query = "SELECT DATE(fecha_visita) as fecha, id_paciente, MIN(id_visita) as min_id
              FROM visitas 
              GROUP BY fecha, id_paciente 
              HAVING COUNT(*) > 1";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Encontrados " . count($duplicates) . " grupos de duplicados.\n";

    foreach ($duplicates as $dup) {
        $fecha = $dup['fecha'];
        $id_paciente = $dup['id_paciente'];
        $min_id = $dup['min_id'];

        $delete = "DELETE FROM visitas 
                   WHERE DATE(fecha_visita) = :fecha 
                   AND id_paciente = :id_paciente 
                   AND id_visita > :min_id";

        $del_stmt = $db->prepare($delete);
        $del_stmt->bindParam(':fecha', $fecha);
        $del_stmt->bindParam(':id_paciente', $id_paciente);
        $del_stmt->bindParam(':min_id', $min_id);
        $del_stmt->execute();

        echo "Borrados duplicados para paciente $id_paciente en fecha $fecha (conservado ID $min_id).\n";
    }

    echo "Limpieza completada.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
