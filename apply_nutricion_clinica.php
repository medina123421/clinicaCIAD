<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $sql = file_get_contents('database/nutricion_clinica.sql');

    if ($sql) {
        $db->exec($sql);
        echo "Tabla consulta_nutricion creada exitosamente.";
    } else {
        echo "Error al leer el archivo SQL.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>