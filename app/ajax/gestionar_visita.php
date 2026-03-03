<?php
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../models/Visita.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$action = $_POST['accion'] ?? '';
$id_visita = isset($_POST['id_visita']) ? (int) $_POST['id_visita'] : 0;

if (!$id_visita) {
    echo json_encode(['success' => false, 'message' => 'ID de visita no proporcionado']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$visitaModel = new Visita($db);

ob_start(); // Prevenir cualquier salida accidental antes del JSON
try {
    error_log("gestionar_visita.php - Accion: $action, ID: $id_visita");

    if ($action === 'cancelar') {
        if ($visitaModel->actualizarEstatus($id_visita, 'Cancelada')) {
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Cita cancelada correctamente']);
        } else {
            error_log("gestionar_visita.php - Error al actualizar estatus a Cancelada para ID: $id_visita");
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'No se pudo cancelar la cita. Verifique que exista y esté activa.']);
        }
    } elseif ($action === 'reagendar') {
        $nueva_fecha = $_POST['nueva_fecha'] ?? '';
        if (empty($nueva_fecha)) {
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Nueva fecha no proporcionada']);
            exit();
        }

        if ($visitaModel->reagendar($id_visita, $nueva_fecha)) {
            ob_clean();
            echo json_encode(['success' => true, 'message' => 'Cita reagendada correctamente']);
        } else {
            error_log("gestionar_visita.php - Error al reagendar cita ID: $id_visita");
            ob_clean();
            echo json_encode(['success' => false, 'message' => 'Error al reagendar la cita']);
        }
    } else {
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    error_log("gestionar_visita.php - Exception: " . $e->getMessage());
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
ob_end_flush();
