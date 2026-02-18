<?php
require_once 'app/config/database.php';
$db = (new Database())->getConnection();

$tablas = ['visitas', 'lab_biometria_hematica', 'lab_quimica_sanguinea', 'lab_examen_orina', 'consulta_medicina_interna'];

foreach ($tablas as $tabla) {
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM $tabla");
        echo "$tabla: " . $stmt->fetchColumn() . "\n";
    } catch (Exception $e) {
        echo "$tabla: Error - " . $e->getMessage() . "\n";
    }
}
