<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../models/Visita.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();
$visita_model = new Visita($db);

$action = $_GET['action'] ?? '';

if ($action === 'counts') {
    $inicio = $_GET['start'] ?? date('Y-m-01');
    $fin = $_GET['end'] ?? date('Y-m-t');

    $counts = $visita_model->obtenerConteosRango($inicio, $fin);
    echo json_encode($counts);
    exit;
}

if ($action === 'details') {
    $fecha = $_GET['fecha'] ?? '';
    if (!$fecha) {
        echo json_encode(['error' => 'Fecha requerida']);
        exit;
    }

    $pacientes = $visita_model->obtenerPacientesPorDia($fecha);
    echo json_encode(['total' => count($pacientes), 'pacientes' => $pacientes]);
    exit;
}

echo json_encode(['error' => 'Acción no válida']);
