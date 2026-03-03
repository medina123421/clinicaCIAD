<?php
require_once 'app/config/database.php';
require_once 'app/models/Visita.php';

$database = new Database();
$db = $database->getConnection();
$visitaModel = new Visita($db);

// Get the latest Programada visit
$query = "SELECT id_visita FROM visitas WHERE estatus = 'Programada' ORDER BY id_visita DESC LIMIT 1";
$stmt = $db->prepare($query);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $id = $row['id_visita'];
    echo "Intentando cancelar visita ID: $id\n";
    if ($visitaModel->actualizarEstatus($id, 'Cancelada')) {
        echo "ÉXITO: Visita cancelada.\n";
    } else {
        echo "FALLO: No se pudo cancelar.\n";
    }
} else {
    echo "No hay visitas programadas para probar.\n";
}
