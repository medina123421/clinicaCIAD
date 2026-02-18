<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();
// Check ENUM columns in consulta_nutricion
$s = $db->query("SHOW COLUMNS FROM consulta_nutricion WHERE Type LIKE 'enum%'");
foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $col) {
    echo $col['Field'] . ": " . $col['Type'] . "\n";
}
