<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Caso 1: García (Materno del Paciente 1)
    $stmt = $db->query("SELECT apellido_materno, LENGTH(apellido_materno) as len_bytes, CHAR_LENGTH(apellido_materno) as len_chars FROM pacientes WHERE id_paciente = 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Paciente 1 (García): " . $row['apellido_materno'] . " | Bytes: " . $row['len_bytes'] . ", Chars: " . $row['len_chars'] . "\n";

    // Caso 2: Lucía (Nombre del Paciente 4)
    $stmt = $db->query("SELECT nombre, LENGTH(nombre) as len_bytes, CHAR_LENGTH(nombre) as len_chars FROM pacientes WHERE id_paciente = 4");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Paciente 4 (Lucía): " . $row['nombre'] . " | Bytes: " . $row['len_bytes'] . ", Chars: " . $row['len_chars'] . "\n";

    if ($row['len_bytes'] > $row['len_chars']) {
        echo "\nRESULTADO: VERIFICADO. Los caracteres especiales usan multibyte (UTF-8 OK).\n";
    } else {
        echo "\nRESULTADO: ERROR. Los caracteres especiales NO son multibyte.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
