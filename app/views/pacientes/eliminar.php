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
        header('Location: lista.php?error=error_eliminar&msg=' . urlencode('No se pudo actualizar el estado del paciente en la base de datos.'));
    }
} catch (PDOException $e) {
    header('Location: lista.php?error=error_db&msg=' . urlencode("Error de base de datos: " . $e->getMessage()));
} catch (Exception $e) {
    header('Location: lista.php?error=error_sistema&msg=' . urlencode("Error de sistema: " . $e->getMessage()));
}
exit();
