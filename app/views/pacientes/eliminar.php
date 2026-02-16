<?php
/**
 * Accion: Eliminar Paciente (Borrado Lógico)
 */

require_once '../../includes/auth.php';
require_once '../../config/database.php';
require_once '../../models/Paciente.php';

if (!isset($_GET['id'])) {
    header('Location: lista.php');
    exit();
}

try {
    $id = (int) $_GET['id'];
    $database = new Database();
    $db = $database->getConnection();
    $paciente_model = new Paciente($db);

    if ($paciente_model->eliminar($id)) {
        header('Location: lista.php?mensaje=eliminado');
    } else {
        header('Location: lista.php?error=error_eliminar');
    }
} catch (Exception $e) {
    // Debug: Puedes descomentar esto si necesitas ver el error exacto
    // die("Error: " . $e->getMessage());
    header('Location: lista.php?error=error_sistema&msg=' . urlencode($e->getMessage()));
}
exit();
