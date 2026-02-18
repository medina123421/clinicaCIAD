<?php
require_once 'app/config/database.php';
try {
    $database = new Database();
    $db = $database->getConnection();
    $sql = file_get_contents('database/psicologia_clinica.sql');
    $db->exec($sql);
    echo "Tabla consulta_psicologia creada exitosamente.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>