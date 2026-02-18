<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $sql = file_get_contents('database/nutricion.sql');

    // Split by semicolon to execute multiple statements if needed, 
    // though this file only has one CREATE TABLE.
    $db->exec($sql);

    echo "Tabla historia_nutricional creada correctamente.\n";

} catch (PDOException $e) {
    echo "Error creando tabla: " . $e->getMessage() . "\n";
}
?>