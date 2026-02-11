<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $sql = "ALTER TABLE estudio_socioeconomico 
            ADD COLUMN IF NOT EXISTS diagnostico_desc_otro TEXT AFTER diagnostico_desc,
            ADD COLUMN IF NOT EXISTS servicio_medico_otro TEXT AFTER servicio_medico,
            ADD COLUMN IF NOT EXISTS tiene_tratamiento BOOLEAN AFTER tratamiento_actual,
            ADD COLUMN IF NOT EXISTS tratamiento_detalle TEXT AFTER tiene_tratamiento";

    $db->exec($sql);
    echo "Database migrated successfully.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>