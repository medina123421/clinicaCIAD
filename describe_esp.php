<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();
foreach (['consulta_medicina_interna', 'consulta_nutricion', 'consulta_psicologia', 'estudio_socioeconomico'] as $tabla) {
    echo "=== $tabla ===\n";
    $s = $db->query("DESCRIBE $tabla");
    foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $col) {
        echo "  " . $col['Field'] . "\n";
    }
}
