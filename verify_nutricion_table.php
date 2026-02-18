<?php
require_once 'app/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "DESCRIBE historia_nutricional";
    $stmt = $db->query($query);
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Tabla 'historia_nutricional' existe con " . count($columns) . " columnas.\n";
    echo "Columnas encontradas: " . implode(", ", array_slice($columns, 0, 5)) . "...\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>