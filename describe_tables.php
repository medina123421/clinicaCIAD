<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();
foreach (['analisis_perfil_lipidico', 'analisis_perfil_renal', 'analisis_glucosa'] as $tabla) {
    echo "=== $tabla ===\n";
    $s = $db->query("DESCRIBE $tabla");
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "  " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
